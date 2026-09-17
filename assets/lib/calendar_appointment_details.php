<?php    

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

session_start();
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_services.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_services_methods_units.php');
include(dirname(dirname(dirname(__FILE__)))."/objects/class_adminprofile.php");
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_services_addon.php');
include_once(dirname(dirname(dirname(__FILE__))) . '/header.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_booking.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_users.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_payments.php');
include(dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_general.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_dayweek_avail.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_off_days.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_front_first_step.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_offbreaks.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_offtimes.php');

$database = new cleanto_db();
$conn = $database->connect();
$database->conn = $conn;
$general=new cleanto_general();
$general->conn=$conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$symbol_position=$settings->get_option('ct_currency_symbol_position');
$decimal=$settings->get_option('ct_price_format_decimal_places');
$timeformat = $settings->get_option('ct_time_format');
$dateformat = $settings->get_option('ct_date_picker_date_format');
$service = new cleanto_services();
$service_method_unit = new cleanto_services_methods_units();
$service_addon = new cleanto_services_addon();
$booking = new cleanto_booking();
$payment = new cleanto_payments();
$user = new cleanto_users();
$obj_week_day = new cleanto_dayweek_avail();
$obj_week_day->conn = $conn;
$service->conn = $conn;
$booking->conn = $conn;
$user->conn = $conn;
$payment->conn = $conn;
$offday=new cleanto_provider_off_day();
$offday->conn = $conn;

$first_step=new cleanto_first_step();
$first_step->conn=$conn;

$objoffbreaks = new cleanto_offbreaks();
$objoffbreaks->conn = $conn;

$obj_offtime = new cleanto_offtimes();
$obj_offtime->conn = $conn;																			 
$appointment_detail = array();
$order_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$is_doctor_readonly = (isset($_SESSION['ct_staffid']) && !isset($_SESSION['ct_adminid']));
$doctor_staff_id = $is_doctor_readonly ? (int)$_SESSION['ct_staffid'] : 0;
/*CHECK FOR VC AND PARKING STATUS*/
$global_vc_status = $settings->get_option('ct_vc_status');
$global_p_status = $settings->get_option('ct_p_status');
/*CHECK FOR VC AND PARKING STATUS END*/

$objadmin = new cleanto_adminprofile();
$objadmin->conn=$conn;

$lang = $settings->get_option("ct_language");
$label_language_values = array();
$language_label_arr = $settings->get_all_labelsbyid($lang);

if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "")
{
	$default_language_arr = $settings->get_all_labelsbyid("en");
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
	$default_language_arr = $settings->get_all_labelsbyid("en");
    
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

/* NEW */
$book_detail = $booking->get_booking_details_appt($order_id);
if (!$book_detail) {
	header('HTTP/1.1 404 Not Found');
	echo json_encode(array('error' => 'Appointment not found'));
	die();
}

/* Doctor may only view appointments assigned to them */
if ($is_doctor_readonly) {
	$assigned = isset($book_detail['staff_ids']) ? $book_detail['staff_ids'] : '';
	$assignedIds = array_filter(array_map('intval', explode(',', (string)$assigned)));
	if (!in_array($doctor_staff_id, $assignedIds, true)) {
		header('HTTP/1.1 403 Forbidden');
		echo json_encode(array('error' => 'Forbidden'));
		die();
	}
}
$appointment_detail['readonly'] = $is_doctor_readonly ? true : false;

$appointment_detail['id'] = $order_id;
$appointment_detail['recurring_id'] = $book_detail[14];
$appointment_detail['booking_price'] = $general->ct_price_format($book_detail[2],$symbol_position,$decimal);
$appointment_detail['recurrence_status'] = $book_detail[16];
$appointment_detail['appointment_starttime'] = str_replace($english_date_array,$selected_lang_label,date($dateformat, strtotime($book_detail[1])));
if($timeformat == 12){
    $appointment_detail['appointment_start_time'] = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($book_detail[1])));
}
else
{
    $appointment_detail['appointment_start_time'] = date("H:i", strtotime($book_detail[1]));
}
/* methods */ 
// SAHIL ADD THE CODE.
$units = $label_language_values['none'];
$methodname = $label_language_values['none'];

