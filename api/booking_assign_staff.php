<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "staff_assign_to_booking") {
	verifyRequiredParams(array("api_key", "order_id", "staff_ids"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$id = $order = $_POST["order_id"];
		$booking->order_id = $_POST["order_id"];
		$staff_ids = $_POST["staff_ids"];
		$booking->save_staff_to_booking($staff_ids);
		$orderdetail = $objdashboard->getclientorder_api($id);
		$clientdetail = $objdashboard->clientemailsender($id);
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
		$staff_ids_array = explode(",",$staff_ids);
		foreach($staff_ids_array as $sid){
			$objadminprofile->id = $sid;
			$staff_details = $objadminprofile->readone();
			$searcharray = array("{{service_name}}", "{{booking_date}}", "{{business_logo}}", "{{business_logo_alt}}", "{{client_name}}", "{{methodname}}", "{{units}}", "{{addons}}", "{{client_email}}", "{{phone}}", "{{payment_method}}", "{{vaccum_cleaner_status}}", "{{parking_status}}", "{{notes}}", "{{contact_status}}", "{{address}}", "{{price}}", "{{admin_name}}", "{{firstname}}", "{{lastname}}", "{{app_remain_time}}", "{{reject_status}}", "{{company_name}}", "{{booking_time}}", "{{client_city}}", "{{client_state}}", "{{client_zip}}", "{{company_city}}", "{{company_state}}", "{{company_zip}}", "{{company_country}}", "{{company_phone}}", "{{company_email}}", "{{company_address}}", "{{admin_name}}", "{{staff_name}}", "{{staff_email}}", "{{client_promocode}}");
			$replacearray = array($service_name, $booking_date, $business_logo, $business_logo_alt, $client_name, $methodname, $units, $addons, $client_email, $client_phone, $payment_status, $final_vc_status, $final_p_status, $client_notes, $client_status, $client_address, $price, $get_admin_name, $firstname, $lastname, "", "", $admin_company_name, $booking_time, $client_city, $client_state, $client_zip, $company_city, $company_state, $company_zip, $company_country, $company_phone, $company_email, $company_address, $get_admin_name, $staff_details["fullname"], $staff_details["email"], "");
			$staff_fullname = $staff_details["fullname"];
			$staff_email = $staff_details["phone"];
			$staff_phone = $staff_details["phone"];
			$emailtemplate->email_subject = "New Appointment Assigned";
			$emailtemplate->user_type = "S";
			$staffemailtemplate = $emailtemplate->readone_client_email_template_body();
			if ($staffemailtemplate[2] != "") {
				$stafftemplate = base64_decode($staffemailtemplate[2]);
			} else {
				$stafftemplate = base64_decode($staffemailtemplate[3]);
			}
			$subject = $staffemailtemplate[1];
			if ($objsettings->get_option("ct_staff_email_notification_status") == "Y" && $staffemailtemplate[4] == "E") {
				$staff_email_body = str_replace($searcharray, $replacearray, $stafftemplate);
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
					$mail->AddAddress($staff_email, $staff_fullname);
					$mail->Subject = $subject;
					$mail->Body = $staff_email_body;
					$mail->send();
					$mail->ClearAllRecipients();
				} else {

					$headers = "MIME-Version: 1.0" . "\r\n";

					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

					mail($get_staff_email, $subject, $staff_email_body, $headers);

				}
			}
			if($staff_phone != ""){
				if ($objsettings->get_option("ct_sms_messagebird_status") == "Y") {
					if ($objsettings->get_option("ct_sms_messagebird_send_sms_to_staff_status") == "Y") {
						$template = $objdashboard->gettemplate_sms("A", "S");
						$phone = $staff_phone;
						if ($template[4] == "E") {
							if ($template[2] == "") {
								$message = base64_decode($template[3]);
							} else {
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
				if ($objsettings->get_option("ct_sms_textlocal_status") == "Y") {
					if ($objsettings->get_option("ct_sms_textlocal_send_sms_to_staff_status") == "Y") {
						$template = $objdashboard->gettemplate_sms("A", "S");
						$phone = $staff_phone;
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
				}
				if ($objsettings->get_option("ct_sms_plivo_status") == "Y") {
					$auth_id = $objsettings->get_option("ct_sms_plivo_account_SID");
					$auth_token = $objsettings->get_option("ct_sms_plivo_auth_token");
					$p = new Plivo\ RestAPI($auth_id, $auth_token, "", "");
					$plivo_sender_number = $objsettings->get_option("ct_sms_plivo_sender_number");
					$twilio_sender_number = $objsettings->get_option("ct_sms_twilio_sender_number");
					if ($objsettings->get_option("ct_sms_plivo_send_sms_to_staff_status") == "Y") {
						$template = $objdashboard->gettemplate_sms("A", "S");
						$phone = $staff_phone;
						if ($template[4] == "E") {
							if ($template[2] == "") {
								$message = base64_decode($template[3]);
							} else {
								$message = base64_decode($template[2]);
							}
							$staff_sms_body = str_replace($searcharray, $replacearray, $message); /* MESSAGE SENDING CODE THROUGH PLIVO */
							$params = array("src" => $plivo_sender_number, "dst" => $phone, "text" => $staff_sms_body, "method" => "POST");
							$response = $p->send_message($params); /* MESSAGE SENDING CODE ENDED HERE*/
						}
					}
				}
				if ($objsettings->get_option("ct_sms_twilio_status") == "Y") {
					if ($objsettings->get_option("ct_sms_twilio_send_sms_to_staff_status") == "Y") {
						$template = $objdashboard->gettemplate_sms("A", "S");
						$phone = $staff_phone;
						if ($template[4] == "E") {
							if ($template[2] == "") {
								$message = base64_decode($template[3]);
							} else {
								$message = base64_decode($template[2]);
							}
							$staff_sms_body = str_replace($searcharray, $replacearray, $message); /*TWILIO CODE*/
							$message = $twilliosms->account->messages->create(array("From" => $twilio_sender_number, "To" => $phone, "Body" => $staff_sms_body));
						}
					}
				}
				if ($objsettings->get_option("ct_nexmo_status") == "Y") {
					if ($objsettings->get_option("ct_sms_nexmo_send_sms_to_staff_status") == "Y") {
						$template = $objdashboard->gettemplate_sms("A", "S");
						$phone = $staff_phone;
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
				}
			}
		}
		$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["appointment_assigned_successfully"]];
		setResponse($valid);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>