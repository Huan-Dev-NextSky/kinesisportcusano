<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	session_start(); 
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_connection.php');
	include(dirname(dirname(dirname(__FILE__))).'/header.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_front_first_step.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_setting.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_dayweek_avail.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_services_methods_units.php');
	include(dirname(dirname(dirname(__FILE__))).'/objects/class_booking.php');					 
	if ( is_file(dirname(dirname(dirname(__FILE__))).'/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php')){
		require_once dirname(dirname(dirname(__FILE__))).'/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php';
	}
	include(dirname(dirname(dirname(__FILE__)))."/objects/class_gc_hook.php");
	  
	$database= new cleanto_db();
	$conn=$database->connect();
	$database->conn=$conn;
	
	$gc_hook = new cleanto_gcHook();
	$gc_hook->conn = $conn;
	
	$first_step=new cleanto_first_step();
	$first_step->conn=$conn;
	
	$week_day_avail=new cleanto_dayweek_avail();
	$week_day_avail->conn=$conn;
	
	$booking=new cleanto_booking();
	$booking->conn=$conn;
	
	$services_methods_units=new cleanto_services_methods_units();
	$services_methods_units->conn=$conn;
	$setting=new cleanto_setting();
	$setting->conn=$conn;
	$date_format = $setting->get_option('ct_date_picker_date_format');
	$time_interval = $setting->get_option('ct_time_interval');	
	$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
	$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
	$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
	$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
	$ct_calendar_firstDay = $setting->get_option('ct_calendar_firstDay');
	$booking_padding_time = $setting->get_option('ct_booking_padding_time');
	$lang = "";
	if(isset($_SESSION['current_lang'])){
		$lang = $_SESSION['current_lang'];
	}else{
		$lang = $setting->get_option("ct_language");
	}
$label_language_values = array();
$language_label_arr = $setting->get_all_labelsbyid($lang);

if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "" || $language_label_arr[6] != "")
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
	if($language_label_arr[6] != ''){
		$label_decode_front_form_errors = base64_decode($language_label_arr[6]);
	}else{
		$label_decode_front_form_errors = base64_decode($default_language_arr[6]);
	}
	
	$label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);
	$label_decode_extra_unserial = unserialize($label_decode_extra);
	$label_decode_front_form_errors_unserial = unserialize($label_decode_front_form_errors);
    
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial,$label_decode_front_form_errors_unserial);
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
	$label_decode_front_form_errors = base64_decode($default_language_arr[6]);
	
	$label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);
	$label_decode_extra_unserial = unserialize($label_decode_extra);
	$label_decode_front_form_errors_unserial = unserialize($label_decode_front_form_errors);
    
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial,$label_decode_front_form_errors_unserial);
	foreach($label_language_arr as $key => $value){
		$label_language_values[$key] = urldecode($value);
	}
}

/*new file include*/
include(dirname(dirname(dirname(__FILE__))).'/assets/lib/date_translate_array.php');