$hh = $booking->get_methods_ofbookings($order_id);
$count_methods = mysqli_num_rows($hh);

$method_titles = [];

if ($count_methods > 0) {
    if ($hh->num_rows > 0) {
        while ($jj = mysqli_fetch_array($hh)) {
            if ($units == $label_language_values['none']) {
                $units = $jj['units_title'] . "-" . $jj['qtys'];
            } else {
                $units = $units . "," . $jj['units_title'] . "-" . $jj['qtys'];
            }

            if (!in_array($jj['method_title'], $method_titles)) {
                $method_titles[] = $jj['method_title'];
            }
        }
    }
}



$addons = $label_language_values['none'];
$hh = $booking->get_addons_ofbookings($order_id);
if($hh->num_rows > 0){
while($jj = mysqli_fetch_array($hh)){
    if($addons == $label_language_values['none']){
        $addons = $jj['addon_service_name']."-".$jj['addons_service_qty'];
    }
    else
    {
        $addons = $addons.",".$jj['addon_service_name']."-".$jj['addons_service_qty'];
    }
}
}

// Combine all method titles into a single string SAHIL ADD A CODE.
$methodname = implode(", ", $method_titles);
$appointment_detail['method_title'] = $methodname !== '' ? $methodname : (isset($label_language_values['none']) ? $label_language_values['none'] : 'None');
$appointment_detail['unit_title'] = $units;
$appointment_detail['addons_title'] = $addons;
$appointment_detail['service_title'] = $book_detail['service_title'];
$appointment_detail['gc_event_id'] = $book_detail[9];
$appointment_detail['gc_staff_event_id'] = $book_detail['gc_staff_event_id'];
$appointment_detail['staff_ids'] = $book_detail['staff_ids'];
$appointment_detail['edit_details'] = '';
 
	$ccnames = explode(" ",$book_detail[3]);
	$cnamess = array_filter($ccnames);
	$client_name = array_values($cnamess);
	if(sizeof((array)$client_name)>0){
		if($client_name[0]!=""){ 	
			$client_first_name =  $client_name[0];
		}else{
			$client_first_name = "";
		} 
		
		if(isset($client_name[1]) && $client_name[1]!=""){ 	
			$client_last_name =  $client_name[1]; 
		}else{
			$client_last_name = "";
		} 
	}else{
		$client_first_name = "";
		$client_last_name = "";
	}
	
	if($client_first_name !="" || $client_last_name !=""){ 
		$appointment_detail['client_name'] = trim($client_first_name . " ".$client_last_name);
	}else{
		$appointment_detail['client_name'] = "";
	} 


$fetch_phone =  strlen($book_detail[7]);
if($fetch_phone >= 6){
	$appointment_detail['client_phone'] = $book_detail[7];
}else{
	$appointment_detail['client_phone'] = "";
}
$appointment_detail['client_email'] = $book_detail[4];
$tem = array(
	'notes' => '',
	'vc_status' => '-',
	'p_status' => '-',
	'address' => '',
	'city' => '',
	'state' => '',
	'zip' => '',
	'contact_status' => '',
);
$personal_raw = isset($book_detail[5]) ? $book_detail[5] : '';
if ($personal_raw !== '' && $personal_raw !== null) {
	$decoded = @base64_decode($personal_raw, true);
	if ($decoded === false) {
		$decoded = $personal_raw;
	}
	$temppp = @unserialize($decoded);
	if ($temppp === false) {
		$temppp = @unserialize($personal_raw);
	}
	if (is_array($temppp)) {
		$tem = array_merge($tem, $temppp);
	}
}
$tem = str_replace('\\', '', $tem);

if($tem['notes']!=""){
	$finalnotes = $tem['notes'];
}else{
	$finalnotes = "";
}
$vc_status = $tem['vc_status'];

if($vc_status == 'N'){
	$final_vc_status = $label_language_values['no'];
}
elseif($vc_status == 'Y'){
	$final_vc_status = $label_language_values['yes'];
}else{
	$final_vc_status = "-";
}
$p_status = $tem['p_status'];
if($p_status == 'N'){
	$final_p_status = $label_language_values['no'];
}
elseif($p_status == 'Y'){
	$final_p_status = $label_language_values['yes'];
}else{
	$final_p_status = "-";
}

