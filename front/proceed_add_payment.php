<?php
session_start();
include(dirname(dirname(__FILE__)).'/header.php');
include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
include(dirname(dirname(__FILE__)).'/objects/class_booking.php');
include(dirname(dirname(__FILE__)).'/objects/class_payment_hook.php');
include(dirname(dirname(__FILE__)).'/objects/class_services.php');
include(dirname(dirname(__FILE__))."/objects/class_userdetails.php");
include(dirname(dirname(__FILE__))."/objects/class_front_first_step.php");
if(!isset($_SESSION['ct_login_user_id'])){
  header('Location:'.SITE_URL."admin/");
}
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

$settings=new cleanto_setting();
$settings->conn = $conn;

$first_step=new cleanto_first_step();
$first_step->conn = $conn;


if($_SESSION['paypal_transaction_id_addmoney']!=""){
		$objuserdetails->email = $_SESSION['ct_details_addmoney']['email'];
		$add_money = $_SESSION['ct_details_addmoney']['add_amount'];
		$previous_money = $_SESSION['ct_details_addmoney']['preamount'];
		
		
		
		
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
		
		
		
		
		
		
		
		
		$objuserdetails->id = $_SESSION['ct_login_user_id'];
		$objuserdetails->add_amount = $add_money;
		$objuserdetails->wallet_status = "C";
		$objuserdetails->wallet_trans_id = $_SESSION['paypal_transaction_id_addmoney'];
		$objuserdetails->wallet_method = "PayPal";
		$objuserdetails->lastmodify = $current_time;
		$objuserdetails->add_wallet_history();
		
		$update_money = $add_money + $previous_money;
		$objuserdetails->update_money = $update_money;
		$wallet_details = $objuserdetails->update_wallet_amount();
		if($wallet_details=="1"){
		   header('Location:'.SITE_URL."admin/wallet-history.php");
		}
}

							
							
 ?>