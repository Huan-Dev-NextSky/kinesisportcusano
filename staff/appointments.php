<?php
/**
 * Doctor Appointments — calendar view (read-only).
 * Same calendar UX as Root Admin Appointments; no edit/confirm/cancel/reschedule.
 */
include(dirname(__FILE__) . '/header-staff.php');

$staff_id = isset($_SESSION['ct_staffid']) ? (int)$_SESSION['ct_staffid'] : 0;
if (!$staff_id) {
?>
<script type="text/javascript">
	window.location = "<?php echo SITE_URL; ?>admin/";
</script>
<?php
	exit;
}

$settings = new cleanto_setting();
$settings->conn = $conn;
$gettimeformat = $settings->get_option('ct_time_format');
$global_vc_status = $settings->get_option('ct_vc_status');
$global_p_status = $settings->get_option('ct_p_status');
?>
<style>
#cta .fc-time-grid-event {
	min-height: 32px;
	overflow: auto;
	height: 102px !important;
}
#ct-doctor-appointments .ct-readonly-banner {
	margin: 10px 15px 18px;
	padding: 12px 16px;
	background: linear-gradient(90deg, #eef6fa 0%, #f7fafc 100%);
	border-left: 3px solid #1596e8;
	border-radius: 8px;
	color: #2c3a42;
	font-size: 13px;
}
</style>

<div id="ct-doctor-appointments" class="cta-panel-default">
	<div class="panel-body">
		<div class="ct-readonly-banner">
			<strong><?php echo isset($label_language_values['appointments']) ? $label_language_values['appointments'] : 'Appointments'; ?></strong>
			— View only. Contact Root Admin to change or cancel a booking.
		</div>

		<div id="ct-calendar-all">
			<div class="ct-legends-panel-body">
				<div class="ct-legends-main">
					<div class="ct-legends-inner">
						<ul class="list-inline nm">
							<li><h4><?php echo $label_language_values['legends']; ?>:</h4></li>
							<li><i class="fa fa-thumbs-o-up txt-completed"></i> <?php echo $label_language_values['completed']; ?></li>
							<li><i class="fa fa-check txt-success"></i> <?php echo $label_language_values['confirmed']; ?></li>
							<li><i class="fa fa-pencil-square-o txt-info"></i> <?php echo $label_language_values['rescheduled']; ?></li>
							<li><i class="fa fa-ban txt-danger"></i> <?php echo $label_language_values['rejected']; ?></li>
							<li><i class="fa fa-times txt-primary"></i> <?php echo $label_language_values['cancelled_by_client']; ?></li>
							<li><i class="fa fa-info-circle txt-warning"></i> <?php echo $label_language_values['pending']; ?></li>
						</ul>
					</div>
				</div>
			</div>
			<div id="calendar" class="ct-booking-calendar"></div>

			<!-- Booking details modal (view-only) — same structure as Admin -->
			<div id="booking-details-calendar" class="modal fade booking-details-calendar ct-doctor-booking-modal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="vertical-alignment-helper">
					<div class="modal-dialog modal-md vertical-align-center">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" id="info_modal_close" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title"><i class="fa fa-calendar-check-o"></i> <?php echo $label_language_values['booking_details']; ?></h4>
							</div>
							<div class="modal-body">
								<ul class="list-unstyled ct-cal-booking-details bkng-detl">
									<li class="ct-change-request-notice" style="display:none; width: 100%;"></li>
									<li class="ct-bd-card ct-bd-meta" style="width:100%;">
										<label><?php echo $label_language_values['booking_status']; ?></label>
										<div class="ct-booking-status"></div>
										<div class="myeditbookingclass" style="display:none;"></div>
										<div class="booking_date_set">
											<label><?php echo $label_language_values['booking_date']; ?></label>
											<span class="ct-bd-chip"><i class="fa fa-calendar"></i><span class="starttime"></span></span>
											<span class="ct-bd-chip"><i class="fa fa-clock-o"></i><span class="start_time"></span></span>
										</div>
									</li>
									<li class="ct-bd-card ct-bd-service" style="width:100%;">
										<h6 class="ct-bd-section-title"><i class="fa fa-briefcase"></i> <?php echo isset($label_language_values['services']) ? $label_language_values['services'] : 'Services'; ?></h6>
										<ul class="list-unstyled ct-bd-grid">
											<li class="ct-bd-full">
												<label><?php echo $label_language_values['service']; ?></label>
												<span class="service-html span-scroll"></span>
											</li>
											<li class="ct-bd-full">
												<label><?php echo $label_language_values['price']; ?></label>
												<span class="price span-scroll"></span>
											</li>
											<li class="ct-bd-full li_of_duration <?php if ($settings->get_option('ct_show_time_duration') == 'N') {echo "ct-bd-hidden";} ?>">
												<label><?php echo $label_language_values['duration']; ?></label>
												<span class="duration span-scroll"></span>
											</li>
										</ul>
									</li>
									<li class="ct-bd-card ct-bd-customer" style="width:100%;">
										<h6 class="ct-bd-section-title"><i class="fa fa-user"></i> <?php echo $label_language_values['customer']." Information"; ?></h6>
										<ul class="list-unstyled ct-bd-grid">
											<li>
												<label><?php echo $label_language_values['name']; ?></label>
												<span class="client_name span-scroll"></span>
											</li>
											<li>
												<label><?php echo $label_language_values['phone']; ?></label>
												<span class="client_phone span-scroll"></span>
											</li>
											<li class="ct-bd-full">
												<label><?php echo $label_language_values['email']; ?></label>
												<span class="client_email span-scroll"></span>
											</li>
											<li class="ct-bd-full">
												<label><?php echo $label_language_values['company_address']; ?></label>
												<a href="javascript:void(0)" id="address_on_map1" class="address_on_map1" data-toggle="modal1" lat="" lng="" target="_blank"><span class="client_address span-scroll"></span></a>
											</li>
											<li>
												<label><?php echo $label_language_values['payment']; ?></label>
												<span class="client_payment span-scroll"></span>
											</li>
											<?php if ($global_vc_status == 'Y') { ?>
											<li class="pop_vc_status ct-bd-hidden">
												<label><?php echo $label_language_values['vaccum_cleaner']; ?></label>
												<span class="client_vc_status span-scroll"></span>
											</li>
											<?php } ?>
											<?php if ($global_p_status == 'Y') { ?>
											<li class="pop_p_status ct-bd-hidden">
												<label><?php echo $label_language_values['parking']; ?></label>
												<span class="client_parking span-scroll"></span>
											</li>
											<?php } ?>
											<li class="ct-bd-full li_of_notes ct-bd-hidden">
												<label><?php echo $label_language_values['notes']; ?></label>
												<span class="notes span-scroll"></span>
											</li>
											<li class="ct-bd-full li_of_reason ct-bd-hidden">
												<label><?php echo $label_language_values['reason']; ?></label>
												<span class="reason span-scroll"></span>
											</li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="modal-footer" style="display:none;">
								<div class="col-xs-12 np ct-footer-popup-btn text-center"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(function ($) {
	$('.ct-loading-main').hide();
	window.ct_doctor_calendar_readonly = true;
});
</script>
