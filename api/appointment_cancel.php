<?php
require_once(dirname(dirname(__FILE__)).'/assets/lib/ct_sms_opt_in.php');
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "cancel_appointment") {
	verifyRequiredParams(array("api_key", "order_id", "cancel_reason", "user_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$id = $order = $_POST["order_id"];
		if(isset($_POST["gc_event_id"]) && isset($_POST["gc_staff_event_id"]) && isset($_POST["pid"])){
			$gc_event_id = $_POST["gc_event_id"];
			$gc_staff_event_id = $_POST["gc_staff_event_id"];
			$pid = $_POST["pid"];
		}
		$lastmodify = date("Y-m-d H:i:s");
		$cancel_reson_book = $_POST["cancel_reason"];
		if ($objsettings->get_option("ct_allow_customer_cancel") === "N") {
			$invalid = ["status" => "false", "statuscode" => 403, "response" => "Customer cancel is disabled"];
			setResponse($invalid);
			exit;
		}
		ct_api_guard_customer_booking_change($conn, $objdashboard, $objsettings, $first_step, $order, 'cancel');
		/* Phase 4: customer cancel is a request — Root Admin must approve before apply */
		$objuserdetails->request_cancel_booking($order, $cancel_reson_book, $lastmodify);
		$orderdetail = $objdashboard->getclientorder_api($id);
		$clientdetail = $objdashboard->clientemailsender($id); /* Google Calendar cancel deferred until admin approval */
		$admin_company_name = $objsettings->get_option("ct_company_name");
		$setting_date_format = $objsettings->get_option("ct_date_picker_date_format");
		$setting_time_format = $objsettings->get_option("ct_choose_time_format");
		$booking_date = str_replace($english_date_array,$selected_lang_label,date($setting_date_format, strtotime($clientdetail["booking_date_time"])));
		if ($setting_time_format == 12) {
			$booking_time = str_replace($english_date_array,$selected_lang_label,date("h:i A", strtotime($clientdetail["booking_date_time"])));
		} else {
			$booking_time = date("H:i", strtotime($clientdetail["booking_date_time"]));
		}
		$company_name = $objsettings->get_option("ct_email_sender_name");
		$company_email = $objsettings->get_option("ct_email_sender_address");
		$service_name = $clientdetail["title"];
		if ($admin_email == "") {
			$admin_email = $clientdetail["email"];
		} /* $admin_name = $clientdetail["fullname"]; */
		$price = $general->ct_price_format($orderdetail[2], $symbol_position, $decimal); /* methods */
		$units = $label_language_values["none"];
		$methodname = $label_language_values["none"];
		$hh = $booking->get_methods_ofbookings($orderdetail[4]);
		$count_methods = mysqli_num_rows($hh);
		$hh1 = $booking->get_methods_ofbookings($orderdetail[4]);
		if ($count_methods > 0) {
			while ($jj = mysqli_fetch_array($hh1)) {
				if ($units == $label_language_values["none"]) {
					$units = $jj["units_title"]."-".$jj["qtys"];
				} else {
					$units = $units.",".$jj["units_title"]."-".$jj["qtys"];
				}
				$methodname = $jj["method_title"];
			}
		} /* Add ons */
		$addons = $label_language_values["none"];
		$hh = $booking->get_addons_ofbookings($orderdetail[4]);
		while ($jj = mysqli_fetch_array($hh)) {
			if ($addons == $label_language_values["none"]) {
				$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
			} else {
				$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
			}
		} /*Guest User */
		if ($orderdetail[4] == 0) {
			$gc = $objdashboard->getguestclient($orderdetail[4]);
			$temppp = unserialize(base64_decode($gc[5]));
			$temp = str_replace("\\", "", $temppp);
			$vc_status = $temp["vc_status"];
			if ($vc_status == "N") {
				$final_vc_status = $label_language_values["no"];
			}
			elseif($vc_status == "Y") {
				$final_vc_status = $label_language_values["yes"];
			} else {
				$final_vc_status = "N/A";
			}
			$p_status = $temp["p_status"];
			if ($p_status == "N") {
				$final_p_status = $label_language_values["no"];
			}
			elseif($p_status == "Y") {
				$final_p_status = $label_language_values["yes"];
			} else {
				$final_p_status = "N/A";
			}
			$client_name = $gc[2];
			$client_email = $gc[3];
			$client_phone = $gc[4];
			$firstname = $client_name;
			$lastname = "";
			$booking_status = $orderdetail[6];
			$final_vc_status;
			$final_p_status;
			$payment_status = $orderdetail[5];
			$client_address = $temp["address"];
			$client_notes = $temp["notes"];
			$client_status = $temp["contact_status"];
			$client_city = $temp["city"];
			$client_state = $temp["state"];
			$client_zip = $temp["zip"];
		} else /*Registered user */ {
			$c = $objdashboard->getguestclient($orderdetail[4]);
			$temppp = unserialize(base64_decode($c[5]));
			$temp = str_replace("\\", "", $temppp);
			$vc_status = $temp["vc_status"];
			if ($vc_status == "N") {
				$final_vc_status = $label_language_values["no"];
			}
			elseif($vc_status == "Y") {
				$final_vc_status = $label_language_values["yes"];
			} else {
				$final_vc_status = "N/A";
			}
			$p_status = $temp["p_status"];
			if ($p_status == "N") {
				$final_p_status = $label_language_values["no"];
			}
			elseif($p_status == "Y") {
				$final_p_status = $label_language_values["yes"];
			} else {
				$final_p_status = "N/A";
			}
			$client_phone_no = $c[4];
			$client_phone_length = strlen($client_phone_no);
			if ($client_phone_length > 6) {
				$client_phone = $client_phone_no;
			} else {
				$client_phone = "N/A";
			}
			$client_namess = explode(" ", $c[2]);
			$cnamess = array_filter($client_namess);
			$ccnames = array_values($cnamess);
			if (sizeof((array)$ccnames) > 0) {
				$client_first_name = $ccnames[0];
				if (isset($ccnames[1])) {
						$client_last_name = $ccnames[1];
				} else {
						$client_last_name = "";
				}
			} else {
				$client_first_name = "";
				$client_last_name = "";
			}
			if ($client_first_name == "" && $client_last_name == "") {
				$firstname = "User";
				$lastname = "";
				$client_name = $firstname." ".$lastname;
			}
			elseif($client_first_name != "" && $client_last_name != "") {
				$firstname = $client_first_name;
				$lastname = $client_last_name;
				$client_name = $firstname." ".$lastname;
			}
			elseif($client_first_name != "") {
				$firstname = $client_first_name;
				$lastname = "";
				$client_name = $firstname." ".$lastname;
			}
			elseif($client_last_name != "") {
				$firstname = "";
				$lastname = $client_last_name;
				$client_name = $firstname." ".$lastname;
			}
			$client_notes = $temp["notes"];
			if ($client_notes == "") {
				$client_notes = "N/A";
			}
			$client_status = $temp["contact_status"];
			if ($client_status == "") {
				$client_status = "N/A";
			}
			$client_email = $c[3];
			$payment_status = $orderdetail[5];
			$final_vc_status;
			$final_p_status;
			$client_address = $temp["address"];
			$client_city = $temp["city"];
			$client_state = $temp["state"];
			$client_zip = $temp["zip"];
		}
		$searcharray = array("{{service_name}}", "{{booking_date}}", "{{business_logo}}", "{{business_logo_alt}}", "{{client_name}}", "{{methodname}}", "{{units}}", "{{addons}}", "{{client_email}}", "{{phone}}", "{{payment_method}}", "{{vaccum_cleaner_status}}", "{{parking_status}}", "{{notes}}", "{{contact_status}}", "{{address}}", "{{price}}", "{{admin_name}}", "{{firstname}}", "{{lastname}}", "{{app_remain_time}}", "{{reject_status}}", "{{company_name}}", "{{booking_time}}", "{{client_city}}", "{{client_state}}", "{{client_zip}}", "{{company_city}}", "{{company_state}}", "{{company_zip}}", "{{company_country}}", "{{company_phone}}", "{{company_email}}", "{{company_address}}", "{{admin_name}}");
		$replacearray = array($service_name, $booking_date, $business_logo, $business_logo_alt, $client_name, $methodname, $units, $addons, $client_email, $client_phone, $payment_status, $final_vc_status, $final_p_status, $client_notes, $client_status, $client_address, $price, $get_admin_name, $firstname, $lastname, "", "", $admin_company_name, $booking_time, $client_city, $client_state, $client_zip, $company_city, $company_state, $company_zip, $company_country, $company_phone, $company_email, $company_address, $get_admin_name); /* Client template */
		$emailtemplate->email_subject = "Appointment Cancelled by you";
		$emailtemplate->user_type = "C";
		$clientemailtemplate = $emailtemplate->readone_client_email_template_body();
		if ($clientemailtemplate[2] != "") {
			$clienttemplate = base64_decode($clientemailtemplate[2]);
		} else {
			$clienttemplate = base64_decode($clientemailtemplate[3]);
		}
		$subject = "Cancellation request received (pending approval) - Order #".$order;
		if ($objsettings->get_option("ct_client_email_notification_status") == "Y" && $clientemailtemplate[4] == "E") {
			$client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
			$client_email_body = '<p><strong>This is a CANCELLATION REQUEST pending Root Admin approval. Your appointment has NOT been cancelled yet.</strong></p>' . $client_email_body;
			if ($objsettings->get_option("ct_smtp_hostname") != "" && $objsettings->get_option("ct_email_sender_name") != "" && $objsettings->get_option("ct_email_sender_address") != "" && $objsettings->get_option("ct_smtp_username") != "" && $objsettings->get_option("ct_smtp_password") != "" && $objsettings->get_option("ct_smtp_port") != "") {
				$mail->IsSMTP();
			} else {
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
			} else {

				$headers = "MIME-Version: 1.0" . "\r\n";

				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				mail($client_email, $subject, $client_email_body, $headers);

			}
		} /* Admin Template */
		$emailtemplate->email_subject = "Appointment Cancelled By Customer";
		$emailtemplate->user_type = "A";
		$adminemailtemplate = $emailtemplate->readone_client_email_template_body();
		if ($adminemailtemplate[2] != "") {
			$admintemplate = base64_decode($adminemailtemplate[2]);
		} else {
			$admintemplate = base64_decode($adminemailtemplate[3]);
		}
		$adminsubject = "Cancellation REQUEST pending approval - Order #".$order;
		if ($objsettings->get_option("ct_admin_email_notification_status") == "Y" && $adminemailtemplate[4] == "E") {
			$admin_email_body = str_replace($searcharray, $replacearray, $admintemplate);
			$admin_email_body = '<p><strong>Customer CANCELLATION REQUEST pending your approval. Appointment has NOT been cancelled yet.</strong></p>' . $admin_email_body;
			if ($objsettings->get_option("ct_smtp_hostname") != "" && $objsettings->get_option("ct_email_sender_name") != "" && $objsettings->get_option("ct_email_sender_address") != "" && $objsettings->get_option("ct_smtp_username") != "" && $objsettings->get_option("ct_smtp_password") != "" && $objsettings->get_option("ct_smtp_port") != "") {
				$mail_a->IsSMTP();
			} else {
				$mail_a->IsMail();
			}
			if ($mail_a->SMTPAuth === "Yes") {
			$mail_a->SMTPDebug = 0;
			$mail_a->IsHTML(true);
			$mail_a->From = $company_email;
			$mail_a->FromName = $company_name;
			$mail_a->Sender = $company_email;
			$mail_a->AddAddress($admin_email, $get_admin_name);
			$mail_a->Subject = $adminsubject;
			$mail_a->Body = $admin_email_body;
			$mail_a->send();
			$mail->ClearAllRecipients();
			} else {

				$headers = "MIME-Version: 1.0" . "\r\n";

				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

				mail($admin_email, $adminsubject, $admin_email_body, $headers);

			}
		} /*SMS SENDING CODE — skip for pending cancel request*/
		if (false) {
		/*GET APPROVED SMS TEMPLATE*/ 
			/* MESSAGEBIRD CODE */
		if($settings->get_option("ct_sms_messagebird_status") == "Y"){
			if (ct_client_sms_allowed($conn, isset($client_id)?$client_id:(isset($sms_client_id)?$sms_client_id:0), isset($order)?$order:(isset($id)?$id:(isset($order_id)?$order_id:0))) && $settings->get_option('ct_sms_messagebird_send_sms_to_client_status') == "Y"){
				$template = $objdashboard->gettemplate_sms("CC", "C");
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
				$template = $objdashboard->gettemplate_sms("CC", "A");
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
		if ($objsettings->get_option("ct_sms_textlocal_status") == "Y") {
			if (ct_client_sms_allowed($conn, isset($client_id)?$client_id:(isset($sms_client_id)?$sms_client_id:0), isset($order)?$order:(isset($id)?$id:(isset($order_id)?$order_id:0))) && $objsettings->get_option("ct_sms_textlocal_send_sms_to_client_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "C");
				$phone = $client_phone;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
				}
				$message = str_replace($searcharray, $replacearray, $message);
				$data = "username=".$textlocal_username."&hash=".$textlocal_hash_id."&message=".$message."&numbers=".$phone."&test=0";
				$ch = curl_init("http://api.textlocal.in/send/?");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);
			}
			if ($objsettings->get_option("ct_sms_textlocal_send_sms_to_admin_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "A");
				$phone = $objsettings->get_option("ct_sms_textlocal_admin_phone");
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
				}
				$message = str_replace($searcharray, $replacearray, $message);
				$data = "username=".$textlocal_username."&hash=".$textlocal_hash_id."&message=".$message."&numbers=".$phone."&test=0";
				$ch = curl_init("http://api.textlocal.in/send/?");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);
			}
		} /*PLIVO CODE*/
		if ($objsettings->get_option("ct_sms_plivo_status") == "Y") {
			$auth_id = $objsettings->get_option("ct_sms_plivo_account_SID");
			$auth_token = $objsettings->get_option("ct_sms_plivo_auth_token");
			$p = new Plivo\RestAPI($auth_id, $auth_token, "", "");
			$plivo_sender_number = $objsettings->get_option("ct_sms_plivo_sender_number");
			$twilio_sender_number = $objsettings->get_option("ct_sms_twilio_sender_number");
			if (ct_client_sms_allowed($conn, isset($client_id)?$client_id:(isset($sms_client_id)?$sms_client_id:0), isset($order)?$order:(isset($id)?$id:(isset($order_id)?$order_id:0))) && $objsettings->get_option("ct_sms_plivo_send_sms_to_client_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "C");
				$phone = $client_phone;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$client_sms_body = str_replace($searcharray, $replacearray, $message); /* MESSAGE SENDING CODE THROUGH PLIVO */
					$params = array("src" => $plivo_sender_number, "dst" => $phone, "text" => $client_sms_body, "method" => "POST");
					$response = $p->send_message($params); /* MESSAGE SENDING CODE ENDED HERE*/
				}
			}
			if ($objsettings->get_option("ct_sms_plivo_send_sms_to_admin_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "A");
				$phone = $admin_phone_plivo;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$client_sms_body = str_replace($searcharray, $replacearray, $message);
					$params = array("src" => $plivo_sender_number, "dst" => $phone, "text" => $client_sms_body, "method" => "POST");
					$response = $p->send_message($params); /* MESSAGE SENDING CODE ENDED HERE*/
				}
			}
		}
		if ($objsettings->get_option("ct_sms_twilio_status") == "Y") {
			if (ct_client_sms_allowed($conn, isset($client_id)?$client_id:(isset($sms_client_id)?$sms_client_id:0), isset($order)?$order:(isset($id)?$id:(isset($order_id)?$order_id:0))) && $objsettings->get_option("ct_sms_twilio_send_sms_to_client_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "C");
				$phone = $client_phone;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$client_sms_body = str_replace($searcharray, $replacearray, $message); /*TWILIO CODE*/
					$message = $twilliosms->account->messages->create(array("From" => $twilio_sender_number, "To" => $phone, "Body" => $client_sms_body));
				}
			}
			if ($objsettings->get_option("ct_sms_twilio_send_sms_to_admin_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "A");
				$phone = $admin_phone_twilio;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$client_sms_body = str_replace($searcharray, $replacearray, $message); /*TWILIO CODE*/
					$message = $twilliosms->account->messages->create(array("From" => $twilio_sender_number, "To" => $phone, "Body" => $client_sms_body));
				}
			}
		}
		if ($objsettings->get_option("ct_nexmo_status") == "Y") {
			if (ct_client_sms_allowed($conn, isset($client_id)?$client_id:(isset($sms_client_id)?$sms_client_id:0), isset($order)?$order:(isset($id)?$id:(isset($order_id)?$order_id:0))) && $objsettings->get_option("ct_sms_nexmo_send_sms_to_client_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "C");
				$phone = $client_phone;
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$ct_nexmo_text = str_replace($searcharray, $replacearray, $message);
					$res = $nexmo_client->send_nexmo_sms($phone, $ct_nexmo_text);
				}
			}
			if ($objsettings->get_option("ct_sms_nexmo_send_sms_to_admin_status") == "Y") {
				$template = $objdashboard->gettemplate_sms("CC", "A");
				$phone = $objsettings->get_option("ct_sms_nexmo_admin_phone_number");
				if ($template[4] == "E") {
					if ($template[2] == "") {
						$message = base64_decode($template[3]);
					} else {
						$message = base64_decode($template[2]);
					}
					$ct_nexmo_text = str_replace($searcharray, $replacearray, $message);
					$res = $nexmo_admin->send_nexmo_sms($phone, $ct_nexmo_text);
				}
			}
		} /*SMS SENDING CODE END*/
		} /* end skip SMS for pending request */
		/* skip staff SMS/email for pending cancel request */
		$valid = ["status" => "true", "statuscode" => 200, "response" => "Cancellation request submitted. Waiting for admin approval."];
		setResponse($valid);
	} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
			setResponse($invalid);
	}
}

?>