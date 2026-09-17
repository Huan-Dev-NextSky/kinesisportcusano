<?php
/**
 * AwwApiMigration - Automatically ensures necessary database tables and columns exist
 */

class AwwApiMigration {
    public static function run($conn) {
        if (!$conn) return false;

        // 1. Add external_service_id and duration to ct_services if not present
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM `ct_services` LIKE 'external_service_id'");
        if ($colCheck && mysqli_num_rows($colCheck) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_services` ADD COLUMN `external_service_id` INT(11) NULL DEFAULT NULL AFTER `id`, ADD INDEX (`external_service_id`)");
        }

        $durCheck = mysqli_query($conn, "SHOW COLUMNS FROM `ct_services` LIKE 'duration'");
        if ($durCheck && mysqli_num_rows($durCheck) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_services` ADD COLUMN `duration` INT(11) NOT NULL DEFAULT '60' AFTER `description`");
        }

        $priceCheck = mysqli_query($conn, "SHOW COLUMNS FROM `ct_services` LIKE 'price'");
        if ($priceCheck && mysqli_num_rows($priceCheck) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_services` ADD COLUMN `price` DOUBLE NOT NULL DEFAULT '0' AFTER `image`");
        }

        // Add kinesis columns to ct_bookings
        $bkCol1 = mysqli_query($conn, "SHOW COLUMNS FROM `ct_bookings` LIKE 'kinesis_appointment_id'");
        if ($bkCol1 && mysqli_num_rows($bkCol1) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_bookings` ADD COLUMN `kinesis_appointment_id` INT(11) NULL DEFAULT NULL, ADD COLUMN `kinesis_customer_id` INT(11) NULL DEFAULT NULL, ADD COLUMN `kinesis_sync_status` VARCHAR(50) DEFAULT 'PENDING', ADD COLUMN `kinesis_sync_time` DATETIME NULL, ADD INDEX (`kinesis_appointment_id`), ADD INDEX (`kinesis_sync_status`)");
        }

        // Add cancellation & reschedule request columns to ct_bookings (for Phase 4 customer self-service)
        $bkColReq = mysqli_query($conn, "SHOW COLUMNS FROM `ct_bookings` LIKE 'change_request_status'");
        if ($bkColReq && mysqli_num_rows($bkColReq) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_bookings` ADD COLUMN `change_request_status` VARCHAR(50) NOT NULL DEFAULT 'NONE', ADD COLUMN `cancel_reason` TEXT NULL DEFAULT NULL, ADD COLUMN `reschedule_reason` TEXT NULL DEFAULT NULL, ADD COLUMN `reschedule_requested_date` VARCHAR(50) NULL DEFAULT NULL, ADD INDEX (`change_request_status`)");
        }

        // Add external_employee_id to ct_admin_info (for Doctor role mapping with API Professionisti)
        $staffCol = mysqli_query($conn, "SHOW COLUMNS FROM `ct_admin_info` LIKE 'external_employee_id'");
        if ($staffCol && mysqli_num_rows($staffCol) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_admin_info` ADD COLUMN `external_employee_id` INT(11) NULL DEFAULT NULL AFTER `role`, ADD INDEX (`external_employee_id`)");
        }

        // Migrate all staff roles to doctor (System uses Doctor role only)
        @mysqli_query($conn, "UPDATE `ct_admin_info` SET `role` = 'doctor' WHERE `role` = 'staff'");

        // Add external_customer_id, dob, sms_opt_in to ct_users (separate checks — older DBs may have only external_customer_id)
        $userCol1 = mysqli_query($conn, "SHOW COLUMNS FROM `ct_users` LIKE 'external_customer_id'");
        if ($userCol1 && mysqli_num_rows($userCol1) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_users` ADD COLUMN `external_customer_id` INT(11) NULL DEFAULT NULL AFTER `id`, ADD INDEX (`external_customer_id`)");
        }
        $userDob = mysqli_query($conn, "SHOW COLUMNS FROM `ct_users` LIKE 'dob'");
        if ($userDob && mysqli_num_rows($userDob) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_users` ADD COLUMN `dob` VARCHAR(50) NULL DEFAULT NULL");
        }
        $userSms = mysqli_query($conn, "SHOW COLUMNS FROM `ct_users` LIKE 'sms_opt_in'");
        if ($userSms && mysqli_num_rows($userSms) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_users` ADD COLUMN `sms_opt_in` VARCHAR(10) NOT NULL DEFAULT 'Y'");
        }

        // 2. Create ct_gcal_kinesis_sync table for tracking sync events
        $createSyncTable = "CREATE TABLE IF NOT EXISTS `ct_gcal_kinesis_sync` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `google_event_id` VARCHAR(255) DEFAULT NULL,
            `local_order_id` INT(11) DEFAULT NULL,
            `local_booking_id` INT(11) DEFAULT NULL,
            `kinesis_customer_id` INT(11) DEFAULT NULL,
            `kinesis_appointment_id` INT(11) DEFAULT NULL,
            `event_summary` VARCHAR(500) DEFAULT NULL,
            `event_start` VARCHAR(50) DEFAULT NULL,
            `event_end` VARCHAR(50) DEFAULT NULL,
            `customer_email` VARCHAR(255) DEFAULT NULL,
            `customer_phone` VARCHAR(100) DEFAULT NULL,
            `customer_name` VARCHAR(255) DEFAULT NULL,
            `customer_dob` VARCHAR(50) DEFAULT NULL,
            `service_id` INT(11) DEFAULT NULL,
            `employee_id` INT(11) DEFAULT NULL,
            `sync_status` VARCHAR(50) DEFAULT 'PENDING',
            `sync_action` VARCHAR(50) DEFAULT 'CREATE',
            `last_sync_message` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_google_event_id` (`google_event_id`),
            KEY `idx_local_order_id` (`local_order_id`),
            KEY `idx_kinesis_appointment_id` (`kinesis_appointment_id`),
            KEY `idx_customer_email` (`customer_email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        @mysqli_query($conn, $createSyncTable);

        // Add local_order_id and local_booking_id if table was already created without them
        $colOrderCheck = mysqli_query($conn, "SHOW COLUMNS FROM `ct_gcal_kinesis_sync` LIKE 'local_order_id'");
        if ($colOrderCheck && mysqli_num_rows($colOrderCheck) === 0) {
            @mysqli_query($conn, "ALTER TABLE `ct_gcal_kinesis_sync` ADD COLUMN `local_order_id` INT(11) NULL DEFAULT NULL AFTER `google_event_id`, ADD COLUMN `local_booking_id` INT(11) NULL DEFAULT NULL AFTER `local_order_id`, ADD INDEX (`local_order_id`)");
        }

        // 3. Ensure default settings exist in ct_settings
        $defaultSettings = array(
            'kinesis_api_status' => 'N',
            'kinesis_api_env' => 'sandbox',
            'kinesis_api_username' => '',
            'kinesis_api_password' => '',
            'kinesis_sync_interval' => '5',
            'kinesis_api_access_token' => '',
            'kinesis_api_refresh_token' => '',
            'kinesis_api_token_expires_at' => '0',
            'kinesis_last_sync_time' => '',
            'kinesis_last_sync_status' => '',
            'gcal_last_sync_time' => '',
            'gcal_last_sync_status' => '',
            'ct_gc_sync_direction' => 'two_way',
            'ct_allow_customer_cancel' => 'Y',
            'ct_allow_customer_reschedule' => 'Y',
            'ct_allow_manual_booking' => 'Y',
            'ct_cron_secret' => ''
        );

        foreach ($defaultSettings as $name => $val) {
            $check = mysqli_query($conn, "SELECT `id` FROM `ct_settings` WHERE `option_name` = '" . mysqli_real_escape_string($conn, $name) . "'");
            if ($check && mysqli_num_rows($check) === 0) {
                @mysqli_query($conn, "INSERT INTO `ct_settings` (`id`, `option_name`, `option_value`, `postalcode`) VALUES (NULL, '" . mysqli_real_escape_string($conn, $name) . "', '" . mysqli_real_escape_string($conn, $val) . "', '')");
            }
        }

        return true;
    }
}
