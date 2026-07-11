<?php
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
			$_SESSION["time_duration"] = 0;
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["appointment_booked_successfully"]];
			setResponse($valid);
		}
		send_email_and_sms($email_order_id, $mail_booking_date_time, $service_id, $address, $city, $state, $notes, $phone, $zip, $net_amount, $symbol_position, $decimal, $booking, $payment, $order_client_info, $service, $settings, $general, $client_email, $admin_email, $vc_status, $p_status, $appointment_auto_confirm, $email_template, $first_name, $last_name, $contact_status, $company_email, $company_name, $objdashboard, $textlocal_username, $textlocal_hash_id, $client_phone, $nexmo_admin, $nexmo_client, $business_logo, $business_logo_alt, $get_admin_name, $company_city, $company_state, $company_zip, $company_country, $company_phone, $company_address, $payment_method, $mail, $mail_a);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>