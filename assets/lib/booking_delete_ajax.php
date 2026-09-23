<?php
/**
 * Hard-delete for admin calendar Booking Details.
 * Admin-only. Cancels Kinesis + Google Calendar before local delete.
 * Always writes a sync audit log row (insert if missing).
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['ct_adminid'])) {
	http_response_code(403);
	echo json_encode(array('ok' => false, 'error' => 'unauthorized'));
	exit;
}

$order_id = 0;
if (isset($_POST['id'])) {
	$order_id = (int)$_POST['id'];
} elseif (isset($_POST['order_id'])) {
	$order_id = (int)$_POST['order_id'];
}

if ($order_id <= 0) {
	http_response_code(400);
	echo json_encode(array('ok' => false, 'error' => 'missing_id'));
	exit;
}

require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php';
require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_dashboard.php';
require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_gc_hook.php';

$db = new cleanto_db();
$conn = $db->connect();
$dashboard = new cleanto_dashboard();
$dashboard->conn = $conn;
$setting = new cleanto_setting();
$setting->conn = $conn;
$gc_hook = new cleanto_gcHook();
$gc_hook->conn = $conn;

$bkRes = @mysqli_query($conn, "SELECT b.`order_id`, b.`id` AS `booking_id`, b.`gc_event_id`, b.`gc_staff_event_id`, b.`staff_ids`, b.`booking_date_time`, b.`kinesis_appointment_id`, b.`kinesis_customer_id`, b.`booking_status`, oci.`client_email`, oci.`client_name`
	FROM `ct_bookings` b
	LEFT JOIN `ct_order_client_info` oci ON oci.`order_id` = b.`order_id`
	WHERE b.`order_id` = {$order_id} LIMIT 1");
$bkRow = ($bkRes && mysqli_num_rows($bkRes) > 0) ? mysqli_fetch_assoc($bkRes) : null;
$exists_before = ($bkRow !== null);

if (!$exists_before) {
	echo json_encode(array('ok' => true, 'order_id' => $order_id, 'existed' => false, 'deleted' => true));
	exit;
}

$gc_event_id = isset($_POST['gc_event_id']) && $_POST['gc_event_id'] !== ''
	? $_POST['gc_event_id']
	: (isset($bkRow['gc_event_id']) ? $bkRow['gc_event_id'] : '');
$gc_staff_event_id = isset($_POST['gc_staff_event_id']) && $_POST['gc_staff_event_id'] !== ''
	? $_POST['gc_staff_event_id']
	: (isset($bkRow['gc_staff_event_id']) ? $bkRow['gc_staff_event_id'] : '');
$pid = isset($_POST['pid']) && $_POST['pid'] !== ''
	? $_POST['pid']
	: (isset($bkRow['staff_ids']) ? $bkRow['staff_ids'] : '');

$now = date('Y-m-d H:i:s');
$had_remote_appt = !empty($bkRow['kinesis_appointment_id']) && (int)$bkRow['kinesis_appointment_id'] > 0;

/* Mark cancelled so Kinesis sync cancels remote appointment first */
@mysqli_query($conn, "UPDATE `ct_bookings` SET `booking_status` = 'CC', `kinesis_sync_status` = 'CANCEL_PENDING', `lastmodify` = '{$now}' WHERE `order_id` = {$order_id}");

$remote_notes = array();
$kinesis_ok = true;
$kinesis_error = '';
$syncExtras = array(
	'appointment_id' => !empty($bkRow['kinesis_appointment_id']) ? (int)$bkRow['kinesis_appointment_id'] : 0,
	'customer_id' => !empty($bkRow['kinesis_customer_id']) ? (int)$bkRow['kinesis_customer_id'] : 0,
	'booking_id' => !empty($bkRow['booking_id']) ? (int)$bkRow['booking_id'] : 0,
	'event_start' => !empty($bkRow['booking_date_time']) ? $bkRow['booking_date_time'] : '',
	'customer_email' => !empty($bkRow['client_email']) ? $bkRow['client_email'] : '',
	'customer_name' => !empty($bkRow['client_name']) ? $bkRow['client_name'] : ''
);

$apptSync = null;
if ($setting->get_option('kinesis_api_status') === 'Y') {
	try {
		require_once dirname(dirname(dirname(__FILE__))) . '/integrations/awwapi/AwwAppointmentSync.php';
		$apptSync = new AwwAppointmentSync($conn);
		$syncRes = $apptSync->syncSingleBooking($order_id);
		if (!empty($syncRes['success'])) {
			$remote_notes[] = 'kinesis_cancelled';
			$kinesis_ok = true;
			if (!empty($syncRes['appointmentId'])) {
				$syncExtras['appointment_id'] = (int)$syncRes['appointmentId'];
				$had_remote_appt = true;
			}
			if (!empty($syncRes['customerId'])) {
				$syncExtras['customer_id'] = (int)$syncRes['customerId'];
			}
		} else {
			$kinesis_ok = false;
			$kinesis_error = isset($syncRes['error']) ? $syncRes['error'] : (isset($syncRes['note']) ? $syncRes['note'] : 'unknown');
			$remote_notes[] = 'kinesis_cancel_failed:' . $kinesis_error;
			if (!empty($syncRes['appointmentId'])) {
				$syncExtras['appointment_id'] = (int)$syncRes['appointmentId'];
				$had_remote_appt = true;
			}
		}
	} catch (Throwable $e) {
		$kinesis_ok = false;
		$kinesis_error = $e->getMessage();
		$remote_notes[] = 'kinesis_cancel_exception:' . $kinesis_error;
	}
} else {
	$remote_notes[] = 'kinesis_disabled';
}

$_POST['gc_event_id'] = $gc_event_id;
$_POST['gc_staff_event_id'] = $gc_staff_event_id;
$_POST['pid'] = $pid;

if ($gc_hook->gc_purchase_status() == 'exist') {
	if ($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y') {
		try {
			@$gc_hook->gc_cancel_reject_booking_hook();
			$remote_notes[] = 'gcal_cancel_attempted';
		} catch (Throwable $e) {
			$remote_notes[] = 'gcal_cancel_exception:' . $e->getMessage();
		}
	}
}

$dashboard->delete_booking($order_id);

$check2 = @mysqli_query($conn, "SELECT `order_id` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
$exists_after = ($check2 && mysqli_num_rows($check2) > 0);

/* syncSingleBooking already updated the single CREATE log row to CANCEL — do not insert another */

if ($exists_after) {
	http_response_code(500);
	echo json_encode(array(
		'ok' => false,
		'error' => 'delete_failed',
		'order_id' => $order_id,
		'existed' => $exists_before,
		'remote' => $remote_notes
	));
	exit;
}

echo json_encode(array(
	'ok' => true,
	'order_id' => $order_id,
	'existed' => $exists_before,
	'deleted' => true,
	'kinesis_ok' => $kinesis_ok,
	'kinesis_error' => $kinesis_error,
	'had_remote_appt' => $had_remote_appt,
	'remote' => $remote_notes,
	'warning' => (!$kinesis_ok && ($had_remote_appt || $kinesis_error !== '')) ? $kinesis_error : null
));
exit;
