<?php
/**
 * AwwAppointmentSync - STEP 2: Syncs Reservations from Local System (Cleanto DB) to Kinesis REST API
 * 
 * Flow:
 * Local System (ct_bookings, ct_order_client_info, ct_users) -> Kinesis API (/api/v1/public/appointments)
 * 
 * This ensures the Local System is the Single Source of Truth for all appointments,
 * whether they originated from Google Calendar, the Frontend Booking Widget, or Admin manual entries.
 */

require_once dirname(__FILE__) . '/AwwApiClient.php';
require_once dirname(__FILE__) . '/AwwApiMigration.php';

class AwwAppointmentSync {
    private $conn;
    private $setting;
    private $apiClient;

    public function __construct($conn) {
        $this->conn = $conn;
        require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
        $this->setting = new cleanto_setting();
        $this->setting->conn = $this->conn;

        $this->apiClient = new AwwApiClient($this->conn);
    }

    /**
     * Batch sync all pending bookings from Local System to Kinesis API
     * 
     * @param int $limit Max records to process per run
     * @return array Summary of synced bookings
     */
    public function syncAllPending($limit = 100) {
        AwwApiMigration::run($this->conn);

        $kinesisStatus = $this->setting->get_option('kinesis_api_status');
        if ($kinesisStatus !== 'Y') {
            $msg = 'Kinesis API integration is disabled in Settings.';
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'failed' => 0,
                'total' => 0
            );
        }

