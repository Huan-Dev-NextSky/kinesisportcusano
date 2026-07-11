<?php
session_start();
include(dirname(dirname(__FILE__)).'/header.php');
include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
include(dirname(dirname(__FILE__)).'/objects/class_booking.php');
include(dirname(dirname(__FILE__)).'/objects/class_payment_hook.php');
include(dirname(dirname(__FILE__)).'/objects/class_services.php');
include(dirname(dirname(__FILE__))."/objects/class_userdetails.php");
include(dirname(dirname(__FILE__))."/objects/class_payments.php");
include(dirname(dirname(__FILE__))."/objects/class_adminprofile.php");
include(dirname(dirname(__FILE__))."/objects/class_front_first_step.php");
if(!isset($_SESSION['ct_login_user_id'])){
  header('Location:'.SITE_URL."admin/");
}
$con = new cleanto_db();
$conn = $con->connect();

$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

$admin_profile=new cleanto_adminprofile();
$admin_profile->conn=$conn;

$settings=new cleanto_setting();
$settings->conn = $conn;

$payments=new cleanto_payments();
$payments->conn = $conn;

$first_step=new cleanto_first_step();
$first_step->conn = $conn;


if($_SESSION['paypal_transaction_id_request']!=""){
		$admin_profile->email = $_SESSION['ct_details_request']['email'];
		$request_money = $_SESSION['ct_details_request']['requestamount'];
		$current_money = $_SESSION['ct_details_request']['currentamount'];
	
		$t_zone_value = $settings->get_option("ct_timezone");
	$server_timezone = date_default_timezone_get();
	if(isset($t_zone_value) && $t_zone_value!=""){
		$offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
		$timezonediff = $offset/3600;  
	}else{
		$timezonediff =0;
	}
	if(is_numeric(strpos($timezonediff,"-"))){
		$timediffmis = str_replace("-","",$timezonediff)*60;
		$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date("Y-m-d H:i:s")));
	}else{
		$timediffmis = str_replace("+","",$timezonediff)*60;
		$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date("Y-m-d H:i:s")));
	}
	$current_time = date("Y-m-d H:i:s",$currDateTime_withTZ);
		
		/* $objuserdetails->id = $_SESSION['ct_login_user_id'];
		$objuserdetails->add_amount = $request_money;
		$objuserdetails->wallet_status = "C";
		$objuserdetails->wallet_trans_id = $_SESSION['paypal_transaction_id_addmoney'];
		$objuserdetails->wallet_method = "PayPal";
		$objuserdetails->lastmodify = $current_time;
		$objuserdetails->add_wallet_history(); */
		
		$update_money = $current_money - $request_money;
		$admin_profile->update_wallet_value = $update_money;
		$wallet_details = $admin_profile->update_staff_wallet_byemail();
		if($wallet_details=="1"){
		
 		$payments->reqid = $_SESSION['ct_details_request']['reqid'];
		$payments->status = "Confirmed";
		
	  $status_update = $payments->update_request_status();   	 
		  header('Location:'.SITE_URL."admin/payment_request.php");
		}
}

							
							
 ?>