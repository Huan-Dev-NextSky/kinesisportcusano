<?php
include(dirname(__FILE__) . '/header.php');
include(dirname(dirname(__FILE__)) . "/objects/class_dayweek_avail.php");
include(dirname(dirname(__FILE__)) . "/objects/class_staff_commision.php");
include(dirname(__FILE__) . '/user_session_check.php');
include(dirname(dirname(__FILE__)) . "/objects/class_adminprofile.php");
include(dirname(dirname(__FILE__)) . "/objects/class_offbreaks.php");
include(dirname(dirname(__FILE__)) . "/objects/class_offtimes.php");
$obj_offtime = new cleanto_offtimes();
$obj_offtime->conn = $conn;
$setting = new cleanto_setting();
$setting->conn = $conn;
$getdateformat = $setting->get_option('ct_date_picker_date_format');
$con = new cleanto_db();
$conn = $con->connect();
$objdayweek_avail = new cleanto_dayweek_avail();
$objdayweek_avail->conn = $conn;
$objoffbreaks = new cleanto_offbreaks();
$objoffbreaks->conn = $conn;
$time_int = $objdayweek_avail->getinterval();
$time_interval = $time_int[2];
$time_format = $setting->get_option('ct_time_format');
$objadmin = new cleanto_adminprofile();
$objadmin->conn = $conn;
$staff_commision = new cleanto_staff_commision();
$staff_commision->conn = $conn;
$getdateformat = $setting->get_option('ct_date_picker_date_format');
$time_format = $setting->get_option('ct_time_format');
$timess = "";

if ($time_format == "24") {
	$timess = "H:i";
} else {
	$timess = "h:i A";
}

$symbol_position = $setting->get_option('ct_currency_symbol_position');
$decimal = $setting->get_option('ct_price_format_decimal_places');
$getcurrency_symbol_position = $setting->get_option('ct_currency_symbol_position');

$kinesis_employees = array();
$used_kinesis_employee_ids = array();
$usedEmpQ = mysqli_query($conn, "SELECT `external_employee_id` FROM `ct_admin_info` WHERE `role` != 'admin' AND `external_employee_id` IS NOT NULL AND `external_employee_id` != 0");
if ($usedEmpQ) {
	while ($usedRow = mysqli_fetch_assoc($usedEmpQ)) {
		$used_kinesis_employee_ids[(string)$usedRow['external_employee_id']] = true;
	}
}
if ($setting->get_option('kinesis_api_status') === 'Y') {
	require_once dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwApiClient.php';
	$awwClient = new AwwApiClient($conn);
	$empRes = $awwClient->getAllEmployees();
	if ($empRes['success'] && !empty($empRes['data'])) {
		foreach ($empRes['data'] as $kEmp) {
			$kId = isset($kEmp['id']) ? $kEmp['id'] : (isset($kEmp['Id']) ? $kEmp['Id'] : '');
			if ($kId === '' || isset($used_kinesis_employee_ids[(string)$kId])) {
				continue;
			}
			$kinesis_employees[] = $kEmp;
		}
	}
}
?>
<style>

#cta div.dataTables_wrapper div.dataTables_paginate {
    float: right;
    z-index: 99;

    display: flex !important;
	justify-content:end !important;
}

#cta #cta-export-details div.dt-buttons, #cta #cta-staff-panel div.dt-buttons {
    text-align: center;
    float: none;
    margin-top: 142px !important;
}
	</style>
