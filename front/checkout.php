<?php   
ob_start();
session_start();

include(dirname(dirname(__FILE__)).'/header.php');
include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
include(dirname(dirname(__FILE__)).'/objects/class_booking.php');
include(dirname(dirname(__FILE__)).'/objects/class_payment_hook.php');
include(dirname(dirname(__FILE__)).'/objects/class_services.php');
include(dirname(dirname(__FILE__)).'/objects/class_userdetails.php');
include(dirname(dirname(__FILE__)).'/objects/class_front_first_step.php');

$con = new cleanto_db();
$conn = $con->connect();

$setting = new cleanto_setting();
$setting->conn = $conn;

$booking=new cleanto_booking();
$booking->conn=$conn;

$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;

$service=new cleanto_services();
$service->conn=$conn;

$payment_hook = new cleanto_paymentHook();
$payment_hook->conn = $conn;
$payment_hook->payment_extenstions_exist();
$purchase_check = $payment_hook->payment_purchase_status();

$first_step=new cleanto_first_step();
$first_step->conn = $conn;


$stripe_trans_id = '';
$twocheckout_trans_id = '';

$t_zone_value = $setting->get_option("ct_timezone");
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

  /*paypal payment method*/
  if(isset($_POST['action']) && $_POST['action']=='add_money_client_wallet'){
		$payment_method = $_POST['payment_method'];
		if($payment_method=='paypal'){
			$array_value_add_money = array('email' => $_POST['email_id'], 'add_amount' => $_POST['add_amount'], 'preamount' => $_POST['preamount']);
    $_SESSION['ct_details_addmoney']=$array_value_add_money;
    
    header('location:'.FRONT_URL.'pp_payment_addmoney.php');
    exit(0);
		}else{
			
			
			if (isset($_POST['st_token']) && filter_var($_POST['st_token']!='' && $_POST['add_amount'], FILTER_SANITIZE_STRING)!=0) {			
			require_once('../assets/stripe/stripe.php');
			
				$stripe_amt = number_format(filter_var($_POST['add_amount'], FILTER_SANITIZE_STRING),2,".",',');
		   
			
				$emails=filter_var($_SESSION['ct_useremail'], FILTER_SANITIZE_EMAIL); 
				
			$objuserdetails->email = $emails;
      $client_name = $objuserdetails->get_user_name(); 
			
			\Stripe\Stripe::setApiKey($setting->get_option("ct_stripe_secretkey"));
			  $error = '';
			  $success = '';
			   
			 try { 				
				$objcharge = new \Stripe\Charge;
		
					$striperesponse = $objcharge::Create(array(
											"amount" => round(((double)$stripe_amt)*100),
											"currency" => $setting->get_option('ct_currency'),
											"source" => filter_var($_POST['st_token'], FILTER_SANITIZE_STRING),
											"description"=>filter_var($client_name[0], FILTER_SANITIZE_STRING).' , '.$emails
											));
					 $stripe_trans_id = $striperesponse->id;
					 							
			  }catch (Exception $e) {
				$error = $e->getMessage();				
				echo filter_var($error, FILTER_SANITIZE_STRING);die;
			  }	
				
				if($stripe_trans_id!=''){
			
					$objuserdetails->id = $_SESSION['ct_login_user_id'];
					$objuserdetails->add_amount = $_POST['add_amount'];
					$objuserdetails->wallet_status = "C";
					$objuserdetails->wallet_trans_id = $stripe_trans_id;
					$objuserdetails->wallet_method = "Stripe";
					$objuserdetails->lastmodify = $current_time;
					$objuserdetails->add_wallet_history();
					
					$update_money = $_POST['add_amount'] + $_POST['preamount'];
					$objuserdetails->update_money = $update_money;
					$wallet_details = $objuserdetails->update_wallet_amount();
					if($wallet_details=="1"){
						 echo "ok";
					}
		
		
				}
			  				 
					
	}
		
			
		}
    
  }
  
  /*paypal payment method*/
  if(isset($_POST['action']) && $_POST['action']=='accept_staff_request'){
    $array_value = array( 'email' => $_POST['email_id'], 'staffid' => $_POST['staffid'], 'currentamount' => $_POST['currentamount'], 'requestamount' => $_POST['requestamount'], 'reqid' => $_POST['reqid']);                                            
    $_SESSION['ct_details_request']=$array_value;
    echo "<pre>";
    print_r($_SESSION);
    header('location:'.FRONT_URL.'pp_payment_accept_request.php');
    exit(0);
  }

  if(isset($_POST['action']) && $_POST['action']=='referral_check'){
    $array_value = array(
      'user_referral_code'=> $_POST['referral_code']
    );
    $_SESSION['referral_detail'] = $array_value; 
    exit(0);
  } 
  
