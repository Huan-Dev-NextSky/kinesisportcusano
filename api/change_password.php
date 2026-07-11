<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "change_password") {
	verifyRequiredParams(array("api_key", "user_id", "type", "old_password", "new_password", "confirm_password"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$old_password = $_POST["old_password"];
		$new_password = $_POST["new_password"];
		$confirm_password = $_POST["confirm_password"];
		if ($new_password != $confirm_password) {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["please_check_your_confirmed_password"]];
			setResponse($invalid);
			die;
		} else {
			$old_password = md5($old_password);
		}
		$user_id = $_POST["user_id"];
		if ($_POST["type"] == "staff") {
			$objadmin->id = $user_id;
			$staff_detail = $objadmin->readone();
			if (!empty($staff_detail)) {
				$orignal_password = $staff_detail["password"];
				if ($orignal_password != $old_password) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["your_password_not_match"]];
					setResponse($invalid);
				} else {
					$objadmin->password = $new_password;
					$password_update = $objadmin->update_password_api();
					if ($password_update) {
						$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["updated_successfully"]];
						setResponse($valid);
					} else {
						$valid = ["status" => "true", "statuscode" => 404, "response" => $label_language_values["something_went_wrong"]];
						setResponse($valid);
					}
				}
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_details_available"]];
				setResponse($invalid);
			}
		} elseif ($_POST["type"] == "user") {
			$user->user_id = $user_id;
			$user_detail = $user->readone();
			if (!empty($user_detail)) {
				$orignal_password = $user_detail["user_pwd"];
				if ($orignal_password != $old_password) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["your_password_not_match"]];
					setResponse($invalid);
				} else {
					$user->user_pwd = $new_password;
					$password_update = $user->update_password();
					if ($password_update) {
						$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["updated_successfully"]];
						setResponse($valid);
					} else {
						$valid = ["status" => "true", "statuscode" => 404, "response" => $label_language_values["something_went_wrong"]];
						setResponse($valid);
					}
				}
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_details_available"]];
				setResponse($invalid);
			}
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["type_is_mismatch"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>