if($tem['address']!="" || $tem['city']!="" || $tem['zip']!="" || $tem['state']!=""  ){ 	
	$app_address ="";
	$app_city ="";
	$app_zip ="";
	$app_state ="";
	if($tem['address']!=""){ $app_address = $tem['address'].", " ; } 
	if($tem['city']!=""){ $app_city = $tem['city'].", " ; } 
	if($tem['zip']!=""){ $app_zip = $tem['zip'].", " ; } 
	if($tem['state']!=""){ $app_state = $tem['state'] ; } 

	$temper = $app_address.$app_city.$app_zip.$app_state;
	$temss = rtrim($temper,", ");
	$appointment_detail['client_address'] = $temss;

}else{
	$appointment_detail['client_address'] = "";
}

/* $appointment_detail['client_address'] = " : ".$tem['address'].", ".$tem['city'].", ".$tem['zip'].", ".$tem['state']; */
$booking_duration = $book_detail['order_duration'];
if($booking_duration != 0){
	$hours = intval($booking_duration/60);
	$minutes = fmod( $booking_duration ,60);

	$appointment_detail['booking_duration'] = $hours." ".$label_language_values['hours']." ".$minutes." ".$label_language_values['minutes'];
}else{
	$appointment_detail['booking_duration'] = "";
}

$appointment_detail['vaccum_cleaner'] = $final_vc_status;
$appointment_detail['parking'] = $final_p_status;
$appointment_detail['client_notes'] = $finalnotes;
$appointment_detail['contact_status'] = isset($tem['contact_status']) ? $tem['contact_status'] : '';
$appointment_detail['global_vc_status'] = $global_vc_status;
$appointment_detail['global_p_status'] = $global_p_status;
if ($settings->get_option('ct_partial_deposit_status') == 'Y')
{
  $appointment_detail['partial_deposit_status'] = "Yes";
}else{
  $appointment_detail['partial_deposit_status'] = "No";
}																									 
$payment_status = strtolower($book_detail[6]);
if($payment_status == "pay at venue"){
	$payment_status = ucwords($label_language_values['pay_locally']);
}else{
	$payment_status = ucwords($payment_status);
}
$appointment_detail['payment_type'] = $payment_status;

if ($book_detail[0] == 'A') {
    $status = $label_language_values['pending'];
	$appointment_detail['reason_view_status'] = "hide";
	$appointment_detail['reject_reason'] = "";
} elseif ($book_detail[0] == 'C') {
    $status = $label_language_values['confirmed'];
	$appointment_detail['reason_view_status'] = "hide";
	$appointment_detail['reject_reason'] = "";
} elseif ($book_detail[0] == 'R') {
    $status = $label_language_values['rejected'];
	$appointment_detail['reason_view_status'] = "show";
	$appointment_detail['reject_reason'] = !empty($book_detail['reject_reason']) ? $book_detail['reject_reason'] : "";
} elseif ($book_detail[0] == 'RS') {
    $status = $label_language_values["rescheduled"];
	$appointment_detail['reason_view_status'] = "show";
	$appointment_detail['reject_reason'] = !empty($book_detail['reject_reason']) ? $book_detail['reject_reason'] : "";
} elseif ($book_detail[0] == 'CC') {
    $status =$label_language_values['cancelled_by_client'];
	$appointment_detail['reason_view_status'] = "show";
	$appointment_detail['reject_reason'] = !empty($book_detail['reject_reason']) ? $book_detail['reject_reason'] : "";
} elseif ($book_detail[0] == 'CS') {
    $status = $label_language_values['cancelled_by_service_provider'];
	$appointment_detail['reason_view_status'] = "show";
	$appointment_detail['reject_reason'] = !empty($book_detail['reject_reason']) ? $book_detail['reject_reason'] : "";
} elseif ($book_detail[0] == 'CO') {
    $status = $label_language_values['completed'];
	$appointment_detail['reason_view_status'] = "hide";
	$appointment_detail['reject_reason'] = "";
} else {
    $book_detail[0] == 'MN';
    $status = $label_language_values['mark_as_no_show'];
	$appointment_detail['reason_view_status'] = "hide";
	$appointment_detail['reject_reason'] = "";
}
$appointment_detail['booking_status'] = $book_detail[0];
if($book_detail[0] == "C"){
    $appointment_detail['hider'] = "c";
}
else
{
    $appointment_detail['hider'] = "r";
}
$booking_day = date("Y-m-d", strtotime($book_detail[1]));
$current_day = date("Y-m-d");
if ($current_day > $booking_day)
{
    $appointment_detail['past'] = "Yes";
}
else
{
    $appointment_detail['past'] = "No";
}

