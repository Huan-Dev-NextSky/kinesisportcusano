<?php
/**
 * AJAX: intersected free slots for a service + date (Kinesis API ∩ Doctor admin).
 * POST: service_id, date (j-m-Y or Y-m-d)
 * JSON: { ok, date, slots: [{time, staff_id, label}] }
 */
ob_start();
header('Content-Type: application/json; charset=UTF-8');

include dirname(dirname(dirname(__FILE__))) . '/header.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_services.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';
include dirname(dirname(dirname(__FILE__))) . '/assets/lib/ksc_dates_with_slots.php';

$database = new cleanto_db();
$conn = $database->connect();
$database->conn = $conn;

$settings = new cleanto_setting();
$settings->conn = $conn;

$first_step = new cleanto_first_step();
$first_step->conn = $conn;

$objservice = new cleanto_services();
$objservice->conn = $conn;

$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$raw_date = isset($_POST['date']) ? trim((string)$_POST['date']) : '';

function ksc_parse_request_date($raw) {
	$raw = trim($raw);
	if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $raw, $m)) {
		return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
	}
	if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $raw, $m)) {
		return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
	}
	$ts = strtotime($raw);
	return $ts ? date('Y-m-d', $ts) : '';
}

$ymd = ksc_parse_request_date($raw_date);
if ($service_id <= 0 || $ymd === '') {
	ob_end_clean();
	echo json_encode(array('ok' => false, 'error' => 'invalid', 'slots' => array()));
	exit;
}

$service_row = null;
$all = $objservice->readall_for_frontend_services();
if ($all) {
	while ($row = mysqli_fetch_assoc($all)) {
		if ((int)$row['id'] === $service_id) {
			$service_row = $row;
			break;
		}
	}
}
if (!$service_row) {
	ob_end_clean();
	echo json_encode(array('ok' => false, 'error' => 'service', 'slots' => array()));
	exit;
}

$intersected = ksc_intersected_slots_for_service_date($conn, $settings, $first_step, $service_row, $ymd);
$slots = array();
foreach ($intersected as $s) {
	$slots[] = array(
		'time' => $s['time'],
		'staff_id' => (int)$s['staff_id'],
		'label' => $s['time'],
	);
}

/* Remember preferred staff for cart assignment */
if (!empty($slots[0]['staff_id'])) {
	$_SESSION['staff_id_cal'] = (string)$slots[0]['staff_id'];
	$_SESSION['provider_sec'] = (string)$slots[0]['staff_id'];
}

ob_end_clean();
echo json_encode(array(
	'ok' => true,
	'date' => $ymd,
	'date_raw' => (int)date('j', strtotime($ymd)) . '-' . date('m-Y', strtotime($ymd)),
	'slots' => $slots,
));
exit;
