<?php
/* PDF download — must not emit any HTML/notices before Output() */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
while (ob_get_level() > 0) {
	ob_end_clean();
}
ob_start();
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_connection.php');
	include_once(dirname(dirname(dirname(__FILE__))).'/header.php');
	/* header.php turns display_errors back on — keep PDF output clean */
	ini_set('display_errors', '0');
	ini_set('display_startup_errors', '0');
	include(dirname(dirname(dirname(__FILE__))).'/assets/pdf/tfpdf/tfpdf.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_booking.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_services.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_services_methods.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_services_methods_units.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_services_addon.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_setting.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_users.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_front_first_step.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_order_client_info.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_payments.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_general.php');	
		
	$database=new cleanto_db();
	$conn=$database->connect();
	$database->conn=$conn;
	
	$booking=new cleanto_booking();
	$service=new cleanto_services();	
	$setting=new cleanto_setting();
	$first_step=new cleanto_first_step();
	$user=new cleanto_users();
	$order=new cleanto_order_client_info();
	$payments=new cleanto_payments();	
	$general=new cleanto_general();
	$smethod=new cleanto_services_methods();
	$smunit=new cleanto_services_methods_units();	
	$saddon=new cleanto_services_addon();
	
	$service->conn=$conn;
	$booking->conn=$conn;	
	$setting->conn=$conn;
	$first_step->conn=$conn;
	$user->conn=$conn;
	$order->conn=$conn;
	$payments->conn=$conn;
	$smethod->conn=$conn;
	$smunit->conn=$conn;
	$general->conn=$conn;
	$saddon->conn=$conn;
	
	$lang = $setting->get_option("ct_language");
$label_language_values = array();
$language_label_arr = $setting->get_all_labelsbyid($lang);

