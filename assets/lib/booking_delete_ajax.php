<?php
/**
 * Hard-delete for admin calendar Booking Details.
 * Admin-only. Cancels Kinesis + Google Calendar before local delete.
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

$bkRes = @mysqli_query($conn, "SELECT `order_id`, `gc_event_id`, `gc_staff_event_id`, `staff_ids`, `kinesis_appointment_id`, `kinesis_customer_id`, `booking_status` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
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

/* Mark cancelled so Kinesis sync cancels remote appointment first */
@mysqli_query($conn, "UPDATE `ct_bookings` SET `booking_status` = 'CC', `kinesis_sync_status` = 'CANCEL_PENDING', `lastmodify` = '{$now}' WHERE `order_id` = {$order_id}");
@mysqli_query($conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'CANCEL_PENDING', `sync_action` = 'CANCEL', `last_sync_message` = 'Admin hard-delete pending remote cancel', `updated_at` = '{$now}' WHERE `local_order_id` = {$order_id}");

$remote_notes = array();

if ($setting->get_option('kinesis_api_status') === 'Y') {
	try {
		require_once dirname(dirname(dirname(__FILE__))) . '/integrations/awwapi/AwwAppointmentSync.php';
		$apptSync = new AwwAppointmentSync($conn);
		$syncRes = $apptSync->syncSingleBooking($order_id);
		if (!empty($syncRes['success'])) {
			$remote_notes[] = 'kinesis_cancelled';
		} else {
			$remote_notes[] = 'kinesis_cancel_failed:' . (isset($syncRes['error']) ? $syncRes['error'] : 'unknown');
		}
	} catch (Exception $e) {
		$remote_notes[] = 'kinesis_cancel_exception:' . $e->getMessage();
	}
}

$_POST['gc_event_id'] = $gc_event_id;
$_POST['gc_staff_event_id'] = $gc_staff_event_id;
$_POST['pid'] = $pid;

if ($gc_hook->gc_purchase_status() == 'exist') {
	if ($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y') {
		try {
			@$gc_hook->gc_cancel_reject_booking_hook();
			$remote_notes[] = 'gcal_cancel_attempted';
		} catch (Exception $e) {
			$remote_notes[] = 'gcal_cancel_exception:' . $e->getMessage();
		}
	}
}

$dashboard->delete_booking($order_id);

$check2 = @mysqli_query($conn, "SELECT `order_id` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
$exists_after = ($check2 && mysqli_num_rows($check2) > 0);

$msgEsc = mysqli_real_escape_string($conn, 'Local booking deleted by admin. ' . implode('; ', $remote_notes));
@mysqli_query($conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'CANCELLED', `sync_action` = 'CANCEL', `last_sync_message` = '{$msgEsc}', `updated_at` = NOW() WHERE `local_order_id` = {$order_id}");

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
	'remote' => $remote_notes
));
exit;
