<?php
/**
 * AJAX: search frontend services for index_one_step cards
 * POST q = search string
 * POST partial=1&offset=N returns the next page of date cards as JSON
 */
ob_start();
header('Content-Type: text/html; charset=UTF-8');

include dirname(dirname(dirname(__FILE__))) . '/header.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_services.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';

$database = new cleanto_db();
$conn = $database->connect();
$database->conn = $conn;

$settings = new cleanto_setting();
$settings->conn = $conn;

$objservice = new cleanto_services();
$objservice->conn = $conn;

$first_step = new cleanto_first_step();
$first_step->conn = $conn;

$lang = $settings->get_option('ct_language');
$label_language_values = array();
$language_label_arr = $settings->get_all_labelsbyid($lang);
if ($language_label_arr && $language_label_arr[1] != '' && $language_label_arr[3] != '' && $language_label_arr[4] != '' && $language_label_arr[5] != '') {
	$default_language_arr = $language_label_arr;
} else {
	$default_language_arr = $settings->get_all_labelsbyid('en');
}
if ($default_language_arr) {
	$label_decode_front = base64_decode($default_language_arr[1]);
	$label_decode_admin = base64_decode($default_language_arr[3]);
	$label_decode_error = base64_decode($default_language_arr[4]);
	$label_decode_extra = base64_decode($default_language_arr[5]);
	$label_language_arr = array_merge(
		(array)unserialize($label_decode_front),
		(array)unserialize($label_decode_admin),
		(array)unserialize($label_decode_error),
		(array)unserialize($label_decode_extra)
	);
	foreach ($label_language_arr as $key => $value) {
		$label_language_values[$key] = urldecode($value);
	}
}

$q = isset($_POST['q']) ? trim((string)$_POST['q']) : '';
if ($q === '' && isset($_GET['q'])) {
	$q = trim((string)$_GET['q']);
}

$ksc_services_limit = 10;
$services_data = $objservice->readall_for_frontend_services();
$services = array();
if ($services_data) {
	while ($row = mysqli_fetch_assoc($services_data)) {
		$services[] = $row;
	}
}

if ($q !== '') {
	$q_norm = function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q);
	$filtered = array();
	foreach ($services as $row) {
		$title = isset($row['title']) ? $row['title'] : '';
		$hay = function_exists('mb_strtolower') ? mb_strtolower($title) : strtolower($title);
		if (strpos($hay, $q_norm) !== false) {
			$filtered[] = $row;
		}
	}
	$services = $filtered;
}

$currency_symbol = $settings->get_option('ct_currency_symbol');
$currency_position = $settings->get_option('ct_currency_symbol_position');
$price_decimals = (int)$settings->get_option('ct_price_format_decimal_places');
if ($price_decimals < 0) {
	$price_decimals = 2;
}

$ksc_format_price = function ($amount) use ($currency_symbol, $currency_position, $price_decimals) {
	$amount = (float)$amount;
	$formatted = number_format($amount, $price_decimals, ',', '.');
	if ($currency_position === 'after' || $currency_position === 'Right' || $currency_position === 'right') {
		return $formatted . $currency_symbol;
	}
	return $currency_symbol . $formatted;
};

/* Cup24 UI — Italian labels */
$cta_label = 'Scegli orario e Prenota';
$load_more_label = 'Carica altri';
$duration_suffix = 'minuti';
$available_label = 'Disponibile';
$ksc_empty_label = 'Nessun servizio trovato.';

$ksc_slot_date = $available_label;
if (class_exists('IntlDateFormatter')) {
	try {
		$fmt = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN, 'EEE, d MMM y');
		$formatted = $fmt->format(new DateTime('now'));
		if ($formatted) {
			$ksc_slot_date = ucfirst($formatted);
		}
	} catch (Exception $e) {
		$ksc_slot_date = date('D, j M Y');
	}
} else {
	$ksc_slot_date = date('D, j M Y');
}

$ksc_offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$ksc_items_only = isset($_POST['partial']) && (string)$_POST['partial'] === '1';
if ($ksc_items_only) {
	ob_start();
}
include dirname(dirname(dirname(__FILE__))) . '/front/partials/service_cards_list.php';
if ($ksc_items_only) {
	$html = ob_get_clean();
	ob_end_clean();
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(array(
		'html' => $html,
		'total' => isset($total_cards) ? (int)$total_cards : 0,
		'count' => isset($ksc_page_count) ? (int)$ksc_page_count : 0,
		'next' => isset($ksc_next_offset) ? (int)$ksc_next_offset : $ksc_offset,
	));
	exit;
}
ob_end_flush();