if(isset($_POST['admin_approve_cancel'])){
	if (!isset($_SESSION['ct_adminid'])) {
		echo "unauthorized";
		exit;
	}
	$order_id = (int)$_POST['order_id'];
	$now = date('Y-m-d H:i:s');

	$bkRes = mysqli_query($conn, "SELECT `gc_event_id`, `gc_staff_event_id`, `staff_ids`, `reject_reason`, `cancel_reason`, `change_request_status`, `client_id` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
	$bkRow = $bkRes ? mysqli_fetch_assoc($bkRes) : null;
	if (!$bkRow || $bkRow['change_request_status'] !== 'CANCEL_REQUESTED') {
		echo "invalid_request";
		exit;
	}
	$cancel_reason = '';
	$gc_event_id = '';
	$gc_staff_event_id = '';
	$pid = '';
	if ($bkRow) {
		$cancel_reason = !empty($bkRow['cancel_reason']) ? $bkRow['cancel_reason'] : (isset($bkRow['reject_reason']) ? $bkRow['reject_reason'] : '');
		$gc_event_id = isset($bkRow['gc_event_id']) ? $bkRow['gc_event_id'] : '';
		$gc_staff_event_id = isset($bkRow['gc_staff_event_id']) ? $bkRow['gc_staff_event_id'] : '';
		$pid = isset($bkRow['staff_ids']) ? $bkRow['staff_ids'] : '';
		$_POST['gc_event_id'] = $gc_event_id;
		$_POST['gc_staff_event_id'] = $gc_staff_event_id;
		$_POST['pid'] = $pid;
	}

	$reasonEsc = mysqli_real_escape_string($conn, $cancel_reason);
	mysqli_query($conn, "UPDATE `ct_bookings` SET `booking_status` = 'CC', `reject_reason` = '{$reasonEsc}', `change_request_status` = 'APPROVED', `kinesis_sync_status` = 'CANCEL_PENDING', `lastmodify` = '{$now}' WHERE `order_id` = {$order_id}");
	mysqli_query($conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'CANCEL_PENDING', `sync_action` = 'CANCEL', `updated_at` = '{$now}' WHERE `local_order_id` = {$order_id}");

	/* Cancel on Google Calendar only after admin approval */
	if ($gc_hook->gc_purchase_status() == 'exist') {
		if ($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y') {
			@$gc_hook->gc_cancel_reject_booking_hook();
		}
	}
	
	if ($setting->get_option('kinesis_api_status') === 'Y') {
		require_once dirname(dirname(dirname(__FILE__))) . '/integrations/awwapi/AwwAppointmentSync.php';
		$apptSync = new AwwAppointmentSync($conn);
		$apptSync->syncSingleBooking($order_id);
	}

	$oci = @mysqli_query($conn, "SELECT `client_email`, `client_name` FROM `ct_order_client_info` WHERE `order_id` = {$order_id} LIMIT 1");
	if ($oci && ($ociRow = mysqli_fetch_assoc($oci)) && !empty($ociRow['client_email'])) {
		$subj = "Cancellation approved - Order #{$order_id}";
		$body = "Hello " . $ociRow['client_name'] . ",\n\nYour cancellation request for order #{$order_id} has been approved.\n\nThank you.";
		$headers = "From: " . $setting->get_option('ct_email_sender_address') . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
		@mail($ociRow['client_email'], $subj, $body, $headers);
	}
	echo "success";
	exit;
}

if(isset($_POST['admin_approve_reschedule'])){
	if (!isset($_SESSION['ct_adminid'])) {
		echo "unauthorized";
		exit;
	}
	$order_id = (int)$_POST['order_id'];
	$now = date('Y-m-d H:i:s');

	$bkRes = mysqli_query($conn, "SELECT `gc_event_id`, `gc_staff_event_id`, `staff_ids`, `booking_date_time`, `change_request_status`, `reschedule_requested_date` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
	$bkRow = $bkRes ? mysqli_fetch_assoc($bkRes) : null;
	if (!$bkRow || $bkRow['change_request_status'] !== 'RESCHEDULE_REQUESTED' || empty($bkRow['reschedule_requested_date'])) {
		echo "invalid_request";
		exit;
	}
	$newDateRaw = $bkRow['reschedule_requested_date'];
	$newDate = mysqli_real_escape_string($conn, $newDateRaw);
	global $dates, $timess, $order_duration;
	$order_duration = 30;
	$durRes = mysqli_query($conn, "SELECT s.`duration` FROM `ct_bookings` b LEFT JOIN `ct_services` s ON b.`service_id` = s.`id` WHERE b.`order_id` = {$order_id} LIMIT 1");
	if ($durRes && ($durRow = mysqli_fetch_assoc($durRes)) && !empty($durRow['duration'])) {
		$dur = trim($durRow['duration']);
		if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $dur, $dm)) {
			$order_duration = ((int)$dm[1]) * 60 + (int)$dm[2];
		} elseif (is_numeric($dur)) {
			$order_duration = (int)$dur;
		}
		if ($order_duration <= 0) { $order_duration = 30; }
	}
	if ($bkRow) {
		$_POST['gc_event_id'] = isset($bkRow['gc_event_id']) ? $bkRow['gc_event_id'] : '';
		$_POST['gc_staff_event_id'] = isset($bkRow['gc_staff_event_id']) ? $bkRow['gc_staff_event_id'] : '';
		$_POST['pid'] = isset($bkRow['staff_ids']) ? $bkRow['staff_ids'] : '';
	}
	$dates = date('Y-m-d', strtotime($newDateRaw));
	$timess = date('H:i:s', strtotime($newDateRaw));

	mysqli_query($conn, "UPDATE `ct_bookings` SET `booking_date_time` = '{$newDate}', `booking_status` = 'RS', `change_request_status` = 'APPROVED', `kinesis_sync_status` = 'UPDATE_PENDING', `lastmodify` = '{$now}' WHERE `order_id` = {$order_id}");
	mysqli_query($conn, "UPDATE `ct_gcal_kinesis_sync` SET `event_start` = '{$newDate}', `sync_status` = 'PENDING', `sync_action` = 'UPDATE', `updated_at` = '{$now}' WHERE `local_order_id` = {$order_id}");

	/* Update Google Calendar only after admin approval */
	if ($gc_hook->gc_purchase_status() == 'exist') {
		if ($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y') {
			@$gc_hook->gc_reschedule_booking_ajax_hook();
		}
	}
	
	if ($setting->get_option('kinesis_api_status') === 'Y') {
		require_once dirname(dirname(dirname(__FILE__))) . '/integrations/awwapi/AwwAppointmentSync.php';
		$apptSync = new AwwAppointmentSync($conn);
		$apptSync->syncSingleBooking($order_id);
	}

	$oci = @mysqli_query($conn, "SELECT `client_email`, `client_name` FROM `ct_order_client_info` WHERE `order_id` = {$order_id} LIMIT 1");
	if ($oci && ($ociRow = mysqli_fetch_assoc($oci)) && !empty($ociRow['client_email'])) {
		$subj = "Reschedule approved - Order #{$order_id}";
		$body = "Hello " . $ociRow['client_name'] . ",\n\nYour reschedule request for order #{$order_id} has been approved.\nNew date/time: {$newDateRaw}\n\nThank you.";
		$headers = "From: " . $setting->get_option('ct_email_sender_address') . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
		@mail($ociRow['client_email'], $subj, $body, $headers);
	}
	echo "success";
	exit;
}

if(isset($_POST['admin_reject_request'])){
	if (!isset($_SESSION['ct_adminid'])) {
		echo "unauthorized";
		exit;
	}
	$order_id = (int)$_POST['order_id'];
	$now = date('Y-m-d H:i:s');
	$bkRes = mysqli_query($conn, "SELECT `change_request_status` FROM `ct_bookings` WHERE `order_id` = {$order_id} LIMIT 1");
	$bkRow = $bkRes ? mysqli_fetch_assoc($bkRes) : null;
	if (!$bkRow || ($bkRow['change_request_status'] !== 'CANCEL_REQUESTED' && $bkRow['change_request_status'] !== 'RESCHEDULE_REQUESTED')) {
		echo "invalid_request";
		exit;
	}
	mysqli_query($conn, "UPDATE `ct_bookings` SET `change_request_status` = 'REJECTED', `lastmodify` = '{$now}' WHERE `order_id` = {$order_id}");

	$oci = @mysqli_query($conn, "SELECT `client_email`, `client_name` FROM `ct_order_client_info` WHERE `order_id` = {$order_id} LIMIT 1");
	if ($oci && ($ociRow = mysqli_fetch_assoc($oci)) && !empty($ociRow['client_email'])) {
		$subj = "Request dismissed - Order #{$order_id}";
		$body = "Hello " . $ociRow['client_name'] . ",\n\nYour cancel/reschedule request for order #{$order_id} was dismissed by the administrator. Your booking remains unchanged.\n\nThank you.";
		$headers = "From: " . $setting->get_option('ct_email_sender_address') . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
		@mail($ociRow['client_email'], $subj, $body, $headers);
	}
	echo "success";
	exit;
}

if(isset($_SESSION['staff_id_cal']) && $_SESSION['staff_id_cal']!=""){
	$staff_id = $_SESSION['staff_id_cal'];
}else{
	$staff_id = '1';
}


if(isset($_POST['get_calendar']))
{
	
	?>
	<script>
	jQuery(document).ready(function() {
		jQuery('.ct-tooltipss-ajax').tooltipster({
			animation: 'grow',
			delay: 20,
			theme: 'tooltipster-shadow',
			trigger: 'hover'
		});
	});
	</script>
	<?php 
	$t_zone_value = $setting->get_option('ct_timezone');
	$server_timezone = date_default_timezone_get();
	if(isset($t_zone_value) && $t_zone_value!=''){
		$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
		$timezonediff = $offset/3600;  
	}else{
		$timezonediff =0;
	}
	
	if(is_numeric(strpos($timezonediff,'-'))){
		$timediffmis = str_replace('-','',$timezonediff)*60;
		$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
	}else{
		$timediffmis = str_replace('+','',$timezonediff)*60;
		$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
	}

  	$ct_max_advance_booking_time = $setting->get_option('ct_max_advance_booking_time');
  	$datetime_withmaxtime = strtotime('+'.$ct_max_advance_booking_time.' month',strtotime(date('Y-m-d',$currDateTime_withTZ)));
  
	$month=$_POST['month'];
	$year=$_POST['year'];
	$date = mktime(12, 0, 0, $month, 1, $year);
	$yearss = date("Y",$date);
	$monthss =  date("m",$date);
	$prevmonthlink =  strtotime(date("Y-m-d",$date));
	$currrmonthlink =  strtotime(date("Y-m-d",$currDateTime_withTZ));
  
  	$daysInMonth = date("t", $date);
  	/* calculate the position of the first day in the calendar (sunday = 1st column, etc) */
  	if($ct_calendar_firstDay == '1'){
		$offset = date("N", $date);
  	}else{
		$offset = date("w", $date);
  	}
  	$rows = 1;
  
  	$next_months=strtotime('+1 month', $date);
  	$prev_months=strtotime('-1 month', $date);
  	?>


  	<div class="calendar-header">
		<?php 
		if($currrmonthlink < $prevmonthlink){
			?>
			<a data-istoday="N" class="previous-date previous_next" href="javascript:void(0)" data-next_month="<?php echo date("m", $prev_months); ?>" data-next_month_year="<?php echo date("Y", $prev_months); ?>"><i class="icon-arrow-left icons"></i></a>
			<?php 
		}else{ ?>
			<a class="previous-date" href="javascript:void(0)" ><i class="icon-arrow-left icons"></i></a>
			<?php 
		} ?>
		
		<div class="calendar-title"><?php echo $label_language_values[strtolower(date("F", $date))]; ?></div>
		<div class="calendar-year"><?php echo date("Y", $date); ?></div>
		<?php 
		if(date('M',$datetime_withmaxtime) == date('M',$date) && date('Y',$datetime_withmaxtime) == date('Y',$date)){
			?>
			<a class="next-date" href="javascript:void(0)"><i class="icon-arrow-right icons"></i></a>
			<?php 
		}else{ 	?>
			<a data-istoday="N" class="next-date previous_next" href="javascript:void(0)" data-next_month="<?php echo date("m", $next_months); ?>" data-next_month_year="<?php echo date("Y", $next_months); ?>"><i class="icon-arrow-right icons"></i></a>
			<?php 
		} ?>
	</div>

 	<div class="calendar-body">
		<div class="weekdays fl">
			<?php  if($ct_calendar_firstDay == '0'){ ?>
			<div class="ct-day">
				<span><?php echo $label_language_values['sun']; ?></span>
			</div>
			<?php  } ?>
			<div class="ct-day">
				<span><?php echo $label_language_values['mon']; ?></span>
			</div>
			<div class="ct-day">
				<span><?php echo $label_language_values['tue']; ?></span>
			</div>
			<div class="ct-day">
				<span><?php echo $label_language_values['wed']; ?></span>
			</div>
			<div class="ct-day">
				<span><?php echo $label_language_values['thu']; ?></span>
			</div>
			<div class="ct-day">
				<span><?php echo $label_language_values['fri']; ?></span>
			</div>
			<?php  if($ct_calendar_firstDay == '0'){ ?>
			<div class="ct-day ct-last-day">
				<span><?php echo $label_language_values['sat']; ?></span>
			</div>
			<?php  } ?>
			<?php  if($ct_calendar_firstDay == '1'){ ?>
			<div class="ct-day">
				<span><?php echo $label_language_values['sat']; ?></span>
			</div>
			<div class="ct-day ct-last-day">
				<span><?php echo $label_language_values['sun']; ?></span>
			</div>
			<?php  } ?>
		</div>
  
  		<div class="dates">
		  	<?php 
		  	if($ct_calendar_firstDay == '1'){
				$get_first_day_starting = 2;
		  	}else{
				$get_first_day_starting = 1;
		  	}
		  	for($i = $get_first_day_starting; $i <= $offset; $i++){
		  		?>
				<div class="ct-week hide_previous_dates"></div>
  				<?php 
				}
  			$k = 0;
  			for($day = 1; $day <= $daysInMonth; $day++){
				$selected_dates = $day."-".$monthss."-".$yearss;
				$selected_dates_available = $day."-".$monthss."-".$yearss;
				$cur_dates = date('j-m-Y',$currDateTime_withTZ);
				$s_date = strtotime($selected_dates);
				$c_date = strtotime($cur_dates);
	
				/* COUNT TOTAL AVAILABLE SLOTS */
	
				$t_zone_value = $setting->get_option('ct_timezone');
				$server_timezone = date_default_timezone_get();
				if(isset($t_zone_value) && $t_zone_value!=''){
					$offset_available= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
					$timezonediff = $offset_available/3600;  
				}else{
					$timezonediff =0;
				}
	
				if(is_numeric(strpos($timezonediff,'-'))){
					$timediffmis = str_replace('-','',$timezonediff)*60;
					$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
				}else{
					$timediffmis = str_replace('+','',$timezonediff)*60;
					$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
				} 
				
				$select_time=date('Y-m-d',strtotime($selected_dates_available));
				$start_date = date($select_time,$currDateTime_withTZ);

				/* Get Google Calendar Bookings */
				$providerCalenderBooking = array();
				if($gc_hook->gc_purchase_status() == 'exist'){
					$gc_hook->google_cal_TwoSync_hook();
				}
				/* Get Google Calendar Bookings */
	
				$time_interval = $setting->get_option('ct_time_interval');	
				$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
				$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
				$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
				$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
				$booking_padding_time = $setting->get_option('ct_booking_padding_time');
				$additional_slot = $setting->get_option('ct_additional_slot_time');
				if ($additional_slot == '' || $additional_slot == null || $additional_slot < 0) {
					$additional_slot_timestamp = 0*60;
				}else{
					$additional_slot_timestamp = $additional_slot *60;
				}
				$reschedule = "No";
				$client_order_id = 0;
				$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$staff_id,$client_order_id,$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time); 
				
				$allbreak_counter = 0;	
				$allofftime_counter = 0;
				$allbooked_counter = 0;
				$google_slot_counter = 0;
				$slot_counter = 0;
				$check = 0;
				$week_day_avail_count = $week_day_avail->get_data_for_front_cal();

				if(isset($time_schedule['slots'])){ 
				    if(mysqli_num_rows($week_day_avail_count) > 0)
					{
						if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots']))
						{
							$gap_array = array();
							$gap_array_after = array();

							$index = 0;
							$nextCounter = 0;
							$slot_counter = 0;
							foreach ($time_schedule['booked'] as $book) {
								$gap_array[] = $book;
								$gap_array_after[$index]['startTime'] = $book;
								$gap_array_after[$index]['stopTime'] = $book + $time_schedule['duration_of_booking'][$index]*60 + $additional_slot_timestamp ;
								$index++;
							}

							foreach($time_schedule['slots']  as $slot) 
							{
								/* Checking in GC booked Slots START */
								$curreslotstr = strtotime(date(date('Y-m-d H:i:s',strtotime($select_time.' '.$slot)),$currDateTime_withTZ));
								
								$gccheck = 'N';
								
								if(sizeof((array)$providerCalenderBooking)>0){
									for($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
										if($curreslotstr >= $providerCalenderBooking[$i]['start'] && $curreslotstr < $providerCalenderBooking[$i]['end']){
											$gccheck = 'Y';
											$google_slot_counter++;
										}
									}
								}
								/* Checking in GC booked Slots END */

								$ifbreak = 'N';
								foreach($time_schedule['breaks'] as $daybreak) {
									if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
									   $ifbreak = 'Y';
									   $check = $check+1;
									}
								}
								if($ifbreak=='Y') {  continue; } 
								
								$ifofftime = 'N';
															
								foreach($time_schedule['offtimes'] as $offtime) {
									if(strtotime($selected_dates.' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($selected_dates.' '.$slot) < strtotime($offtime['offtime_end'])) {
									   $ifofftime = 'Y';
									   $check = $check+1;
									}
								 }
								if($ifofftime=='Y') {  continue; }
								
								$complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 
									
							 	if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && in_array($complete_time_slot,$time_schedule['booked'])) {
								 	$check = $check+1;
								 	continue;
							 	}
							 	if (isset($gap_array_after[$nextCounter]['stopTime']) && $complete_time_slot > $gap_array_after[$nextCounter]['stopTime'] ){
									$nextCounter++;
								}

								if( in_array($complete_time_slot,$time_schedule['booked']) && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { 
									if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
										$check = $check+1;
									}
								}else if( (in_array($complete_time_slot,$gap_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { 
									$check = $check+1;
									
								}else if( isset($gap_array_after[$nextCounter]['startTime']) && ( ($complete_time_slot > $gap_array_after[$nextCounter]['startTime'] && $complete_time_slot < $gap_array_after[$nextCounter]['stopTime']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) {
									$check = $check+1;
								} else { 
									if($setting->get_option('ct_time_format')==24){
										$slot_time = date("H:i",strtotime($slot));
										$slotdbb_time = date("H:i",strtotime($slot));
										$ct_time_selected = date("H:i",strtotime($slot));
									}else{
										$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
										$slotdbb_time = date("H:i",strtotime($slot));
										$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA",strtotime($slot)));
									}
								} $slot_counter++; 
							}
							$finals = sizeof((array)$time_schedule['slots'])-$check;
							$available_time_slots = $finals;
						}
						else 
						{
							$available_time_slots =  "0";
						}
					}
				} else{  $available_time_slots =  "0";} 
	
				/* COUNT TOTAL AVAILABLE SLOTS */
				if($ct_calendar_firstDay == '1'){
					if( ($day + $offset - $get_first_day_starting) % 7 == 0 && $day >= 0){
					  $k = $k+7;
					  ?>
					  </div>
					  <div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo $k; ?>"></div>
					  <div class="dates">
					  <?php 
					  $rows++;
					}
				}else{
					if( ($day + $offset - $get_first_day_starting) % 7 == 0 && $day != $get_first_day_starting){
					  $k = $k+7;
					  ?>
					  </div>
					  <div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo $k; ?>"></div>
					  <div class="dates">
					  <?php 
					  $rows++;
					}
				}
	
				if(date('j',$datetime_withmaxtime) <= $day && date('M',$datetime_withmaxtime) == date('M',$date) && date('Y',$datetime_withmaxtime) == date('Y',$date)){
					?>
					<div class="ct-week hide_previous_dates"><?php echo $day; ?></div>
					<?php 
				}else{ 
					$available_text = "";
					if($s_date < $c_date){}
					elseif($available_time_slots <= 0){ 
						$available_text = $label_language_values['none_available'];
					}else{ 
						$available_text =  $available_time_slots." ".$label_language_values['available'];
					}
					?>
					<div title="<?php if($s_date < $c_date){}else{ echo $available_text;} ?>" class="<?php if($s_date < $c_date){}else{ echo "ct-tooltipss-ajax";}?> ct-week <?php  if($c_date == $s_date){ echo 'by_default_today_selected'; } ?> <?php  if($s_date < $c_date){ echo 'hide_previous_dates'; }else{ echo 'selected_datess'.$selected_dates; echo ' remove_selection selected_date';} ?>"  data-id="<?php if($day < 35){echo $k+7; }else{echo $k;} ?>" data-selected_dates="<?php echo $selected_dates; ?>" data-cur_dates="<?php echo $cur_dates; ?>" data-c_date="<?php echo $c_date; ?>" data-s_date="<?php echo $s_date; ?>"><a href="javascript:void(0)"><span><?php echo $day; ?></span></a></div>
					<?php 
				}
			}
  
			if($ct_calendar_firstDay == '1'){
				while( (($day-1) + $offset) <= $rows * 7){ ?>
					<div class="ct-week hide_previous_dates"></div>
					<?php 
					$day++;
				 }
		    }else{
				while( ($day + $offset) <= $rows * 7){ ?>
					<div class="ct-week hide_previous_dates"></div>
					<?php 
					$day++;
			    }
		    } ?>
  		</div>
  		<div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo  $k+7;?>"></div>
  		<div class="today-date">
  			<a class="ct-button nm today_btttn ct-lg-offset-1" data-istoday="Y" data-cur_dates="<?php echo $cur_dates; ?>" data-next_month="<?php echo date("m",$currDateTime_withTZ); ?>" data-next_month_year="<?php echo date("Y",$currDateTime_withTZ); ?>"><?php echo $label_language_values['today']; ?></a>
  			<div class="ct-selected-date-view ct-lg-pull-1"><span class="add_date" data-date=""></span><span class="add_time"></span></div>
  		</div>
  	<?php 
}
if(isset($_POST['get_calendar_on_page_load'])){
	?>
	<script>
	jQuery(document).ready(function() {
		jQuery('.ct-tooltipss-load').tooltipster({
			animation: 'grow',
			delay: 20,
			theme: 'tooltipster-shadow',
			trigger: 'hover'
		});
	});
	</script>
	<?php 
  	$t_zone_value = $setting->get_option('ct_timezone');
	$server_timezone = date_default_timezone_get();
	if(isset($t_zone_value) && $t_zone_value!=''){
		$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
		$timezonediff = $offset/3600;  
	}else{
		$timezonediff =0;
	}
	
	if(is_numeric(strpos($timezonediff,'-'))){
		$timediffmis = str_replace('-','',$timezonediff)*60;
		$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
	}else{
		$timediffmis = str_replace('+','',$timezonediff)*60;
		$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
	} 

		
	list($year, $month, $iNowDay) = explode('-', date('Y-m-d',$currDateTime_withTZ));
	  $ct_max_advance_booking_time = $setting->get_option('ct_max_advance_booking_time');
	  $datetime_withmaxtime = strtotime('+'.$ct_max_advance_booking_time.' month',strtotime(date('Y-m-d',$currDateTime_withTZ)));
	  
  	$date = mktime(12, 0, 0, $month, 1, $year);
  	$yearss = date("Y",$date);
  	$monthss =  date("m",$date);
  	$monthssss =  date("M",$date);
	  
	$daysInMonth = date("t", $date);
	/* calculate the position of the first day in the calendar (sunday = 1st column, etc) */
	if($ct_calendar_firstDay == '1'){
		$offset = date("N", $date);
	}else{
		$offset = date("w", $date);
	}
	$rows = 1; 
	  
	$next_months=strtotime('+1 month', $date);
	$prev_months=strtotime('-1 month', $date);
	?>
	<div class="calendar-header">
		<?php 
		if($monthssss != date('M')){ ?>
			<a data-istoday="N" class="previous-date previous_next" href="javascript:void(0)" data-next_month="<?php echo date("m", $prev_months); ?>" data-next_month_year="<?php echo date("Y", $prev_months); ?>"><i class="icon-arrow-left icons"></i></a>
		<?php 
		}else{ ?>
			<a class="previous-date" href="javascript:void(0)" ><i class="icon-arrow-left icons"></i></a>
		<?php 
		} ?>
		<div class="calendar-title"><?php echo $label_language_values[strtolower(date("F", $date))]; ?></div>
		<div class="calendar-year"><?php echo date("Y", $date); ?></div>
			<a data-istoday="N" class="next-date previous_next" href="javascript:void(0)" data-next_month="<?php echo date("m", $next_months); ?>" data-next_month_year="<?php echo date("Y", $next_months); ?>"><i class="icon-arrow-right icons"></i></a>
		</div>
	 	<div class="calendar-body">
			<div class="weekdays fl">
				<?php  
				if($ct_calendar_firstDay == '0'){ ?>
					<div class="ct-day">
						<span><?php echo $label_language_values['sun']; ?></span>
					</div>
					<?php  
				} ?>
				<div class="ct-day">
					<span><?php echo $label_language_values['mon']; ?></span>
				</div>
				<div class="ct-day">
					<span><?php echo $label_language_values['tue']; ?></span>
				</div>
				<div class="ct-day">
					<span><?php echo $label_language_values['wed']; ?></span>
				</div>
				<div class="ct-day">
					<span><?php echo $label_language_values['thu']; ?></span>
				</div>
				<div class="ct-day">
					<span><?php echo $label_language_values['fri']; ?></span>
				</div>
				<?php  
				if($ct_calendar_firstDay == '0'){ ?>
					<div class="ct-day ct-last-day">
						<span><?php echo $label_language_values['sat']; ?></span>
					</div>
					<?php  
				} ?>
				<?php  
				if($ct_calendar_firstDay == '1'){ ?>
					<div class="ct-day">
						<span><?php echo $label_language_values['sat']; ?></span>
					</div>
					<div class="ct-day ct-last-day">
						<span><?php echo $label_language_values['sun']; ?></span>
					</div>
					<?php  
				} ?>
			</div>
	   		<div class="dates">
			  	<?php 
			 	if($ct_calendar_firstDay == '1'){
					$get_first_day_starting = 2;
			  	}else{
					$get_first_day_starting = 1;
			  	}
			  	for($i = $get_first_day_starting; $i <= $offset; $i++){
	  				?>
					<div class="ct-week hide_previous_dates"></div>
	  				<?php 
	  			}
				$k = 0;
				for($day = 1; $day <= $daysInMonth; $day++){
					$selected_dates = $day."-".$monthss."-".$yearss;
					$selected_dates_available = $day."-".$monthss."-".$yearss;
					$cur_dates = date('j-m-Y',$currDateTime_withTZ);
					$s_date = strtotime($selected_dates);
					$c_date = strtotime($cur_dates);
		
					/* COUNT TOTAL AVAILABLE SLOTS */
		
					$t_zone_value = $setting->get_option('ct_timezone');
					$server_timezone = date_default_timezone_get();
					if(isset($t_zone_value) && $t_zone_value!=''){
						$offset_available= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
						$timezonediff = $offset_available/3600;  
					}else{
						$timezonediff =0;
					}
		
					if(is_numeric(strpos($timezonediff,'-'))){
						$timediffmis = str_replace('-','',$timezonediff)*60;
						$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
					}else{
						$timediffmis = str_replace('+','',$timezonediff)*60;
						$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
					} 
		
					$select_time=date('Y-m-d',strtotime($selected_dates_available));
					$start_date = date($select_time,$currDateTime_withTZ);

					/* Get Google Calendar Bookings */
					$providerCalenderBooking = array();
					if($gc_hook->gc_purchase_status() == 'exist'){
						$gc_hook->google_cal_TwoSync_hook();
					}
					/* Get Google Calendar Bookings */

					$time_interval = $setting->get_option('ct_time_interval');	
					$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
					$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
					$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
					$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
					$booking_padding_time = $setting->get_option('ct_booking_padding_time');
					$additional_slot = $setting->get_option('ct_additional_slot_time');
					if ($additional_slot == '' || $additional_slot == null || $additional_slot < 0) {
						$additional_slot_timestamp = 0*60;
					}else{
						$additional_slot_timestamp = $additional_slot *60;
					}
					$reschedule = "No";
					$client_order_id = 0;
					$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$staff_id,$client_order_id,$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time); 
					
					$allbreak_counter = 0;	
					$allofftime_counter = 0;
					$allbooked_counter = 0;
					$slot_counter = 0;
					$google_slot_counter = 0;	
					$check = 0;
					$week_day_avail_count = $week_day_avail->get_data_for_front_cal();
					

					if(isset($time_schedule['slots'])){ 
					    if(mysqli_num_rows($week_day_avail_count) > 0)
						{
							if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots']))
							{
								$gap_array = array();
								$gap_array_after = array();

								$index = 0;
								$nextCounter = 0;
								$slot_counter = 0;
								foreach ($time_schedule['booked'] as $book) {
									$gap_array[] = $book;
									$gap_array_after[$index]['startTime'] = $book;
									$gap_array_after[$index]['stopTime'] = $book + $time_schedule['duration_of_booking'][$index]*60 + $additional_slot_timestamp;
									$index++;
								}

								foreach($time_schedule['slots']  as $slot) 
								{
									/* Checking in GC booked Slots START */
									$curreslotstr = strtotime(date(date('Y-m-d H:i:s',strtotime($select_time.' '.$slot)),$currDateTime_withTZ));
									
									$gccheck = 'N';
									
									if(sizeof((array)$providerCalenderBooking)>0){
										for($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
											if($curreslotstr >= $providerCalenderBooking[$i]['start'] && $curreslotstr < $providerCalenderBooking[$i]['end']){
												$gccheck = 'Y';$google_slot_counter++;
											}
										}
									}
									/* Checking in GC booked Slots END */

									$ifbreak = 'N';
									foreach($time_schedule['breaks'] as $daybreak) {
										if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
										   $ifbreak = 'Y';
										   $check = $check+1;
										}
									}
									if($ifbreak=='Y') {  continue; } 
									
									$ifofftime = 'N';
									
									foreach($time_schedule['offtimes'] as $offtime) {
										if(strtotime($selected_dates.' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($selected_dates.' '.$slot) < strtotime($offtime['offtime_end'])) {
										   $ifofftime = 'Y';
										   $check = $check+1;
										}
									 }
									if($ifofftime=='Y') {  continue; }
									
									$complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 
										
							 		if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && in_array($complete_time_slot,$time_schedule['booked'])|| $gccheck=='Y') {
									 	$check = $check+1;
									 	continue;
								 	}

								 	if (isset($gap_array_after[$nextCounter]['stopTime']) && $complete_time_slot > $gap_array_after[$nextCounter]['stopTime'] ){
										$nextCounter++;
									}


									if( in_array($complete_time_slot,$time_schedule['booked']) && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { 
										if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
											$check = $check+1;
										}
									}else if( (in_array($complete_time_slot,$gap_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { 
										$check = $check+1;
										
									}else if( isset($gap_array_after[$nextCounter]['startTime']) && ( ($complete_time_slot > $gap_array_after[$nextCounter]['startTime'] && $complete_time_slot < $gap_array_after[$nextCounter]['stopTime']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) {
										$check = $check+1;
										
									}else { 
										if($setting->get_option('ct_time_format')==24){
											$slot_time = date("H:i",strtotime($slot));
											$slotdbb_time = date("H:i",strtotime($slot));
											$ct_time_selected = date("H:i",strtotime($slot));
										}else{
											$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
											$slotdbb_time = date("H:i",strtotime($slot));
											$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA",strtotime($slot)));
										}
									} 
									$slot_counter++; 
								}
								$finals = sizeof((array)$time_schedule['slots'])-$check;
								$available_time_slots = $finals;
							}
							else 
							{
								$available_time_slots =  "0";
							}
						}
					} else{  $available_time_slots =  "0";} 
		
					/* COUNT TOTAL AVAILABLE SLOTS */
		
					if($ct_calendar_firstDay == '1'){
						if( ($day + $offset - $get_first_day_starting) % 7 == 0 && $day >= 0){
						  $k = $k+7;
						  ?>
						  </div>
						  <div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo $k; ?>"></div>
						  <div class="dates">
						  <?php 
						  $rows++;
						}
					}else{
						if( ($day + $offset - $get_first_day_starting) % 7 == 0 && $day != $get_first_day_starting){
						  $k = $k+7;
						  ?>
						  </div>
						  <div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo $k; ?>"></div>
						  <div class="dates">
						  <?php 
						  $rows++;
						}
					}
		
					$available_text = "";
					if($s_date < $c_date){}
					elseif($available_time_slots <= 0){ 
						$available_text =  $label_language_values['none_available'];
					}else{ 
						$available_text =  $available_time_slots." ".$label_language_values['available'];
					} ?>
					<div  title="<?php if($s_date < $c_date){}else{ echo $available_text;} ?>" class=" <?php  if($s_date < $c_date){}else{ echo "ct-tooltipss-load";}?> ct-week <?php  if($c_date == $s_date){ echo 'by_default_today_selected'; } ?> <?php  if($s_date < $c_date){ echo 'hide_previous_dates'; }else{ echo 'selected_datess'.$selected_dates;  echo ' remove_selection selected_date';} ?>"  data-id="<?php if($day < 35){echo $k+7; }else{echo $k;} ?>" data-selected_dates="<?php echo $selected_dates; ?>" data-cur_dates="<?php echo $cur_dates; ?>" data-c_date="<?php echo $c_date; ?>" data-s_date="<?php echo $s_date; ?>"><a href="javascript:void(0)"><span><?php echo $day; ?></span></a></div>
					<?php 
	  			}
	  			if($ct_calendar_firstDay == '1'){
		  			while( (($day-1) + $offset) <= $rows * 7){
						?>
						<div class="ct-week hide_previous_dates"></div>
						<?php 
						$day++;
		  			}
	  			}else{
		  			while( ($day + $offset) <= $rows * 7){
						?>
						<div class="ct-week hide_previous_dates"></div>
						<?php 
						$day++;
		  			}
	  			} ?>
	 		</div> 
	  		<div class="ct-show-time time_slot_box display_selected_date_slots_box<?php  echo  $k+7;?>"></div>
  			<div class="today-date">
  				<a class="ct-button nm today_btttn ct-lg-offset-1" data-istoday="Y" data-cur_dates="<?php echo $cur_dates; ?>" data-next_month="<?php echo date("m",$currDateTime_withTZ); ?>" data-next_month_year="<?php echo date("Y",$currDateTime_withTZ); ?>"><?php echo $label_language_values['today']; ?></a>
	  			<div class="ct-selected-date-view ct-lg-pull-1"><span class="add_date" data-date=""></span><span class="add_time"></span></div>
	  			<input type="hidden" id="save_selected_date" value="" />
	  		</div>
	  <?php 
}
if(isset($_POST['get_slots'])){

	$staff_ids = explode(",", $staff_id);
	if (isset($_POST['reschedule']) && $_POST['reschedule'] == 'yes') {
																					
		$t_zone_value = $setting->get_option('ct_timezone');
																										 
		$server_timezone = date_default_timezone_get();

	 
	 
		if(isset($t_zone_value) && $t_zone_value!=''){
			$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
			$timezonediff = $offset/3600; 
		}else{
			$timezonediff =0;																	
		}
		
		if(is_numeric(strpos($timezonediff,'-'))){
			$timediffmis = str_replace('-','',$timezonediff)*60;
			$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}else{
			$timediffmis = str_replace('+','',$timezonediff)*60;
			$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}
		$select_time=date('Y-m-d',strtotime($_POST['selected_dates']));
		$start_date = date($select_time,$currDateTime_withTZ);
		/* Get Google Calendar Bookings */
		$providerCalenderBooking = array();
		if($gc_hook->gc_purchase_status() == 'exist'){
			$gc_hook->google_cal_TwoSync_hook();
		}
		/* Get Google Calendar Bookings */
																																																														
		$time_interval = $setting->get_option('ct_time_interval');	
	  	$break_time_gap = $setting->get_option('ct_break_time_gap');
	  	$break_time_gap_timestamp = $break_time_gap*60;
			
		$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
		$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
		$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
		$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
		$booking_padding_time = $setting->get_option('ct_booking_padding_time');
		$reschedule = "Yes";
		$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$_POST['staff_id'],$_POST['client_order_id'],$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time); 
		
		$google_slot_counter = 0;	
		$allbreak_counter = 0;	
		$allofftime_counter = 0;
		$slot_counter = 0;
		$google_slot_counter = 0;
		$week_day_avail_count = $week_day_avail->get_data_for_front_cal();

	?>
		<select class="form-control reschedule-time-slots">
			<?php  
			if(mysqli_num_rows($week_day_avail_count) > 0)
			{
				if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots']))
				{
					$gap_array = array();
					$gap_array_after = array();
					$booked_counter = 0;
					foreach($time_schedule['slots'] as $slot){ 							
						/* Checking in GC booked Slots START */
						$curreslotstr = strtotime(date(date('Y-m-d H:i:s',strtotime($select_time.' '.$slot)),$currDateTime_withTZ));
						$gccheck = 'N';
						if(sizeof((array)$providerCalenderBooking)>0){
							for($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
								if($curreslotstr >= $providerCalenderBooking[$i]['start'] && $curreslotstr < $providerCalenderBooking[$i]['end']){
									$gccheck = 'Y';
									$google_slot_counter++;
								}
							}
						}
						/* Checking in GC booked Slots END */
						
						$ifbreak = 'N';
						/* Need to check if the appointment slot come under break time. */
						foreach($time_schedule['breaks'] as $daybreak) {
							if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
							   $ifbreak = 'Y';   
							}
						}
						/* if yes its break time then we will not show the time for booking  */
						if($ifbreak=='Y') { $allbreak_counter++; continue; } 
						
						$ifofftime = 'N';
														
						foreach($time_schedule['offtimes'] as $offtime) {
							if(strtotime($_POST['selected_dates'].' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($_POST['selected_dates'].' '.$slot) < strtotime($offtime['offtime_end'])) {
							   $ifofftime = 'Y';
							}
						}
						/* if yes its offtime time then we will not show the time for booking  */
						if($ifofftime=='Y') { $allofftime_counter++; continue; }
						
						$complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 

						if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && (in_array($complete_time_slot,$time_schedule['booked'])) || $gccheck=='Y') {
							continue;
						}

						$index = 0;
						$nextCounter = 0;

						$booked_slots_array = [];
						foreach ($time_schedule['booked'] as $book) {
							$gap_array[] = $book - $break_time_gap_timestamp;
							$gap_array_after[$index]['startTime'] = $book;
							$gap_array_after[$index]['stopTime'] = $book + $time_schedule['duration_of_booking'][$index]*60 + $break_time_gap_timestamp;

							foreach($time_schedule['slots'] as $t_slot){
								$strtotime_slot = mktime(date('H',strtotime($t_slot)),date('i',strtotime($t_slot)),date('s',strtotime($t_slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 
								if ($strtotime_slot > $gap_array_after[$index]['startTime'] && $strtotime_slot < $gap_array_after[$index]['stopTime']) {
									$booked_slots_array[] = $strtotime_slot;
								}
							}
							
							$index++;
						}

						if ($complete_time_slot > $gap_array_after[$nextCounter]['stopTime'] ){
							$nextCounter++;
						}
						
						if( (in_array($complete_time_slot,$time_schedule['booked']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
							<?php 
							if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
								?>
								<option class="res-booked-slots first" value="<?php  echo date("H:i:s",strtotime($slot)); ?>" disabled style="background-color:greenyellow;">
									<?php  
									if($setting->get_option('ct_time_format')==24){
										echo date("H:i:s",strtotime($slot));
									}else{
										echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
									}?>
								</option>
							<?php 
							$booked_counter++;
							}
							?>
						<?php 
						} else if( (in_array($complete_time_slot,$gap_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
							<?php 
							if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
								$nextBlock = true;
								?>
								<option class="res-booked-slots second" value="<?php  echo date("H:i:s",strtotime($slot)); ?>" disabled>
									<?php  
									if($setting->get_option('ct_time_format')==24){
										echo date("H:i:s",strtotime($slot));
									}else{
										echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
									}?>
								</option>
							<?php 
							$booked_counter++;
							}
							?>
						<?php 
						} else if( (in_array($complete_time_slot,$booked_slots_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
							<?php 
							if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
								$nextBlock = true;
								?>
								<option class="res-booked-slots third" value="<?php  echo date("H:i:s",strtotime($slot)); ?>" disabled style="background-color:greenyellow;">
									<?php  
									if($setting->get_option('ct_time_format')==24){
										echo date("H:i:s",strtotime($slot));
									}else{
										echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
									}?>
								</option>
							<?php 
							$booked_counter++;
							}
							?>
						<?php 
						} else if( (in_array(mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))),$time_schedule['same_booking_slots']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
							<?php 
							if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
								$nextBlock = true;
								?>
								<option class="four" style="background-color:#bfe3f2;" value="<?php  echo date("H:i:s",strtotime($slot)); ?>">
									<?php  
									if($setting->get_option('ct_time_format')==24){
										echo date("H:i:s",strtotime($slot));
									}else{
										echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
									}?>
								</option>
							<?php 
							$booked_counter++;
							}
							?>
						<?php 
						}
						else {
							$nextBlock = false;
							if($setting->get_option('ct_time_format')==24){
								$slot_time = date("H:i",strtotime($slot));
								$slotdbb_time = date("H:i",strtotime($slot));
								$ct_time_selected = date("H:i",strtotime($slot));
							}else{
								$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
								$slotdbb_time = date("H:i",strtotime($slot));
								$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA",strtotime($slot)));
							}
							?>
							<option value="<?php  echo date("H:i:s",strtotime($slot)); ?>" class="five">
								<?php  
								if($setting->get_option('ct_time_format')==24){
									echo date("H:i:s",strtotime($slot));
								}else{
									echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
								}?>
							</option>
						<?php  
						} 
						$slot_counter++; 
					}
					if ($slot_counter == $booked_counter) {
					?>
						<option class="res-booked-slots" value="all-booked" selected disabled> All slots are booked</option>
					<?php  
					}

					if($allbreak_counter != 0 && $allofftime_counter != 0){ ?>
						<option class="res-booked-slots" value="not-available" selected disabled>
							<?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?>
						</option>
				   		<?php  
					}
				   	if($google_slot_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ ?>
						<option class="res-booked-slots" value="not-available" selected disabled>
							<?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?>
						</option>
				   		<?php  
					}
					if($allbreak_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ ?>
						<option class="res-booked-slots" value="not-available" selected disabled>
							<?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?>
						</option>
				   <?php  
					}
				   	if($allofftime_counter > sizeof((array)$time_schedule['offtimes']) && sizeof((array)$time_schedule['slots'])==$allofftime_counter){?>
						<option class="res-booked-slots" value="not-available" selected disabled>
							<?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?>
						</option>
				   	<?php  
				   	}      
				} else { ?>
					<option class="res-booked-slots" value="not-available" selected disabled>
						<?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?>
					</option>
				   <?php  
				} 
			} else { ?>
				<option class="res-booked-slots" value="not-available" selected disabled>
					<?php echo $label_language_values['availability_is_not_configured_from_admin_side']; ?>
				</option>
				<?php  
			} ?>
		</select>
	<?php  
	}
	else if($staff_id == 1 || count($staff_ids) == 1){ 
	
		
		$t_zone_value = $setting->get_option('ct_timezone');
	
		$server_timezone = date_default_timezone_get();
		
			
			
		if(isset($t_zone_value) && $t_zone_value!=''){
			$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
			$timezonediff = $offset/3600; 
		}else{
			$timezonediff =0;																	
		}
		
		if(is_numeric(strpos($timezonediff,'-'))){
			$timediffmis = str_replace('-','',$timezonediff)*60;
			$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}else{
			$timediffmis = str_replace('+','',$timezonediff)*60;
			$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}
		$select_time=date('Y-m-d',strtotime($_POST['selected_dates']));
		$start_date = date($select_time,$currDateTime_withTZ);
		/* Get Google Calendar Bookings */
		$providerCalenderBooking = array();
		if($gc_hook->gc_purchase_status() == 'exist'){
			$gc_hook->google_cal_TwoSync_hook();
		}
		/* Get Google Calendar Bookings */
		$time_interval = $setting->get_option('ct_time_interval');	
    $break_time_gap = $setting->get_option('ct_break_time_gap');
    $break_time_gap_timestamp = $break_time_gap*60;
		$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
		$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
		$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
		$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
		$reschedule = "No";
		$client_order_id = 0;
		$booking_padding_time = $setting->get_option('ct_booking_padding_time');
		$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$staff_id,$client_order_id,$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time);
		/*print_r($time_schedule); die;*/
		$google_slot_counter = 0;	
		$allbreak_counter = 0;	
		$allofftime_counter = 0;
		$slot_counter = 0;
		$week_day_avail_count = $week_day_avail->get_data_for_front_cal();
		?>
		<div class="my_extra_slot" data-selected-date="<?php echo $_POST['selected_dates'];?>" data-cur-date="<?php echo $_POST['cur_dates'];?>" >
			<div class="time-slot-container">
				<div class="ct-slot-legends">
					<ul class="ct-legends-ul">
						<li><span class="ct-slot-legends-box ct-available-new"></span><?php echo $label_language_values['available']; ?></li>
						<li><span class="ct-slot-legends-box ct-selected-new"></span><?php echo $label_language_values['selected']; ?></li>
						<li><span class="ct-slot-legends-box ct-not-available-new"></span><?php echo $label_language_values['not_available']; ?></li><br>
					</ul>
				</div>
				<ul class="list-inline time-slot-ul br-5">
					<?php  
					if(mysqli_num_rows($week_day_avail_count) > 0)
					{
						if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots']))
						{
							$gap_array = array();
							$gap_array_after = array();

							foreach($time_schedule['slots'] as $slot){ 							
								/* Checking in GC booked Slots START */
								$curreslotstr = strtotime(date(date('Y-m-d H:i:s',strtotime($select_time.' '.$slot)),$currDateTime_withTZ));
								$gccheck = 'N';
								if(sizeof((array)$providerCalenderBooking)>0){
									for($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
										if($curreslotstr >= $providerCalenderBooking[$i]['start'] && $curreslotstr < $providerCalenderBooking[$i]['end']){
											$gccheck = 'Y';
											$google_slot_counter++;
										}
									}
								}
								/* Checking in GC booked Slots END */
								
								$ifbreak = 'N';
								/* Need to check if the appointment slot come under break time. */
								foreach($time_schedule['breaks'] as $daybreak) {
									if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
									   $ifbreak = 'Y';   
									}
								}
								/* if yes its break time then we will not show the time for booking  */
								if($ifbreak=='Y') { $allbreak_counter++; continue; } 
								
								$ifofftime = 'N';
																
								foreach($time_schedule['offtimes'] as $offtime) {
									if(strtotime($_POST['selected_dates'].' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($_POST['selected_dates'].' '.$slot) < strtotime($offtime['offtime_end'])) {
									   $ifofftime = 'Y';
									}
								}
								/* if yes its offtime time then we will not show the time for booking  */
								if($ifofftime=='Y') { $allofftime_counter++; continue; }
								
								$complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 

								if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && (in_array($complete_time_slot,$time_schedule['booked'])) || $gccheck=='Y') {
									continue;
								}

								$index = 0;
								$nextCounter = 0;

								$booked_slots_array = [];
								foreach ($time_schedule['booked'] as $book) {
									$gap_array[] = $book - $break_time_gap_timestamp;
									$gap_array_after[$index]['startTime'] = $book;
									$gap_array_after[$index]['stopTime'] = $book + $time_schedule['duration_of_booking'][$index]*60 + $break_time_gap_timestamp;

									foreach($time_schedule['slots'] as $t_slot){
										$strtotime_slot = mktime(date('H',strtotime($t_slot)),date('i',strtotime($t_slot)),date('s',strtotime($t_slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 
										if ($strtotime_slot > $gap_array_after[$index]['startTime'] && $strtotime_slot < $gap_array_after[$index]['stopTime']) {
											$booked_slots_array[] = $strtotime_slot;
										}
									}
									
									$index++;
								}

								if(isset($gap_array_after[$nextCounter]['stopTime'])){
									if ($complete_time_slot > $gap_array_after[$nextCounter]['stopTime'] ){
										$nextCounter++;
									}
								}
								
								
								if( (in_array($complete_time_slot,$gap_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
									<?php 
									if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
										$nextBlock = true;
										?>

										<li class="time-slot br-2 ct-slot-booked ds">
											<?php  
											if($setting->get_option('ct_time_format')==24){
												echo date("H:i",strtotime($slot));
											}else{
												echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
											}?>
										</li>
									<?php 
									}
									?>
								<?php 
								} else if( (in_array($complete_time_slot,$booked_slots_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
									<?php 
									if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
										$nextBlock = true;
										?>
										<li class="time-slot br-2 ct-slot-booked sfd">
											<?php  
											if($setting->get_option('ct_time_format')==24){
												echo date("H:i",strtotime($slot));
											}else{
												echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
											}?>
									<?php 
									}
									?>
								<?php 
								}
								else {
									$nextBlock = false;
									if($setting->get_option('ct_time_format')==24){
										$slot_time = date("H:i",strtotime($slot));
										$slotdbb_time = date("H:i",strtotime($slot));
										$ct_time_selected = date("H:i",strtotime($slot));
									}else{
										$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
										$slotdbb_time = date("H:i",strtotime($slot));
										$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA",strtotime($slot)));
									}
									?>
									<li class="time-slot br-2 time_slotss wer" data-slot_date_to_display="<?php echo str_replace($english_date_array,$selected_lang_label,date($date_format,strtotime($_POST["selected_dates"]))); ?>" data-ct_date_selected="<?php echo  str_replace($english_date_array,$selected_lang_label,date('D, j F, Y',strtotime($_POST["selected_dates"]))); ?>"  data-slot_date="<?php echo $_POST["selected_dates"]; ?>" data-slot_time="<?php echo $slot_time; ?>" data-slotdb_time="<?php echo $slotdbb_time; ?>" data-slotdb_date="<?php echo date('Y-m-d',strtotime($_POST["selected_dates"])); ?>" data-ct_time_selected="<?php echo $ct_time_selected; ?>">
										<?php 
										if($setting->get_option('ct_time_format')==24){
											echo date("H:i",strtotime($slot));
										}else{
											echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
										}?>
									</li>
								<?php  
								} 
								$slot_counter++; 
							}

							if($allbreak_counter != 0 && $allofftime_counter != 0){ ?>
								<li class="time-slot ct-slot-booked sgf" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
						   		<?php  
							}
						   	if($google_slot_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ ?>
								<li class="time-slot ct-slot-booked fgh" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
						   		<?php  
							}
							if($allbreak_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ ?>
								<li class="time-slot ct-slot-booked kl" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
						   <?php  
							}
						   	if($allofftime_counter > sizeof((array)$time_schedule['offtimes']) && sizeof((array)$time_schedule['slots'])==$allofftime_counter){?>
								<li class="time-slot ct-slot-booked iol" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
						   	<?php  
						   	}      
						} else { ?>
							<li class="time-slot ct-slot-booked lkj" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
						   <?php  
						} 
					} else {?>
						<li class="time-slot ct-slot-booked ytd" style="width: 99%;" ><?php echo $label_language_values['availability_is_not_configured_from_admin_side']; ?></li>
						<?php  
					} ?>
				</ul>
			</div>
		</div>
	<?php 
	}else{ 
		
		?>
		<div class="my_extra_slot" data-selected-date="<?php echo $_POST['selected_dates'];?>" data-cur-date="<?php echo $_POST['cur_dates'];?>" >
			<div class="time-slot-container">
				<div class="ct-slot-legends">
					<ul class="ct-legends-ul">
						<li><span class="ct-slot-legends-box ct-available-new"></span><?php echo $label_language_values['available']; ?></li>
						<li><span class="ct-slot-legends-box ct-selected-new"></span><?php echo $label_language_values['selected']; ?></li>
						<li><span class="ct-slot-legends-box ct-not-available-new"></span><?php echo $label_language_values['not_available']; ?></li><br>
					</ul>
				</div>
				<ul class="list-inline time-slot-ul br-5">
					<?php
					$week_day_avail_count = $week_day_avail->get_data_for_front_cal();
				
					$t_zone_value = $setting->get_option('ct_timezone');
				
					$server_timezone = date_default_timezone_get();
				
						
						
					if(isset($t_zone_value) && $t_zone_value!=''){
						$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
						$timezonediff = $offset/3600; 
					}else{
						$timezonediff =0;
					}
				
					if(is_numeric(strpos($timezonediff,'-'))){
						$timediffmis = str_replace('-','',$timezonediff)*60;
						$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
					}else{
						$timediffmis = str_replace('+','',$timezonediff)*60;
						$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
					}
					$select_time=date('Y-m-d',strtotime($_POST['selected_dates']));
					$start_date = date($select_time,$currDateTime_withTZ);
					/* Get Google Calendar Bookings */
					$providerCalenderBooking = array();
					if($gc_hook->gc_purchase_status() == 'exist'){
						$gc_hook->google_cal_TwoSync_hook();
					}
					/* Get Google Calendar Bookings */
					
					$time_interval = $setting->get_option('ct_time_interval');	
				    $break_time_gap = $setting->get_option('ct_break_time_gap');
				    $break_time_gap_timestamp = $break_time_gap*60;
				  
					$time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
					$advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
					$ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
					$ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
					
					$booking_padding_time = $setting->get_option('ct_booking_padding_time');
				
					$google_slot_counter = 0;	
					$allbreak_counter = 0;	
					$allofftime_counter = 0;
					$slot_counter = 0;

					if(mysqli_num_rows($week_day_avail_count) > 0)
					{
						$time_not_available =array();
						$time_not_available1 =array();
						$time_not_available2 =array();
						$time_not_available3 =array();
						$time_not_available4 =array();
						$slot_display = array();
						$slot_displayy = array();
						foreach ($staff_ids as $staff_id_123) {
						
							if($staff_id_123 != ""){	
								$reschedule = "No";
								$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$staff_id_123,$_POST['client_order_id'],$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time); 
							 	
								if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots'])){ 	
									
									$gap_array = array();
									$gap_array_after = array();

									$index = 0;
									$nextCounter = 0;
									$slot_counter = 0;
									foreach ($time_schedule['booked'] as $book) {
										$gap_array[] = $book - $break_time_gap_timestamp;
										$gap_array_after[$index]['startTime'] = $book;
										$gap_array_after[$index]['stopTime'] = $book + $time_schedule['duration_of_booking'][$index]*60 + $break_time_gap_timestamp;
										$index++;
									}

									$html = "";
									foreach($time_schedule['slots'] as $slot){ 
										
										array_push($slot_display,$slot);

										/* Checking in GC booked Slots START */
										$curreslotstr = strtotime(date(date('Y-m-d H:i:s',strtotime($select_time.' '.$slot)),$currDateTime_withTZ));
										$gccheck = 'N';
										if(sizeof((array)$providerCalenderBooking)>0){
											for($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
												if($curreslotstr >= $providerCalenderBooking[$i]['start'] && $curreslotstr < $providerCalenderBooking[$i]['end']){
													$gccheck = 'Y';$google_slot_counter++;
												}
											}
										}
										/* Checking in GC booked Slots END */
										
										$ifbreak = 'N';
										/* Need to check if the appointment slot come under break time. */
										foreach($time_schedule['breaks'] as $daybreak) {
											if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
											   $ifbreak = 'Y';   
											}
										}
										/* if yes its break time then we will not show the time for booking  */
										if($ifbreak=='Y') { $allbreak_counter++; continue; } 
										
										$ifofftime = 'N';
																		
										foreach($time_schedule['offtimes'] as $offtime) {
											if(strtotime($_POST['selected_dates'].' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($_POST['selected_dates'].' '.$slot) < strtotime($offtime['offtime_end'])) {
											   $ifofftime = 'Y';
											}
										}
										/* if yes its offtime time then we will not show the time for booking  */
										if($ifofftime=='Y') { $allofftime_counter++; continue; }
										
										$complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date']))); 
												
										if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && (in_array($complete_time_slot,$time_schedule['booked'])) || $gccheck=='Y') {
											continue;
										}

										if ($complete_time_slot > $gap_array_after[$nextCounter]['stopTime'] ){
											$nextCounter++;
										}
										
										$html = "";
										$bookslots_hide = 'N';	
										if( (in_array($complete_time_slot,$time_schedule['booked']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
											<?php 
											if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
												$html .='<li class="time-slot br-2 ct-slot-booked ews">';
													if($setting->get_option('ct_time_format')==24){
														$html .= date("H:i",strtotime($slot));
													}else{
														$html .= str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
													}
												$html .='</li>';
											}
											?>
										<?php 
										} else if( (in_array($complete_time_slot,$gap_array) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
											<?php 
											if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
												
												$nextBlock = true;
												$html .='<li class="time-slot br-2 ct-slot-booked ds">';
													if($setting->get_option('ct_time_format')==24){
														$html .= date("H:i",strtotime($slot));
													}else{
														$html .= str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
													}
												$html .= '</li>';
											
											}
											?>
										<?php 
										} else if( ( ($complete_time_slot > $gap_array_after[$nextCounter]['startTime'] && $complete_time_slot < $gap_array_after[$nextCounter]['stopTime']) || $gccheck=='Y') && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
											<?php 
											if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="off"){
												
												
												$html .='<li class="time-slot br-2 ct-slot-booked sfd">';
													if($setting->get_option('ct_time_format')==24){
														$html .= date("H:i",strtotime($slot));
													}else{
														$html .= str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
													}
												$html .= '</li>';
											}
											?>
										<?php 
										} else {
											$nextBlock = false;
											if($setting->get_option('ct_time_format')==24){
												$slot_time = date("H:i",strtotime($slot));
												$slotdbb_time = date("H:i",strtotime($slot));
												$ct_time_selected = date("H:i",strtotime($slot));
											}else{
												$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
												$slotdbb_time = date("H:i",strtotime($slot));
												$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA",strtotime($slot)));
											}
											$html .='<li class="time-slot br-2 time_slotss wer" data-slot_date_to_display="'.str_replace($english_date_array,$selected_lang_label,date($date_format,strtotime($_POST["selected_dates"]))).'" data-ct_date_selected="'.str_replace($english_date_array,$selected_lang_label,date('D, j F, Y',strtotime($_POST["selected_dates"]))).'"  data-slot_date="'.$_POST["selected_dates"].'" data-slot_time="'.$slot_time.'" data-slotdb_time="'.$slotdbb_time.'" data-slotdb_date="'. date('Y-m-d',strtotime($_POST["selected_dates"])).'" data-ct_time_selected="'.$ct_time_selected.'">';
												if($setting->get_option('ct_time_format')==24){
													$html .= date("H:i",strtotime($slot));
												}else{
													$html .= str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
												}
											$html .='</li>';
										}
										/*print_r($slot);
										print_r($slot_display);
										die();*/
										foreach ($slot_display as $slot_d) {
											if ($slot == $slot_d) {
												$slot_check = 1;
											}else{
												$slot_check = 0;
											}
										}
											
										if($slot_check == 1){
										
											$slot_displayy[$slot][$staff_id_123] =  $html;
										}else{
											
											$slot_array = array($slot => array($staff_id_123 => $html));
											$slot_displayy[] = $slot_array;
										}
											
										
										
										$slot_counter++; 
										
									}
									
									if($allbreak_counter != 0 && $allofftime_counter != 0){ 
										array_push($time_not_available4,1); 
									}else{
										array_push($time_not_available4,0); 
									}
								  if($google_slot_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ 
								   		array_push($time_not_available3,1); 
									}else{
										array_push($time_not_available3,0); 
									}
									if($allbreak_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ 
										array_push($time_not_available2,1); 
									}else{
										array_push($time_not_available2,0); 
									}
								   	if($allofftime_counter > sizeof((array)$time_schedule['offtimes']) && sizeof((array)$time_schedule['slots'])==$allofftime_counter){
										
										array_push($time_not_available1,1); 
									}else{
										array_push($time_not_available1,0); 
									}  
									array_push($time_not_available,0);    
								} else {  
									array_push($time_not_available,1);
								} 
							}
						}
						
						foreach ($slot_displayy as $slotdisplay) {
							/*print_r($slotdisplay);
								die;*/
							$flag = 0;
							$booked_flag = 0;
							/*foreach ($slotdisplay as $slotdisplayy) {
								print_r($slotdisplayy);
								die();
								if(strpos($slotdisplayy, "wer") == false){
									$flag = 1;
									$booked_slot = $slotdisplayy;
								}else{
									foreach ($staff_ids as $staff_id) {
										if (isset($slotdisplay[$staff_id])) {
											$common_slot = $slotdisplay[$staff_id];
											break;
										}
									}
								}
							}*/

							foreach ($slotdisplay as $slotdisplayy) {
								if (strpos($slotdisplayy, "wer") == true) {
									$booked_flag = 0;
								}else{
									$booked_flag = 1;
									if (isset($slotdisplayy)) {
										$booked_slot = $slotdisplayy;
									}
								}
								if (isset($slotdisplayy)) {
									foreach ($staff_ids as $staff_id) {
										if (isset($slotdisplay[$staff_id])) {
											if(strpos($slotdisplay[$staff_id], "wer") == true){
												$common_slot = $slotdisplay[$staff_id];
												break;
											}/*else{
												$flag == 1;
												if (isset($slotdisplayy) || $slotdisplayy !== '') {
													$main_slot = $slotdisplayy;
												}else{
													$main_slot = $slotdisplay[$staff_id];
												}
											}*/
										}
									}
								}
							}

							if($booked_flag == 1){
								echo $booked_slot;
							}else{
								echo $common_slot;
							}
							
						}

						if(!in_array(0, $time_not_available, true)){
							?>
							<li class="time-slot ct-slot-booked lkj" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
							<?php
						}
						if(!in_array(0, $time_not_available1, true)){
							?>
							<li class="time-slot ct-slot-booked iol" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
							<?php
						}
						if(!in_array(0, $time_not_available2, true)){
							?>
							<li class="time-slot ct-slot-booked kl" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
							<?php
						}
						if(!in_array(0, $time_not_available3, true)){
							?>
							<li class="time-slot ct-slot-booked fgh" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
							<?php
						}
						if(!in_array(0, $time_not_available4, true)){
							?>
							<li class="time-slot ct-slot-booked sgf" style="width: 99%;" ><?php echo $label_language_values['none_of_time_slot_available_please_check_another_dates']; ?></li>
							<?php
						}

					} else { ?>
						<li class="time-slot ct-slot-booked ytd" style="width: 99%;" ><?php echo $label_language_values['availability_is_not_configured_from_admin_side']; ?></li>
						<?php  
					} 
					?>
				</ul>
			</div>
		</div> 
		<?php
	}
}
?>