        // Test API login
        $loginRes = $this->apiClient->login();
        if (!$loginRes['success']) {
            $msg = 'Kinesis API Login failed: ' . (isset($loginRes['error']) ? $loginRes['error'] : 'Invalid credentials');
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'failed' => 0,
                'total' => 0
            );
        }

        // Find all bookings in Local System requiring Kinesis sync
        $query = "SELECT 
                    b.id as booking_id,
                    b.order_id,
                    b.client_id,
                    b.booking_date_time,
                    b.booking_status,
                    b.service_id,
                    b.staff_ids,
                    b.gc_event_id,
                    b.kinesis_appointment_id,
                    b.kinesis_customer_id,
                    b.kinesis_sync_status,
                    s.title as service_title,
                    s.external_service_id,
                    s.duration,
                    oci.client_name,
                    oci.client_email,
                    oci.client_phone,
                    oci.client_personal_info,
                    u.first_name,
                    u.last_name,
                    u.user_email,
                    u.phone as user_phone
                  FROM ct_bookings b
                  LEFT JOIN ct_services s ON b.service_id = s.id
                  LEFT JOIN ct_order_client_info oci ON b.order_id = oci.order_id
                  LEFT JOIN ct_users u ON b.client_id = u.id
                  WHERE (
                    (b.kinesis_appointment_id IS NULL OR b.kinesis_appointment_id = 0)
                    AND IFNULL(b.kinesis_sync_status, 'PENDING') = 'PENDING'
                    AND b.booking_status NOT IN ('CC', 'CS', 'R')
                  )
                  OR (
                    b.kinesis_sync_status = 'UPDATE_PENDING'
                    AND b.kinesis_appointment_id > 0
                  )
                  OR (
                    (b.kinesis_sync_status = 'CANCEL_PENDING' OR b.booking_status IN ('CC', 'CS', 'R'))
                    AND b.kinesis_appointment_id > 0
                    AND IFNULL(b.kinesis_sync_status, '') != 'CANCELLED'
                  )
                  GROUP BY b.order_id
                  ORDER BY b.id ASC
                  LIMIT " . (int)$limit;

        $res = mysqli_query($this->conn, $query);
        if (!$res) {
            $msg = 'Database error fetching local bookings: ' . mysqli_error($this->conn);
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'failed' => 0,
                'total' => 0
            );
        }

        $createdCount = 0;
        $updatedCount = 0;
        $cancelledCount = 0;
        $failedCount = 0;
        $errors = array();

        while ($row = mysqli_fetch_assoc($res)) {
            $syncRes = $this->processLocalBooking($row);
            if ($syncRes['success']) {
                if ($syncRes['action'] === 'CREATE') {
                    $createdCount++;
                } elseif ($syncRes['action'] === 'UPDATE') {
                    $updatedCount++;
                } elseif ($syncRes['action'] === 'CANCEL') {
                    $cancelledCount++;
                }
            } else {
                $failedCount++;
                $errors[] = "Order #{$row['order_id']}: " . $syncRes['error'];
            }
        }

        $totalProcessed = $createdCount + $updatedCount + $cancelledCount + $failedCount;
        $summaryMsg = sprintf(
            'System -> Kinesis API sync completed: %d booking(s) processed. Created on API: %d, Updated: %d, Cancelled: %d, Failed: %d.',
            $totalProcessed,
            $createdCount,
            $updatedCount,
            $cancelledCount,
            $failedCount
        );

        $this->updateSyncStatus(true, $summaryMsg);

        return array(
            'success' => true,
            'message' => $summaryMsg,
            'total' => $totalProcessed,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'cancelled' => $cancelledCount,
            'failed' => $failedCount,
            'errors' => $errors
        );
    }

    /**
     * Sync a single booking from Local System to Kinesis API by order_id
     * 
     * @param int $orderId
     * @return array
     */
    public function syncSingleBooking($orderId) {
        AwwApiMigration::run($this->conn);

        $kinesisStatus = $this->setting->get_option('kinesis_api_status');
        if ($kinesisStatus !== 'Y') {
            return array('success' => false, 'error' => 'Kinesis API is disabled in settings.');
        }

        $orderEsc = (int)$orderId;
        $query = "SELECT 
                    b.id as booking_id,
                    b.order_id,
                    b.client_id,
                    b.booking_date_time,
                    b.booking_status,
                    b.service_id,
                    b.staff_ids,
                    b.gc_event_id,
                    b.kinesis_appointment_id,
                    b.kinesis_customer_id,
                    b.kinesis_sync_status,
                    s.title as service_title,
                    s.external_service_id,
                    s.duration,
                    oci.client_name,
                    oci.client_email,
                    oci.client_phone,
                    oci.client_personal_info,
                    u.first_name,
                    u.last_name,
                    u.user_email,
                    u.phone as user_phone
                  FROM ct_bookings b
                  LEFT JOIN ct_services s ON b.service_id = s.id
                  LEFT JOIN ct_order_client_info oci ON b.order_id = oci.order_id
                  LEFT JOIN ct_users u ON b.client_id = u.id
                  WHERE b.order_id = '{$orderEsc}'
                  LIMIT 1";

        $res = mysqli_query($this->conn, $query);
        if (!$res || mysqli_num_rows($res) === 0) {
            return array('success' => false, 'error' => 'Booking not found.');
        }

        $row = mysqli_fetch_assoc($res);
        return $this->processLocalBooking($row);
    }

    /**
     * Process an individual booking record to Kinesis API
     */
    private function processLocalBooking($row) {
        $orderId = (int)$row['order_id'];
        $bookingId = (int)$row['booking_id'];
        $currentTime = date('Y-m-d H:i:s');

        // Check if this is a cancellation
        $isCancelled = in_array($row['booking_status'], array('CC', 'CS', 'R')) || $row['kinesis_sync_status'] === 'CANCEL_PENDING';
        if ($isCancelled) {
            if (!empty($row['kinesis_appointment_id'])) {
                if (empty($row['kinesis_customer_id'])) {
                    mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCEL_PENDING', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
                    return array('success' => false, 'action' => 'CANCEL', 'error' => 'Missing kinesis_customer_id; cannot cancel remote appointment.');
                }

                $cancelRes = $this->apiClient->cancelAppointment((int)$row['kinesis_customer_id'], (int)$row['kinesis_appointment_id']);

                if (!empty($cancelRes['success'])) {
                    mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCELLED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
                    mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'CANCELLED', `sync_action` = 'CANCEL', `last_sync_message` = 'Cancelled on Kinesis API', `updated_at` = '{$currentTime}' WHERE `local_order_id` = '{$orderId}'");
                    return array('success' => true, 'action' => 'CANCEL', 'appointmentId' => $row['kinesis_appointment_id']);
                }

                $errMsg = isset($cancelRes['error']) ? $cancelRes['error'] : 'Failed to cancel appointment on Kinesis API';
                mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCEL_PENDING', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
                return array('success' => false, 'action' => 'CANCEL', 'error' => $errMsg);
            }
            mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCELLED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
            return array('success' => true, 'action' => 'CANCEL', 'note' => 'No active Kinesis appointment to cancel.');
        }

        // Stuck UPDATE_PENDING without remote ids — never fall through to CREATE
        if ($row['kinesis_sync_status'] === 'UPDATE_PENDING') {
            if (empty($row['kinesis_appointment_id']) || empty($row['kinesis_customer_id'])) {
                return array('success' => false, 'action' => 'UPDATE', 'error' => 'UPDATE_PENDING but missing Kinesis appointment/customer ids; refused CREATE fallback.');
            }
            $isoDateTime = date('c', strtotime($row['booking_date_time']));
            $employeeId = $this->resolveExternalEmployeeId($row['staff_ids']);
            if ($employeeId === null) {
                return array('success' => false, 'action' => 'UPDATE', 'error' => 'Missing external_employee_id for assigned doctor; refuse using local staff id.');
            }

            $patchRes = $this->apiClient->updateAppointment(
                (int)$row['kinesis_customer_id'],
                (int)$row['kinesis_appointment_id'],
                $isoDateTime,
                $employeeId
            );

            if ($patchRes['success']) {
                mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'SYNCED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
                mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'SYNCED', `sync_action` = 'UPDATE', `last_sync_message` = 'Updated on Kinesis API', `updated_at` = '{$currentTime}' WHERE `local_order_id` = '{$orderId}'");
                return array('success' => true, 'action' => 'UPDATE', 'appointmentId' => $row['kinesis_appointment_id']);
            } else {
                $errMsg = isset($patchRes['error']) ? $patchRes['error'] : 'Failed to patch appointment on Kinesis API';
                return array('success' => false, 'action' => 'UPDATE', 'error' => $errMsg);
            }
        }

        // Otherwise: Create appointment on Kinesis API
        if (empty($row['external_service_id'])) {
            return array('success' => false, 'action' => 'CREATE', 'error' => 'Missing external_service_id; refuse using local service id on Kinesis API.');
        }
        $isoDateTime = date('c', strtotime($row['booking_date_time']));
        $extServiceId = (int)$row['external_service_id'];

        $employeeId = $this->resolveExternalEmployeeId($row['staff_ids']);
        if ($employeeId === null) {
            return array('success' => false, 'action' => 'CREATE', 'error' => 'Missing external_employee_id for assigned doctor; refuse using local staff id.');
        }

        // Resolve customer information
        $customer = $this->resolveCustomerDetails($row);
        if (empty($row['client_email']) && empty($row['user_email'])) {
            return array('success' => false, 'action' => 'CREATE', 'error' => 'Missing customer email; refuse creating Kinesis appointment with synthetic address.');
        }

        // 1. Search if customer exists in Kinesis API
        $kinesisCustomerId = null;
        if (!empty($row['kinesis_customer_id'])) {
            $kinesisCustomerId = (int)$row['kinesis_customer_id'];
        } else {
            $searchRes = $this->apiClient->searchCustomerByEmail($customer['email']);
            if ($searchRes['success'] && !empty($searchRes['data'])) {
                $custData = is_array($searchRes['data']) && isset($searchRes['data'][0]) ? $searchRes['data'][0] : $searchRes['data'];
                $kinesisCustomerId = isset($custData['id']) ? (int)$custData['id'] : (isset($custData['Id']) ? (int)$custData['Id'] : null);
            }
        }

        $kinesisApptId = null;

        if ($kinesisCustomerId) {
            // Customer already exists in Kinesis API -> create appointment for existing customer
            $createRes = $this->apiClient->createAppointmentForExistingCustomer(
                $kinesisCustomerId,
                $isoDateTime,
                $extServiceId,
                $employeeId
            );

            if ($createRes['success']) {
                $kinesisApptId = isset($createRes['data']['id']) ? (int)$createRes['data']['id'] : (isset($createRes['data']['appointmentId']) ? (int)$createRes['data']['appointmentId'] : 0);
            } else {
                return array(
                    'success' => false,
                    'action' => 'CREATE',
                    'error' => isset($createRes['error']) ? $createRes['error'] : 'API error creating appointment for existing customer'
                );
            }
        } else {
            // Customer does not exist in Kinesis API -> create appointment with new customer
            $createRes = $this->apiClient->createAppointmentWithNewCustomer(
                $isoDateTime,
                $extServiceId,
                $employeeId,
                $customer
            );

            if ($createRes['success']) {
                $kinesisApptId = isset($createRes['data']['id']) ? (int)$createRes['data']['id'] : (isset($createRes['data']['appointmentId']) ? (int)$createRes['data']['appointmentId'] : 0);
                $kinesisCustomerId = isset($createRes['data']['customerId']) ? (int)$createRes['data']['customerId'] : 0;
            } else {
                return array(
                    'success' => false,
                    'action' => 'CREATE',
                    'error' => isset($createRes['error']) ? $createRes['error'] : 'API error creating appointment with new customer'
                );
            }
        }

        // Successfully created on Kinesis API -> Update Local System records
        $custVal = $kinesisCustomerId ? (int)$kinesisCustomerId : "NULL";
        $apptVal = $kinesisApptId ? (int)$kinesisApptId : "NULL";

        mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_appointment_id` = {$apptVal}, `kinesis_customer_id` = {$custVal}, `kinesis_sync_status` = 'SYNCED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");

        // Also update ct_gcal_kinesis_sync
        $syncCheck = mysqli_query($this->conn, "SELECT id FROM `ct_gcal_kinesis_sync` WHERE `local_order_id` = '{$orderId}' LIMIT 1");
        if ($syncCheck && mysqli_num_rows($syncCheck) > 0) {
            mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET `kinesis_customer_id` = {$custVal}, `kinesis_appointment_id` = {$apptVal}, `sync_status` = 'SYNCED', `last_sync_message` = 'Synced to Kinesis API', `updated_at` = '{$currentTime}' WHERE `local_order_id` = '{$orderId}'");
        } else {
            $escSummary = mysqli_real_escape_string($this->conn, $customer['firstName'] . ' ' . $customer['lastName'] . ' - ' . $row['service_title']);
            $escEmail = mysqli_real_escape_string($this->conn, $customer['email']);
            $escPhone = mysqli_real_escape_string($this->conn, $customer['phoneNumber']);
            $escName = mysqli_real_escape_string($this->conn, $customer['firstName'] . ' ' . $customer['lastName']);
            $escStart = mysqli_real_escape_string($this->conn, $row['booking_date_time']);
            $gEventId = mysqli_real_escape_string($this->conn, $row['gc_event_id']);

            mysqli_query($this->conn, "INSERT INTO `ct_gcal_kinesis_sync` (
                `google_event_id`, `local_order_id`, `local_booking_id`, `kinesis_customer_id`, `kinesis_appointment_id`,
                `event_summary`, `event_start`, `customer_email`, `customer_phone`, `customer_name`, `customer_dob`,
                `service_id`, `employee_id`, `sync_status`, `sync_action`, `last_sync_message`, `created_at`, `updated_at`
            ) VALUES (
                '{$gEventId}', '{$orderId}', '{$bookingId}', {$custVal}, {$apptVal},
                '{$escSummary}', '{$escStart}', '{$escEmail}', '{$escPhone}', '{$escName}', '1990-01-01',
                '{$extServiceId}', '{$employeeId}', 'SYNCED', 'CREATE', 'Synced from Local System to Kinesis API', '{$currentTime}', '{$currentTime}'
            )");
        }

        return array(
            'success' => true,
            'action' => 'CREATE',
            'appointmentId' => $kinesisApptId,
            'customerId' => $kinesisCustomerId
        );
    }

    /**
     * Map local staff_ids to Kinesis external_employee_id.
     * Never fall back to local DB id (wrong on API). Unassigned bookings use default 1.
     *
     * @param string|int|null $staffIds
     * @return int|null null when staff assigned but no external id
     */
    private function resolveExternalEmployeeId($staffIds) {
        if ($staffIds === null || $staffIds === '' || $staffIds === '0') {
            return 1;
        }
        $parts = explode(',', (string)$staffIds);
        $staffId = (int)trim($parts[0]);
        if ($staffId <= 0) {
            return 1;
        }
        $stCheck = mysqli_query($this->conn, "SELECT `external_employee_id` FROM `ct_admin_info` WHERE `id` = " . $staffId . " LIMIT 1");
        if ($stCheck && mysqli_num_rows($stCheck) > 0) {
            $stRow = mysqli_fetch_assoc($stCheck);
            if (!empty($stRow['external_employee_id']) && (int)$stRow['external_employee_id'] > 0) {
                return (int)$stRow['external_employee_id'];
            }
        }
        return null;
    }

    /**
     * Resolve customer first name, last name, email, phone, dob
     */
    private function resolveCustomerDetails($row) {
        $firstName = 'Guest';
        $lastName = 'User';
        $email = '';
        $phone = '0000000000';
        $dob = '1990-01-01';

        if (!empty($row['client_name'])) {
            $parts = explode(' ', trim($row['client_name']), 2);
            $firstName = isset($parts[0]) ? $parts[0] : 'Guest';
            $lastName = isset($parts[1]) ? $parts[1] : 'User';
        } elseif (!empty($row['first_name'])) {
            $firstName = trim($row['first_name']);
            $lastName = !empty($row['last_name']) ? trim($row['last_name']) : 'User';
        }

        if (!empty($row['client_email'])) {
            $email = trim($row['client_email']);
        } elseif (!empty($row['user_email'])) {
            $email = trim($row['user_email']);
        } else {
            $cleanFirst = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($firstName));
            $cleanLast = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($lastName));
            $email = ($cleanFirst && $cleanLast) ? "{$cleanFirst}.{$cleanLast}@kinesisport.it" : "order_{$row['order_id']}@kinesisport.it";
        }

        if (!empty($row['client_phone'])) {
            $phone = trim($row['client_phone']);
        } elseif (!empty($row['user_phone'])) {
            $phone = trim($row['user_phone']);
        }

        return array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => strtolower($email),
            'phoneNumber' => $phone,
            'dateOfBirth' => $dob
        );
    }

    /**
     * Update sync status in settings table
     */
    private function updateSyncStatus($success, $message) {
        $now = date('Y-m-d H:i:s');
        $statusStr = $success ? 'SUCCESS: ' . $message : 'FAILED: ' . $message;
        $this->setting->set_option('kinesis_last_sync_time', $now);
        $this->setting->set_option('kinesis_last_sync_status', $statusStr);
    }
}