$get_staff_services = $objadmin->readall_staff_booking();
$booking->order_id = $order_id;
$get_staff_assignid = explode(",",$booking->fetch_staff_of_booking());

$staff_html = "";
$staff_html .= "<select id='staff_select' class='selectpicker ct-bd-staff-select' data-live-search='true' multiple data-actions-box='true' data-orderid='".$order_id."'>";

$booking->booking_date_time = $book_detail[1];
$staff_status = $booking->booked_staff_status();
$staff_status_arr = explode(",",$staff_status);

foreach($get_staff_services as $staff_details){
	$i = "no";
	$staffname = $staff_details['fullname'];
	$staffid = $staff_details['id'];
	$time_format = $settings->get_option('ct_time_format');
 	$booking->booking_date_time = $book_detail[1];
	
	$booking_date_time = $booking->booking_date_time;
	$booking_date = date("Y-m-d",strtotime($booking_date_time));
	$off_day_result = $first_step->check_off_day($booking_date,$staffid);
	
   	$timezonediff=0;
   	if(is_numeric(strpos($timezonediff,'-'))){
			$timediffmis = str_replace('-','',$timezonediff)*60;
			$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}else{
			$timediffmis = str_replace('+','',$timezonediff)*60;
			$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));	
		}
		$providerschedule_type_result = $obj_week_day->get_schedule_type_according_provider($staffid);
		$providerschedule_type = $providerschedule_type_result[7];
    
    if ($providerschedule_type == 'weekly') {
      $week_id = 1;
    } else {
      $week_id = $first_step->get_week_of_month_by_date(date('Y-m-d', strtotime($booking_date_time)));
    }

		/* if calendar starting date is missing then it will take starting date to current date */
    if ($booking_date_time == '') {
      $day_id = date('N', $currDateTime_withTZ);
    } else {
      $day_id = date('N', strtotime($booking_date_time));
    }
    $get_weekday_slots_result = $first_step->get_weekday_slots($day_id,$week_id,$staffid);

    if(mysqli_num_rows($get_weekday_slots_result) > 0){
			$time_slots = mysqli_fetch_assoc($get_weekday_slots_result);
			
			$day_start_time_strtotime = strtotime($booking_date." ".$time_slots["day_start_time"]);
			$day_end_time_strtotime = strtotime($booking_date." ".$time_slots["day_end_time"]);

			if(strtotime($booking_date_time) >= $day_start_time_strtotime && strtotime($booking_date_time) < $day_end_time_strtotime){
				$get_day_breaks_result = $first_step->get_day_break($week_id,$day_id,$staffid);

				$get_day_break_bool = true;
				if(mysqli_num_rows($get_day_breaks_result) > 0){
					while($get_day_breaks = mysqli_fetch_assoc($get_day_breaks_result)){
						$get_day_break_start = strtotime($booking_date." ".$get_day_breaks["break_start"]);
						$get_day_break_end = strtotime($booking_date." ".$get_day_breaks["break_end"]);
						if(strtotime($booking_date_time) >= $get_day_break_start && strtotime($booking_date_time) < $get_day_break_end){
							$get_day_break_bool = false;
						}
					}
				}
				if($get_day_break_bool){
					$get_provider_offtime_result = $first_step->get_provider_offtime($staffid);
					$get_provider_offtime_bool = true;
					if(!empty($get_provider_offtime_result)){
						foreach($get_provider_offtime_result as $provider_offtime){
							$get_provider_offtime_start = $provider_offtime["offtime_start"];
							$get_provider_offtime_end = $provider_offtime["offtime_end"];
							if(strtotime($booking_date_time) >= strtotime($get_provider_offtime_start) && strtotime($booking_date_time) < strtotime($get_provider_offtime_end)){
								$get_provider_offtime_bool = false;
							}
						}
					}
					if($get_provider_offtime_bool){
						$s_s = "";
						if(in_array($staffid,$staff_status_arr)){
							$s_s = "fa fa-calendar-check-o";
						}
						
						if(in_array($staffid,$get_staff_assignid)){
							$i = "yes";
						}
						if($i == "yes") {
	
							$staff_html .= "<option selected='selected' data-icon='".$s_s." booking-staff-assigned' value='$staffid'>$staffname</option>";
						} else {
			
							$staff_html .= "<option data-icon='".$s_s." booking-staff-assigned' value='$staffid'>$staffname</option>";
						}
					}
				}
			}
		}
}

