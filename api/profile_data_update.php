<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "profile_detail_update") {
	verifyRequiredParams(array("api_key", "user_id", "type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$user_id = $_POST["user_id"];
		if ($_POST["type"] == "staff") {
			verifyRequiredParams(array("fullname", "email", "phone", "address"));
			$objadmin->id = $user_id;
			$staff_detail = $objadmin->readone();
			if (!empty($staff_detail)) {
				$objadmin->password = $staff_detail["password"];
				$objadmin->fullname = ucwords($_POST["fullname"]);
				$objadmin->email = $_POST["email"];
				$objadmin->phone = $_POST["phone"];
				$objadmin->address = $_POST["address"];
				$objadmin->city = $_POST["city"] ?? "";
				$objadmin->state = $_POST["state"] ?? "";
				$objadmin->zip = $_POST["zip"] ?? "";
				$objadmin->country = $_POST["country"] ?? "";
				if ($objadmin->update_profile()) {
					$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["updated_successfully"]];
					setResponse($valid);
				} else {
					$valid = ["status" => "true", "statuscode" => 404, "response" => $label_language_values["something_went_wrong"]];
					setResponse($valid);
				}
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_details_available"]];
				setResponse($invalid);
			}
		} elseif ($_POST["type"] == "user") {
			$user->user_id = $user_id;
			$user_detail = $user->readone();
			if (!empty($user_detail)) { /* objuserdetails */
				verifyRequiredParams(array("firstname", "phone", "lastname", "address", "city", "state", "zip"));
				$objuserdetails->password = $user_detail["user_pwd"];
				$objuserdetails->firstname = $_POST["firstname"];
				$objuserdetails->phone = $_POST["phone"];
				$objuserdetails->lastname = $_POST["lastname"];
				$objuserdetails->address = $_POST["address"];
				$objuserdetails->city = $_POST["city"];
				$objuserdetails->state = $_POST["state"];
				$objuserdetails->zip = $_POST["zip"];
				$objuserdetails->id = $user_id;
				if ($objuserdetails->update_profile()) {
					$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["updated_successfully"]];
					setResponse($valid);
				} else {
					$valid = ["status" => "true", "statuscode" => 404, "response" => $label_language_values["something_went_wrong"]];
					setResponse($valid);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $user_detail];
				setResponse($valid);
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