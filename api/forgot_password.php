<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "forgot_password") {
	verifyRequiredParams(array("api_key", "email", "newpassword"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$user->user_pwd = md5($_POST["newpassword"]);
		$user->user_email = $_POST["email"];
		$res = $user->forgot_update_password();
		if ($res) {
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["password_is_change"]];
			setResponse($valid);
		} else {
			$valid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["password_not_change"]];
			setResponse($valid);
		}
	}
}

?>