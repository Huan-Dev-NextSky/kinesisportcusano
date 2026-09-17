<?php
/**
 * Customer — List Bookings (table view).
 * Calendar view remains on my-appointments.php.
 */
include (dirname(__FILE__) . '/header.php');
include (dirname(__FILE__) . '/admin_session_check.php');
include (dirname(dirname(__FILE__)) . "/objects/class_userdetails.php");
include (dirname(dirname(__FILE__)) . "/objects/class_booking.php");
include_once (dirname(dirname(__FILE__)) . '/objects/class_front_first_step.php');
include (dirname(dirname(__FILE__)) . "/objects/class_rating_review.php");
include (dirname(dirname(__FILE__)) . "/objects/class_frequently_discount.php");
include (dirname(dirname(__FILE__)) . "/objects/class_order_client_info.php");

if (!isset($_SESSION['ct_login_user_id']))
{
    header('Location:' . SITE_URL . "admin/");
    exit;
}
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;
$booking = new cleanto_booking();
$booking->conn = $conn;
$setting = new cleanto_setting();
$setting->conn = $conn;
$general = new cleanto_general();
$general->conn = $conn;
$first_step = new cleanto_first_step();
$first_step->conn = $conn;
$rating_review = new cleanto_rating_review();
$rating_review->conn = $conn;
$frequently_discount = new cleanto_frequently_discount();
$frequently_discount->conn = $conn;
$objocinfo = new cleanto_order_client_info();
$objocinfo->conn = $conn;
$symbol_position = $setting->get_option('ct_currency_symbol_position');
$decimal = $setting->get_option('ct_price_format_decimal_places');
$getdateformat = $setting->get_option('ct_date_picker_date_format');
$time_format = $setting->get_option('ct_time_format');
$date_format = $setting->get_option('ct_date_picker_date_format');
$getmaximumbooking = $setting->get_option('ct_max_advance_booking_time');
$t_zone_value = $setting->get_option('ct_timezone');
$server_timezone = date_default_timezone_get();
if (isset($t_zone_value) && $t_zone_value != '')
{
    $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
    $timezonediff = $offset / 3600;
}
else
{
    $timezonediff = 0;
}
if (is_numeric(strpos($timezonediff, '-')))
{
    $timediffmis = str_replace('-', '', $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
}
else
{
    $timediffmis = str_replace('+', '', $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
}
$list_bookings_label = isset($label_language_values['bookings']) && $label_language_values['bookings'] !== ''
	? $label_language_values['bookings']
	: 'List Bookings';
?>
	<div id="cta-user-appointments" class="ct-customer-list-bookings">    
		<div class="panel-body">        
			<div class="tab-content">            
				<h4 class="header4"><?php echo htmlspecialchars($list_bookings_label); ?>
					<a href="<?php echo SITE_URL; ?>" class="btn btn-success pull-right" target="_BLANK"><?php echo $label_language_values['book_appointment']; ?></a>
				</h4>

				<?php
$ct_customer_show_booking_table = true;
include(dirname(__FILE__) . '/includes/customer_booking_modals.php');
?>
<?php if ($gc_hook->gc_purchase_status() == 'exist')
{
    if ($setting->get_option('ct_gc_status_configure') == 'Y' && $setting->get_option('ct_gc_status') == 'Y')
    { ?>
    	<input type="hidden" id="extension_js" value="true" />	
	<?php } else  { ?>		
		<input type="hidden" id="extension_js" value="false" />        
    <?php } } ?>
<script type="text/javascript">
	window.ct_customer_calendar = false;
	window.ct_doctor_calendar_readonly = false;
</script>
<?php
include (dirname(__FILE__) . '/footer.php'); ?>
