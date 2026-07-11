<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "feedback_email_send"){
	verifyRequiredParams(array("api_key","fullname","message"));
	if(isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")){
		$mess	  = $_POST["message"];
		$to       = "anuj.sachdeva@broadviewinnovations.in";
		$subject  = "Client Feedback";
		$message  = $mess;
		$headers  = "From: anuj .sachdeva@broadviewinnovations.in" . "\r\n" .
					"MIME-Version: 1.0" . "\r\n" .
					"Content-type: text/html; charset=utf-8";
		if(mail($to, $subject, $message, $headers)){
			$valid = [ "status" => "true", "statuscode"=> 200, "response" => $label_language_values["email_send"]];
			setResponse($valid); 
		} else {
			$invalid = [ "status" => "false", "statuscode"=> 404, "response" => $label_language_values["email_sending_failed"]];
			setResponse($invalid);
		}
	}
}

?>