<div id="cta-staff-panel" class="panel tab-content np">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h1 class="panel-title">Doctors &amp; Specialists Management</h1>
		</div>
		<div class="panel-body">
			<ul class="nav nav-tabs ct-segment-tabs">
				<li class="active"><a data-toggle="tab" href="#add-new-staff"><i class="fa fa-user-md"></i> Doctors Management</a></li>
				<li><a data-toggle="tab" href="#staff-booking-payments"><i class="fa fa-money"></i> Doctor Bookings &amp; Payments</a></li>
			</ul>
			<div class="tab-content">
				<div id="add-new-staff" class="tab-pane fade in active">
					<div id="accordion" class="panel-group">
						<div class="ct-staff-list col-md-3 col-sm-4 col-xs-12 col-lg-3">
							<div class="ct-staff-container">
								<h3>Doctors
									<span>(<?php echo $objadmin->countall_staff(); ?>)</span>
									<a href="#ct-add-new-staff" title="Add New Doctor" role="button" class="btn btn-info pull-right" data-toggle="modal"><i class="fa fa-user-plus"></i> Add New Doctor</a>
								</h3>

								<!-- end popover -->
								<ul class="nav nav-tab nav-stacked ct-left-staff" id="sortable">
									<?php $all_staff = $objadmin->readall_staff();
									$i = 1;
									if($all_staff->num_rows > 0){
									while ($arr_staff = mysqli_fetch_array($all_staff)) {
										if ($i == 1) {
											$trigger =  $i;
										} else {
											$trigger = "0";
										}
										$objadmin->id = $arr_staff['id'];
										$staff_read = $objadmin->readone();
										if ($staff_read[16] == '') {
											$imagepath = SITE_URL . "assets/images/user.png";
										} else {
											$imagepath = SITE_URL . "assets/images/services/" . $staff_read[16];
										}
									?>

										<li class="staff-list br-2 staff_click staff_<?php echo $trigger; ?> staff_c_<?php if(isset($arr_staff['id'])){ echo $arr_staff['id']; }else{ } ?>" id="" data-id="<?php if(isset($arr_staff['id'])){ echo $arr_staff['id']; }else{ } ?>">
											<a href="" data-toggle="pill">
												<span class="ct-staff-image"><img class="ct-staf-img-small 
					    			small-staff-image<?php if(isset($arr_staff['id'])){ echo $arr_staff['id']; }else{ } ?>" src="<?php
																							echo $imagepath; ?>" /></span>
												<span class="ct-staff-name"><?php if(isset($arr_staff['id'])){ echo $arr_staff['fullname']; }else{ } ?></span>
											</a>
										</li>
									<?php $i++;
									} } ?>
								</ul>
							</div>
						</div>
						<div class="panel-body get_staff_details">
						</div>
					</div>
				</div>
				<div id="staff-booking-payments" class="tab-pane fade">
					<div class="panel-body pall-15">
						<h3>Doctor Booking Details and Payment
						</h3>
						<div id="accordion" class="panel-group">
							<div class="ct-calendar-top-bar">
								<div class="col-md-4 col-sm-6 col-xs-12 col-lg-4 mb-10"> <label><?php echo $label_language_values['select_option_to_show_bookings']; ?></label>
									<div id="reportrange" class="form-control">
										<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp; <span></span> <i class="fa fa-caret-down"></i>
									</div>
								</div>
								<div class="col-md-3 col-sm-12 sel-ser col-xs-12 col-lg-3 mb-10">
									<label><?php echo $label_language_values['select_service']; ?></label><br />
									<select name="" class="selectpicker get_serid_for_staff_pymnt" data-size="10" style="display: none;" data-live-search="true">
										<option value="all" selected><?php echo $label_language_values['all_services']; ?>
										</option>
										<?php
										$readall_staff_pymt_ser = $staff_commision->readall_staff_pymt_ser();
										if($readall_staff_pymt_ser->num_rows > 0){
										while ($allser = mysqli_fetch_array($readall_staff_pymt_ser)) { ?>
											<option value="<?php if(isset($allser['id'])){ echo $allser['id']; }else{ } ?>"><?php if(isset($allser['title'])){ echo $allser['title']; }else{ } ?></option> <?php } }	?>
									</select>
								</div>
								<div class="col-md-3 col-sm-6 col-xs-6 col-lg-3 mb-10">
									<button type="button" class="form-group btn btn-info ct-btn-width ct-submit-btn mt-20 get_staff_bookingandpayment_by_dateser" name=""><?php echo $label_language_values['submit']; ?></button>
								</div>
							</div>
							<div class="table-responsive ser_staffpayment_append">
								<table id="staff-payments-details" class="display responsive nowrap table table-striped table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th><?php echo $label_language_values['service'] . " " . $label_language_values['name']; ?></th>
											<th><?php echo $label_language_values['app_date']; ?></th>
											<th><?php echo $label_language_values['customer'] . " " . $label_language_values['name']; ?></th>
											<th><?php echo $label_language_values['status']; ?></th>
											<th>Doctor Name</th>
											<th><?php echo $label_language_values['net_total']; ?></th>
											<th><?php echo $label_language_values['commission_total']; ?></th>
											<th><?php echo $label_language_values['action']; ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$all_bookings = $staff_commision->readall_booking();
										if($all_bookings->num_rows > 0){
										while ($all = mysqli_fetch_array($all_bookings)) {
											$service_name = $staff_commision->get_service_name($all['service_id']);
											$client_name = $staff_commision->get_client_name($all['client_id']);
											$staff_name = $staff_commision->get_staff_name($all['staff_ids']);
											$net_total = $staff_commision->get_net_total($all['order_id']);
											$data_staff_pymnt_id = explode(',', $all['staff_ids']);
											$get_booking_nettotal = 0;

											for ($i = 0; $i < sizeof((array)$data_staff_pymnt_id); $i++) {
												$staff_id = $data_staff_pymnt_id[$i];
												$get_booking_nettotal += (float)$staff_commision->get_booking_nettotal($staff_id, $all['order_id']);
											}
											if ($all['booking_status'] == 'A') {
												$status = 'Active';
											} elseif ($all['booking_status'] == 'C') {
												$status = 'Confirm';
											} elseif ($all['booking_status'] == 'R') {
												$status = 'Rejected';
											} elseif ($all['booking_status'] == 'CC') {
												$status = 'Cancelled By Client';
											} elseif ($all['booking_status'] == 'CS') {
												$status = 'Cancelled By Doctor';
											} elseif ($all['booking_status'] == 'CO') {
												$status = 'Completed';
											} elseif ($all['booking_status'] == 'MN') {
												$status = 'Mark As No Show';
											} elseif ($all['booking_status'] == 'RS') {
												$status = 'Rescheduled';
											}
										?>
											<tr>
												<td><?php if(isset($all['order_id'])){ echo $all['order_id']; }else{ } ?></td>
												<td><?php echo $service_name; ?></td>
												<td><?php echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($all['booking_date_time']))); ?></td>
												<td><?php echo $client_name; ?></td>
												<td><?php echo $status; ?></td>
												<td><?php echo rtrim($staff_name); ?></td>
												<td><?php echo  $general->ct_price_format($net_total, $symbol_position, $decimal); ?></td>
												<td><?php echo $general->ct_price_format($get_booking_nettotal, $symbol_position, $decimal); ?></td>
												<td><a href="#add-staff-payment" role="button" class="btn btn-success show_staff_payment_details" data-toggle="modal" data-order_id="<?php if(isset($all['order_id'])){ echo $all['order_id']; }else{ } ?>" data-staff_ids="<?php echo $all['staff_ids']; ?>">Doctor Payment</a></td>
											</tr>
										<?php 	} }	?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Add new staff -->

