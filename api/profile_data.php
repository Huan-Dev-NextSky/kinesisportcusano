<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_profile_detail") {
	verifyRequiredParams(array("api_key", "user_id", "type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) { /* objadmin  	user */
		$user_id = $_POST["user_id"];
		$new_array = array();
		if ($_POST["type"] == "staff") {
			$objadmin->id = $user_id;
			$array = array();
			$staff_detail = $objadmin->readone();
			if (!empty($staff_detail)) {
				$array["id"] = $staff_detail["id"];
				$array["password"] = $staff_detail["password"];
				$array["user_email"] = $staff_detail["email"];
				$array["fullname"] = $staff_detail["fullname"];
				$array["phone"] = $staff_detail["phone"];
				$array["address"] = $staff_detail["address"];
				$array["city"] = $staff_detail["city"];
				$array["state"] = $staff_detail["state"];
				$array["zip"] = $staff_detail["zip"];
				$array["country"] = $staff_detail["country"];
				$array["role"] = $staff_detail["role"];
				$array["description"] = $staff_detail["description"];
				$array["enable_booking"] = $staff_detail["enable_booking"];
				$array["service_commission"] = $staff_detail["service_commission"];
				$array["commision_value"] = $staff_detail["commision_value"];
				$array["schedule_type"] = $staff_detail["schedule_type"];
				$array["image"] = $staff_detail["image"];
				$array["service_ids"] = $staff_detail["service_ids"];
				foreach($array as $field => $value) {
					if ($array[$field] == "") {
						$array[$field] = null;
					} else {
						$array[$field] = $value;
					}
				}
				array_push($new_array, $array);
				$valid = ["status" => "true", "statuscode" => 200, "response" => $new_array];
				setResponse($valid);
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_details_available"]];
				setResponse($invalid);
			}
		} elseif ($_POST["type"] == "user") {
			$user->user_id = $user_id;
			$user_detail = $user->readone();
			$array = array();
			if (!empty($user_detail)) {
				$array["id"] = $user_detail["id"];
				$array["user_email"] = $user_detail["user_email"];
				$array["user_pwd"] = $user_detail["user_pwd"];
				$array["fullname"] = $user_detail["first_name"]." ".$user_detail["last_name"];
				$array["first_name"] = $user_detail["first_name"];
				$array["last_name"] = $user_detail["last_name"];
				$array["phone"] = $user_detail["phone"];
				$array["zip"] = $user_detail["zip"];
				$array["address"] = $user_detail["address"];
				$array["city"] = $user_detail["city"];
				$array["state"] = $user_detail["state"];
				$array["notes"] = $user_detail["notes"];
				$array["vc_status"] = $user_detail["vc_status"];
				$array["p_status"] = $user_detail["p_status"];
				$array["contact_status"] = $user_detail["contact_status"];
				$array["status"] = $user_detail["status"];
				$array["usertype"] = $user_detail["usertype"];
				$array["cus_dt"] = $user_detail["cus_dt"];
				$user_date_timestamp = strtotime($user_detail["cus_dt"]);
				$array["join_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $user_date_timestamp));
				$array["join_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $user_date_timestamp));
				foreach($array as $field => $value) {
					if ($array[$field] == "") {
						$array[$field] = null;
					} else {
						$array[$field] = $value;
					}
				}
				array_push($new_array, $array);
				$valid = ["status" => "true", "statuscode" => 200, "response" => $new_array];
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