if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "")
{
	$default_language_arr = $setting->get_all_labelsbyid("en");
	if($language_label_arr[1] != ''){
		$label_decode_front = base64_decode($language_label_arr[1]);
	}else{
		$label_decode_front = base64_decode($default_language_arr[1]);
	}
	if($language_label_arr[3] != ''){
		$label_decode_admin = base64_decode($language_label_arr[3]);
	}else{
		$label_decode_admin = base64_decode($default_language_arr[3]);
	}
	if($language_label_arr[4] != ''){
		$label_decode_error = base64_decode($language_label_arr[4]);
	}else{
		$label_decode_error = base64_decode($default_language_arr[4]);
	}
	if($language_label_arr[5] != ''){
		$label_decode_extra = base64_decode($language_label_arr[5]);
	}else{
		$label_decode_extra = base64_decode($default_language_arr[5]);
	}
		
    $label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);
	$label_decode_extra_unserial = unserialize($label_decode_extra);
    
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial);
	foreach($label_language_arr as $key => $value){
		$label_language_values[$key] = urldecode($value);
	}
}
else
{
    $default_language_arr = $setting->get_all_labelsbyid("en");
	
	$label_decode_front = base64_decode($default_language_arr[1]);
	$label_decode_admin = base64_decode($default_language_arr[3]);
	$label_decode_error = base64_decode($default_language_arr[4]);
	$label_decode_extra = base64_decode($default_language_arr[5]);
	
	$label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);   
	$label_decode_extra_unserial = unserialize($label_decode_extra);   
	
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial);
	foreach($label_language_arr as $key => $value){
		$label_language_values[$key] = urldecode($value);
	}
}
/*new file include*/
include(dirname(dirname(dirname(__FILE__))).'/assets/lib/date_translate_array.php');

	$dateformat=$setting->get_option('ct_date_picker_date_format');
    $symbol_position=$setting->get_option('ct_currency_symbol_position');    
    $symbol=$setting->get_option('ct_currency_symbol');    
    $decimal=$setting->get_option('ct_price_format_decimal_places');	
	$dateformat=$setting->get_option('ct_date_picker_date_format');	
	$time_format=$setting->get_option('ct_time_format');		
	/*Invoice Details*/
	$order_id = isset($_GET['iid']) ? (int)$_GET['iid'] : 0;
	if ($order_id <= 0) {
		header('HTTP/1.1 400 Bad Request');
		echo 'Invalid invoice request.';
		exit;
	}

	$payments->order_id = $order_id;
	$payment_row = $payments->readone_payment_details();
	if (!$payment_row) {
		header('HTTP/1.1 404 Not Found');
		echo 'Invoice not found.';
		exit;
	}

	$payment_is_completed = (isset($payment_row['payment_status']) && $payment_row['payment_status'] === 'Completed');
	$is_admin = isset($_SESSION['ct_adminid']);
	$is_staff = isset($_SESSION['ct_staffid']);
	$is_customer = isset($_SESSION['ct_login_user_id']) && !$is_admin;

	/* Admin/staff: any invoice. Customers: Completed + own booking. Anonymous: denied. */
	if ($is_admin || $is_staff) {
		/* allowed */
	} elseif ($is_customer) {
		if (!$payment_is_completed) {
			header('HTTP/1.1 403 Forbidden');
			echo 'Invoice is available after payment is completed.';
			exit;
		}
		$booking->order_id = $order_id;
		$bookings_check = $booking->get_details_for_invoice_client();
		if (!$bookings_check || (int)$bookings_check[4] !== (int)$_SESSION['ct_login_user_id']) {
			header('HTTP/1.1 403 Forbidden');
			echo 'You are not allowed to download this invoice.';
			exit;
		}
	} else {
		header('HTTP/1.1 403 Forbidden');
		echo 'Please log in to download this invoice.';
		exit;
	}

	$booking->order_id=$order_id;
	$bookings = $booking->get_details_for_invoice_client();
	if (!$bookings) {
		header('HTTP/1.1 404 Not Found');
		echo 'Invoice not found.';
		exit;
	}
	
	/*Business Id by location id*/
	
	$business_name=$setting->get_option('ct_company_name');
	$business_email=$setting->get_option('ct_company_email');
	$business_address=$setting->get_option('ct_company_address');
	$business_city=$setting->get_option('ct_company_city');
	$business_state=$setting->get_option('ct_company_state');
	$business_zip=$setting->get_option('ct_company_zip_code');
	$business_country=$setting->get_option('ct_company_country');	
	$business_logo=$setting->get_option('ct_company_logo');
	
	
	$invoice_number = strtoupper(date('M',strtotime($bookings[2]))).'-'.$order_id;
	$invoice_date = date($dateformat,strtotime($bookings[2]));	
	
	/*Client info — prefer ct_order_client_info (snapshot at booking).
	  Do NOT use mysqli_fetch_row indices on ct_users: Kinesis columns shifted them
	  (e.g. first_name index became user_pwd → MD5 shown as customer name). */
	$order->order_id = $order_id;
	$oci = $order->readone_order_client();
	$personal = array();
	if (!empty($oci['client_personal_info'])) {
		$decoded = @unserialize(base64_decode($oci['client_personal_info']));
		if (is_array($decoded)) {
			$personal = $decoded;
		}
	}

	$client_name = !empty($oci['client_name']) ? $oci['client_name'] : '';
	$client_email = !empty($oci['client_email']) ? $oci['client_email'] : '';
	$client_phone = !empty($oci['client_phone']) ? $oci['client_phone'] : '';
	$client_address = isset($personal['address']) ? $personal['address'] : '';
	$client_city = isset($personal['city']) ? $personal['city'] : '';
	$client_state = isset($personal['state']) ? $personal['state'] : '';
	$client_zip = isset($personal['zip']) ? $personal['zip'] : '';
	$client_notes = isset($personal['notes']) ? $personal['notes'] : '';
	$client_country = '';

	/* Fallback to ct_users by column name if order snapshot incomplete */
	if ($client_name === '' || $client_email === '' || $client_phone === '') {
		$uid = (int)$bookings[4];
		$uRes = @mysqli_query($conn, "SELECT `first_name`,`last_name`,`user_email`,`phone`,`address`,`city`,`state`,`zip`,`notes` FROM `ct_users` WHERE `id` = {$uid} LIMIT 1");
		$uRow = ($uRes && mysqli_num_rows($uRes) > 0) ? mysqli_fetch_assoc($uRes) : null;
		if ($uRow) {
			if ($client_name === '') {
				$client_name = trim((isset($uRow['first_name']) ? $uRow['first_name'] : '') . ' ' . (isset($uRow['last_name']) ? $uRow['last_name'] : ''));
			}
			if ($client_email === '' && !empty($uRow['user_email'])) {
				$client_email = $uRow['user_email'];
			}
			if ($client_phone === '' && !empty($uRow['phone'])) {
				$client_phone = $uRow['phone'];
			}
			if ($client_address === '' && !empty($uRow['address'])) {
				$client_address = $uRow['address'];
			}
			if ($client_city === '' && !empty($uRow['city'])) {
				$client_city = $uRow['city'];
			}
			if ($client_state === '' && !empty($uRow['state'])) {
				$client_state = $uRow['state'];
			}
			if ($client_zip === '' && !empty($uRow['zip'])) {
				$client_zip = $uRow['zip'];
			}
		}
	}
	if ($client_phone === '') {
		$client_phone = 'N/A';
	}
	if ($client_name === '') {
		$client_name = 'Customer';
	}

	/* Doctor / Duration / Notes for invoice */
	$invoice_doctor = '';
	$invoice_duration_mins = 0;
	if (!empty($oci['order_duration'])) {
		$invoice_duration_mins = (int)$oci['order_duration'];
	}
	$invoice_notes = trim((string)$client_notes);

	/*Payment Info — use associative row (column order may change) */
	$payments->order_id=$order_id;
	$payinfo=$payments->readone_payment_details();
	if (!is_array($payinfo)) {
		$payinfo = array();
	}

	$payment_transaction_id = isset($payinfo['transaction_id']) ? $payinfo['transaction_id'] : '';
	$payment_amount = isset($payinfo['amount']) ? $payinfo['amount'] : 0;
	$payment_discount = isset($payinfo['discount']) ? $payinfo['discount'] : 0;
	$payment_taxes = isset($payinfo['taxes']) ? $payinfo['taxes'] : 0;
	$payment_partial_amount = isset($payinfo['partial_amount']) ? $payinfo['partial_amount'] : 0;
	$payment_date = isset($payinfo['payment_date']) ? $payinfo['payment_date'] : '';
	$payment_net_amount = isset($payinfo['net_amount']) ? $payinfo['net_amount'] : 0;
	$payment_freq_discount_amt = isset($payinfo['frequently_discount_amount']) ? $payinfo['frequently_discount_amount'] : 0;
	$payment_freq_type = isset($payinfo['frequently_discount']) ? $payinfo['frequently_discount'] : '';
	$raw_payment_method = isset($payinfo['payment_method']) ? trim((string)$payinfo['payment_method']) : '';

	$payment_method_map = array(
		'Pay At Venue' => isset($label_language_values['cash']) ? $label_language_values['cash'] : 'Cash',
		'pay at venue' => isset($label_language_values['cash']) ? $label_language_values['cash'] : 'Cash',
		'Card Payment' => isset($label_language_values['card_payment']) ? $label_language_values['card_payment'] : 'Card Payment',
		'Card-payment' => isset($label_language_values['card_payment']) ? $label_language_values['card_payment'] : 'Card Payment',
		'Stripe-payment' => 'Card Payment',
		'Stripe-Reccurance' => 'Card Payment',
		'2checkout-payment' => 'Card Payment',
		'Bank Transfer' => 'Bank Transfer',
		'Paypal' => 'Paypal',
		'google-calendar' => 'Google Calendar',
		'Free' => 'Free',
		'Wallet' => 'Wallet',
	);
	if ($raw_payment_method !== '' && isset($payment_method_map[$raw_payment_method])) {
		$payment_method = $payment_method_map[$raw_payment_method];
	} elseif ($raw_payment_method !== '') {
		$payment_method = $raw_payment_method;
	} else {
		$payment_method = 'N/A';
	}
		
	
	/* Booking Details */
	$booking_info_details=array();
	$all_booking_details=array();
	
	$booking->order_id = $order_id;
	$bookings_info = $booking->readall_bookings();	
	if($bookings_info && $bookings_info->num_rows > 0){
		while($row=mysqli_fetch_array($bookings_info)){
			$all_booking_details[]=$row;
		}
	}

	/* Resolve doctor name from first booking staff assignment */
	if (!empty($all_booking_details[0]['staff_ids'])) {
		$staffRaw = trim((string)$all_booking_details[0]['staff_ids']);
		$staffParts = preg_split('/\s*,\s*/', $staffRaw);
		$doctorNames = array();
		foreach ($staffParts as $staffIdPart) {
			$staffId = (int)$staffIdPart;
			if ($staffId <= 0) {
				continue;
			}
			$stRes = @mysqli_query($conn, "SELECT `fullname` FROM `ct_admin_info` WHERE `id` = {$staffId} LIMIT 1");
			$stRow = ($stRes && mysqli_num_rows($stRes) > 0) ? mysqli_fetch_assoc($stRes) : null;
			if ($stRow && !empty($stRow['fullname'])) {
				$doctorNames[] = $stRow['fullname'];
			}
		}
		$invoice_doctor = implode(', ', $doctorNames);
	}
	if ($invoice_doctor === '') {
		$invoice_doctor = 'N/A';
	}

	$service_price_sum=0;
	foreach($all_booking_details as $book_info){
		/* Service title by column name — numeric index broke after external_service_id */
		$sid = (int)$book_info['service_id'];
		$svcRes = @mysqli_query($conn, "SELECT `title`,`price`,`duration` FROM `ct_services` WHERE `id` = {$sid} LIMIT 1");
		$svcRow = ($svcRes && mysqli_num_rows($svcRes) > 0) ? mysqli_fetch_assoc($svcRes) : null;
		$service_name = ($svcRow && !empty($svcRow['title'])) ? $svcRow['title'] : ('Service #'.$sid);

		/* Fallback duration from service if order_duration empty */
		if ($invoice_duration_mins <= 0 && $svcRow && !empty($svcRow['duration'])) {
			$dur = trim((string)$svcRow['duration']);
			if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $dur, $dm)) {
				$invoice_duration_mins = ((int)$dm[1]) * 60 + (int)$dm[2];
			} elseif (is_numeric($dur)) {
				$invoice_duration_mins = (int)$dur;
			}
		}

		$unitname = '';
		$methodqty = !empty($book_info['method_unit_qty']) ? $book_info['method_unit_qty'] : 1;
		$rate = isset($book_info['method_unit_qty_rate']) ? (float)$book_info['method_unit_qty_rate'] : 0.0;
		if ($rate <= 0 && $svcRow && isset($svcRow['price']) && (float)$svcRow['price'] > 0) {
			$rate = (float)$svcRow['price'];
		}
		if ($rate <= 0 && count($all_booking_details) === 1 && (float)$payment_amount > 0) {
			$rate = (float)$payment_amount;
		}
		$service_price = $general->ct_price_format_for_pdf($rate, $symbol_position, $decimal);

		if (!empty($book_info['method_id']) && (int)$book_info['method_id'] > 0) {
			$smethod->id = $book_info['method_id'];
			$sminfo = $smethod->readone();
			if (is_array($sminfo) && !empty($sminfo[2])) {
				$unitname = $sminfo[2];
			}
			if (!empty($book_info['method_unit_id']) && (int)$book_info['method_unit_id'] > 0) {
				$smunit->units_id = $book_info['method_unit_id'];
				$smunitinfo = $smunit->readone();
				if (is_array($smunitinfo) && !empty($smunitinfo[3])) {
					$unitname = $smunitinfo[3];
				}
			}
		}

		$booking_info_details[] = array(
			"service_name" => $service_name,
			"unitname" => $unitname,
			"methodqty" => $methodqty,
			"service_price" => $service_price
		);
	}

	/* Format duration label */
	if ($invoice_duration_mins > 0) {
		$h = intval($invoice_duration_mins / 60);
		$m = (int)fmod($invoice_duration_mins, 60);
		$hours_lbl = isset($label_language_values['hours']) ? $label_language_values['hours'] : 'hours';
		$mins_lbl = isset($label_language_values['minutes']) ? $label_language_values['minutes'] : 'minutes';
		if ($h > 0 && $m > 0) {
			$invoice_duration_label = $h . ' ' . $hours_lbl . ' ' . $m . ' ' . $mins_lbl;
		} elseif ($h > 0) {
			$invoice_duration_label = $h . ' ' . $hours_lbl;
		} else {
			$invoice_duration_label = $m . ' ' . $mins_lbl;
		}
	} else {
		$invoice_duration_label = 'N/A';
	}
	if ($invoice_notes === '') {
		$invoice_notes = 'N/A';
	}
	/* Keep notes readable on one/two lines */
	if (function_exists('mb_substr')) {
		$invoice_notes_pdf = mb_substr($invoice_notes, 0, 120);
	} else {
		$invoice_notes_pdf = substr($invoice_notes, 0, 120);
	}
	
	/* Addon's details */
	$booking_addon_details=array();
	$all_addon_details=array();
	
	$saddon->order_id=$order_id;
	$sainfo=$saddon->addon_readall();
	
	$sainfosize=($sainfo) ? count((array)$sainfo) : 0;
	if($sainfosize>0){
	while($rows=mysqli_fetch_array($sainfo)){
		$all_addon_details[]=$rows;
	}
	if(!empty($all_addon_details)){
	foreach($all_addon_details as $book_add_info){
	
		$asid = (int)$book_add_info['service_id'];
		$aSvcRes = @mysqli_query($conn, "SELECT `title` FROM `ct_services` WHERE `id` = {$asid} LIMIT 1");
		$aSvcRow = ($aSvcRes && mysqli_num_rows($aSvcRes) > 0) ? mysqli_fetch_assoc($aSvcRes) : null;
		$addon_service_name = ($aSvcRow && !empty($aSvcRow['title'])) ? $aSvcRow['title'] : '';
		
		$saddon->id=$book_add_info['addons_service_id'];
		$addoninfo=$saddon->readone_single();
		$addonname = (is_array($addoninfo) && isset($addoninfo[2])) ? $addoninfo[2] : '';
		$addonqty=$book_add_info['addons_service_qty'];
		
		$addonprice=$general->ct_price_format_for_pdf($book_add_info['addons_service_rate'],$symbol_position,$decimal);
		
		$booking_addon_details[]= array(
		"service_name"=>"$addon_service_name",
		"addonname"=>"$addonname",
		"addonqty"=>"$addonqty",
		"addonprice"=>"$addonprice"		
		);
	
	}
	}
	}
	

	$backgroundimage=SITE_URL."assets/images/background_image_client.jpeg";
	
	if($business_logo!=='' || $business_logo!==null){
		$logo=SITE_URL."assets/images/services/".$business_logo;
	}else{
		
		$logo='';
	}
	
	$client_city_state = '';
	if($client_city != '' && $client_state != ''){
		$client_city_state = $client_city.",".$client_state;
	}elseif($client_city != '' && $client_state == ''){
		$client_city_state = $client_city;
	}elseif($client_city == '' && $client_state != ''){
		$client_city_state = $client_state;
	}
	
	$pdf = new tFPDF();
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->SetFont('DejaVu','',14);
	$pdf->SetMargins(0,0);
	$pdf->SetTopMargin(0);
	$pdf->SetAutoPageBreak(true,0);
	$pdf->AddPage();
	$pdf->SetFillColor(242,242,242);
    $pdf->SetTextColor(102,103,102);
    $pdf->SetDrawColor(128,255,0);
    $pdf->SetLineWidth(0);
   
	$pdf->Cell(210,297,'',0,1,'C',true);
	$pdf->Image($backgroundimage,0,0,210); /* background */
	/* $pdf->Image($logo,20,15,20); */ /* Logo */
	$pdf->SetFont('DejaVu','',12);
	$pdf->Text(25,10,$business_name);
	$pdf->Text(25,15,$business_address);
	$pdf->Text(25,20,$business_city.",".$business_state);
	$pdf->Text(25,25,$business_country);
	$pdf->Text(25,30,$business_email);
	/* $pdf->Text(130,25,$business_phone);*/
	
	$pdf->SetFont('DejaVu','',13);
	$pdf->Text(133,10,$label_language_values['invoice_to']);
	
	$pdf->SetFont('DejaVu','',10);
	$pdf->Text(133,15,ucwords($client_name));
	
	$pdf->SetFont('DejaVu','',9);
	
	/* here first no.is position from left and second is from top ok */
	$pdf->Text(133,20,$client_address);
	$pdf->Text(133,25,$client_city_state);
	$pdf->Text(140,33,$client_phone);
	$pdf->Text(140,38,$client_email);
	
	$pdf->SetFont('DejaVu','',30);
	$pdf->SetTextColor(55,55,55);
	$pdf->Text(30,60,$label_language_values['invoice']);
	$pdf->SetFont('DejaVu','',22);
	$pdf->Text(31,75,"#".strtoupper(date('M',strtotime($invoice_date)))."-".sprintf("%04d",$order_id));

	$pdf->SetFont('DejaVu','',13);
	$pdf->SetTextColor(255,255,255);
	$pdf->Text(98,60 ,$label_language_values['invoice_date']);
	/* $pdf->Text(120 - $pdf->GetStringWidth("Invoice Date")/2,77,"Invoice Date"); */
	/* $pdf->Text(147 - $pdf->GetStringWidth("Invoice Due Date")/2,77,"Invoice Due Date"); */
	$pdf->Text(160,60,$label_language_values['payment_method']);
	/* $pdf->Text(185 - $pdf->GetStringWidth("Payment Method")/2,77,"Payment Method"); */
	
	$pdf->SetFont('DejaVu','',11);
    $pdf->SetTextColor(255,255,255);
	$pdf->Text(100,68,$invoice_date);
	/* $pdf->Text(109 - $pdf->GetStringWidth(date($dateformat,strtotime($bookings[3])))/2,82,$invoice_date); */
	/* $pdf->Text(147 - $pdf->GetStringWidth(date($dateformat,strtotime($bookings[3])))/2,82,$invoice_date); */
	if($payment_method == 'Bank Transfer'){
		$pdf->Text(173-($pdf->GetStringWidth($payment_method)/2),68,strtoupper($payment_method));
	}else{
		$pdf->Text(177-($pdf->GetStringWidth($payment_method)/2),68,strtoupper($payment_method));
	}
	/* $pdf->Text(181 - $pdf->GetStringWidth(strtoupper($payment_method))/2,82,strtoupper($payment_method)); */
	
	$pdf->SetFont('DejaVu','',13);
	$pdf->SetTextColor(55,55,55);
	$pdf->Text(20,107,$label_language_values['service_name']);
	/* $pdf->Text(50,107,"Method"); */
	/* $pdf->Text(100,107,"Unit"); */
	$pdf->Text(135,107,$label_language_values['qty']);
	/* $pdf->Text(150,107,""); */
	$pdf->Text(179,107,$label_language_values['price']);
	
	
	
	$addondetails_startpoint = 122;
	$pdf->SetFont('DejaVu','',11);
	$service_title_pdf = (!empty($booking_info_details[0]['service_name'])) ? $booking_info_details[0]['service_name'] : '';
	$first_qty = (!empty($booking_info_details[0]['methodqty'])) ? $booking_info_details[0]['methodqty'] : '1';
	$first_price = (!empty($booking_info_details[0]['service_price'])) ? $booking_info_details[0]['service_price'] : '';
	$pdf->Text(20,118,$service_title_pdf);
	$pdf->Text(137,118,(string)$first_qty);
	if ($first_price !== '') {
		$pdf->Text(190-$pdf->GetStringWidth($first_price),118,$first_price);
	}
	/* Only service lines — skip method/unit sub-rows (e.g. "Test") */
	$pdf->SetFont('DejaVu','',9);
	$line_idx = 0;
	foreach ($booking_info_details as $book_detail) {
		if ($line_idx === 0) {
			$line_idx++;
			continue;
		}
		if (empty($book_detail['service_name'])) {
			$line_idx++;
			continue;
		}
		$pdf->Text(20, $addondetails_startpoint, $book_detail['service_name']);
		$pdf->Text(137, $addondetails_startpoint, (string)$book_detail['methodqty']);
		$pdf->Text(190 - $pdf->GetStringWidth($book_detail['service_price']), $addondetails_startpoint, $book_detail['service_price']);
		$addondetails_startpoint += 5;
		$line_idx++;
	}

	/* Doctor / Duration / Notes block under service lines */
	$meta_y = $addondetails_startpoint + 4;
	$pdf->SetFont('DejaVu','',10);
	$pdf->SetTextColor(55,55,55);
	$doctor_lbl = isset($label_language_values['staff_name']) ? $label_language_values['staff_name'] : (isset($label_language_values['staff']) ? $label_language_values['staff'] : 'Doctor');
	$duration_lbl = isset($label_language_values['duration']) ? $label_language_values['duration'] : 'Duration';
	$notes_lbl = isset($label_language_values['notes']) ? $label_language_values['notes'] : 'Notes';
	$pdf->Text(20, $meta_y, $doctor_lbl . ': ' . $invoice_doctor);
	$pdf->Text(20, $meta_y + 6, $duration_lbl . ': ' . $invoice_duration_label);
	$pdf->Text(20, $meta_y + 12, $notes_lbl . ': ' . $invoice_notes_pdf);
	$addondetails_startpoint = $meta_y + 18;
	$pdf->SetTextColor(102,103,102);
	
	$addondetails_sttpoint = 0;
	if(!empty($booking_addon_details)){
	$addondetails_sttpoint = $addondetails_startpoint+10;
	$pdf->SetFont('DejaVu','',11);
	$pdf->Text(22,$addondetails_sttpoint-5,"Add-ons");
	$pdf->SetFont('DejaVu','',9);
	
	foreach($booking_addon_details as $booking_addon){	
	
			/* $pdf->Text(20,$addondetails_sttpoint,$booking_addon['service_name']); */
			$pdf->Text(22,$addondetails_sttpoint,$booking_addon['addonname']);
			$pdf->Text(137,$addondetails_sttpoint,$booking_addon['addonqty']);
			/* $pdf->Text(150,$addondetails_sttpoint,$booking_addon['methodqty']); */
			/* $pdf->Text(150,$addondetails_startpoint,$book_detail['service_price']); */
			$pdf->Text(190-$pdf->GetStringWidth($booking_addon["addonprice"]),$addondetails_sttpoint,$booking_addon['addonprice']);
			
			$addondetails_sttpoint=$addondetails_sttpoint+5;
	
	}
	}
	
	$pdf->SetFont('DejaVu','',10);
	
	
	if($addondetails_sttpoint==0) {
		$addondetails_sttpoint = $addondetails_startpoint;
	}
	/* $pdf->Text(160,170,"Frequently Discount"); */
	$pdf->Text(155-$pdf->GetStringWidth($label_language_values['sub_total']),$addondetails_sttpoint+5,$label_language_values['sub_total']);
	if($payment_freq_type == 'O'){
    $fd = "Once";
		}
		elseif($payment_freq_type == 'W'){
			$fd = "Weekly";
		}
		elseif($payment_freq_type == 'B'){
			$fd = "Bi-Weekly";
		}
		elseif($payment_freq_type == 'M'){
			$fd = "Monthly";
		}
		else{
			$fd = "None";
		}
	$pdf->Text(155-$pdf->GetStringWidth($label_language_values['frequently_discount']."(".$fd.")"),$addondetails_sttpoint+10,$label_language_values['frequently_discount']."(".$fd.")");
	$pdf->Text(155-$pdf->GetStringWidth($label_language_values['coupon_discount']),$addondetails_sttpoint+15,$label_language_values['coupon_discount']);
	$pdf->Text(155-$pdf->GetStringWidth($label_language_values['tax']),$addondetails_sttpoint+20,$label_language_values['tax']);
	
	 $printamount=$general->ct_price_format_for_pdf($payment_amount,$symbol_position,$decimal); 
	 $printtaxes=$general->ct_price_format_for_pdf($payment_taxes,$symbol_position,$decimal);	  
	 $printdiscount='-'.$general->ct_price_format_for_pdf($payment_discount,$symbol_position,$decimal);	   
	 $printfrequency='-'.$general->ct_price_format_for_pdf($payment_freq_discount_amt,$symbol_position,$decimal);	   
	 $printnetamount=$general->ct_price_format_for_pdf($payment_net_amount,$symbol_position,$decimal);
	   
	$pdf->SetFont('DejaVu','',10);
	$pdf->Text(190-$pdf->GetStringWidth($printamount),$addondetails_sttpoint+5,$printamount);
	$pdf->Text(190-$pdf->GetStringWidth($printfrequency),$addondetails_sttpoint+10,$printfrequency);
	$pdf->Text(190-$pdf->GetStringWidth($printdiscount),$addondetails_sttpoint+15,$printdiscount);
	$pdf->Text(190-$pdf->GetStringWidth($printtaxes),$addondetails_sttpoint+20,$printtaxes);
	
	$pdf->SetFont('DejaVu','',13);
	$pdf->SetTextColor(255,255,255);
	$total_label = isset($label_language_values['total']) ? $label_language_values['total'] : 'Total';
	$pdf->Text(150-$pdf->GetStringWidth($total_label),265,$total_label);
	$pdf->Text(188-$pdf->GetStringWidth($printnetamount),265,$printnetamount);

	$pdf->SetFont('DejaVu','',12);
	$pdf->SetTextColor(102,103,102);
	
