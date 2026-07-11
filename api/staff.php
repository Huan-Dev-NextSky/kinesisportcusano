<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "add_staff") {
	verifyRequiredParams(array("api_key", "fullname", "email", "pass"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$objadminprofile->fullname = ucwords($_POST["fullname"]);
		$objadminprofile->email = $_POST["email"];
		$objadminprofile->pass = $_POST["pass"];
		$objadminprofile->role = "staff";
		$count_exist_email = $objadminprofile->check_staff_email_existing();
		if($count_exist_email > 0){
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["staff_already_exist"]];
			setResponse($invalid);
		}else{
			$add_staff = $objadminprofile->add_staff();
			if ($add_staff) {
				$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["staff_created_successfully"]];
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
}elseif(isset($_POST["action"]) && $_POST["action"] == "all_staff_details") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$all_staff_details = $objadminprofile->readall_staff();
		$pass_array = array();
		if(mysqli_num_rows($all_staff_details) > 0){
			while($row = mysqli_fetch_assoc($all_staff_details)){
				$array = $row;
				foreach($array as $field => $value) {
					if ($array[$field] == "") {
						$array[$field] = null;
					}else {
						$array[$field] = $value;
					}
				}
				$booking->staff_id = $array["id"];
				$array["booking_count"] = $booking->get_booking_count_of_staff();
				array_push($pass_array,$array);
			}
			$valid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
			setResponse($valid);
		}else{
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_staff_available"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}elseif(isset($_POST["action"]) && $_POST["action"] == "update_staff") {
	verifyRequiredParams(array("api_key", "id", "fullname", "email", "enable_booking"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$description = "";if(isset($_POST["description"])){$description = $_POST["description"];}
		$phone = "";if(isset($_POST["phone"])){$phone = $_POST["phone"];}
		$address = "";if(isset($_POST["address"])){$address = $_POST["address"];}
		$city = "";if(isset($_POST["city"])){$city = $_POST["city"];}
		$state = "";if(isset($_POST["state"])){$state = $_POST["state"];}
		$zip = "";if(isset($_POST["zip"])){$zip = $_POST["zip"];}
		$country = "";if(isset($_POST["country"])){$country = $_POST["country"];}
		$ct_service_staff = "";if(isset($_POST["ct_service_staff"])){$ct_service_staff = $_POST["ct_service_staff"];}
		$objadminprofile->id = ucwords($_POST["id"]);
		$objadminprofile->fullname = ucwords($_POST["fullname"]);
		$objadminprofile->email = $_POST["email"];
		$objadminprofile->description = $description;
		$objadminprofile->phone = $phone;
		$objadminprofile->address = $address;
		$objadminprofile->city = $city;
		$objadminprofile->state = $state;
		$objadminprofile->zip = $zip;
		$objadminprofile->country = $country;
		$objadminprofile->enable_booking = $_POST["enable_booking"];
		$objadminprofile->image = "";
		$objadminprofile->ct_service_staff = $ct_service_staff;
		$update_staff = $objadminprofile->update_staff_details();
		if ($update_staff) {
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["profile_updated_successfully"]];
			setResponse($valid);
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["error_occurred_please_try_again"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}elseif(isset($_POST["action"]) && $_POST["action"] == "all_available_staff_details") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$pass_array = array();
		$available_staff_result = $objadminprofile->readall_staff_booking();
		if(mysqli_num_rows($available_staff_result) > 0){
			while($row = mysqli_fetch_assoc($available_staff_result)){
				$array = array();
				$array["id"] = $row["id"];
				$array["fullname"] = $row["fullname"];
				array_push($pass_array,$array);
			}
			$valid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
			setResponse($valid);
		}else{
			$valid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["no_staff_available"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>