<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "otp") {
	verifyRequiredParams(array("api_key", "email"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		if (isset($_POST["email"])) {
			$user->user_email = $_POST["email"];
			$res = $user->check_customer_email_existing();
			if ($res['user_email'] == $_POST["email"]) {
				$company_email = $objsettings->get_option("ct_company_email");
				$company_name = $settings->get_option("ct_company_name");
				$client_email = $_POST["email"];
				$client_name = "cleaning";
				$subject = "Email Send for Otp";
				$otp_randome = rand(100000, 999999);
				$client_email_body = "Your Otp is :- ".$otp_randome;
				$mail->SMTPDebug = 0;
				$mail->IsHTML(true);
				$mail->From = $company_email;
				$mail->FromName = $company_name;
				$mail->Sender = $company_email;
				$mail->AddAddress($client_email, "");
				$mail->Subject = $subject;
				$mail->Body = $client_email_body;
				$mail->send();
				$mail->ClearAllRecipients();
				$user->user_otp = $otp_randome;
				$user->user_email = $client_email;
				$resadd = $user->send_otp_using_mail();
				$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["email_exist"]];
				setResponse($valid);
			} else {
				$email_error = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["email_does_not_exist"]];
				setResponse($email_error);
			}
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["invalid_credentials"]];
		setResponse($invalid);
	}
}

?>