/*	$pdf->Text(23,217,"Payment Information");
	$pdf->SetFont('DejaVu','',8);
	$pdf->Text(23,222,"Please pay for the service on or before ".date($dateformat,strtotime($bookings[3])));*/
	
	$pdf->SetFont('DejaVu','',12);
	$pdf->Text(15,265,$label_language_values['booked_on']." : ");
	/* for booking date and time */
	$book_times;
	if ($time_format == 24) {
		$book_times =  date("H:i", strtotime($bookings[1]));
	} else {
		$book_times = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($bookings[1])));
	}
	$datevar = date($dateformat,strtotime($bookings[1]));
	$pdf->Text($pdf->GetStringWidth(($label_language_values['booked_on']))+15,265,date($dateformat,strtotime($bookings[1])));
	$pdf->Text($pdf->GetStringWidth(($label_language_values['booked_on']))+ $pdf->GetStringWidth(($datevar)) + 18,265,$book_times);
	
	/*  
		$pdf->Text(23,240,"THANK YOU FOR YOUR BUSINESS!");
		$pdf->SetFont('DejaVu','',8);
		$pdf->Text(150,240,"Company Director");
		$pdf->SetFont('DejaVu','',14);
		$pdf->Text(130,250,"For ".$business_name); 
	*/
	/* Discard any accidental buffered output (notices, whitespace) before PDF headers */
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	$pdf->Output("#".$invoice_number.".pdf","D");
	exit;