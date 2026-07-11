<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "add_customer") {
	verifyRequiredParams(array("api_key", "email", "password", "first_name", "last_name"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$user->existing_username = $_POST["email"];
		$user->existing_password = md5($_POST["password"]);
		$existing_login = $user->check_login();
		if ($existing_login) {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["customer_already_exist"]];
			setResponse($invalid);
		} else {
			$phone = "";if(isset($_POST["phone"])){$phone = $_POST["phone"];}
			$address = "";if(isset($_POST["address"])){$address = $_POST["address"];}
			$zipcode = "";if(isset($_POST["zipcode"])){$zipcode = $_POST["zipcode"];}
			$city = "";if(isset($_POST["city"])){$city = $_POST["city"];}
			$state = "";if(isset($_POST["state"])){$state = $_POST["state"];}
			$user->user_pwd = md5($_POST["password"]);
			$user->first_name = ucwords($_POST["first_name"]);
			$user->last_name = ucwords($_POST["last_name"]);
			$user->user_email = $_POST["email"];
			$user->phone = $phone;
			$user->address = $address;
			$user->zip = $zipcode;
			$user->city = ucwords($city);
			$user->state = ucwords($state);
			$user->notes = "";
			$user->vc_status = "N";
			$user->p_status = "N";
			$user->status = "E";
			$user->usertype = serialize(array("client"));
			$user->contact_status = "";
			$add_user = $user->add_user();
			if ($add_user) {
				$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["customer_created_successfully"]];
				setResponse($valid);
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["error_occurred_please_try_again"]];
				setResponse($invalid);
			}
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>