if(isset($_POST['action']) && $_POST['action']=='complete_booking'){
  $recurrence_booking_status = $_POST['recurrence_booking'];
  if(isset($_POST['st_token']) && $_POST['st_token']!='' && $_POST['net_amount']!=0 && ($setting->get_option('ct_stripe_create_plan') == "N" || $_POST["guest_user_status"] == "on" || $recurrence_booking_status == "N")) {
    require_once(dirname(dirname(__FILE__)).'/assets/stripe/stripe.php');
    $partialdeposite_status = $setting->get_option('ct_partial_deposit_status');
    if($partialdeposite_status=='Y'){
      $stripe_amt = number_format($_POST['partial_amount'],2,".",',');
    }else{
      $stripe_amt = number_format($_POST['net_amount'],2,".",',');
    }
    if($_POST['existing_username']!=''){ 
      $emails=$_POST['existing_username']; 
    }else{ 
      $emails=$_POST['email']; 
    }
    \Stripe\Stripe::setApiKey($setting->get_option("ct_stripe_secretkey"));
    $error = '';
    $success = '';
   try {
    $objcharge = new \Stripe\Charge;
      $striperesponse = $objcharge::Create(array(
        "amount" => round(((double)$stripe_amt)*100),
        "currency" => $setting->get_option('ct_currency'),
        "source" => $_POST['st_token'],
        "description"=>$_POST['firstname'].' , '.$emails
      ));
      $stripe_trans_id = $striperesponse->id;
    }
    catch (Exception $e) {
      $error = $e->getMessage();        
      echo $error;die;
    }
  }elseif (isset($_POST['twoctoken']) && $_POST['twoctoken']!='' && $_POST['net_amount']!=0) {      
      require_once('../assets/twocheckout/Twocheckout.php');
      $twocc_private_key = $setting->get_option("ct_2checkout_privatekey");
      $twocc_sellerId = $setting->get_option("ct_2checkout_sellerid");
      $twocc_sandbox_mode = $setting->get_option("ct_2checkout_sandbox_mode");
      if($twocc_sandbox_mode == 'Y'){
        $twocc_sandbox = true;
      }else{
        $twocc_sandbox = false;
      }
      Twocheckout::privateKey($twocc_private_key); 
      Twocheckout::sellerId($twocc_sellerId); 
      Twocheckout::sandbox($twocc_sandbox);
      Twocheckout::verifySSL(false);
      if($_POST['existing_username']!=''){
        $emails=$_POST['existing_username'];
      }else{
        $emails=$_POST['email'];
      }
      $last_order_id=$booking->last_booking_id();
      if($last_order_id=='0' || $last_order_id==null){
        $orderid = 1000;
      }else{
        $orderid = $last_order_id+1;
      }
      $partialdeposite_status = $setting->get_option('ct_partial_deposit_status');
      if($partialdeposite_status=='Y'){
        $twocheckout_amt = number_format($_POST['partial_amount'],2,".",',');
      }else{
        $twocheckout_amt = number_format($_POST['net_amount'],2,".",',');
      }
      try {
        $charge = Twocheckout_Charge::auth(array(
          "merchantOrderId" => $orderid,
          "token"      => $_REQUEST['twoctoken'],
          "currency"   => $setting->get_option('ct_currency'),
          "total"      => $twocheckout_amt,
          "billingAddr" => array(
            "name" => $_POST['firstname'].' '.$_POST['lastname'],
            "addrLine1" => $_POST['address'],
            "city" => $_POST['city'],
            "state" => $_POST['state'],
            "zipCode" => $_POST['zipcode'],
            "country" => $setting->get_option('ct_company_country'),
            "email" => $emails,
            "phoneNumber" => $_POST['phone']
          )
        ));
        
        if ($charge['response']['responseCode'] == 'APPROVED') {
          $twocheckout_trans_id = $charge['response']['transactionId'];
        }
      } catch (Twocheckout_Error $e) {
        $error = $e->getMessage();
        echo $error;die;
      }
  }
  
  if ($_POST['discount'] == 'undefined' || $_POST['discount'] == '' || $_POST['discount'] == 'undefined- undefined') {
    $_POST['discount'] = 0;
  }else{
    $_POST['discount'] = $_POST['discount'];
  }
 
  $total_discount =  @number_format($_POST['frequent_discount_amount'],2,".",',') + @number_format($_POST['discount'],2,".",',');

  $phone = "";
  if (substr($_POST['phone'], 0, 1) === '+')
  {
    $phone = $_POST['phone'];
  }
  else
  {
    $country_codes = explode(',',$setting->get_option("ct_company_country_code"));
    $phone = $country_codes[0].$_POST['phone'];
  }
  if($setting->get_option("ct_tax_vat_status") == 'N'){
    $tax = 0;
  }else{
    $tax = $_POST['taxes'];
  }
  
  $service->id = $_SESSION['ct_cart']['method'][0]['service_id'];
  $service_name = $service->get_service_name_for_mail();
  
  $email = addslashes($_POST['email']);
  $firstname = addslashes($_POST['firstname']);
  $lastname = addslashes($_POST['lastname']);
  $address = addslashes($_POST['address']);
  $zipcode = addslashes($_POST['zipcode']);
  $city = addslashes($_POST['city']);
  $state = addslashes($_POST['state']);
  $user_address = addslashes($_POST['user_address']);
  $user_zipcode = addslashes($_POST['user_zipcode']);
  $coupon_code = addslashes($_POST['coupon_code']);
  $user_city = addslashes($_POST['user_city']);
  $user_state = addslashes($_POST['user_state']);
  $notes = addslashes($_POST['notes']);
  
  if($_POST['staff_id'] == ""){
      $staff_id = 1; 
  }else{
      $staff_id = addslashes($_POST['staff_id']);
  }
 
  if (isset($_POST['user_coupon_val'])) {
    $user_coupon_val = $_POST['user_coupon_val'];
  }else{
    $user_coupon_val = 0;
  }
  
  $random_string = substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 10)),1,10);

  $app_address ="";
  $app_city ="";
  $app_zip ="";
  $app_state ="";
  if($_POST['address']!=""){ $app_address = $_POST['address'].", " ; } 
  if($_POST['city']!=""){ $app_city = $_POST['city'].", " ; } 
  if($_POST['zipcode']!=""){ $app_zip = $_POST['zipcode'].", " ; } 
  if($_POST['state']!=""){ $app_state = $_POST['state'] ; } 

  $temper = $app_address.$app_city.$app_zip.$app_state;
  $full_address = rtrim($temper,", ");

  $uniqueServices = [];
  $uniqueMethods = [];
  $commaSeparatedMethodNames = '';
  $commaSeparatedAddonName = '';
  $service_name = '';
  $method_name = [];
  
  for ($i = 0; $i < count($_SESSION['ct_cart']['method']); $i++) {
      $serviceId = $_SESSION['ct_cart']['method'][$i]['service_id'];
      $methodId = $_SESSION['ct_cart']['method'][$i]['method_id'];
      if($_SESSION['ct_cart']['method'][$i]['type'] == 'method_units'){
          $methodName = $_SESSION['ct_cart']['method'][$i]['method_name'];
          $commaSeparatedMethodNames .= $methodName . ', ';
      }else if($_SESSION['ct_cart']['method'][$i]['type'] == 'addon'){
          $addonName = $_SESSION['ct_cart']['method'][$i]['method_name'];
          $commaSeparatedAddonName .= $addonName . ', ';
      }
      
      $uniqueServices[$serviceId] = true;
      $uniqueMethods[$methodId] = true;
  }
  $service_ids = array_keys($uniqueServices);
  $method_ids = array_keys($uniqueMethods);
  foreach ($service_ids as $value) {
      $service->id = $value;
      $service_names = $service->get_service_name_gc();
      $service_name .= $service_names . ', ';
  }
  
  foreach ($method_ids as $value) {
      $service->id = $value;
      $method_names = $service->get_method_name_gc();
      $method_name[] = $method_names;
  }
  $uniquemethod_names = array_values(array_unique($method_name));
