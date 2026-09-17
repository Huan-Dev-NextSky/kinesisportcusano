<?php
include(dirname(__FILE__) . '/header-staff.php');
include(dirname(dirname(__FILE__)) . "/objects/class_payments.php");
include(dirname(dirname(__FILE__)) . "/objects/class_adminprofile.php");
include(dirname(dirname(__FILE__)) . "/objects/class_staff_commision.php");
include(dirname(dirname(__FILE__)) . "/objects/class_order_client_info.php");
include(dirname(dirname(__FILE__)) . "/objects/class_services.php");
include(dirname(dirname(__FILE__)) . "/objects/class_booking.php");
include(dirname(dirname(__FILE__)) . "/objects/class_rating_review.php");
include(dirname(dirname(__FILE__)) . "/objects/class_dayweek_avail.php");
include(dirname(dirname(__FILE__)) . "/objects/class_offbreaks.php");
include(dirname(dirname(__FILE__)) . "/objects/class_offtimes.php");
if (is_file(dirname(dirname(__FILE__)) . '/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php')) {
	require_once dirname(dirname(__FILE__)) . '/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php';
}
include(dirname(dirname(__FILE__)) . "/objects/class_gc_hook.php");

$con = new cleanto_db();
$conn = $con->connect();
$objpayment = new cleanto_payments();
$objpayment->conn = $conn;
$bookings = new cleanto_booking();
$bookings->conn = $conn;
$objrating_review = new cleanto_rating_review();
$objrating_review->conn = $conn;
$gc_hook = new cleanto_gcHook();
$gc_hook->conn = $conn;
$obj_offtime = new cleanto_offtimes();
$obj_offtime->conn = $conn;
$objdayweek_avail = new cleanto_dayweek_avail();
$objdayweek_avail->conn = $conn;
$objoffbreaks = new cleanto_offbreaks();
$objoffbreaks->conn = $conn;
$objservices = new cleanto_services();
$objservices->conn = $conn;
/* general setting object */
$general = new cleanto_general();
$general->conn = $conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$symbol_position = $settings->get_option('ct_currency_symbol_position');
$decimal = $settings->get_option('ct_price_format_decimal_places');
$objadmin = new cleanto_adminprofile();
$objadmin->conn = $conn;
$order_client_info = new cleanto_order_client_info();
$order_client_info->conn = $conn;
$staff_commision = new cleanto_staff_commision();
$staff_commision->conn = $conn;

$symbol_position = $settings->get_option('ct_currency_symbol_position');
$decimal = $settings->get_option('ct_price_format_decimal_places');
$currency_symbol = $settings->get_option('ct_currency_symbol');

$getdateformat = $settings->get_option('ct_date_picker_date_format');
$time_format = $settings->get_option('ct_time_format');
$timess = "";
if ($time_format == "24") {
	$timess = "H:i";
} else {
	$timess = "h:i A";
}
$staff_id = isset($_SESSION['ct_staffid']) ? $_SESSION['ct_staffid'] : 0;
if (!$staff_id) {
?>
<script type="text/javascript">
	window.location = "<?php echo SITE_URL; ?>admin/";
</script>
<?php
	exit;
}

/* Doctor Bookings are view-only for staff status / payment — no Accept/Decline */
if (!function_exists('ct_doctor_staff_status_label')) {
	function ct_doctor_staff_status_label($status, $label_language_values) {
		if ($status === 'C') {
			$txt = isset($label_language_values['accepted']) ? $label_language_values['accepted'] : 'Accepted';
			return '<span class="label label-success">' . htmlspecialchars($txt) . '</span>';
		}
		if ($status === 'A') {
			$txt = isset($label_language_values['pending']) ? $label_language_values['pending'] : 'Pending';
			return '<span class="label label-info">' . htmlspecialchars($txt) . '</span>';
		}
		if ($status === 'CS' || $status === 'D') {
			$txt = isset($label_language_values['decline']) ? $label_language_values['decline'] : 'Declined';
			return '<span class="label label-danger">' . htmlspecialchars($txt) . '</span>';
		}
		return '<span class="label label-default">—</span>';
	}
}
if (!function_exists('ct_doctor_payment_status_label')) {
	function ct_doctor_payment_status_label($payment_details, $label_language_values) {
		$ps = (is_array($payment_details) && isset($payment_details['payment_status'])) ? $payment_details['payment_status'] : '';
		if ($ps === 'Completed') {
			$txt = isset($label_language_values['completed']) ? $label_language_values['completed'] : 'Completed';
			return '<span class="label label-success">' . htmlspecialchars($txt) . '</span>';
		}
		if ($ps !== '') {
			return '<span class="label label-default">' . htmlspecialchars($ps) . '</span>';
		}
		$txt = isset($label_language_values['pending']) ? $label_language_values['pending'] : 'Pending';
		return '<span class="label label-warning">' . htmlspecialchars($txt) . '</span>';
	}
}
?>
<script type="text/javascript">
jQuery(function ($) {
	$('.ct-loading-main').hide();

	// Keep main nav active state in sync with Doctor dashboard pills
	$('#cta-staff-nav a[data-toggle="pill"]').on('shown.bs.tab', function () {
		$('#cta-staff-nav > li').removeClass('active');
		$(this).parent('li').addClass('active');
	});
	$('.my-profile[data-toggle="pill"]').on('shown.bs.tab', function () {
		$('#cta-staff-nav > li').removeClass('active');
	});
	$('.my-bookings[data-toggle="pill"], .my-schedule[data-toggle="pill"]').on('shown.bs.tab', function () {
		$('#navbarCollapsetop .nav > li').removeClass('active');
	});
});
</script>

<style>
/* Doctor dashboard uses admin double-header — keep content clear of fixed navs */
#cta #ct-staff-dashboard {
	margin-top: 10px;
	clear: both;
}
#cta #ct-staff-dashboard > .panel-body {
	padding-left: 15px;
	padding-right: 15px;
}
</style>

