<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "check_login") {
	verifyRequiredParams(array("api_key", "email", "password"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$user->existing_username = trim(strip_tags(mysqli_real_escape_string($conn, $_POST["email"])));
		$user->existing_password = md5($_POST["password"]);
		$existing_login = $user->check_login_process();
		$array = array();
		if (mysqli_num_rows($existing_login) == 0) {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["incorrect_email_address_or_password"]];
			setResponse($invalid);
		} else {
			$data = mysqli_fetch_assoc($existing_login);
			if (isset($data["usertype"])) {
				$res = unserialize($data["usertype"]);
				$data["usertype"] = $res[0];
				$data["fullname"] = $data["first_name"]." ".$data["last_name"];
			}
			if (isset($data["role"])) {
				$data["usertype"] = $data["role"];
				$data["user_email"] = $data["email"];
			}
			$valid = ["status" => "true", "statuscode" => 200, "response" => $data];
			setResponse($valid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>