$staff_html .= "</select>";
if ($is_doctor_readonly) {
	$appointment_detail['staff'] = '';
} else {
	$staff_html .= "<a href='javascript:void(0)' data-orderid='".$order_id."' class='save_staff_booking edit_staff btn btn-primary ct-bd-staff-save'>Save</a>";
	$appointment_detail['staff'] = $staff_html;
}

$reqCheck = mysqli_query($conn, "SELECT `change_request_status`, `cancel_reason`, `reschedule_reason`, `reschedule_requested_date` FROM `ct_bookings` WHERE `order_id` = " . (int)$order_id . " LIMIT 1");
$reqRow = $reqCheck ? mysqli_fetch_assoc($reqCheck) : null;
$changeRequestHtml = "";
if ($reqRow) {
    if ($reqRow['change_request_status'] === 'CANCEL_REQUESTED') {
        $reason = htmlspecialchars($reqRow['cancel_reason'] ?: 'No reason specified');
        if ($is_doctor_readonly) {
            $changeRequestHtml = "<div class='alert alert-danger' style='margin-top:10px;'><strong><i class='fa fa-exclamation-triangle'></i> Customer Requested Cancellation</strong><br>Reason: {$reason}<br><em>Awaiting Root Admin approval.</em></div>";
        } else {
            $changeRequestHtml = "<div class='alert alert-danger' style='margin-top:10px;'><strong><i class='fa fa-exclamation-triangle'></i> Customer Requested Cancellation</strong><br>Reason: {$reason}<br><div style='margin-top:8px;'><button type='button' class='btn btn-xs btn-danger ct-admin-approve-cancel' data-order_id='{$order_id}'>Approve & Cancel Appointment</button> <button type='button' class='btn btn-xs btn-default ct-admin-reject-request' data-order_id='{$order_id}'>Dismiss Request</button></div></div>";
        }
    } elseif ($reqRow['change_request_status'] === 'RESCHEDULE_REQUESTED') {
        $reqDate = htmlspecialchars($reqRow['reschedule_requested_date']);
        $reason = htmlspecialchars($reqRow['reschedule_reason'] ?: 'No reason specified');
        if ($is_doctor_readonly) {
            $changeRequestHtml = "<div class='alert alert-warning' style='margin-top:10px;'><strong><i class='fa fa-clock-o'></i> Customer Requested Reschedule</strong><br>Proposed Date/Time: <b>{$reqDate}</b><br>Reason: {$reason}<br><em>Awaiting Root Admin approval.</em></div>";
        } else {
            $changeRequestHtml = "<div class='alert alert-warning' style='margin-top:10px;'><strong><i class='fa fa-clock-o'></i> Customer Requested Reschedule</strong><br>Proposed Date/Time: <b>{$reqDate}</b><br>Reason: {$reason}<br><div style='margin-top:8px;'><button type='button' class='btn btn-xs btn-success ct-admin-approve-reschedule' data-order_id='{$order_id}' data-newdate='{$reqDate}'>Approve Reschedule</button> <button type='button' class='btn btn-xs btn-default ct-admin-reject-request' data-order_id='{$order_id}'>Dismiss Request</button></div></div>";
        }
    }
}
$appointment_detail['change_request_html'] = $changeRequestHtml;

echo json_encode($appointment_detail);
die();