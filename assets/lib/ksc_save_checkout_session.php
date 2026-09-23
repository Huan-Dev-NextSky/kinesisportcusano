<?php
/**
 * Persist Cup24 booking snapshot so checkout_one_step.php can restore cart UI.
 */
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(array('ok' => 0, 'error' => 'method'));
	exit;
}

$cart = isset($_SESSION['ct_cart']['method']) ? $_SESSION['ct_cart']['method'] : array();
if (!is_array($cart) || count($cart) === 0) {
	echo json_encode(array('ok' => 0, 'error' => 'empty_cart'));
	exit;
}

$get = function ($key) {
	return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
};

$_SESSION['ksc_checkout'] = array(
	'service_title' => $get('service_title'),
	'cart_date' => $get('cart_date'),
	'cart_date_val' => $get('cart_date_val'),
	'cart_time' => $get('cart_time'),
	'cart_time_val' => $get('cart_time_val'),
	'cart_html' => isset($_POST['cart_html']) ? (string)$_POST['cart_html'] : '',
	'cart_sub_total' => $get('cart_sub_total'),
	'cart_tax' => $get('cart_tax'),
	'cart_total' => $get('cart_total'),
	'cart_discount' => $get('cart_discount'),
	'frequent_discount' => $get('frequent_discount'),
	'duration_text' => $get('duration_text'),
	'total_cart_count' => $get('total_cart_count') !== '' ? $get('total_cart_count') : '2',
	'staff_id' => $get('staff_id'),
	'saved_at' => time(),
);

$staffId = (int)$get('staff_id');
if ($staffId > 0) {
	$_SESSION['staff_id_cal'] = (string)$staffId;
}

echo json_encode(array('ok' => 1));
exit;