if (count($uniquemethod_names) > 1) {
    $uniquemethod_name = implode(", ", $uniquemethod_names);
} else {
    $uniquemethod_name = $uniquemethod_names[0] ?? ''; // Use the first element or an empty string if the array is empty
}

$service_name = rtrim($service_name, ', ');
$uniquemethod_name = is_array($uniquemethod_name) ? '' : rtrim($uniquemethod_name, ', ');
$commaSeparatedUnitNames = rtrim($commaSeparatedMethodNames, ', ');
$commaSeparatedAddonNames = rtrim($commaSeparatedAddonName, ', ');

$cc_card_num = isset($_POST['cc_card_num']) ? $_POST['cc_card_num'] : '';
$cc_exp_month = isset($_POST['cc_exp_month']) ? $_POST['cc_exp_month'] : '';
$cc_exp_year = isset($_POST['cc_exp_year']) ? $_POST['cc_exp_year'] : '';
$cc_card_code = isset($_POST['cc_card_code']) ? $_POST['cc_card_code'] : '';
    
$array_value = array('existing_username' => $_POST['existing_username'], 'existing_password' => $_POST['existing_password'], 'password' => $_POST['password'], 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'phone' => $phone, 'user_address' => $user_address, 'user_zipcode' => $user_zipcode, 'user_city' => $user_city, 'user_state' => $user_state, 'address' => $address, 'zipcode' => $zipcode, 'city' => $city, 'state' => $state, 'notes' => $notes, 'vc_status' => $_POST['vc_status'],'staff_id' => $staff_id, 'p_status' => $_POST['p_status'], 'contact_status' => $_POST['contact_status'], 'payment_method' => $_POST['payment_method'], 'tip_value' => $_POST['tip_value'], 'amount' => $_POST['amount'], 'discount' => abs(number_format($total_discount, 2, ".", ',')), 'taxes' => $tax, 'partial_amount' => $_POST['partial_amount'], 'net_amount' => $_POST['net_amount'], 'booking_date_time' => $_POST['booking_date_time'], 'frequently_discount' => $_POST['frequently_discount'], 'frequent_discount_amount' => abs($_POST['frequent_discount_amount']), 'action' => "complete_booking", 'coupon_discount' => $_POST['discount'], 'cc_card_num' => $cc_card_num,'cc_exp_month' => $cc_exp_month,'cc_exp_year' => $cc_exp_year,'cc_card_code' => $cc_card_code,'guest_user_status' => $_POST['guest_user_status'],'is_login_user' => $_POST['is_login_user'],'service_name' => $service_name,'coupon_code'=> $coupon_code,'user_coupon_val'=> $user_coupon_val,'recurrence_booking_status'=> $recurrence_booking_status,'random_string'=> $random_string,'service_names'=> $service_name,'uniquemethod_name'=> $uniquemethod_name,'commaSeparatedUnitNames'=> $commaSeparatedUnitNames,'full_address'=> $full_address,'commaSeparatedAddonNames'=> $commaSeparatedAddonNames);
  
  $_SESSION['ct_details']=$array_value;  
  /*paypal payment method*/
  if($_POST['payment_method'] == 'paypal'){
    header('location:'.FRONT_URL.'pp_payment_process.php');
    exit(0);
  }
  
  /*Stripe payment method single*/
  if($_POST['payment_method'] == 'stripe-payment' && ($setting->get_option('ct_stripe_create_plan') == "N" || $_POST["guest_user_status"] == "on" || $recurrence_booking_status == "N")){
    $_SESSION['ct_details']['stripe_trans_id'] = $stripe_trans_id;
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
  }
  if($_POST['payment_method'] == 'stripe-payment' && ($setting->get_option('ct_stripe_create_plan') == "Y" || $_POST["guest_user_status"] == "off" || $recurrence_booking_status == "Y")){
    $_SESSION['ct_details']['stripe_token'] = $_POST["st_token"];
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
  }
  /*2checkout payment method*/
  if($_POST['payment_method'] == '2checkout-payment'){
    $_SESSION['ct_details']['twocheckout_trans_id'] =   $twocheckout_trans_id;
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
  } 
  /*pay locally payment method*/
  if($_POST['payment_method'] == 'pay at venue'){
    $transaction_id ='';
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
  } 
  
  if($_POST['payment_method'] == 'Wallet-payment'){
    
    if( $_POST['current_amount'] >  $_POST['net_amount']){
      $update_money = $_POST['current_amount'] - $_POST['net_amount'];
      $objuserdetails->update_money = $update_money;
      $id = $_SESSION['ct_login_user_id'];
      $objuserdetails->id = $id;
      $objuserdetails->update_wallet_amount_withid();   
      $transaction_id = rand(1111,99999999);
      /* $objuserdetails->id = $_SESSION['ct_login_user_id']; */
      $objuserdetails->add_amount = $_POST['net_amount'];
      $objuserdetails->wallet_status = "D";
      $objuserdetails->wallet_trans_id = $transaction_id;
      $objuserdetails->wallet_method = "Wallet";
      $objuserdetails->lastmodify = $current_time;
      $objuserdetails->add_wallet_history();
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
    }else{
      $wallet_amount = $_POST['current_amount'];
      $net_amount = $_POST['net_amount'];
      $pending_amount = $net_amount - $wallet_amount;
      $objuserdetails->update_money = 0;
      $id = $_SESSION['ct_login_user_id'];
      $objuserdetails->id = $id;
      $transaction_id = rand(1111,99999999);
      $objuserdetails->add_amount = $wallet_amount;
      $objuserdetails->wallet_status = "D";
      $objuserdetails->wallet_trans_id = $transaction_id;
      $objuserdetails->wallet_method = "Wallet";
      $objuserdetails->lastmodify = $current_time;
      $objuserdetails->add_wallet_history();
      $objuserdetails->update_wallet_amount_withid();   
      $array_value_add_money = array('add_amount' => $pending_amount);
      $_SESSION['ct_details_pendingpayment']=$array_value_add_money;
      header('location:'.FRONT_URL.'pp_payment_pendingpayment.php');
    exit(0);
    }
  } 
  /*bank transfer payment method*/
  if($_POST['payment_method'] == 'bank transfer'){
    $transaction_id ='';
    header('location:'.FRONT_URL.'booking_complete.php');
    exit(0);
  }
  /*card payment method*/
  if($_POST['payment_method'] == 'card-payment'){
    $transaction_id ='';
    header('location:'.FRONT_URL.'authorizenet_payment_process.php');
    exit(0);
  }
  
  /* Payment Extension method */
  
  if(sizeof((array)$purchase_check)>0){
    $payment_status = "off";
    $check_pay = 'N';
    foreach($purchase_check as $key=>$val){
      if($val == 'Y'){
        echo $payment_hook->payment_checkout_hook($key);
      }
    }
  }
} ?>