<div class="cta-panel-default" id="ct-staff-dashboard">
	<div class="panel-body">
		<div class="tab-content staff-right-content col-md-12 col-sm-12 col-lg-12 col-xs-12">
			<div class="company-details tab-pane fade in active" id="my-bookings">
				<div class="panel panel-default ">
					<div class="panel-heading">
						<h1 class="panel-title text-left"><?php echo $label_language_values['bookings']; ?></h1>
					</div>
					<div class="panel-body">
						<ul class="nav nav-tabs nav-justified ct-staff-right-menu ct-segment-tabs">
							<li class="active today_staff_appointments">
								<a href="#today-appointments" data-toggle="tab"><i class="fa fa-sun-o"></i><?php echo $label_language_values['today_bookings']; ?></a>
							</li>
							<li>
								<a href="#future-appointments" data-toggle="tab" class="future_staff_appointments"><i class="fa fa-calendar"></i><?php echo $label_language_values['future_bookings']; ?></a>
							</li>
							<li>
								<a href="#past-appointments" data-toggle="tab" class="past_staff_appointments"><i class="fa fa-history"></i><?php echo $label_language_values['past_bookings']; ?></a>
							</li>
						</ul>
						<div class="tab-pane active">
							<!-- first staff nmember -->
							<div class="container-fluid tab-content ct-staff-right-details">
								<div class="table-responsive active tab-pane col-lg-12 col-md-12 col-sm-12 col-xs-12" id="today-appointments">
									<table id="staff-today-bookings-table" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?php echo $label_language_values['service']; ?></th>
												<th><?php echo $label_language_values['app_date']; ?></th>
												<th><?php echo $label_language_values['customer']; ?></th>
												<th><?php echo $label_language_values['email']; ?></th>
												<th><?php echo $label_language_values['address']; ?></th>
												<th><?php echo $label_language_values['phone']; ?></th>
												<th><?php echo $label_language_values['net_total']; ?></th>
												<th><?php echo $label_language_values['staff_booking_status']; ?></th>
												<th><?php echo $label_language_values['payment_status']; ?></th>
												<th><?php echo $label_language_values['rating_and_review']; ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$today_date = date('Y-m-d');
											$staff_service_details = $staff_commision->staff_today_booking_details($staff_id);
	  										
											if (sizeof((array)$staff_service_details) > 0) {
												foreach ($staff_service_details as $arr_staff) {
													$get_booking_nettotal = $staff_commision->get_booking_nettotal($staff_id, $arr_staff['order_id']);
													$service_name = $staff_commision->get_service_name($arr_staff['service_id']);
													$bookings->staff_id = $staff_id;
													$bookings->order_id = $arr_staff['order_id'];

													$status_insert_id = $bookings->staff_status_select_staff_id();
													$bookings->id = $status_insert_id;
													$order_client_info->order_id = $arr_staff['order_id'];
													$order_client_detail = $order_client_info->readone_order_client();

													$tem = unserialize(base64_decode($order_client_detail[5]));

													if ($tem['address'] != "" || $tem['city'] != "" || $tem['zip'] != "" || $tem['state'] != "") {
														$app_address = "";
														$app_city = "";
														$app_zip = "";
														$app_state = "";
														if ($tem['address'] != "") {
															$app_address = $tem['address'];
														}
														if ($tem['city'] != "") {
															$app_city = $tem['city'];
														}
														if ($tem['zip'] != "") {
															$app_zip = $tem['zip'];
														}
														if ($tem['state'] != "") {
															$app_state = $tem['state'];
														}
													}
											?>
													<tr>
														<td><?php echo $service_name; ?></td>
														<td><?php
															$book_datetime_array = explode(" ", $arr_staff['booking_date_time']);
															$book_date = $book_datetime_array[0];
															echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($arr_staff['booking_date_time']))); ?> <?php echo str_replace($english_date_array, $selected_lang_label, date($timess, strtotime($arr_staff['booking_date_time']))); ?></td>
														<td><?php echo $order_client_detail[2]; ?></td>
														<td><?php echo $order_client_detail[3]; ?></td>

														<td><?php echo $app_address . "," . $app_city . "," . $app_zip . "," . $app_state; ?></td>

														<td><?php echo $order_client_detail[4]; ?></td>
														<td><?php echo $general->ct_price_format($get_booking_nettotal, $symbol_position, $decimal); ?></td>
														<td>
															<?php
															$rec_status_details = $bookings->readone_bookings_details_by_order_id_s_id();
															echo ct_doctor_staff_status_label($rec_status_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objpayment->order_id = $arr_staff['order_id'];
															$payment_details = $objpayment->readone_payment_details();
															echo ct_doctor_payment_status_label($payment_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objrating_review->order_id = $arr_staff['order_id'];
															$rating_order_detail = $objrating_review->readone_order();
															if (!empty($rating_order_detail)) {
															?>
																<input id="staff_ratings" name="staff_ratings" class="rating staff_ratings_class staff_ratings<?php echo $arr_staff['order_id']; ?>" data-order_id="<?php echo $arr_staff['order_id']; ?>" data-min="0" data-max="5" data-step="0.1" value="<?php echo $rating_order_detail['rating']; ?>" />
															<?php echo $rating_order_detail['review'];
															} ?>
														</td>
													</tr>
											<?php
												}
											}
											?>
										</tbody>
									</table>
								</div>
								<div class="table-responsive tab-pane col-lg-12 col-md-12 col-sm-12 col-xs-12" id="future-appointments"> </br> </br>
									<!-- <div class="col-md-12 col-sm-12 col-xs-12">
								
									<p id="date_filter">
										<span id="date-label-from" class="date-label"><strong>From: </strong> </span><input class="date_range_filter date" type="text" id="datepicker_from" />
										<span id="date-label-to" class="date-label"><strong>To: </strong><input class="date_range_filter date change_date_past" type="text" id="datepicker_to" />

									</p>
								
									</div>	-->
									<table id="staff-future-bookings-table" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?php echo $label_language_values['service']; ?></th>
												<th><?php echo $label_language_values['app_date']; ?></th>
												<th><?php echo $label_language_values['customer']; ?></th>
												<th><?php echo $label_language_values['email']; ?></th>
												<th><?php echo "address" ?></th>
												<th><?php echo $label_language_values['phone']; ?></th>
												<th><?php echo $label_language_values['net_total']; ?></th>
												<th><?php echo $label_language_values['staff_booking_status']; ?></th>
												<th><?php echo $label_language_values['payment_status']; ?></th>
												<th><?php echo "Rating & Review"; ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$today_date = date('Y-m-d');
											$staff_service_details = $staff_commision->staff_future_booking_details($staff_id);
											if (sizeof((array)$staff_service_details) > 0) {
												foreach ($staff_service_details as $arr_staff) {
													$get_booking_nettotal = $staff_commision->get_booking_nettotal($staff_id, $arr_staff['order_id']);
													$service_name = $staff_commision->get_service_name($arr_staff['service_id']);
													$bookings->staff_id = $staff_id;
													$bookings->order_id = $arr_staff['order_id'];

													$status_insert_id = $bookings->staff_status_select_staff_id();
													$bookings->id = $status_insert_id;
													$order_client_info->order_id = $arr_staff['order_id'];
													$order_client_detail = $order_client_info->readone_order_client();

													$tem = unserialize(base64_decode($order_client_detail[5]));

													if ($tem['address'] != "" || $tem['city'] != "" || $tem['zip'] != "" || $tem['state'] != "") {
														$app_address = "";
														$app_city = "";
														$app_zip = "";
														$app_state = "";
														if ($tem['address'] != "") {
															$app_address = $tem['address'];
														}
														if ($tem['city'] != "") {
															$app_city = $tem['city'];
														}
														if ($tem['zip'] != "") {
															$app_zip = $tem['zip'];
														}
														if ($tem['state'] != "") {
															$app_state = $tem['state'];
														}
													}
											?>
													<tr>
														<td><?php echo $service_name; ?></td>
														<td><?php
															$book_datetime_array = explode(" ", $arr_staff['booking_date_time']);
															$book_date = $book_datetime_array[0];
															echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($arr_staff['booking_date_time']))); ?> <?php echo str_replace($english_date_array, $selected_lang_label, date($timess, strtotime($arr_staff['booking_date_time']))); ?></td>
														<td><?php echo $order_client_detail[2]; ?></td>
														<td><?php echo $order_client_detail[3]; ?></td>

														<td><?php echo $app_address . "," . $app_city . "," . $app_zip . "," . $app_state; ?></td>

														<td><?php echo $order_client_detail[4]; ?></td>
														<td><?php echo $general->ct_price_format($get_booking_nettotal, $symbol_position, $decimal); ?></td>
														<td>
															<?php
															$rec_status_details = $bookings->readone_bookings_details_by_order_id_s_id();
															echo ct_doctor_staff_status_label($rec_status_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objpayment->order_id = $arr_staff['order_id'];
															$payment_details = $objpayment->readone_payment_details();
															echo ct_doctor_payment_status_label($payment_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objrating_review->order_id = $arr_staff['order_id'];
															$rating_order_detail = $objrating_review->readone_order();
															if (!empty($rating_order_detail)) {
															?>
																<input id="staff_ratings" name="staff_ratings" class="rating staff_ratings_class staff_ratings<?php echo $arr_staff['order_id']; ?>" data-order_id="<?php echo $arr_staff['order_id']; ?>" data-min="0" data-max="5" data-step="0.1" value="<?php echo $rating_order_detail['rating']; ?>" />
															<?php echo $rating_order_detail['review'];
															} ?>
														</td>
													</tr>
											<?php
												}
											}
											?>
										</tbody>
									</table>
								</div>
								<div class="table-responsive tab-pane col-lg-12 col-md-12 col-sm-12 col-xs-12" id="past-appointments">
									<table id="staff-past-bookings-table" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?php echo $label_language_values['service']; ?></th>
												<th><?php echo $label_language_values['app_date']; ?></th>
												<th><?php echo $label_language_values['customer']; ?></th>
												<th><?php echo $label_language_values['email']; ?></th>
												<th><?php echo "address" ?></th>
												<th><?php echo $label_language_values['phone']; ?></th>
												<th><?php echo $label_language_values['net_total']; ?></th>
												<th><?php echo $label_language_values['staff_booking_status']; ?></th>
												<th><?php echo $label_language_values['payment_status']; ?></th>
												<th><?php echo "Rating & Review"; ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$today_date = date('Y-m-d');
											$staff_service_details = $staff_commision->staff_past_booking_details($staff_id);
											if (sizeof((array)$staff_service_details) > 0) {
												foreach ($staff_service_details as $arr_staff) {
													$get_booking_nettotal = $staff_commision->get_booking_nettotal($staff_id, $arr_staff['order_id']);
													$service_name = $staff_commision->get_service_name($arr_staff['service_id']);
													$bookings->staff_id = $staff_id;
													$bookings->order_id = $arr_staff['order_id'];

													$status_insert_id = $bookings->staff_status_select_staff_id();
													$bookings->id = $status_insert_id;
													$order_client_info->order_id = $arr_staff['order_id'];
													$order_client_detail = $order_client_info->readone_order_client();

													$tem = unserialize(base64_decode($order_client_detail[5]));

													if ($tem['address'] != "" || $tem['city'] != "" || $tem['zip'] != "" || $tem['state'] != "") {
														$app_address = "";
														$app_city = "";
														$app_zip = "";
														$app_state = "";
														if ($tem['address'] != "") {
															$app_address = $tem['address'];
														}
														if ($tem['city'] != "") {
															$app_city = $tem['city'];
														}
														if ($tem['zip'] != "") {
															$app_zip = $tem['zip'];
														}
														if ($tem['state'] != "") {
															$app_state = $tem['state'];
														}
													}
											?>
													<tr>
														<td><?php echo $service_name; ?></td>
														<td><?php
															$book_datetime_array = explode(" ", $arr_staff['booking_date_time']);
															$book_date = $book_datetime_array[0];
															echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($arr_staff['booking_date_time']))); ?> <?php echo str_replace($english_date_array, $selected_lang_label, date($timess, strtotime($arr_staff['booking_date_time']))); ?></td>
														<td><?php echo $order_client_detail[2]; ?></td>
														<td><?php echo $order_client_detail[3]; ?></td>

														<td><?php echo $app_address . "," . $app_city . "," . $app_zip . "," . $app_state; ?></td>

														<td><?php echo $order_client_detail[4]; ?></td>
														<td><?php echo $general->ct_price_format($get_booking_nettotal, $symbol_position, $decimal); ?></td>
														<td>
															<?php
															$rec_status_details = $bookings->readone_bookings_details_by_order_id_s_id();
															echo ct_doctor_staff_status_label($rec_status_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objpayment->order_id = $arr_staff['order_id'];
															$payment_details = $objpayment->readone_payment_details();
															echo ct_doctor_payment_status_label($payment_details, $label_language_values);
															?>
														</td>
														<td>
															<?php
															$objrating_review->order_id = $arr_staff['order_id'];
															$rating_order_detail = $objrating_review->readone_order();
															if (!empty($rating_order_detail)) {
															?>
																<input id="staff_ratings" name="staff_ratings" class="rating staff_ratings_class staff_ratings<?php echo $arr_staff['order_id']; ?>" data-order_id="<?php echo $arr_staff['order_id']; ?>" data-min="0" data-max="5" data-step="0.1" value="<?php echo $rating_order_detail['rating']; ?>" />
															<?php echo $rating_order_detail['review'];
															} ?>
														</td>
													</tr>
											<?php
												}
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="company-details tab-pane fade in" id="my-requests">
				<div class="panel panel-default ">
					<div class="panel-heading">
						<h1 class="panel-title text-left"><?php echo "Request List"; ?></h1>
					</div>
					<div class="panel-body">
						<div class="table-responsive">
							<table id="staff-request-table" class="display responsive nowrap table table-striped table-bordered dataTable" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th><?php echo "Request Id"; ?></th>
										<th><?php echo "Request Amount"; ?></th>
										<th><?php echo "Request Status"; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$objpayment->staffid = $staff_id;
									$request_details = $objpayment->get_whole_request();
									if($request_details->num_rows > 0){
									while ($rrr = mysqli_fetch_array($request_details)) {
									?>
										<tr>
											<td><?php echo $rrr['request_id']; ?></td>
											<td><?php echo $currency_symbol . "" . $rrr['request_amount']; ?></td>
											<td><?php echo $rrr['status']; ?>
												<?php if ($rrr['status'] == "Pending") { ?>

												<?php } else { ?>
													<i class="fa fa-check-circle" style="font-size:18px;color:Green"></i>
												<?php
												}
												?>
											</td>
										</tr>
									<?php } } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane fade" id="my-schedule">
				<div class="panel panel-default panel panel-default-pm">
					<div class="panel-heading">
						<h1 class="panel-title text-left"><?php echo $label_language_values['schedule']; ?></h1>
					</div>
					<div class="panel-body mt-30">
						<ul class="nav nav-tabs nav-justified ct-staff-right-menu ct-segment-tabs">
							<li class="active"><a href="#member-details" data-toggle="tab"><i class="fa fa-sliders"></i><?php echo $label_language_values['view_slots_by']; ?></a></li>
							<li><a href="#member-availabilty" class="availability" data-toggle="tab"><i class="fa fa-clock-o"></i><?php echo $label_language_values['availabilty']; ?></a></li>
							<li><a href="#member-addbreaks" data-toggle="tab"><i class="fa fa-coffee"></i><?php echo $label_language_values['add_breaks']; ?></a></li>
							<li><a href="#member-offtime" data-toggle="tab" class="myoff_timeslink"><i class="fa fa-ban"></i><?php echo $label_language_values['off_time']; ?></a></li>
							<li><a href="#member-offdays" data-toggle="tab"><i class="fa fa-calendar-times-o"></i><?php echo $label_language_values['off_days']; ?></a></li>
						</ul>
						<div class="tab-pane active">
							<!-- first staff nmember -->
							<div class="container-fluid tab-content ct-staff-right-details">
								<div class="tab-pane col-lg-12 col-md-12 col-sm-12 col-xs-12 active" id="member-details">
									<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
										<table class="ct-staff-common-table ct-staff-common-table-w-pm">
											<tbody>
												<tr>
													<td><label for="phone-number"><?php echo $label_language_values['schedule_type']; ?></label></td>
													<td>
														<label for="schedule-type1">
															<?php
															$staff_id = $_SESSION['ct_staffid'];
															$option = $objdayweek_avail->get_schedule_type_according_provider($staff_id);
															?>
															<input class='weekly_monthly_slots' data-toggle="toggle" data-size="small" type='checkbox' id="schedule-type1" <?php if ($option[7] == "monthly") { ?> checked <?php  } ?> data-on="<?php echo $label_language_values['monthly']; ?>" data-off="<?php echo $label_language_values['weekly']; ?>" data-onstyle='info' data-offstyle='warning' />
														</label>
													</td>
												</tr>
												<tr>
													<td><span class="login_user_id" id="login_user_id" data-id="<?php echo $_SESSION['ct_staffid']; ?>"></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<div class="tab-pane member-availabilty myloadedslots" id="member-availabilty">
									<?php
									$staff_id = $_SESSION['ct_staffid'];
									$option = $objdayweek_avail->get_schedule_type_according_provider($staff_id);
									$weeks = $objdayweek_avail->get_dataof_week();
									$weekname = array($label_language_values['first'], $label_language_values['second'], $label_language_values['third'], $label_language_values['fourth'], $label_language_values['fifth']);
									$weeknameid = array($label_language_values['first_week'], $label_language_values['second_week'], $label_language_values['third_week'], $label_language_values['fourth_week'], $label_language_values['fifth_week']);
									if ($option[7] == 'monthly') {
										$minweek = 1;
										$maxweek = 5;
									} elseif ($option[7] == 'weekly') {
										$minweek = 1;
										$maxweek = 1;
									} else {
										$minweek = 1;
										$maxweek = 1;
									}

									$time_interval = 30;
									?>
									<form id="" method="POST">
										<div class="panel panel-default">
											<div class="col-sm-3 col-md-3 col-lg-3 col-xs-12 ct-weeks-schedule-menu">
												<ul class="nav nav-pills nav-stacked">
													<?php
													if ($minweek == 1 && $maxweek == 5) {
														for ($i = $minweek; $i <= $maxweek; $i++) {
													?>
															<li class="<?php if ($i == 1) {
																			echo "active";
																		} ?>"><a href="#<?php echo $weeknameid[$i - 1]; ?>" data-toggle="tab"><?php echo $weeknameid[$i - 1]; ?> </a></li>
														<?php
														}
													} else {
														$i = 1; ?>
														<li class="<?php if ($i == 1) {
																		echo "active";
																	} ?>"><a href="#<?php echo $weeknameid[$i - 1]; ?>" data-toggle="tab"><?php echo $label_language_values['this_week']; ?></a></li>
													<?php
													}
													?>
												</ul>
											</div>
											<div class="col-sm-9 col-md-9 col-lg-9 col-xs-12">
												<hr id="vr" />
												<div class="tab-content">
													<span class="prove_schedule_type" style="visibility: hidden;"><?php echo $option[7]; ?></span>
													<?php
													for ($i = $minweek; $i <= $maxweek; $i++) {
													?>
														<div class="tab-pane <?php if ($i == 1) {
																					echo "active";
																				} ?>" id="<?php echo $weeknameid[$i - 1]; ?>">
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php if ($minweek == 1 && $maxweek == 1) { ?>
																		<h4 class="ct-right-header"><?php echo $label_language_values['this_week_time_scheduling']; ?></h4>
																	<?php
																	} else {
																	?>
																		<h4 class="ct-right-header"><?php echo $weekname[$i - 1]; ?><?php echo " " . $label_language_values['week_time_scheduling']; ?></h4>
																	<?php  } ?>
																	<ul class="list-unstyled" id="ct-staff-timing">
																		<?php
																		$staff_id = $_SESSION['ct_staffid'];
																		for ($j = 1; $j <= 7; $j++) {
																			$objdayweek_avail->week_id = $i;
																			$objdayweek_avail->weekday_id = $j;
																			$getvalue = $objdayweek_avail->get_time_slots($staff_id);
																			$daystart_time = $getvalue[4];
																			$dayend_time = $getvalue[5];
																			$offdayst = $getvalue[6];
																		?>
																			<li class="active">
																				<span class="col-sm-3 col-md-3 col-lg-3 col-xs-12 ct-day-name"><?php echo  $label_language_values[strtolower($objdayweek_avail->get_daynamebyid($j))]; ?></span>
																				<span class="col-sm-2 col-md-2 col-lg-2 col-xs-12">
																					<label class="cta-col2" for="ct-monFirst<?php echo $i; ?><?php echo $j; ?>_<?php echo $getvalue[0]; ?>">
																						<input class='chkdaynew' data-toggle="toggle" data-size="small" type='checkbox' id="ct-monFirst<?php echo $i; ?><?php echo $j; ?>_<?php echo $getvalue[0]; ?>" <?php if ($getvalue[6] == 'Y' || $getvalue[6] == '') {echo "";
																																																															} else {
																																																																echo "checked";
																																																															} ?> data-on="<?php echo $label_language_values['o_n']; ?>" data-off="<?php echo $label_language_values['off']; ?>" data-onstyle='primary' data-offstyle='default' />
																					</label>
																				</span>
																				<span class="col-sm-7 col-md-7 col-lg-7 col-xs-12 ct-staff-time-schedule">
																					<div class="pull-right">
																						<select class="selectpicker starttimenew" data-aid="<?php echo $i; ?>_<?php echo $j; ?>" id="starttimenews_<?php echo $i; ?>_<?php echo $j; ?>" data-size="10" style="display: none;">
																							<?php
																							$min = 0;
																							$t = 1;
																							while ($min < 1440) {
																								if ($min == 1440) {
																									$timeValue = date('G:i', mktime(0, $min - 1, 0, 1, 1, 2015));
																								} else {
																									$timeValue = date('G:i', mktime(0, $min, 0, 1, 1, 2015));
																								}
																								$timetoprint = date('G:i', mktime(0, $min, 0, 1, 1, 2014)); ?>
																								<option <?php
																										if ($getvalue[4] == date("H:i:s", strtotime($timeValue))) {
																											$t = 10;
																											echo "selected";
																										}
																										if ($t == 1) {
																											if ("10:00:00" == date("H:i:s", strtotime($timeValue))) {
																												echo "selected";
																											}
																										}
																										?> value="<?php echo date("H:i:s", strtotime($timeValue)); ?>">
																									<?php
																									if ($time_format == 24) {
																										echo date("H:i", strtotime($timetoprint));
																									} else {
																										echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($timetoprint)));
																									}
																									?>
																								</option>
																							<?php
																								$min = $min + $time_interval;
																							}
																							?>
																						</select>
																						<span class="ct-staff-hours-to"> <?php echo $label_language_values['to']; ?> </span>
																						<select class="selectpicker endtimenew" data-aid="<?php echo $i; ?>_<?php echo $j; ?>" data-size="10" id="endtimenews_<?php echo $i; ?>_<?php echo $j; ?>" style="display: none;">
																							<?php
																							$min = 0;
																							$t = 1;
																							while ($min < 1440) {
																								if ($min == 1440) {
																									$timeValue = date('G:i', mktime(0, $min - 1, 0, 1, 1, 2015));
																								} else {
																									$timeValue = date('G:i', mktime(0, $min, 0, 1, 1, 2015));
																								}
																								$timetoprint = date('G:i', mktime(0, $min, 0, 1, 1, 2014)); ?>
																								<option <?php
																										if ($getvalue[5] == date("H:i:s", strtotime($timeValue))) {
																											$t = 10;
																											echo "selected";
																										}
																										if ($t == 1) {
																											if ("20:00:00" == date("H:i:s", strtotime($timeValue))) {
																												echo "selected";
																											}
																										}
																										?> value="<?php echo date("H:i:s", strtotime($timeValue)); ?>">
																									<?php
																									if ($time_format == 24) {
																										echo date("H:i", strtotime($timetoprint));
																									} else {
																										echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($timetoprint)));
																									}
																									?>
																								</option>
																							<?php
																								$min = $min + $time_interval;
																							}
																							?>
																						</select>
																					</div>
																				</span>
																			</li>
																		<?php  }
																		?>
																	</ul>
																</div>
															</div>
														</div>
													<?php
													}
													?>
												</div>
											</div>
										</div>
										<table class="ct-staff-common-table">
											<tbody>
												<tr>
													<td></td>
													<td>
														<a id="" value="" name="update_schedule" class="btn btn-success ct-btn-width btnupdatenewtimeslots_monthly" type="submit"><?php echo $label_language_values['save_availability']; ?>
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</form>
								</div>
								<div class="tab-pane member-addbreaks" id="member-addbreaks">
									<div class="panel panel-default">
										<div class="panel-body">
											<?php
											$breaks_weekname = array($label_language_values['first'], $label_language_values['second'], $label_language_values['third'], $label_language_values['fourth'], $label_language_values['fifth']);

											$breaks_weeknameid = array($label_language_values['first_week'], $label_language_values['second_week'], $label_language_values['third_week'], $label_language_values['fourth_week'], $label_language_values['fifth_week']);
											if ($option[7] == 'monthly') {
												$minweek = 1;
												$maxweek = 5;
											} elseif ($option[7] == 'weekly') {
												$minweek = 1;
												$maxweek = 1;
											} else {
												$minweek = 1;
												$maxweek = 1;
											}
											?>
											<!-- Start here -->
											<div class="col-sm-3 col-md-3 col-lg-3 col-xs-12 ct-weeks-breaks-menu">
												<ul class="nav nav-pills nav-stacked">
													<?php
													if ($minweek == 1 && $maxweek == 5) {
														for ($i = $minweek; $i <= $maxweek; $i++) {
													?>
															<li class="<?php if ($i == 1) {
																			echo "active";
																		} ?>"><a href="#<?php echo $breaks_weeknameid[$i - 1] . "_br"; ?>" data-toggle="tab"><?php echo $breaks_weeknameid[$i - 1]; ?> </a></li>
														<?php
														}
													} else {
														$i = 1;
														?>
														<li class="<?php if ($i == 1) {
																		echo "active";
																	} ?>"><a href="#<?php echo $breaks_weeknameid[$i - 1] . "_br"; ?>" data-toggle="tab"><?php echo $label_language_values['this_week']; ?></a></li>
													<?php
													}
													?>
												</ul>
											</div>
											<div class="col-sm-9 col-md-9 col-lg-9 col-xs-12 ct-weeks-breaks-details">
												<div class="tab-content">
													<?php
													$breaks_weekname = array($label_language_values['first'], $label_language_values['second'], $label_language_values['third'], $label_language_values['fourth'], $label_language_values['fifth']);

													$breaks_weeknameid = array($label_language_values['first_week'], $label_language_values['second_week'], $label_language_values['third_week'], $label_language_values['fourth_week'], $label_language_values['fifth_week']);
													for ($i = $minweek; $i <= $maxweek; $i++) {
													?>
														<div class="tab-pane <?php if ($i == 1) {
																					echo "active";
																				} ?>" id="<?php echo $breaks_weeknameid[$i - 1] . "_br"; ?>">
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php if ($minweek == 1 && $maxweek == 1) { ?>
																		<h4 class="ct-right-header"><?php echo $label_language_values['this_week_breaks']; ?> </h4>
																	<?php  } else { ?>
																		<h4 class="ct-right-header"><?php echo $breaks_weekname[$i - 1]; ?><?php echo $label_language_values['week_breaks']; ?> </h4>
																	<?php  } ?>
																	<ul class="list-unstyled" id="ct-staff-breaks">
																		<?php
																		$staff_id = $_SESSION['ct_staffid'];
																		for ($j = 1; $j <= 7; $j++) {
																			$break_weekday = $j;
																			$objdayweek_avail->week_id = $i;
																			$objdayweek_avail->weekday_id = $j;
																			$getdatafrom_week_days = $objdayweek_avail->getdata_byweekid($staff_id);
																		?>
																			<li class="active">
																				<span class="col-sm-3 col-md-3 col-lg-3 col-xs-12 ct-day-name"><?php echo  $label_language_values[strtolower($objdayweek_avail->get_daynamebyid($j))]; ?></span>
																				<?php
																				if ($getdatafrom_week_days[0] == 'Y' || $getdatafrom_week_days[0] == '') {
																				?>
																					<span class="col-sm-2 col-md-2 col-lg-2 col-xs-12">
																						<a class="btn btn-small btn-default ct-small-br-btn disabled"><?php echo $label_language_values['closed']; ?></a>
																					</span>
																				<?php
																				} else { ?>
																					<span class="col-sm-2 col-md-2 col-lg-2 col-xs-12">
																						<a id="ct-add-staff-breaks" data-staff_id="<?php echo $_SESSION['ct_staffid']; ?>" data-weekid="<?php echo $i; ?>" data-weekday="<?php echo $j; ?>" class="btn btn-small btn-success ct-small-br-btn myct-add-staff-breaks" data-id="<?php echo $i; ?>_<?php echo $j; ?>"><?php echo $label_language_values['add_break']; ?></a>
																					</span>
																				<?php    } ?>
																				<span class="col-sm-7 col-md-7 col-lg-7 col-xs-12 ct-staff-breaks-schedule">
																					<ul class="list-unstyled" id="ct-add-break-ul<?php echo $i; ?>_<?php echo $j; ?>">
																						<?php
																						$staff_id = $_SESSION['ct_staffid'];
																						$objoffbreaks->week_id = $i;
																						$objoffbreaks->weekday_id = $j;
																						$jc = $objoffbreaks->getdataby_week_day_id($staff_id);
																						if($jc->num_rows > 0){
																						while ($rrr = mysqli_fetch_array($jc)) {
																						?>
																							<li>
																								<select class="selectpicker selectpickerstart" id="start_break_<?php echo $rrr['id']; ?>_<?php echo $rrr['week_id']; ?>_<?php echo $rrr['weekday_id']; ?>" data-id="<?php echo $rrr['id']; ?>" data-weekid="<?php echo $rrr['week_id']; ?>" data-weekday="<?php echo $rrr['weekday_id']; ?>" data-size="10" style="">
																									<?php
																									$min = 0;
																									while ($min < 1440) {
																										if ($min == 1440) {
																											$timeValue = date('G:i', mktime(0, $min - 1, 0, 1, 1, 2015));
																										} else {
																											$timeValue = date('G:i', mktime(0, $min, 0, 1, 1, 2015));
																										}
																										$timetoprint = date('G:i', mktime(0, $min, 0, 1, 1, 2014)); ?>
																										<option <?php if ($rrr['break_start'] == date("H:i:s", strtotime($timeValue))) {
																													echo "selected";
																												} ?> value="<?php echo date("H:i:s", strtotime($timeValue)); ?>">
																											<?php
																											if ($time_format == 24) {
																												echo date("H:i", strtotime($timetoprint));
																											} else {
																												echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($timetoprint)));
																											}
																											?>
																										</option>
																									<?php
																										$min = $min + $time_interval;
																									}
																									?>
																								</select>
																								<span class="ct-staff-hours-to"> <?php echo $label_language_values['to']; ?> </span>
																								<select class="selectpicker selectpickerend" data-id="<?php echo $rrr['id']; ?>" data-weekid="<?php echo $rrr['week_id']; ?>" data-weekday="<?php echo $rrr['weekday_id']; ?>" data-size="10" style="display: none;">
																									<?php
																									$min = 0;
																									while ($min < 1440) {
																										if ($min == 1440) {
																											$timeValue = date('G:i', mktime(0, $min - 1, 0, 1, 1, 2015));
																										} else {
																											$timeValue = date('G:i', mktime(0, $min, 0, 1, 1, 2015));
																										}
																										$timetoprint = date('G:i', mktime(0, $min, 0, 1, 1, 2014)); ?>
																										<option <?php if ($rrr['break_end'] == date("H:i:s", strtotime($timeValue))) {
																													echo "selected";
																												} ?> value="<?php echo date("H:i:s", strtotime($timeValue)); ?>">
																											<?php
																											if ($time_format == 24) {
																												echo date("H:i", strtotime($timetoprint));
																											} else {
																												echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($timetoprint)));
																											}
																											?>
																										</option>
																									<?php
																										$min = $min + $time_interval;
																									}
																									?>
																								</select>
																								<button id="ct-delete-staff-break<?php echo $rrr['id']; ?>_<?php echo $i; ?>_<?php echo $j; ?>" data-wiwdibi='<?php echo $rrr['id']; ?>_<?php echo $i; ?>_<?php echo $j; ?>' data-break_id="<?php echo $rrr['id']; ?>" class="pull-right btn btn-circle btn-default delete_break" rel="popover" data-placement='left' title="<?php echo $label_language_values['are_you_sure']; ?>?"> <i class="fa fa-trash"></i></button>
																								<div id="popover-delete-breaks<?php echo $rrr['id']; ?>_<?php echo $i; ?>_<?php echo $j; ?>" style="display: none;">
																									<div class="arrow"></div>
																									<table class="form-horizontal" cellspacing="0">
																										<tbody>
																											<tr>
																												<td>
																													<button id="" value="Delete" data-break_id='<?php echo  $rrr['id']; ?>' class="btn btn-danger mybtndelete_breaks" type="submit"><?php echo $label_language_values['yes']; ?></button>
																													<button id="ct-close-popover-delete-breaks" class="btn btn-default close_popup" href="javascript:void(0)"><?php echo $label_language_values['cancel']; ?></button>
																												</td>
																											</tr>
																										</tbody>
																									</table>
																								</div>
																							</li>
																						<?php   } }
																						?>
																					</ul>
																			</li>
																		<?php  }
																		?>
																	</ul>
																</div>
															</div>
														</div>
													<?php
													}
													?>
												</div>
												<!-- end tab content main right -->
											</div> <!-- End Here -->
										</div>
									</div>
								</div>
								<div class="tab-pane member-offtime" id="member-offtime">
									<div class="panel panel-default">
										<div class="panel-body">
											<div class="ct-member-offtime-inner">
												<h3><?php echo $label_language_values['add_your_off_times']; ?></h3>
												<div class="col-md-6 col-sm-7 col-xs-12 col-lg-6 mb-10">
													<label><?php echo $label_language_values['add_new_off_time']; ?></label>
													<div id="offtime-daterange" class="form-control dis_table">
														<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
														<span></span> <i class="fa fa-caret-down"></i>
													</div>
												</div>
												<div class="col-md-2 col-sm-2 col-xs-12 col-lg-2">
													<a href="javascript:void(0)" id="add_break" class="form-group btn btn-info mt-20" name=""><?php echo $label_language_values['add_break']; ?> </a>
												</div>
											</div>
											<div class="ct-staff-member-offtime-list-main mytablefor_offtimes cb col-md-12 col-xs-12 mt-20">
												<?php echo $label_language_values['your_added_off_times']; ?>
												<div class="table-responsive">
													<table id="ct-staff-member-offtime-list" class="ct-staff-member-offtime-lists table table-striped table-bordered dt-responsive nowrap myadded_offtimes" cellspacing="0" width="100%">
														<thead class="">
															<tr>
																<th>#</th>
																<th><?php echo $label_language_values['start_date']; ?></th>
																<th><?php echo $label_language_values['start_time']; ?></th>
																<th><?php echo $label_language_values['end_date']; ?></th>
																<th><?php echo $label_language_values['end_time']; ?></th>
																<th><?php echo $label_language_values['action']; ?></th>
															</tr>
														</thead>
														<tbody class="mytbodyfor_offtimes staff-sch">
															<?php
															$staff_id = $_SESSION['ct_staffid'];
															$res = $obj_offtime->get_all_offtimes($staff_id);
															$i = 1;
															if($res->num_rows > 0){
															while ($r = mysqli_fetch_array($res)) {
																$st = $r['start_date_time'];
																$stt = explode(" ", $st);
																$sdates = $stt[0];
																$stime = $stt[1];
																$et = $r['end_date_time'];
																$ett = explode(" ", $et);
																$edates = $ett[0];
																$etime = $ett[1];
															?>
																<tr id="myofftime_<?php echo $r['id'] ?>">
																	<td><?php echo $i++; ?></td>
																	<td><?php echo
																		str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($sdates))); ?></td>
																	<?php
																	if ($time_format == 12) {
																	?>
																		<td><?php echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($stime))); ?></td>
																	<?php
																	} else {
																	?>
																		<td><?php echo date("H:i", strtotime($stime)); ?></td>
																	<?php
																	}
																	?>
																	<td><?php echo
																		str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($edates))); ?></td>
																	<?php
																	if ($time_format == 12) {
																	?>
																		<td><?php echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($etime))); ?></td>
																	<?php
																	} else {
																	?>
																		<td><?php echo date("H:i", strtotime($etime)); ?></td>
																	<?php
																	}
																	?>
																	<td><a data-id="<?php echo $r['id']; ?>" class='btn btn-danger ct_delete_provider left-margin'><span class='glyphicon glyphicon-remove'></span></a></td>
																</tr>
															<?php
															} }
															?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane member-offdays mt-10" id="member-offdays">
									<div class="panel panel-default">
										<?php
										$offday->user_id = $_SESSION['ct_staffid'];
										$displaydate = $offday->select_date();
										$arr_all_off_day = array();
										if($displaydate->num_rows > 0){
											while ($readdate = mysqli_fetch_array($displaydate)) {
												$arr_all_off_day[] = $readdate['off_date'];
											}
										}
										$year_arr = array(date('Y'), date('Y') + 1);
										$month_num = date('n');
										if (isset($_GET['y']) && in_array($_GET['y'], $year_arr)) {
											$year = $_GET['y'];
										} else {
											$year = date('Y');
										}
										$nextYear = date('Y') + 1;
										$date = date('d');
										$month = array(ucfirst(strtolower($label_language_values['january'])), ucfirst(strtolower($label_language_values['february'])), ucfirst(strtolower($label_language_values['march'])), ucfirst(strtolower($label_language_values['april'])), ucfirst(strtolower($label_language_values['may'])), ucfirst(strtolower($label_language_values['june'])), ucfirst(strtolower($label_language_values['july'])), ucfirst(strtolower($label_language_values['august'])), ucfirst(strtolower($label_language_values['september'])), ucfirst(strtolower($label_language_values['october'])), ucfirst(strtolower($label_language_values['november'])), ucfirst(strtolower($label_language_values['december'])));
										echo '<table class="offdaystable">';
										echo '<tr>';
										for ($reihe = 1; $reihe <= 12; $reihe++) { /* 4 */
											$this_month = ($reihe - 1) * 0 + $reihe; /*write 0 instead of 12*/
											$current_year = date('Y');
											$currnt_month = date('m');
											if (($currnt_month < $this_month) || ($currnt_month == $this_month)) {
												$year = $current_year;
											} else {
												$year = $current_year + 1;
											}
											$erster = date('w', mktime(0, 0, 0, $this_month, 1, $year));
											$insgesamt = date('t', mktime(0, 0, 0, $this_month, 1, $year));
											if ($erster == 0) $erster = 7;
											echo '<td class="ct-calendar-box col-lg-4 col-md-4 col-sm-6 col-xs-12 pull-left">';
											echo '<table align="center" class="table table-bordered table-striped monthtable">'; ?>
											<tbody class="ta-c">
												<div class="ct-schedule-month-name pull-right">
													<div class="pull-left">
														<div class="ct-custom-checkbox">
															<ul class="ct-checkbox-list">
																<li>
																	<input style="margin:0px;" type="checkbox" class="fullmonthoff all" data-prov_id="<?php echo $_SESSION['ct_staffid']; ?>" id="<?php echo $year . '-' . $this_month; ?>" <?php $offday->off_year_month = $year . '-' . $this_month;
																																																										if ($offday->check_full_month_off() == true) {
																																																											echo " checked ";
																																																										}  ?> />
																	<label for="<?php echo $year . '-' . $this_month; ?>"><span></span>
																		<?php echo $month[$reihe - 1] . " " . $year; ?>
																	</label>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</tbody>
										<?php
											echo '<tr><td><b>' . $label_language_values['mon'] . '</b></td><td><b>' . $label_language_values['tue'] . '</b></td>';
											echo '<td><b>' . $label_language_values['wed'] . '</b></td><td><b>' . $label_language_values['thu'] . '</b></td>';
											echo '<td><b>' . $label_language_values['fri'] . '</b></td><td class="sat"><b>' . $label_language_values['sat'] . '</b></td>';
											echo '<td class="sun"><b>' . $label_language_values['sun'] . '</b></td></tr>';
											echo '<tr class="dateline selmonth_' . $year . '-' . $this_month . '"><br>';
											$i = 1;
											while ($i < $erster) {
												echo '<td> </td>';
												$i++;
											}
											$i = 1;
											while ($i <= $insgesamt) {
												$rest = ($i + $erster - 1) % 7;
												$cal_cur_date =  $year . "-" . sprintf('%02d', $this_month) . "-" . sprintf('%02d', $i);
												if (($i == $date) && ($this_month == $month_num)) {
													if (isset($arr_all_off_day)  && in_array($cal_cur_date, $arr_all_off_day)) {
														echo '<td  id="' . $year . '-' . $this_month . '-' . $i . '" data-prov_id="' . $_SESSION['ct_staffid'] . '" class="selectedDate RR offsingledate"  align=center >';
													} else {
														echo '<td  id="' . $year . '-' . $this_month . '-' . $i . '" data-prov_id="' . $_SESSION['ct_staffid'] . '"  class="date_single RR offsingledate"  align=center>';
													}
												} else {
													if (isset($arr_all_off_day)  &&  in_array($cal_cur_date, $arr_all_off_day)) {
														echo '<td  id="' . $year . '-' . $this_month . '-' . $i . '"  data-prov_id="' . $_SESSION['ct_staffid'] . '"  class="selectedDate RR offsingledate highlight"  align=center>';
													} else {
														echo '<td  id="' . $year . '-' . $this_month . '-' . $i . '" data-prov_id="' . $_SESSION['ct_staffid'] . '" class="date_single RR offsingledate"  align=center>';
													}
												}
												if (($i == $date) && ($this_month == $month_num)) {
													echo '<span style="color:#000;font-weight: bold;font-size: 15px;">' . $i . '</span>';
												} elseif ($rest == 6) {
													echo '<span   style="color:#0000cc;">' . $i . '</span>';
												} elseif ($rest == 0) {
													echo '<span  style="color:#cc0000;">' . $i . '</span>';
												} else {
													echo $i;
												}
												echo "</td>\n";
												if ($rest == 0) echo "</tr>\n<tr class='dateline selmonth_" . $year . "-" . $this_month . "'>\n";
												$i++;
											}
											echo '</tr>';
											echo '</tbody>';
											echo '</table>';
											echo '</td>';
										}
										echo '</tr>';
										echo '</table>';
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if (false) { /* Payment tab hidden for Doctor role */ ?>
			<div class="tab-pane fade" id="my-wallet">
				<div class="panel panel-default">
					<div class="panel-heading">
						<?php
						$objadmin->id = $_SESSION['ct_staffid'];
						$wallet_amount = $objadmin->get_previous_staff_wallet();
						?>
						<h1 class="panel-title text-left">Payment Details</h1>
					</div>
					<div class="panel-body mt-30">
						<div class="table-responsive get_payment_staff_by_date_append">
							<table id="staff-payments-details" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo $label_language_values['payment_method']; ?></th>
										<th><?php echo $label_language_values['payment_date']; ?></th>
										<th><?php echo $label_language_values['amount']; ?></th>
										<th><?php echo $label_language_values['advance_paid']; ?></th>
										<th><?php echo $label_language_values['net_total']; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$readall_ct_staff_commision = $staff_commision->get_booking_assign($staff_id);

									if (mysqli_num_rows($readall_ct_staff_commision) > 0) {
										$i = 1;
										while ($row = mysqli_fetch_array($readall_ct_staff_commision)) {
									?>
											<tr>
												<td><?php echo $i; ?></td>
												<td><?php echo $row['payment_method']; ?></td>
												<td><?php echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($row['payment_date']))); ?></td>
												<td><?php echo  $general->ct_price_format($row['amt_payable'], $symbol_position, $decimal); ?></td>
												<td><?php echo  $general->ct_price_format($row['advance_paid'], $symbol_position, $decimal); ?></td>
												<td><?php echo  $general->ct_price_format($row['net_total'], $symbol_position, $decimal); ?></td>
											</tr>
									<?php
											$i++;
										}
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="myModal" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<?php
							$objadmin->id = $_SESSION['ct_staffid'];
							$staff_id = $_SESSION['ct_staffid'];
							$wallet_amount = $objadmin->get_previous_staff_wallet();
							?>
							<h4 class="modal-title"> <?php echo "Wallet Amount ( " . $currency_symbol . "" . $wallet_amount[0] . " )"; ?> </h4>
						</div>
						<div class="modal-body">
							<label for="add-money" class="amt-title"><?php echo "Transfer Amount";  ?></label>

							<input type="text" style="width:50%;" class="form-control transfer_amount_value" required name="transfer_amount_value" id="transfer_amount_value" Placeholder="<?php echo "Please Enter Transfer Amount";  ?>" />
							<br><span style="color:red; display:none;" class="wallet_error">Sorry! Not Sufficient Wallet Amount</span> <br>
							<a href="javascript:void(0)" data-email="<?php echo $wallet_amount[1]; ?>" data-staffid="<?php echo $staff_id; ?>" data-currentamount="<?php echo $wallet_amount[0]; ?>" class="btn btn-success ld-btn-width request_for_transfer amt-submit">Submit Request</a>

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php
			/* Payment and Google Calendar Settings removed from Doctor dashboard */
			?>
			<div class="company-details tab-pane fade" id="my-profile">
				<div class="ct-profile-shell">
					<?php
					$objadmin->id = $staff_id;
					$staff_read = $objadmin->readone();
					$doc_name = isset($staff_read['fullname']) ? $staff_read['fullname'] : (isset($staff_read[3]) ? $staff_read[3] : '');
					$doc_email = isset($staff_read['email']) ? $staff_read['email'] : (isset($staff_read[2]) ? $staff_read[2] : '');
					$doc_phone = isset($staff_read['phone']) ? $staff_read['phone'] : (isset($staff_read[4]) ? $staff_read[4] : '');
					$doc_address = isset($staff_read['address']) ? $staff_read['address'] : (isset($staff_read[5]) ? $staff_read[5] : '');
					$doc_city = isset($staff_read['city']) ? $staff_read['city'] : (isset($staff_read[6]) ? $staff_read[6] : '');
					$doc_state = isset($staff_read['state']) ? $staff_read['state'] : (isset($staff_read[7]) ? $staff_read[7] : '');
					$doc_zip = isset($staff_read['zip']) ? $staff_read['zip'] : (isset($staff_read[8]) ? $staff_read[8] : '');
					$doc_country = isset($staff_read['country']) ? $staff_read['country'] : (isset($staff_read[9]) ? $staff_read[9] : '');
					$doc_desc = isset($staff_read['description']) ? $staff_read['description'] : '';
					$doc_image = isset($staff_read['image']) ? $staff_read['image'] : '';
					$doc_enable = isset($staff_read['enable_booking']) ? $staff_read['enable_booking'] : (isset($staff_read[12]) ? $staff_read[12] : 'N');
					$doc_pass = isset($staff_read['password']) ? $staff_read['password'] : (isset($staff_read[1]) ? $staff_read[1] : '');
					$doc_id = isset($staff_read['id']) ? $staff_read['id'] : (isset($staff_read[0]) ? $staff_read[0] : $staff_id);
					if ($doc_image == '') {
						$imagepath = SITE_URL . "assets/images/user.png";
					} else {
						$imagepath = SITE_URL . "assets/images/services/" . $doc_image;
					}
					$objrating_review->staff_id = $staff_id;
					$rating_details = $objrating_review->readall_by_staff_id();
					$rating_count = 0;
					$divide_count = 0;
					if (mysqli_num_rows($rating_details) > 0) {
						while ($row_rating_details = mysqli_fetch_assoc($rating_details)) {
							$divide_count++;
							$rating_count += (float)$row_rating_details['rating'];
						}
					}
					$rating_point = 0;
					if ($divide_count != 0) {
						$rating_point = round(($rating_count / $divide_count), 1);
					}
					?>
					<div class="ct-profile-hero">
						<div class="ct-profile-avatar">
							<div class="ct-clean-service-image-uploader">
								<img data-imagename="" id="pppp<?php echo $staff_id; ?>staffimage" src="<?php echo $imagepath; ?>" class="ct-clean-staff-image br-100" height="100" width="100">
								<input data-us="pppp<?php echo $staff_id; ?>" class="hide ct-upload-images" type="file" name="" id="ct-upload-imagepppp<?php echo $staff_id; ?>" data-id="<?php echo $staff_id; ?>" />
								<?php if ($doc_image == '') { ?>
									<label for="ct-upload-imagepppp<?php echo $staff_id; ?>" class="ct-clean-staff-img-icon-label old_cam_ser<?php echo $staff_id; ?>">
										<i class="ct-camera-icon-common br-100 fa fa-camera" id="pcls<?php echo $staff_id; ?>camera"></i>
										<i class="pull-left fa fa-plus-circle fa-2x" id="ctsc<?php echo $staff_id; ?>plus"></i>
									</label>
								<?php } ?>
								<label for="ct-upload-imagepppp<?php echo $staff_id; ?>" class="ct-clean-staff-img-icon-label new_cam_ser ser_cam_btn<?php echo $staff_id; ?>" id="ct-upload-imagepppp<?php echo $staff_id; ?>" style="display:none;">
									<i class="ct-camera-icon-common br-100 fa fa-camera stfp-cam-icon" id="pppp<?php echo $staff_id; ?>camera"></i>
									<i class="pull-left fa fa-plus-circle fa-2x stfp-add-icon" id="ctsc<?php echo $staff_id; ?>plus"></i>
								</label>
								<?php if ($doc_image !== '') { ?>
									<a id="ct-remove-staff-imagepppp<?php echo $staff_id; ?>" data-pclsid="<?php echo $staff_id; ?>" data-staff_id="<?php echo $staff_id; ?>" class="delete_staff_image pull-left br-100 btn-danger bt-remove-staff-img btn-xs ser_new_del<?php echo $staff_id; ?>" rel="popover" data-placement='left' title="<?php echo $label_language_values['remove_image']; ?>"> <i class="fa fa-trash" title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a>
								<?php } ?>
								<label><b class="error-service error_image" style="color:red;"></b></label>
								<div id="popover-ct-remove-staff-imagepppp<?php echo $staff_id; ?>" style="display: none;">
									<div class="arrow"></div>
									<table class="form-horizontal" cellspacing="0">
										<tbody>
											<tr>
												<td>
													<a href="javascript:void(0)" id="staff_del_images" value="Delete" data-staff_id="<?php echo $staff_id; ?>" class="btn btn-danger btn-sm" type="submit"><?php echo $label_language_values['yes']; ?></a>
													<a href="javascript:void(0)" id="ct-close-popover-staff-image" class="btn btn-default btn-sm" href="javascript:void(0)"><?php echo $label_language_values['cancel']; ?></a>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="ct-profile-hero-meta">
							<h2><?php echo htmlspecialchars($doc_name !== '' ? $doc_name : 'Doctor'); ?></h2>
							<p><?php echo htmlspecialchars($doc_email); ?></p>
							<span class="ct-profile-badge">Doctor</span>
						</div>
					</div>

					<div id="ct-image-upload-popuppppp<?php echo $staff_id; ?>" class="ct-image-upload-popup modal fade" tabindex="-1" role="dialog">
						<div class="vertical-alignment-helper">
							<div class="modal-dialog modal-md vertical-align-center">
								<div class="modal-content">
									<div class="modal-header">
										<div class="col-md-12 col-xs-12">
											<a data-staff_id="<?php echo $staff_id; ?>" data-us="pppp<?php echo $staff_id; ?>" class="btn btn-success ct_upload_img_staff" data-imageinputid="ct-upload-imagepppp<?php echo $staff_id; ?>" data-id="<?php echo $staff_id; ?>"><?php echo $label_language_values['crop_and_save']; ?></a>
											<button type="button" class="btn btn-default hidemodal" data-dismiss="modal" aria-hidden="true"><?php echo $label_language_values['cancel']; ?></button>
										</div>
									</div>
									<div class="modal-body">
										<img id="ct-preview-imgpppp<?php echo $staff_id; ?>" style="width: 100%;" />
									</div>
									<div class="modal-footer">
										<div class="col-md-12 np">
											<div class="col-md-12 np">
												<div class="col-md-4 col-xs-12">
													<label class="pull-left"><?php echo $label_language_values['file_size']; ?></label> <input type="text" class="form-control" id="ppppfilesize<?php echo $staff_id; ?>" name="filesize" />
												</div>
												<div class="col-md-4 col-xs-12">
													<label class="pull-left">H</label> <input type="text" class="form-control" id="pppp<?php echo $staff_id; ?>h" name="h" />
												</div>
												<div class="col-md-4 col-xs-12">
													<label class="pull-left">W</label> <input type="text" class="form-control" id="pppp<?php echo $staff_id; ?>w" name="w" />
												</div>
												<input type="hidden" id="pppp<?php echo $staff_id; ?>x1" name="x1" />
												<input type="hidden" id="pppp<?php echo $staff_id; ?>y1" name="y1" />
												<input type="hidden" id="pppp<?php echo $staff_id; ?>x2" name="x2" />
												<input type="hidden" id="pppp<?php echo $staff_id; ?>y2" name="y2" />
												<input type="hidden" id="pppp<?php echo $staff_id; ?>id" name="id" value="<?php echo $staff_id; ?>" />
												<input id="ppppctimage<?php echo $staff_id; ?>" type="hidden" name="ctimage" />
												<input type="hidden" id="recordid" value="<?php echo $staff_id; ?>">
												<input type="hidden" id="pppp<?php echo $staff_id; ?>ctimagename" class="ppppimg" name="ctimagename" value="<?php echo htmlspecialchars($doc_image); ?>" />
												<input type="hidden" id="pppp<?php echo $staff_id; ?>newname" value="staff_" />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<form id="staff_update_details">
						<div class="ct-profile-card">
							<h3><i class="fa fa-user-md"></i> <?php echo $label_language_values['personal_information']; ?></h3>
							<div class="ct-profile-grid">
								<div class="ct-profile-field">
									<label for="ct-member-name"><?php echo $label_language_values['name']; ?></label>
									<input type="text" class="form-control" id="ct-member-name" value="<?php echo htmlspecialchars($doc_name); ?>" name="u_member_name" />
								</div>
								<div class="ct-profile-field">
									<label for="ct-member-email"><?php echo $label_language_values['email']; ?></label>
									<input type="text" class="form-control" id="ct-member-email" readonly value="<?php echo htmlspecialchars($doc_email); ?>" name="" />
								</div>
								<div class="ct-profile-field">
									<label for="phone-number"><?php echo $label_language_values['phone']; ?></label>
									<input type="tel" class="form-control" id="phone-number" name="phone-number" value="<?php echo htmlspecialchars($doc_phone); ?>" />
								</div>
								<div class="ct-profile-field full">
									<label for="ct-member-desc"><?php echo $label_language_values['description']; ?></label>
									<textarea class="form-control" id="ct-member-desc" name="ct-member-desc"><?php echo htmlspecialchars($doc_desc); ?></textarea>
								</div>
								<div class="ct-profile-field full">
									<label for="ct-member-address"><?php echo $label_language_values['address']; ?></label>
									<input type="text" class="form-control" name="ct-member-address" id="ct-member-address" placeholder="Member Street Address" value="<?php echo htmlspecialchars($doc_address); ?>" />
								</div>
								<div class="ct-profile-field">
									<label for="ct-member-city"><?php echo $label_language_values['city']; ?></label>
									<input class="form-control value_city" id="ct-member-city" name="ct-member-city" value="<?php echo htmlspecialchars($doc_city); ?>" type="text">
								</div>
								<div class="ct-profile-field">
									<label for="ct-member-state"><?php echo $label_language_values['state']; ?></label>
									<input class="form-control value_state" id="ct-member-state" name="ct-member-state" type="text" value="<?php echo htmlspecialchars($doc_state); ?>">
								</div>
								<div class="ct-profile-field">
									<label for="ct-member-zip"><?php echo $label_language_values['zip']; ?></label>
									<input class="form-control value_zip" id="ct-member-zip" name="ct-member-zip" type="text" value="<?php echo htmlspecialchars($doc_zip); ?>">
								</div>
								<div class="ct-profile-field">
									<label for="ct-member-country"><?php echo $label_language_values['country']; ?></label>
									<input class="form-control value_country" id="ct-member-country" name="ct-member-countrys" type="text" value="<?php echo htmlspecialchars($doc_country); ?>">
								</div>
								<?php
								$assigned_services = isset($staff_read['service_ids']) ? $staff_read['service_ids'] : (isset($staff_read[17]) ? $staff_read[17] : '');
								/* Keep selected services in a hidden select so Save does not clear mappings */
								?>
								<select id="ct_service_staff" multiple class="hidden" style="display:none !important;" aria-hidden="true" tabindex="-1">
									<?php
									$getservice = $objservices->getalldata();
									if ($getservice && $getservice->num_rows > 0) {
										$get_service_assignid = array_filter(array_map('trim', explode(",", (string)$assigned_services)));
										while ($arr = @mysqli_fetch_array($getservice)) {
											$svc_id = isset($arr['id']) ? $arr['id'] : $arr[0];
											if (!in_array((string)$svc_id, $get_service_assignid, true) && !in_array($svc_id, $get_service_assignid, false)) {
												continue;
											}
											echo "<option selected='selected' value='" . htmlspecialchars((string)$svc_id) . "'></option>";
										}
									}
									?>
								</select>
								<div class="ct-profile-field" style="display:none">
									<input class="form-control value_APIUsername" id="APIUsername" name="APIUsername" value="<?php echo isset($staff_read[19]) ? htmlspecialchars($staff_read[19]) : ''; ?>" type="text">
									<input class="form-control value_APIPassword" id="APIPassword" name="APIPassword" value="<?php echo isset($staff_read[20]) ? htmlspecialchars($staff_read[20]) : ''; ?>" type="text">
									<input class="form-control value_APISignature" id="APISignature" name="APISignature" value="<?php echo isset($staff_read[21]) ? htmlspecialchars($staff_read[21]) : ''; ?>" type="text">
									<input type="checkbox" id="APItestmode" <?php if (isset($staff_read[22]) && $staff_read[22] == "Y") { echo "checked"; } ?> />
									<input class="form-control value_latitude" readonly id="ct-member-latitude" name="ct-member-latitude" type="hidden" value="<?php echo isset($staff_read['latitude']) ? htmlspecialchars($staff_read['latitude']) : (isset($staff_read[24]) ? htmlspecialchars($staff_read[24]) : ''); ?>">
									<input class="form-control value_longitude" readonly id="ct-member-longitude" name="ct-member-longitude" type="hidden" value="<?php echo isset($staff_read['longitude']) ? htmlspecialchars($staff_read['longitude']) : (isset($staff_read[25]) ? htmlspecialchars($staff_read[25]) : ''); ?>">
								</div>
								<div class="ct-profile-field">
									<label for="enable-booking1"><?php echo $label_language_values['enable_booking']; ?></label>
									<div>
										<input type="checkbox" id="enable-booking1" data-toggle="toggle" data-size="small" data-on="<?php echo $label_language_values['yes']; ?>" <?php if ($doc_enable == "Y") { echo "checked"; } ?> data-off="<?php echo $label_language_values['no']; ?>" data-onstyle="success" data-offstyle="danger" />
									</div>
								</div>
								<div class="ct-profile-field">
									<label>Rating</label>
									<input id="ratings_staff_display" name="ratings_staff_display" class="rating" data-min="0" data-max="5" data-step="0.1" value="<?php echo $rating_point; ?>" />
								</div>
							</div>
							<div class="ct-profile-actions">
								<a href="javascript:void(0)" id="update_staff_details_staffsection" data-old_schedule_type="" class="btn btn-success ct-btn-width" data-id="<?php echo $doc_id; ?>"><?php echo $label_language_values['save']; ?></a>
								<a href="javascript:void(0)" id="change_password_link" class="btn btn-default ct-btn-width"><?php echo $label_language_values['change_password']; ?></a>
							</div>
						</div>
					</form>

					<div id="staff_password_update" class="ct-profile-card" style="display:none;">
						<h3><i class="fa fa-lock"></i> <?php echo $label_language_values['change_password']; ?></h3>
						<form id="staff_password_update_form" novalidate="novalidate">
							<div class="ct-profile-grid">
								<div class="ct-profile-field full">
									<label><?php echo $label_language_values['old_password']; ?></label>
									<input type="password" class="form-control" name="staff_old_password" id="staff_old_password" />
									<input type="hidden" class="form-control" name="staff_olddb_password" id="staff_olddb_password" value="<?php echo htmlspecialchars($doc_pass); ?>" />
									<label id="msg_oldps" class="old_pass_msg error"></label>
								</div>
								<div class="ct-profile-field">
									<label><?php echo $label_language_values['new_password']; ?></label>
									<input type="password" class="form-control" name="staff_new_password" id="staff_new_password" />
								</div>
								<div class="ct-profile-field">
									<label><?php echo $label_language_values['retype_new_password']; ?></label>
									<input type="password" class="form-control" name="staff_retype_new_password" id="staff_retype_new_password" />
								</div>
							</div>
							<div class="ct-profile-actions">
								<a id="update_staff_password" class="btn btn-success ct-btn-width" data-id="<?php echo $doc_id; ?>"><?php echo $label_language_values['update']; ?></a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
include(dirname(dirname(__FILE__)) . '/admin/footer.php');
?>
<script type="text/javascript">
	var ajax_url = '<?php echo AJAX_URL; ?>';
	var servObj = {
		'site_url': '<?php echo SITE_URL . 'assets/images/business/'; ?>'
	};
	var imgObj = {
		'img_url': '<?php echo SITE_URL . 'assets/images/'; ?>'
	};
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript">
	$('#datepicker_from').datepicker({

	});

	$('#datepicker_to').datepicker({

	});
</script>