<div id="ct-add-new-staff" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><i class="fa fa-user-md"></i> Add New Doctor (Specialist)</h4>
			</div>
			<div class="modal-body">
				<form id="staff_insert">
					<input type="hidden" class="staff_role" id="staff_role" name="staff_role" value="doctor" />
					<table class="form-horizontal" cellspacing="0" class="table-responsive">
						<tbody>
							<tr class="form-field">
								<td><label for="external_employee_id">Kinesis Employee (API)</label></td>
								<td>
									<select class="form-control external_employee_id" id="external_employee_id" name="external_employee_id">
										<option value="" data-name="" data-email="">-- Select Kinesis Employee (None / Local Only) --</option>
										<?php if (!empty($kinesis_employees)) { 
											foreach ($kinesis_employees as $kEmp) { 
												$kId = isset($kEmp['id']) ? $kEmp['id'] : (isset($kEmp['Id']) ? $kEmp['Id'] : '');
												$kName = isset($kEmp['name']) ? $kEmp['name'] : (isset($kEmp['fullName']) ? $kEmp['fullName'] : (isset($kEmp['firstName']) ? ($kEmp['firstName'] . ' ' . (isset($kEmp['lastName']) ? $kEmp['lastName'] : '')) : 'Employee #' . $kId));
												$kEmail = isset($kEmp['email']) ? $kEmp['email'] : (isset($kEmp['Email']) ? $kEmp['Email'] : '');
											?>
												<option value="<?php echo $kId; ?>" data-name="<?php echo htmlspecialchars($kName); ?>" data-email="<?php echo htmlspecialchars($kEmail); ?>"><?php echo "ID: " . $kId . " - " . htmlspecialchars($kName) . ($kEmail ? " (" . htmlspecialchars($kEmail) . ")" : ""); ?></option>
											<?php } 
										} else { ?>
												<option value="" disabled>No available Kinesis employees (all already mapped)</option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr class="form-field form-required">
								<td><label for="ab-newstaff-fullname"><?php echo $label_language_values['name']; ?> <span class="error">*</span></label></td>
								<td><input type="text" class="form-control staff_name" id="staff_name" name="staff_name" required="required" placeholder="Doctor Full Name" />
								</td>
							</tr>
							<tr class="form-field form-required">
								<td><label for="ab-newstaff-fullname"><?php echo $label_language_values['email']; ?> <span class="error">*</span>
									</label></td>
								<td><input type="email" placeholder="Doctor Email address" class="form-control staff_email" id="staff_email" name="staff_email" required="required" /></td>
							</tr>
							<tr class="form-field form-required">
								<td><label for="staff_pass"><?php echo $label_language_values['password']; ?> <span class="error">*</span></label></td>
								<td><input type="password" class="form-control staff_pass" placeholder="Type password" id="staff_pass" name="staff_pass" required="required" /></td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success save_staff staff-create-btn"><?php echo $label_language_values['create']; ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $label_language_values['close']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Modal manage payment staff -->
<div id="add-staff-payment" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content ">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Add Payment to Doctor account</h4>
			</div>
			<div class="modal-body">
				<table id="staff-payments-adding" class="display responsive nowrap table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Doctor Name</th>
							<th><?php echo $label_language_values['amount_payable']; ?></th>
							<th><?php echo $label_language_values['advance_paid']; ?></th>
							<th><?php echo $label_language_values['net_total']; ?></th>
						</tr>
					</thead>
					<tbody class="custm_staff_payment_details">
					</tbody>
				</table>
				<span class="error hide comission_error_display"><?php echo $label_language_values['commission_amount_should_not_be_greater_then_order_amount']; ?>

				</span>
			</div>
			<div class="modal-footer">
				<button type="button" clastextareas="btn btn-default close_spc_popup" data-dismiss="modal"><?php echo $label_language_values['close']; ?>

				</button>
				<button type="button" class="btn btn-primary save_sp_staff_commision"><?php echo $label_language_values['save_changes']; ?></button>
			</div>
		</div>
	</div>
</div>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
<script type="text/javascript">
	var ajax_url = '<?php echo AJAX_URL; ?>';
	var servObj = {
		'site_url': '<?php echo SITE_URL . 'assets/images/business/'; ?>'
	};
	var imgObj = {
		'img_url': '<?php echo SITE_URL . 'assets/images/'; ?>'
	};
</script>