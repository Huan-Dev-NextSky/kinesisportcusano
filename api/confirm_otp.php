<?php
if(isset($_POST["action"]) && $_POST["action"] == "confirm_otp_email") {
	verifyRequiredParams(array("api_key", "email", "otp"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$user->email = $_POST["email"];
		$optres = $user->readall_opt();
		if ($optres == $_POST["otp"]) {
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["otp_match"]];
			$user->otp = $_POST["otp"];
			$optresa = $user->opt_update_status();
			setResponse($valid);
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["otp_not_match"]];
			setResponse($invalid);
		}
	}
}
?>