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
                    AND IFNULL(b.kinesis_sync_status, 'PENDING') IN ('PENDING', 'UPDATE_PENDING')
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
     * Extract appointment + customer ids from Kinesis create/get response payloads.
     */
    private function extractIdsFromApiData($data) {
        $apptId = 0;
        $custId = 0;
        if (!is_array($data)) {
            return array($apptId, $custId);
        }
        if (!empty($data['id'])) {
            $apptId = (int)$data['id'];
        } elseif (!empty($data['appointmentId'])) {
            $apptId = (int)$data['appointmentId'];
        }
        if (!empty($data['customerId'])) {
            $custId = (int)$data['customerId'];
        } elseif (!empty($data['customer']['id'])) {
            $custId = (int)$data['customer']['id'];
        }
        return array($apptId, $custId);
    }

    /**
     * Ensure we have both Kinesis appointment + customer ids before calling cancel.
     * Recovers from sync table / email lookup when create previously saved incomplete ids.
     */
    private function resolveKinesisIdsForCancel($row) {
        $orderId = (int)$row['order_id'];
        $apptId = !empty($row['kinesis_appointment_id']) ? (int)$row['kinesis_appointment_id'] : 0;
        $custId = !empty($row['kinesis_customer_id']) ? (int)$row['kinesis_customer_id'] : 0;

        if ($apptId > 0 && $custId > 0) {
            return array($apptId, $custId);
        }

        $syncRes = mysqli_query($this->conn, "SELECT `kinesis_appointment_id`, `kinesis_customer_id`, `customer_email`, `event_start`
            FROM `ct_gcal_kinesis_sync` WHERE `local_order_id` = {$orderId} ORDER BY `id` DESC LIMIT 1");
        $syncRow = ($syncRes && mysqli_num_rows($syncRes) > 0) ? mysqli_fetch_assoc($syncRes) : null;
        if ($syncRow) {
            if ($apptId <= 0 && !empty($syncRow['kinesis_appointment_id'])) {
                $apptId = (int)$syncRow['kinesis_appointment_id'];
            }
            if ($custId <= 0 && !empty($syncRow['kinesis_customer_id'])) {
                $custId = (int)$syncRow['kinesis_customer_id'];
            }
        }

        $email = '';
        if (!empty($row['client_email'])) {
            $email = trim($row['client_email']);
        } elseif (!empty($row['user_email'])) {
            $email = trim($row['user_email']);
        } elseif ($syncRow && !empty($syncRow['customer_email'])) {
            $email = trim($syncRow['customer_email']);
        }

        if ($custId <= 0 && $email !== '') {
            $searchRes = $this->apiClient->searchCustomerByEmail($email);
            if (!empty($searchRes['success']) && !empty($searchRes['data'])) {
                $custData = is_array($searchRes['data']) && isset($searchRes['data'][0]) ? $searchRes['data'][0] : $searchRes['data'];
                $custId = isset($custData['id']) ? (int)$custData['id'] : (isset($custData['Id']) ? (int)$custData['Id'] : 0);
            }
        }

        if ($custId > 0 && $apptId <= 0 && !empty($row['booking_date_time'])) {
            try {
                $dt = new DateTime($row['booking_date_time'], new DateTimeZone('Europe/Rome'));
            } catch (Exception $e) {
                $dt = new DateTime($row['booking_date_time']);
            }
            $start = $dt->format('Y-m-d');
            $end = $dt->format('Y-m-d');
            $list = $this->apiClient->getCustomerAppointments($custId, $start, $end);
            if (!empty($list['success']) && is_array($list['data'])) {
                $wanted = $dt->format('Y-m-d\TH:i');
                foreach ($list['data'] as $appt) {
                    if (empty($appt['dateTime']) || empty($appt['id'])) {
                        continue;
                    }
                    try {
                        $slot = (new DateTime($appt['dateTime']))->format('Y-m-d\TH:i');
                    } catch (Exception $e) {
                        continue;
                    }
                    if ($slot === $wanted) {
                        $apptId = (int)$appt['id'];
                        break;
                    }
                }
            }
        }

        if ($apptId > 0 || $custId > 0) {
            $apptSql = $apptId > 0 ? (string)$apptId : 'NULL';
            $custSql = $custId > 0 ? (string)$custId : 'NULL';
            mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_appointment_id` = {$apptSql}, `kinesis_customer_id` = {$custSql} WHERE `order_id` = {$orderId}");
            /* Do not UPDATE historical sync log rows — append-only audit trail */
        }

        return array($apptId, $custId);
    }

    private function markCancelFailure($orderId, $errMsg) {
        $currentTime = date('Y-m-d H:i:s');
        mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCEL_PENDING', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
        $this->writeSyncLog((int)$orderId, 'CANCEL_PENDING', 'CANCEL', $errMsg, array());
    }

    /**
     * Sync log policy (1 row per booking lifecycle):
     * - CREATE  → always INSERT a new row
     * - UPDATE / CANCEL → UPDATE the latest active row for that order (do not insert duplicates)
     */
    public function writeSyncLog($orderId, $syncStatus, $syncAction, $message, $extra = array()) {
        $orderId = (int)$orderId;
        if ($orderId <= 0) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $statusEsc = mysqli_real_escape_string($this->conn, (string)$syncStatus);
        $actionEsc = mysqli_real_escape_string($this->conn, (string)$syncAction);
        $msgEsc = mysqli_real_escape_string($this->conn, (string)$message);
        $apptId = isset($extra['appointment_id']) ? (int)$extra['appointment_id'] : 0;
        $custId = isset($extra['customer_id']) ? (int)$extra['customer_id'] : 0;
        $eventStart = isset($extra['event_start']) ? mysqli_real_escape_string($this->conn, (string)$extra['event_start']) : '';
        $email = isset($extra['customer_email']) ? mysqli_real_escape_string($this->conn, (string)$extra['customer_email']) : '';
        $phone = isset($extra['customer_phone']) ? mysqli_real_escape_string($this->conn, (string)$extra['customer_phone']) : '';
        $name = isset($extra['customer_name']) ? mysqli_real_escape_string($this->conn, (string)$extra['customer_name']) : '';
        $summary = isset($extra['event_summary']) ? mysqli_real_escape_string($this->conn, (string)$extra['event_summary']) : '';
        $bookingId = isset($extra['booking_id']) ? (int)$extra['booking_id'] : 0;
        $serviceId = isset($extra['service_id']) ? (int)$extra['service_id'] : 0;
        $employeeId = isset($extra['employee_id']) ? (int)$extra['employee_id'] : 0;
        $gEventId = isset($extra['google_event_id']) ? mysqli_real_escape_string($this->conn, (string)$extra['google_event_id']) : '';

        $actionUpper = strtoupper((string)$syncAction);
        $isCreate = ($actionUpper === 'CREATE');

        /* CANCEL / UPDATE: mutate the latest active row for this order (1 row per booking) */
        if (!$isCreate) {
            $activeId = 0;
            if ($apptId > 0) {
                $q = mysqli_query($this->conn, "SELECT `id` FROM `ct_gcal_kinesis_sync`
                    WHERE `local_order_id` = {$orderId} AND `kinesis_appointment_id` = {$apptId}
                    ORDER BY `id` DESC LIMIT 1");
                if ($q && ($r = mysqli_fetch_assoc($q))) {
                    $activeId = (int)$r['id'];
                }
            }
            if ($activeId <= 0) {
                $q = mysqli_query($this->conn, "SELECT `id` FROM `ct_gcal_kinesis_sync`
                    WHERE `local_order_id` = {$orderId}
                      AND (`sync_status` IS NULL OR `sync_status` NOT IN ('CANCELLED'))
                    ORDER BY `id` DESC LIMIT 1");
                if ($q && ($r = mysqli_fetch_assoc($q))) {
                    $activeId = (int)$r['id'];
                }
            }
            if ($activeId <= 0) {
                /* Fallback: latest row for order */
                $q = mysqli_query($this->conn, "SELECT `id` FROM `ct_gcal_kinesis_sync` WHERE `local_order_id` = {$orderId} ORDER BY `id` DESC LIMIT 1");
                if ($q && ($r = mysqli_fetch_assoc($q))) {
                    $activeId = (int)$r['id'];
                }
            }

            if ($activeId > 0) {
                $sets = array(
                    "`sync_status` = '{$statusEsc}'",
                    "`sync_action` = '{$actionEsc}'",
                    "`last_sync_message` = '{$msgEsc}'",
                    "`updated_at` = '{$now}'"
                );
                if ($apptId > 0) {
                    $sets[] = "`kinesis_appointment_id` = {$apptId}";
                }
                if ($custId > 0) {
                    $sets[] = "`kinesis_customer_id` = {$custId}";
                }
                if ($eventStart !== '') {
                    $sets[] = "`event_start` = '{$eventStart}'";
                }
                if ($email !== '') {
                    $sets[] = "`customer_email` = '{$email}'";
                }
                if ($phone !== '') {
                    $sets[] = "`customer_phone` = '{$phone}'";
                }
                if ($name !== '') {
                    $sets[] = "`customer_name` = '{$name}'";
                }
                if ($summary !== '') {
                    $sets[] = "`event_summary` = '{$summary}'";
                }
                if ($bookingId > 0) {
                    $sets[] = "`local_booking_id` = {$bookingId}";
                }
                if ($serviceId > 0) {
                    $sets[] = "`service_id` = {$serviceId}";
                }
                if ($employeeId > 0) {
                    $sets[] = "`employee_id` = {$employeeId}";
                }
                return (bool)mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET " . implode(', ', $sets) . " WHERE `id` = {$activeId}");
            }
            /* No existing row — fall through to INSERT once */
        }

        $apptSql = $apptId > 0 ? (string)$apptId : 'NULL';
        $custSql = $custId > 0 ? (string)$custId : 'NULL';
        $bookingSql = $bookingId > 0 ? (string)$bookingId : 'NULL';
        $serviceSql = $serviceId > 0 ? (string)$serviceId : 'NULL';
        $employeeSql = $employeeId > 0 ? (string)$employeeId : 'NULL';
        $startSql = $eventStart !== '' ? "'{$eventStart}'" : 'NULL';
        if ($summary === '') {
            $summary = $actionEsc . ' sync';
        }

        return (bool)mysqli_query($this->conn, "INSERT INTO `ct_gcal_kinesis_sync` (
            `google_event_id`, `local_order_id`, `local_booking_id`, `kinesis_customer_id`, `kinesis_appointment_id`,
            `event_summary`, `event_start`, `customer_email`, `customer_phone`, `customer_name`, `customer_dob`,
            `service_id`, `employee_id`, `sync_status`, `sync_action`, `last_sync_message`, `created_at`, `updated_at`
        ) VALUES (
            '{$gEventId}', {$orderId}, {$bookingSql}, {$custSql}, {$apptSql},
            '{$summary}', {$startSql}, '{$email}', '{$phone}', '{$name}', '1990-01-01',
            {$serviceSql}, {$employeeSql}, '{$statusEsc}', '{$actionEsc}', '{$msgEsc}', '{$now}', '{$now}'
        )");
    }

    /** @deprecated */
    public function appendSyncLog($orderId, $syncStatus, $syncAction, $message, $extra = array()) {
        return $this->writeSyncLog($orderId, $syncStatus, $syncAction, $message, $extra);
    }

    /** @deprecated */
    public function upsertSyncLog($orderId, $syncStatus, $syncAction, $message, $extra = array()) {
        return $this->writeSyncLog($orderId, $syncStatus, $syncAction, $message, $extra);
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
            list($apptId, $custId) = $this->resolveKinesisIdsForCancel($row);

            if ($apptId > 0) {
                if ($custId <= 0) {
                    $err = 'Missing kinesis_customer_id; cannot cancel remote appointment #' . $apptId;
                    $this->markCancelFailure($orderId, $err);
                    return array('success' => false, 'action' => 'CANCEL', 'error' => $err, 'appointmentId' => $apptId);
                }

                $cancelRes = $this->apiClient->cancelAppointment($custId, $apptId);

                if (!empty($cancelRes['success'])) {
                    mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCELLED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
                    $this->writeSyncLog($orderId, 'CANCELLED', 'CANCEL', 'Cancelled on Kinesis API', array(
                        'appointment_id' => $apptId,
                        'customer_id' => $custId,
                        'booking_id' => $bookingId,
                        'event_start' => isset($row['booking_date_time']) ? $row['booking_date_time'] : '',
                        'customer_email' => !empty($row['client_email']) ? $row['client_email'] : (isset($row['user_email']) ? $row['user_email'] : ''),
                        'customer_name' => trim((isset($row['first_name']) ? $row['first_name'] : '') . ' ' . (isset($row['last_name']) ? $row['last_name'] : '')),
                        'event_summary' => trim((isset($row['client_name']) ? $row['client_name'] : '') . ' - ' . (isset($row['service_title']) ? $row['service_title'] : '')),
                        'service_id' => !empty($row['external_service_id']) ? (int)$row['external_service_id'] : 0
                    ));
                    return array('success' => true, 'action' => 'CANCEL', 'appointmentId' => $apptId, 'customerId' => $custId);
                }

                $errMsg = isset($cancelRes['error']) ? $cancelRes['error'] : 'Failed to cancel appointment on Kinesis API';
                $this->markCancelFailure($orderId, $errMsg);
                return array(
                    'success' => false,
                    'action' => 'CANCEL',
                    'error' => $errMsg,
                    'status' => isset($cancelRes['status']) ? $cancelRes['status'] : null,
                    'appointmentId' => $apptId,
                    'customerId' => $custId
                );
            }
            mysqli_query($this->conn, "UPDATE `ct_bookings` SET `kinesis_sync_status` = 'CANCELLED', `kinesis_sync_time` = '{$currentTime}' WHERE `order_id` = '{$orderId}'");
            $this->writeSyncLog($orderId, 'CANCELLED', 'CANCEL', 'No active Kinesis appointment id on local booking; remote cancel skipped', array(
                'booking_id' => $bookingId,
                'event_start' => isset($row['booking_date_time']) ? $row['booking_date_time'] : '',
                'customer_email' => !empty($row['client_email']) ? $row['client_email'] : (isset($row['user_email']) ? $row['user_email'] : '')
            ));
            return array('success' => true, 'action' => 'CANCEL', 'note' => 'No active Kinesis appointment to cancel.');
        }

        // UPDATE_PENDING with no remote ids = never pushed to Kinesis yet (e.g. GCal
        // import then time change). Treat as CREATE instead of blocking.
        if ($row['kinesis_sync_status'] === 'UPDATE_PENDING') {
            if (empty($row['kinesis_appointment_id']) || empty($row['kinesis_customer_id'])) {
                $row['kinesis_sync_status'] = 'PENDING';
            } else {
            $isoDateTime = $this->toKinesisDateTime($row['booking_date_time']);
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
                $this->writeSyncLog($orderId, 'SYNCED', 'UPDATE', 'Updated on Kinesis API', array(
                    'appointment_id' => (int)$row['kinesis_appointment_id'],
                    'customer_id' => (int)$row['kinesis_customer_id'],
                    'booking_id' => $bookingId,
                    'event_start' => $row['booking_date_time'],
                    'customer_email' => !empty($row['client_email']) ? $row['client_email'] : (isset($row['user_email']) ? $row['user_email'] : ''),
                    'customer_name' => trim((isset($row['first_name']) ? $row['first_name'] : '') . ' ' . (isset($row['last_name']) ? $row['last_name'] : '')),
                    'service_id' => !empty($row['external_service_id']) ? (int)$row['external_service_id'] : 0,
                    'event_summary' => trim((!empty($row['client_name']) ? $row['client_name'] : trim((isset($row['first_name']) ? $row['first_name'] : '') . ' ' . (isset($row['last_name']) ? $row['last_name'] : ''))) . ' - ' . (isset($row['service_title']) ? $row['service_title'] : ''))
                ));
                return array('success' => true, 'action' => 'UPDATE', 'appointmentId' => $row['kinesis_appointment_id']);
            }
            $errMsg = isset($patchRes['error']) ? $patchRes['error'] : 'Failed to patch appointment on Kinesis API';
            return array('success' => false, 'action' => 'UPDATE', 'error' => $errMsg);
            }
        }

        // Otherwise: Create appointment on Kinesis API
        if (empty($row['external_service_id'])) {
            return array('success' => false, 'action' => 'CREATE', 'error' => 'Missing external_service_id; refuse using local service id on Kinesis API.');
        }
        $isoDateTime = $this->toKinesisDateTime($row['booking_date_time']);
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

        // Pre-check availability to return a clearer error than raw Italian API conflict.
        $availabilityHint = $this->explainOutsideWorkingHours($extServiceId, $employeeId, $row['booking_date_time'], $isoDateTime);
        if ($availabilityHint !== null) {
            return array('success' => false, 'action' => 'CREATE', 'error' => $availabilityHint);
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
                list($parsedAppt, $parsedCust) = $this->extractIdsFromApiData(isset($createRes['data']) ? $createRes['data'] : null);
                $kinesisApptId = $parsedAppt;
                if ($parsedCust > 0) {
                    $kinesisCustomerId = $parsedCust;
                }
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
                list($parsedAppt, $parsedCust) = $this->extractIdsFromApiData(isset($createRes['data']) ? $createRes['data'] : null);
                $kinesisApptId = $parsedAppt;
                $kinesisCustomerId = $parsedCust;
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

        $localStaffId = 0;
        if (!empty($row['staff_ids'])) {
            $parts = explode(',', (string)$row['staff_ids']);
            $localStaffId = (int)trim($parts[0]);
        }
        $this->writeSyncLog($orderId, 'SYNCED', 'CREATE', 'Created booking on Kinesis API', array(
            'appointment_id' => $kinesisApptId ? (int)$kinesisApptId : 0,
            'customer_id' => $kinesisCustomerId ? (int)$kinesisCustomerId : 0,
            'booking_id' => $bookingId,
            'event_start' => $row['booking_date_time'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['phoneNumber'],
            'customer_name' => $customer['firstName'] . ' ' . $customer['lastName'],
            'event_summary' => $customer['firstName'] . ' ' . $customer['lastName'] . ' - ' . $row['service_title'],
            'service_id' => $extServiceId,
            'employee_id' => $localStaffId,
            'google_event_id' => isset($row['gc_event_id']) ? $row['gc_event_id'] : ''
        ));

        return array(
            'success' => true,
            'action' => 'CREATE',
            'appointmentId' => $kinesisApptId,
            'customerId' => $kinesisCustomerId
        );
    }

    /**
     * Build ISO-8601 datetime for Kinesis using clinic timezone.
     * Local booking_date_time is wall-clock time (from GCal/admin), not UTC.
     */
    private function toKinesisDateTime($bookingDateTime) {
        $tzName = $this->setting->get_option('ct_timezone');
        if ($tzName === '' || $tzName === null || strtoupper($tzName) === 'UTC') {
            $tzName = 'Europe/Rome';
        }
        try {
            $tz = new DateTimeZone($tzName);
        } catch (Exception $e) {
            $tz = new DateTimeZone('Europe/Rome');
        }
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $bookingDateTime, $tz);
        if (!$dt) {
            $dt = new DateTime($bookingDateTime, $tz);
        }
        return $dt->format('c');
    }

    /**
     * If requested slot is not in Kinesis availability, return human-readable error.
     * Returns null when slot looks available (or availability API failed — let create proceed).
     */
    private function explainOutsideWorkingHours($serviceId, $employeeId, $bookingDateTime, $isoDateTime) {
        try {
            $tzName = 'Europe/Rome';
            $tz = new DateTimeZone($tzName);
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $bookingDateTime, $tz);
            if (!$dt) {
                $dt = new DateTime($bookingDateTime, $tz);
            }
            $dayStart = $dt->format('Y-m-d') . 'T00:00:00' . $dt->format('P');
            $dayEnd = $dt->format('Y-m-d') . 'T23:59:59' . $dt->format('P');
            $av = $this->apiClient->getAvailability((int)$serviceId, $dayStart, $dayEnd, (int)$employeeId);
            if (empty($av['success']) || !is_array($av['data'])) {
                return null;
            }
            $wanted = $dt->format('Y-m-d\TH:i');
            foreach ($av['data'] as $slot) {
                if (empty($slot['dateTime'])) {
                    continue;
                }
                $slotDt = new DateTime($slot['dateTime']);
                if ($slotDt->format('Y-m-d\TH:i') === $wanted) {
                    return null;
                }
            }
            $slotSamples = array();
            foreach (array_slice($av['data'], 0, 8) as $slot) {
                if (!empty($slot['dateTime'])) {
                    $slotSamples[] = (new DateTime($slot['dateTime']))->format('H:i');
                }
            }
            $dayLabel = $dt->format('l Y-m-d');
            if (count($av['data']) === 0) {
                return "Kinesis rejected {$isoDateTime}: employee #{$employeeId} has no available slots on {$dayLabel}. Update Google Calendar / booking time, or set working hours for this professional in Kinesis.";
            }
            return "Kinesis rejected {$isoDateTime}: outside professional working hours on {$dayLabel}. Available that day: " . implode(', ', $slotSamples) . (count($av['data']) > 8 ? ', ...' : '') . '.';
        } catch (Exception $e) {
            return null;
        }
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
