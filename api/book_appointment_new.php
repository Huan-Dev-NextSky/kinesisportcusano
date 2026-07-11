<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "book_appointment") {
	/* "cart_detail" this parameter also required */
	
	verifyRequiredParams(array("api_key", "recurrence_id", "user_id", "staff_id", "payment_method", "sub_total", "tax", "discount", "net_amount", "order_duration"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		
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
		$last_order_id=$booking->last_booking_id();
		if($last_order_id=="0" || $last_order_id==null){
			$orderid = 1000;
		}else{
			$orderid = $last_order_id+1;
		}
		$last_recurring_id=$order_client_info->last_recurring_id();
		if($last_recurring_id=="0" || $last_recurring_id==null){
			$rec_id = 1;
		}else{
			$rec_id = $last_recurring_id+1;
		}
		$appointment_auto_confirm=$settings->get_option("ct_appointment_auto_confirm_status");
		if($appointment_auto_confirm=="Y"){
			$booking_status="C";
		}else{
			$booking_status="A";
		}
		$email_order_id = $orderid;
		$client_id = $user->user_id = $_POST["user_id"];
		$staff_id = $_POST["staff_id"];
		$service_id = $_POST["service_id"];
		$service->id = $service_id;
		$service_name = $service->get_service_name_for_mail();
	
		$method_id = $_POST["method_id"];
		$mail_booking_date_time = $_POST["booking_date_time"];
		$booking_date_time = $_POST["booking_date_time"];
		$payment_method = $_POST["payment_method"];
		$sub_total = $_POST["sub_total"];
		$tax = $_POST["tax"];
		$partial_amount = 0;
		$discount = $_POST["discount"];
		$_SESSION["time_duration"] = $order_duration = $_POST["order_duration"];
		$recurrence_id = $_POST["recurrence_id"];
		$freq_discount_amount = $_POST["freq_discount_amount"];
		$net_amount = $_POST["net_amount"];
		if(isset($_POST["transaction_id"])){
		$transaction_id = $_POST["transaction_id"];
		}else{
		$transaction_id = "";
		}
		$one_user_detail = $user->readone();
		$first_name = ucwords($one_user_detail["first_name"]);
		$last_name = ucwords($one_user_detail["last_name"]);
		$client_name = ucwords($one_user_detail["first_name"])." ".ucwords($one_user_detail["last_name"]);
		$client_email = $one_user_detail["user_email"];
		$phone = $client_phone = $one_user_detail["phone"];
		$address = $one_user_detail["address"];
		$city = $one_user_detail["city"];
		$state = $one_user_detail["state"];
		$notes = $one_user_detail["notes"];
		$zip = $one_user_detail["zip"];
		$vc_status = $one_user_detail["vc_status"];
		$p_status = $one_user_detail["p_status"];
		$contact_status = $one_user_detail["contact_status"];
		$client_personal_info = base64_encode(serialize(array("zip"=>$one_user_detail["zip"],"address"=>$one_user_detail["address"],"city"=>$one_user_detail["city"],"state"=>$one_user_detail["state"],"notes"=>$one_user_detail["notes"],"vc_status"=>$one_user_detail["vc_status"],"p_status"=>$one_user_detail["p_status"],"contact_status"=>$one_user_detail["contact_status"])));
		$cart_detail = $_POST["cart_detail"];
		if($recurrence_id == "1"){
			if(count((array)$cart_detail) != 0) {
				$booking->order_id = $orderid;
				for ($i = 0;$i < (count((array)$cart_detail));$i++){
					if ($cart_detail[$i]["type"] == "unit"){
						$booking->client_id = $client_id;
						$booking->order_date = $current_time;
						$booking->booking_date_time = $booking_date_time;
						$booking->service_id = $cart_detail[$i]["service_id"];
						$booking->method_id = $cart_detail[$i]["method_id"];
						$booking->method_unit_id = $cart_detail[$i]["unit_id"];
						$booking->method_unit_qty = $cart_detail[$i]["qty"];
						$booking->method_unit_qty_rate = $cart_detail[$i]["rate"];
						$booking->booking_status = $booking_status;
						$booking->reject_reason = "";
						$booking->lastmodify = $current_time;
						$booking->read_status = "U";
						$booking->staff_id = $staff_id;
						$booking->add_booking();
					} else {
						$booking->addons_service_id = $cart_detail[$i]["unit_id"];
						$booking->addons_service_qty = $cart_detail[$i]["qty"];
						$booking->addons_service_rate = $cart_detail[$i]["rate"];
						$booking->add_addons_booking();
					}
				}
				if($staff_id != ""){
					$staff_id_array = explode(",",$staff_id);
					foreach($staff_id_array as $key => $value){
						$booking->staff_id = $value;
						$booking->staff_status_insert();
					}
				}
				$payment->order_id = $orderid;
				$payment->payment_method = ucwords($payment_method);
				if (isset($transaction_id) && $transaction_id != ""){
					$payment->transaction_id = $transaction_id;
					$payment->payment_status = "Completed";
				} else {
					$payment->transaction_id = "";
					$payment->payment_status = "Pending";
				}
				$payment->amount = $sub_total;
				$payment->discount = $discount;
				$payment->taxes = $tax;
				$payment->partial_amount = $partial_amount;
				$payment->payment_date = $current_time;
				$payment->lastmodify = $current_time;
				$payment->net_amount = $net_amount;
				$payment->frequently_discount = $recurrence_id;
				$payment->frequently_discount_amount = $freq_discount_amount;
				$payment->recurrence_status = "N";
				$payment->add_payments();
				$order_client_info->order_id = $orderid;
				$order_client_info->client_name = $client_name;
				$order_client_info->client_email = $client_email;
				$order_client_info->client_phone = $client_phone;
				$order_client_info->client_personal_info = $client_personal_info;
				$order_client_info->order_duration = $order_duration;
				$order_client_info->recurring_id = $rec_id;
				$order_client_info->add_order_client();
				/* GC Code Start */
				if($gc_hook->gc_purchase_status() == "exist"){
					$array_value = array("firstname" => $first_name,"lastname" => $last_name, "email" => $client_email,"phone" => $client_phone,"staff_id" => "");
					$_SESSION["ct_details"]=$array_value;
					echo $gc_hook->gc_add_booking_ajax_hook();
					if($staff_id != ""){
						$staff_id_array = explode(",",$staff_id);
						foreach($staff_id_array as $key => $value){
							$_SESSION["ct_details"]["staff_id"] = $value;
							echo $gc_hook->gc_add_staff_booking_ajax_hook();
						}
					}
				}
				/* GC Code End */
				$_SESSION["ct_details"] = array();
				$_SESSION["time_duration"] = 0;
				$admin_infoo = $order_client_info->readone_for_email();
	$service->id = $service_id;
	$service_name = $service->get_service_name_for_mail(); /* methods */
	$units = "None";
	$methodname = "None";
	$hh = $booking->get_methods_ofbookings($orderid);
	$count_methods = mysqli_num_rows($hh);
	$hh1 = $booking->get_methods_ofbookings($orderid);
	if ($count_methods > 0){
		while ($jj = mysqli_fetch_array($hh1)){
			if ($units == "None"){
				$units = $jj['units_title'] . "-" . $jj['qtys'];
			}
			else{
				$units = $units . "," . $jj['units_title'] . "-" . $jj['qtys'];
			}
			$methodname = $jj['method_title'];
		}
	} /* ADDONS */
	$addons = "None";
	$hh = $booking->get_addons_ofbookings($orderid);
	while ($jj = mysqli_fetch_array($hh)){
		if ($addons == "None"){
			$addons = $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
		}
		else{
			$addons = $addons . "," . $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
		}
	}
	if ($company_name == ""){
		$company_name = $settings->get_option('ct_company_name');
	}
	$setting_date_format = $settings->get_option('ct_date_picker_date_format');
	$setting_time_format = $settings->get_option('ct_choose_time_format');
	$booking_date = date($setting_date_format, strtotime($booking_date_time));
	if ($setting_time_format == 12){
		$booking_time = str_replace($english_date_array,$selected_lang_label,date("h:i A", strtotime($booking_date_time)));
	}
	else{
		$booking_time = date("H:i", strtotime($booking_date_time));
	}
	$price = $general->ct_price_format($net_amount, $symbol_position, $decimal);
	$c_address = $address;
	$client_city = $city;
	$client_state = $state;
	$client_zip = $zip;
	$client_email = $client_email;
	$subject = ucwords($service_name) . " on " . $booking_date;
	if ($admin_email == ""){
		$admin_email = $admin_infoo['email'];
	}
	if ($vc_status == "Y"){
		$vc_status_v = "Yes";
	}
	elseif ($vc_status == "N"){
		$vc_status_v = "No";
	}
	else{
		$vc_status_v = "N/A";
	}
	if ($p_status == "Y"){
		$p_status_v = "Yes";
	}
	elseif ($p_status == "N"){
		$p_status_v = "No";
	}
	else{
		$p_status_v = "N/A";
	}
	$cemail = $client_email;
	if ($appointment_auto_confirm == "Y"){
		$email_template->email_template_type = 'C';
	}
	else{
		$email_template->email_template_type = 'A';
	}
	$clientemailtemplate = $email_template->readone_client_email_template();
	if ($clientemailtemplate['email_message'] != ''){
		$clienttemplate = base64_decode($clientemailtemplate['email_message']);
	}
	else{
		$clienttemplate = base64_decode($clientemailtemplate['default_message']);
	}
	if ($appointment_auto_confirm == "Y"){
		$email_template->email_template_type = 'C';
	}
	else{
		$email_template->email_template_type = 'A';
	}
	$adminemailtemplate = $email_template->readone_admin_email_template();
	if ($adminemailtemplate['email_message'] != ''){
		$admintemplate = base64_decode($adminemailtemplate['email_message']);
	}
	else{
		$admintemplate = base64_decode($adminemailtemplate['default_message']);
	}
	$staffemailtemplate = $email_template->readone_staff_email_template();
  if($staffemailtemplate["email_message"] != ""){
    $stafftemplate = base64_decode($staffemailtemplate["email_message"]);
  }else{
    $stafftemplate = base64_decode($staffemailtemplate["default_message"]);
  }
	$client_phone_info = "";
	$client_phone_no = "";
	$client_phone_length = "";
	$client_first_name = "";
	$client_last_name = "";
	$client_fname = "";
	$client_lname = "";
	$email_notes = "";
	$client_notes = "";
	$client_phone_no = $phone;
	$client_phone_length = strlen($client_phone_no);
	if ($client_phone_length > 6){
		$client_phone_info = $client_phone_no;
	}
	else{
		$client_phone_info = "N/A";
	}
	$client_first_name = ucwords(stripslashes($first_name));
	$client_last_name = ucwords(stripslashes($last_name));
	if ($client_first_name == "" && $client_last_name == ""){
		$client_fname = "User";
		$client_lname = "";
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_first_name != "" && $client_last_name != ""){
		$client_fname = $client_first_name;
		$client_lname = $client_last_name;
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_first_name != ""){
		$client_fname = $client_first_name;
		$client_lname = "";
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_last_name != ""){
		$client_fname = "";
		$client_lname = $client_last_name;
		$client_name = $client_fname . ' ' . $client_lname;
	}
	$client_notes = stripslashes($notes);
	if ($client_notes == ""){
		$client_notes = "N/A";
	}
	$contact_status_cont = $contact_status;
	if ($contact_status_cont == ""){
		$contact_status_cont = "N/A";
	}
	if(isset($staff_id) && !empty($staff_id))
  {
    $objadminprofile->id = $staff_id;
    $staff_details = $objadminprofile->readone();
    $get_staff_name = $staff_details["fullname"];
    $get_staff_email = $staff_details["email"];
    $staff_phone = $staff_details["phone"];
  }
	$searcharray = array('{{service_name}}','{{booking_date}}','{{business_logo}}','{{business_logo_alt}}','{{client_name}}','{{methodname}}','{{units}}','{{addons}}','{{firstname}}','{{lastname}}','{{client_email}}','{{phone}}','{{payment_method}}','{{vaccum_cleaner_status}}','{{parking_status}}','{{notes}}','{{contact_status}}','{{admin_name}}','{{price}}','{{address}}','{{app_remain_time}}','{{reject_status}}','{{company_name}}','{{booking_time}}','{{client_city}}','{{client_state}}','{{client_zip}}','{{company_city}}','{{company_state}}','{{company_zip}}','{{company_country}}','{{company_phone}}','{{company_email}}','{{company_address}}','{{admin_name}}');
  $replacearray = array($service_name,$booking_date,$business_logo,$business_logo_alt,stripslashes($client_name) ,$methodname,$units,$addons,$client_fname,$client_lname,$cemail,$client_phone_info,ucwords($payment_method) ,$vc_status_v,$p_status_v,$client_notes,$contact_status_cont,$get_admin_name,$price,stripslashes($c_address) ,'','',$company_name,$booking_time,stripslashes($client_city) ,stripslashes($client_state) ,$client_zip,stripslashes($company_city) ,stripslashes($company_state) ,$company_zip,$company_country,$company_phone,$company_email,stripslashes($company_address) ,stripslashes($get_admin_name));
  if ($settings->get_option('ct_client_email_notification_status') == 'Y' && $clientemailtemplate['email_template_status'] == 'E'){
		$client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
		if ($settings->get_option('ct_smtp_hostname') != '' && $settings->get_option('ct_email_sender_name') != '' && $settings->get_option('ct_email_sender_address') != '' && $settings->get_option('ct_smtp_username') != '' && $settings->get_option('ct_smtp_password') != '' && $settings->get_option('ct_smtp_port') != '')
		{
			$mail->IsSMTP();
		}
		else
		{
			$mail->IsMail();
		}
		if($mail->SMTPAuth === "Yes") {
		$mail->SMTPDebug = 0;
		$mail->IsHTML(true);
		$mail->From = $company_email;
		$mail->FromName = $company_name;
		$mail->Sender = $company_email;
		$mail->AddAddress($client_email, $client_name);
		$mail->Subject = $subject;
		$mail->Body = $client_email_body;
		$mail->send();
		$mail->ClearAllRecipients();
		}else{
		    $client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
              $headers = "MIME-Version: 1.0" . "\r\n";
              $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
              mail($client_email, $subject, $client_email_body, $headers);
		}
	}
	if ($settings->get_option('ct_admin_email_notification_status') == 'Y' && $adminemailtemplate['email_template_status'] == 'E'){
		$admin_email_body = str_replace($searcharray, $replacearray, $admintemplate);
		if ($settings->get_option('ct_smtp_hostname') != '' && $settings->get_option('ct_email_sender_name') != '' && $settings->get_option('ct_email_sender_address') != '' && $settings->get_option('ct_smtp_username') != '' && $settings->get_option('ct_smtp_password') != '' && $settings->get_option('ct_smtp_port') != '')
		{
			$mail_a->IsSMTP();
		}
		else
		{
			$mail_a->IsMail();
		}
		if($mail_a->SMTPAuth === "Yes") {
		$mail_a->SMTPDebug = 0;
		$mail_a->IsHTML(true);
		$mail_a->From = $company_email;
		$mail_a->FromName = $company_name;
		$mail_a->Sender = $company_email;
		$mail_a->AddAddress($admin_email, $get_admin_name);
		$mail_a->Subject = $subject;
		$mail_a->Body = $admin_email_body;
		$mail_a->send();
		$mail_a->ClearAllRecipients();
		}else{  
      $admin_email_body = str_replace($searcharray,$replacearray,$admintemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($admin_email, $subject, $admin_email_body, $headers);
    }
	}
	if($settings->get_option("ct_staff_email_notification_status") == "Y" && $clientemailtemplate["email_template_status"] == "E"){
    if ($mail->SMTPAuth === "Yes") {    
      $client_email_body = str_replace($searcharray,$replacearray,$stafftemplate);
      $mail->SMTPDebug  = 0;
      $mail->IsHTML(true);
      $mail->From = $company_email;
      $mail->FromName = $company_name;
      $mail->Sender = $company_email;
      $mail->AddAddress($get_staff_email, $get_staff_name);
      $mail->Subject = $subject;
      $mail->Body = $client_email_body;
      $mail->send();
      $mail->ClearAllRecipients();
    }else{
      $client_email_body = str_replace($searcharray,$replacearray,$stafftemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($get_staff_email, $subject, $client_email_body, $headers);
    }
  
  }/*** Email Code End ***/ /*SMS SENDING CODE*/ 
	/* MESSAGEBIRD CODE */
		if($settings->get_option("ct_sms_messagebird_status") == "Y"){
			if ($settings->get_option('ct_sms_messagebird_send_sms_to_client_status') == "Y"){
				$template = $objdashboard->gettemplate_sms("A", 'C');
				$phone = $client_phone;
				if ($template[4] == "E"){
					if ($template[2] == ""){
						$message = base64_decode($template[3]);
					}
					else{
						$message = base64_decode($template[2]);
					}
				}
				$messagebird_apikey =$settings->get_option("ct_sms_messagebird_account_apikey");     

				$message = str_replace($searcharray, $replacearray, $message);

				require_once(dirname(dirname(__FILE__)).'/messagebird/vendor/autoload.php');
				$MessageBird = new \MessageBird\Client($messagebird_apikey);

				$Message = new \MessageBird\Objects\Message();
				$Message->originator = 'MessageBird';
				$Message->recipients = $staff_phone;
				$Message->body = $message;

				$res = $MessageBird->messages->create($Message);
				var_dump($res);
				$Balance = $MessageBird->balance->read();
			}
			if ($settings->get_option('ct_sms_messagebird_send_sms_to_admin_status') == "Y"){
				$template = $objdashboard->gettemplate_sms("A", 'A');
				$phone = $settings->get_option('ct_sms_messagebird_admin_phone');;
				if ($template[4] == "E"){
					if ($template[2] == ""){
						$message = base64_decode($template[3]);
					}
					else{
						$message = base64_decode($template[2]);
					}
				}
				$messagebird_apikey =$settings->get_option("ct_sms_messagebird_account_apikey");     

				$message = str_replace($searcharray, $replacearray, $message);

				require_once(dirname(dirname(__FILE__)).'/messagebird/vendor/autoload.php');
				$MessageBird = new \MessageBird\Client($messagebird_apikey);

				$Message = new \MessageBird\Objects\Message();
				$Message->originator = 'MessageBird';
				$Message->recipients = $phone;
				$Message->body = $message;

				$res = $MessageBird->messages->create($Message);
				var_dump($res);
				$Balance = $MessageBird->balance->read();
			}
	  }
	  /* TEXTLOCAL CODE */
  if ($settings->get_option('ct_sms_textlocal_status') == "Y"){
		if ($settings->get_option('ct_sms_textlocal_send_sms_to_client_status') == "Y"){
			$template = $objdashboard->gettemplate_sms("A", 'C');
			$phone = $client_phone;
			if ($template[4] == "E"){
				if ($template[2] == ""){
					$message = base64_decode($template[3]);
				}
				else{
					$message = base64_decode($template[2]);
				}
			}
			$message = str_replace($searcharray, $replacearray, $message);
			$data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
			$ch = curl_init('http://api.textlocal.in/send/?');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
		if ($settings->get_option('ct_sms_textlocal_send_sms_to_admin_status') == "Y"){
			$template = $objdashboard->gettemplate_sms("A", 'A');
			$phone = $settings->get_option('ct_sms_textlocal_admin_phone');;
			if ($template[4] == "E"){
				if ($template[2] == ""){
					$message = base64_decode($template[3]);
				}
				else{
					$message = base64_decode($template[2]);
				}
			}
			$message = str_replace($searcharray, $replacearray, $message);
			$data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
			$ch = curl_init('http://api.textlocal.in/send/?');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
  }
				$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["appointment_booked_successfully"]];
				setResponse($valid);
			}
		}else{
			$frequently_discount->id = $recurrence_id;
			$frequently_discount_detail = $frequently_discount->readone();
			$days = $frequently_discount_detail["days"];
			$cart_date_strtotime = strtotime($booking_date_time);
			$end_3_month_strtotime = strtotime("+3 months",$cart_date_strtotime);
			$cust_datediff = $end_3_month_strtotime - $cart_date_strtotime;
			$total_days = abs(floor($cust_datediff / (60 * 60 * 24)))+1;
			for($j=0;$j<$total_days;$j+=$days) {
				$booking_date_time = date("Y-m-d H:i:s",strtotime("+".$j." days",$cart_date_strtotime));
				$booking->order_id=$orderid;
				if(count((array)$_POST["cart_detail"]) != 0) {
					for ($i = 0;$i < (count((array)$cart_detail));$i++){
						if ($cart_detail[$i]["type"] == "unit"){
							$booking->client_id = $client_id;
							$booking->order_date = $current_time;
							$booking->booking_date_time = $booking_date_time;
							$booking->service_id = $cart_detail[$i]["service_id"];
						    $booking->method_id = $cart_detail[$i]["method_id"];
						    $booking->method_unit_id = $cart_detail[$i]["unit_id"];
							$booking->method_unit_id = $cart_detail[$i]["unit_id"];
							$booking->method_unit_qty = $cart_detail[$i]["qty"];
							$booking->method_unit_qty_rate = $cart_detail[$i]["rate"];
							$booking->booking_status = $booking_status;
							$booking->reject_reason = "";
							$booking->lastmodify = $current_time;
							$booking->read_status = "U";
							$booking->staff_id = $staff_id;
							$booking->add_booking();
						} else {
							$booking->addons_service_id = $cart_detail[$i]["unit_id"];
							$booking->addons_service_qty = $cart_detail[$i]["qty"];
							$booking->addons_service_rate = $cart_detail[$i]["rate"];
							$booking->add_addons_booking();
						}
					}
				}
				if($staff_id != ""){
					$staff_id_array = explode(",",$staff_id);
					foreach($staff_id_array as $key => $value){
						$booking->staff_id = $value;
						$booking->staff_status_insert();
					}
				}
				$payment->order_id = $orderid;
				if($j == 0){
					if (isset($transaction_id) && $transaction_id != ""){
						$payment->transaction_id = $transaction_id;
						$payment->payment_status = "Completed";
					} else {
						$payment->transaction_id = "";
						$payment->payment_status = "Pending";
					}
					$payment->payment_method=$payment_method;
					$payment->payment_date=$current_time;
				}else{
					$payment->payment_method=ucwords("pay at venue");
					$payment->transaction_id="";
					$payment->payment_status="Pending";
					$payment->payment_date=$booking_date_time;
				}
				$payment->amount = $sub_total;
				$payment->discount = $discount;
				$payment->taxes = $tax;
				$payment->partial_amount = $partial_amount;
				$payment->payment_date = $current_time;
				$payment->lastmodify = $current_time;
				$payment->net_amount = $net_amount;
				$payment->frequently_discount = $recurrence_id;
				$payment->frequently_discount_amount = $freq_discount_amount;
				$payment->recurrence_status = "N";
				$payment->add_payments();
				$order_client_info->order_id = $orderid;
				$order_client_info->client_name = $client_name;
				$order_client_info->client_email = $client_email;
				$order_client_info->client_phone = $client_phone;
				$order_client_info->client_personal_info = $client_personal_info;
				$order_client_info->order_duration = $order_duration;
				$order_client_info->recurring_id = $rec_id;
				$order_client_info->add_order_client();
				if($settings->get_option("ct_allow_multiple_booking_for_same_timeslot_status") == "N"){
					$count_j = $j+$days;
					$next_booking_date_time = date("Y-m-d H:i:s",strtotime("+".$count_j." days",$cart_date_strtotime));
					$check_for_booking_date_time = $booking->check_for_booking_date_time($next_booking_date_time,$staff_id);
					if(!$check_for_booking_date_time){
						$j+=$days;
						$booking_date_time = date("Y-m-d H:i:s",strtotime("+".$j." days",$cart_date_strtotime));
						$orderid++;
						continue;
					}
				}
				/* GC Code Start */
				if($gc_hook->gc_purchase_status() == "exist"){
					$array_value = array("firstname" => $first_name,"lastname" => $last_name,"service_name" => $service_name,"email" => $client_email,"phone" => $client_phone,"staff_id" => "");
					$_SESSION["ct_details"]=$array_value;
					echo $gc_hook->gc_add_booking_ajax_hook();
					if($staff_id != ""){
						$staff_id_array = explode(",",$staff_id);
						foreach($staff_id_array as $key => $value){
							$_SESSION["ct_details"]["staff_id"] = $value;
							echo $gc_hook->gc_add_staff_booking_ajax_hook();
						}
					}
				}
				/* GC Code End */
				$_SESSION["ct_details"] = array();
				$orderid++;
			}
			$admin_infoo = $order_client_info->readone_for_email();
	$service->id = $service_id;
	$service_name = $service->get_service_name_for_mail(); /* methods */
	$units = "None";
	$methodname = "None";
	$hh = $booking->get_methods_ofbookings($orderid);
	$count_methods = mysqli_num_rows($hh);
	$hh1 = $booking->get_methods_ofbookings($orderid);
	if ($count_methods > 0){
		while ($jj = mysqli_fetch_array($hh1)){
			if ($units == "None"){
				$units = $jj['units_title'] . "-" . $jj['qtys'];
			}
			else{
				$units = $units . "," . $jj['units_title'] . "-" . $jj['qtys'];
			}
			$methodname = $jj['method_title'];
		}
	} /* ADDONS */
	$addons = "None";
	$hh = $booking->get_addons_ofbookings($orderid);
	while ($jj = mysqli_fetch_array($hh)){
		if ($addons == "None"){
			$addons = $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
		}
		else{
			$addons = $addons . "," . $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
		}
	}
	if ($company_name == ""){
		$company_name = $settings->get_option('ct_company_name');
	}
	$setting_date_format = $settings->get_option('ct_date_picker_date_format');
	$setting_time_format = $settings->get_option('ct_choose_time_format');
	$booking_date = date($setting_date_format, strtotime($booking_date_time));
	if ($setting_time_format == 12){
		$booking_time = str_replace($english_date_array,$selected_lang_label,date("h:i A", strtotime($booking_date_time)));
	}
	else{
		$booking_time = date("H:i", strtotime($booking_date_time));
	}
	$price = $general->ct_price_format($net_amount, $symbol_position, $decimal);
	$c_address = $address;
	$client_city = $city;
	$client_state = $state;
	$client_zip = $zip;
	$client_email = $email;
	$subject = ucwords($service_name) . " on " . $booking_date;
	if ($admin_email == ""){
		$admin_email = $admin_infoo['email'];
	}
	if ($vc_status == "Y"){
		$vc_status_v = "Yes";
	}
	elseif ($vc_status == "N"){
		$vc_status_v = "No";
	}
	else{
		$vc_status_v = "N/A";
	}
	if ($p_status == "Y"){
		$p_status_v = "Yes";
	}
	elseif ($p_status == "N"){
		$p_status_v = "No";
	}
	else{
		$p_status_v = "N/A";
	}
	$cemail = $email;
	if ($appointment_auto_confirm == "Y"){
		$email_template->email_template_type = 'C';
	}
	else{
		$email_template->email_template_type = 'A';
	}
	$clientemailtemplate = $email_template->readone_client_email_template();
	if ($clientemailtemplate['email_message'] != ''){
		$clienttemplate = base64_decode($clientemailtemplate['email_message']);
	}
	else{
		$clienttemplate = base64_decode($clientemailtemplate['default_message']);
	}
	if ($appointment_auto_confirm == "Y"){
		$email_template->email_template_type = 'C';
	}
	else{
		$email_template->email_template_type = 'A';
	}
	$adminemailtemplate = $email_template->readone_admin_email_template();
	if ($adminemailtemplate['email_message'] != ''){
		$admintemplate = base64_decode($adminemailtemplate['email_message']);
	}
	else{
		$admintemplate = base64_decode($adminemailtemplate['default_message']);
	}
	$staffemailtemplate = $email_template->readone_staff_email_template();
  if($staffemailtemplate["email_message"] != ""){
    $stafftemplate = base64_decode($staffemailtemplate["email_message"]);
  }else{
    $stafftemplate = base64_decode($staffemailtemplate["default_message"]);
  }
	$client_phone_info = "";
	$client_phone_no = "";
	$client_phone_length = "";
	$client_first_name = "";
	$client_last_name = "";
	$client_fname = "";
	$client_lname = "";
	$email_notes = "";
	$client_notes = "";
	$client_phone_no = $phone;
	$client_phone_length = strlen($client_phone_no);
	if ($client_phone_length > 6){
		$client_phone_info = $client_phone_no;
	}
	else{
		$client_phone_info = "N/A";
	}
	$client_first_name = ucwords(stripslashes($first_name));
	$client_last_name = ucwords(stripslashes($last_name));
	if ($client_first_name == "" && $client_last_name == ""){
		$client_fname = "User";
		$client_lname = "";
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_first_name != "" && $client_last_name != ""){
		$client_fname = $client_first_name;
		$client_lname = $client_last_name;
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_first_name != ""){
		$client_fname = $client_first_name;
		$client_lname = "";
		$client_name = $client_fname . ' ' . $client_lname;
	}
	elseif ($client_last_name != ""){
		$client_fname = "";
		$client_lname = $client_last_name;
		$client_name = $client_fname . ' ' . $client_lname;
	}
	$client_notes = stripslashes($notes);
	if ($client_notes == ""){
		$client_notes = "N/A";
	}
	$contact_status_cont = $contact_status;
	if ($contact_status_cont == ""){
		$contact_status_cont = "N/A";
	}
		if(isset($staff_id) && !empty($staff_id))
  {
    $objadminprofile->id = $staff_id;
    $staff_details = $objadminprofile->readone();
    $get_staff_name = $staff_details["fullname"];
    $get_staff_email = $staff_details["email"];
    $staff_phone = $staff_details["phone"];
  }
	$searcharray = array('{{service_name}}','{{booking_date}}','{{business_logo}}','{{business_logo_alt}}','{{client_name}}','{{methodname}}','{{units}}','{{addons}}','{{firstname}}','{{lastname}}','{{client_email}}','{{phone}}','{{payment_method}}','{{vaccum_cleaner_status}}','{{parking_status}}','{{notes}}','{{contact_status}}','{{admin_name}}','{{price}}','{{address}}','{{app_remain_time}}','{{reject_status}}','{{company_name}}','{{booking_time}}','{{client_city}}','{{client_state}}','{{client_zip}}','{{company_city}}','{{company_state}}','{{company_zip}}','{{company_country}}','{{company_phone}}','{{company_email}}','{{company_address}}','{{admin_name}}');
  $replacearray = array($service_name,$booking_date,$business_logo,$business_logo_alt,stripslashes($client_name) ,$methodname,$units,$addons,$client_fname,$client_lname,$cemail,$client_phone_info,ucwords($payment_method) ,$vc_status_v,$p_status_v,$client_notes,$contact_status_cont,$get_admin_name,$price,stripslashes($c_address) ,'','',$company_name,$booking_time,stripslashes($client_city) ,stripslashes($client_state) ,$client_zip,stripslashes($company_city) ,stripslashes($company_state) ,$company_zip,$company_country,$company_phone,$company_email,stripslashes($company_address) ,stripslashes($get_admin_name));
  if ($settings->get_option('ct_client_email_notification_status') == 'Y' && $clientemailtemplate['email_template_status'] == 'E'){
		$client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
		if ($settings->get_option('ct_smtp_hostname') != '' && $settings->get_option('ct_email_sender_name') != '' && $settings->get_option('ct_email_sender_address') != '' && $settings->get_option('ct_smtp_username') != '' && $settings->get_option('ct_smtp_password') != '' && $settings->get_option('ct_smtp_port') != '')
		{
			$mail->IsSMTP();
		}
		else
		{
			$mail->IsMail();
		}
		if ($mail->SMTPAuth === "Yes") {
		$mail->SMTPDebug = 0;
		$mail->IsHTML(true);
		$mail->From = $company_email;
		$mail->FromName = $company_name;
		$mail->Sender = $company_email;
		$mail->AddAddress($client_email, $client_name);
		$mail->Subject = $subject;
		$mail->Body = $client_email_body;
		$mail->send();
		$mail->ClearAllRecipients();
		}else{
		    $client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($client_email, $subject, $client_email_body, $headers);
		}
	}
	if ($settings->get_option('ct_admin_email_notification_status') == 'Y' && $adminemailtemplate['email_template_status'] == 'E'){
		$admin_email_body = str_replace($searcharray, $replacearray, $admintemplate);
		if ($settings->get_option('ct_smtp_hostname') != '' && $settings->get_option('ct_email_sender_name') != '' && $settings->get_option('ct_email_sender_address') != '' && $settings->get_option('ct_smtp_username') != '' && $settings->get_option('ct_smtp_password') != '' && $settings->get_option('ct_smtp_port') != '')
		{
			$mail_a->IsSMTP();
		}
		else
		{
			$mail_a->IsMail();
		}
		if ($mail_a->SMTPAuth === "Yes") {
		$mail_a->SMTPDebug = 0;
		$mail_a->IsHTML(true);
		$mail_a->From = $company_email;
		$mail_a->FromName = $company_name;
		$mail_a->Sender = $company_email;
		$mail_a->AddAddress($admin_email, $get_admin_name);
		$mail_a->Subject = $subject;
		$mail_a->Body = $admin_email_body;
		$mail_a->send();
		$mail_a->ClearAllRecipients();
		}else{
		    $admin_email_body = str_replace($searcharray,$replacearray,$admintemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($admin_email, $subject, $admin_email_body, $headers);
		}
	}
	if($settings->get_option("ct_staff_email_notification_status") == "Y" && $clientemailtemplate["email_template_status"] == "E"){
    if ($mail->SMTPAuth === "Yes") {    
      $client_email_body = str_replace($searcharray,$replacearray,$stafftemplate);
      $mail->SMTPDebug  = 0;
      $mail->IsHTML(true);
      $mail->From = $company_email;
      $mail->FromName = $company_name;
      $mail->Sender = $company_email;
      $mail->AddAddress($get_staff_email, $get_staff_name);
      $mail->Subject = $subject;
      $mail->Body = $client_email_body;
      $mail->send();
      $mail->ClearAllRecipients();
    }else{
      $client_email_body = str_replace($searcharray,$replacearray,$stafftemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($get_staff_email, $subject, $client_email_body, $headers);
    }
  
  }/*** Email Code End ***/ /*SMS SENDING CODE*/ 
	/* MESSAGEBIRD CODE */
		if($settings->get_option("ct_sms_messagebird_status") == "Y"){
			if ($settings->get_option('ct_sms_messagebird_send_sms_to_client_status') == "Y"){
				$template = $objdashboard->gettemplate_sms("A", 'C');
				$phone = $client_phone;
				if ($template[4] == "E"){
					if ($template[2] == ""){
						$message = base64_decode($template[3]);
					}
					else{
						$message = base64_decode($template[2]);
					}
				}
				$messagebird_apikey =$settings->get_option("ct_sms_messagebird_account_apikey");     

				$message = str_replace($searcharray, $replacearray, $message);

				require_once(dirname(dirname(__FILE__)).'/messagebird/vendor/autoload.php');
				$MessageBird = new \MessageBird\Client($messagebird_apikey);

				$Message = new \MessageBird\Objects\Message();
				$Message->originator = 'MessageBird';
				$Message->recipients = $staff_phone;
				$Message->body = $message;

				$res = $MessageBird->messages->create($Message);
				var_dump($res);
				$Balance = $MessageBird->balance->read();
			}
			if ($settings->get_option('ct_sms_messagebird_send_sms_to_admin_status') == "Y"){
				$template = $objdashboard->gettemplate_sms("A", 'A');
				$phone = $settings->get_option('ct_sms_messagebird_admin_phone');;
				if ($template[4] == "E"){
					if ($template[2] == ""){
						$message = base64_decode($template[3]);
					}
					else{
						$message = base64_decode($template[2]);
					}
				}
				$messagebird_apikey =$settings->get_option("ct_sms_messagebird_account_apikey");     

				$message = str_replace($searcharray, $replacearray, $message);

				require_once(dirname(dirname(__FILE__)).'/messagebird/vendor/autoload.php');
				$MessageBird = new \MessageBird\Client($messagebird_apikey);

				$Message = new \MessageBird\Objects\Message();
				$Message->originator = 'MessageBird';
				$Message->recipients = $phone;
				$Message->body = $message;

				$res = $MessageBird->messages->create($Message);
				var_dump($res);
				$Balance = $MessageBird->balance->read();
			}
	  }
	  /* TEXTLOCAL CODE */
  if ($settings->get_option('ct_sms_textlocal_status') == "Y"){
		if ($settings->get_option('ct_sms_textlocal_send_sms_to_client_status') == "Y"){
			$template = $objdashboard->gettemplate_sms("A", 'C');
			$phone = $client_phone;
			if ($template[4] == "E"){
				if ($template[2] == ""){
					$message = base64_decode($template[3]);
				}
				else{
					$message = base64_decode($template[2]);
				}
			}
			$message = str_replace($searcharray, $replacearray, $message);
			$data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
			$ch = curl_init('http://api.textlocal.in/send/?');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
		if ($settings->get_option('ct_sms_textlocal_send_sms_to_admin_status') == "Y"){
			$template = $objdashboard->gettemplate_sms("A", 'A');
			$phone = $settings->get_option('ct_sms_textlocal_admin_phone');;
			if ($template[4] == "E"){
				if ($template[2] == ""){
					$message = base64_decode($template[3]);
				}
				else{
					$message = base64_decode($template[2]);
				}
			}
			$message = str_replace($searcharray, $replacearray, $message);
			$data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
			$ch = curl_init('http://api.textlocal.in/send/?');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
  }
			$_SESSION["time_duration"] = 0;
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["appointment_booked_successfully"]];
			setResponse($valid);
		}
		
		
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>