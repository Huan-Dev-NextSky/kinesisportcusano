<?php 

if(isset($_POST['action']) && $_POST['action']=='edit_complete_bookings'){
	ob_start();
	session_start();
	include(dirname(dirname(__FILE__)).'/header.php');
	include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
	include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
	include(dirname(dirname(__FILE__)).'/objects/class_booking.php');
	include(dirname(dirname(__FILE__)).'/objects/class_services.php');
	include(dirname(dirname(__FILE__)).'/objects/class_front_first_step.php');
	include(dirname(dirname(__FILE__)).'/objects/class_users.php');
	include(dirname(dirname(__FILE__)).'/objects/class_order_client_info.php');
	include(dirname(dirname(__FILE__)).'/objects/class_coupon.php');
	include(dirname(dirname(__FILE__)).'/objects/class_frequently_discount.php');
	include(dirname(dirname(__FILE__)).'/objects/class_payments.php');
	include(dirname(dirname(__FILE__)).'/objects/class.phpmailer.php');
	include(dirname(dirname(__FILE__)).'/objects/class_general.php');
	include(dirname(dirname(__FILE__)).'/objects/class_email_template.php');
	include(dirname(dirname(__FILE__)).'/objects/class_adminprofile.php');
	include(dirname(dirname(__FILE__)).'/objects/plivo.php');
	include(dirname(dirname(__FILE__)).'/assets/twilio/Services/Twilio.php');
	include(dirname(dirname(__FILE__))."/objects/class_dashboard.php");
	include(dirname(dirname(__FILE__))."/objects/class_nexmo.php");
	
	$con = new cleanto_db();
	$conn = $con->connect();
	
	$setting = new cleanto_setting();
	$setting->conn = $conn;

	$booking=new cleanto_booking();
	$booking->conn=$conn;
	
	$objdashboard = new cleanto_dashboard();
	$objdashboard->conn = $conn;

	$objadminprofile = new cleanto_adminprofile();
	$objadminprofile->conn = $conn;

	$nexmo_admin = new cleanto_ct_nexmo();
	$nexmo_client = new cleanto_ct_nexmo();

	$first_step=new cleanto_first_step();
	$first_step->conn=$conn;

	$email_template = new cleanto_email_template();
	$email_template->conn=$conn;

	$general=new cleanto_general();
	$general->conn=$conn;

	$user=new cleanto_users();
	$order_client_info=new cleanto_order_client_info();
	$settings=new cleanto_setting();
	$coupon=new cleanto_coupon();
	$frequently_discount = new cleanto_frequently_discount();
	$payment = new cleanto_payments();
	$service = new cleanto_services();

	$frequently_discount->conn = $conn;
	$user->conn=$conn;
	$order_client_info->conn=$conn;
	$settings->conn=$conn;
	$coupon->conn=$conn;
	$payment->conn=$conn;
	$service->conn=$conn;

	$last_order_id=$booking->last_booking_id();

	$symbol_position=$settings->get_option('ct_currency_symbol_position');
	$decimal=$settings->get_option('ct_price_format_decimal_places');

	$company_email=$settings->get_option('ct_email_sender_address');
	$company_name=$settings->get_option('ct_email_sender_name');
	
	if(isset($_POST['recurrence_booking'])){
		$recurrence_booking_status = $_POST['recurrence_booking'];
	}else{
		$recurrence_booking_status = '';
	}
	
  if ($_POST['discount'] == 'undefined' || $_POST['discount'] == '' || $_POST['discount'] == 'undefined- undefined') {
    $_POST['discount'] = 0.00;
  }else{
    $_POST['discount'] = $_POST['discount'];
  }

	$total_discount =  @number_format($_POST['frequent_discount_amount'],2,".",',') + @number_format($_POST['discount'],2,".",',');

	if($setting->get_option("ct_tax_vat_status") == 'N'){
		$tax = 0;
	}else{
		$tax = $_POST['taxes'];
	}

  $t_zone_value = $settings->get_option('ct_timezone');
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
	$current_time = date('Y-m-d H:i:s',$currDateTime_withTZ);

  if ($_POST['order_id']) {
    if(count((array)$_SESSION["ct_cart"]) != 0) {
      $booking->order_id=$_POST['order_id'];
      $chk_booking=$booking->chk_and_get_booking();
      if($chk_booking){
        $client_id = $chk_booking['client_id'];
        $order_date = $chk_booking['order_date'];
        $booking_date_time = $chk_booking['booking_date_time'];
        $booking_status = $chk_booking['booking_status'];
        $delete_booking=$booking->delete_method_addons_booking();
        if ($delete_booking) {
          for($i=0;$i<(count((array)$_SESSION['ct_cart']['method']));$i++){
            $booking->service_id=$_SESSION['ct_cart']['method'][$i]['service_id'];
            if($_SESSION['ct_cart']["method"][$i]["type"] == "method_units"){
              $booking->client_id=$client_id;
              $booking->order_date=$order_date;
              $booking->booking_date_time=$booking_date_time;
              $booking->method_id=$_SESSION['ct_cart']['method'][$i]['method_id'];
              $booking->method_unit_id=$_SESSION['ct_cart']['method'][$i]['units_id'];
              $booking->method_unit_qty=$_SESSION['ct_cart']['method'][$i]['s_m_qty'];
              $booking->method_unit_qty_rate=$_SESSION['ct_cart']['method'][$i]['s_m_rate'];
              $booking->booking_status=$booking_status;
              $booking->lastmodify=$current_time;
              $booking->read_status='U';
              $booking->staff_id= $_POST['staff_id'];
              $add_booking=$booking->add_booking();
            }elseif($_SESSION['ct_cart']["method"][$i]["type"] == "addon"){
              $booking->addons_service_id=$_SESSION['ct_cart']['method'][$i]['units_id'];
              $booking->addons_service_qty=$_SESSION['ct_cart']['method'][$i]['s_m_qty'];
              $booking->addons_service_rate=$_SESSION['ct_cart']['method'][$i]['s_m_rate'];
              $add_booking=$booking->add_addons_booking();
            }
          }
          
          $booking->order_id=$_POST['order_id'];
          $booking->amount=$_POST['amount'];
          $booking->discount=abs($total_discount);
          $booking->taxes=$_POST['taxes'];
          $booking->lastmodify=$current_time;
          $booking->net_amount=$_POST['net_amount'];
          $booking->frequently_discount=$_POST['frequently_discount'];
          $booking->frequently_discount_amount=abs($_POST['frequent_discount_amount']);
          $add_payment=$booking->edit_update_payments();
          
          $booking->order_id=$_POST['order_id'];
          $booking->order_duration=$_SESSION['time_duration'];
          $add_guest_user=$booking->edit_update_order_client();
          if($add_guest_user){
            echo "ok";
          }
        }
      }
    }
  }
}
?>