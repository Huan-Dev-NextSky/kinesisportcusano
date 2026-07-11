<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "check_couponcode") {
	verifyRequiredParams(array("api_key", "coupon_code"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$coupon->coupon_code = $_POST["coupon_code"];
		$result = $coupon->checkcode();
		if ($result) {
			$coupon_exp_date = strtotime($result["coupon_expiry"]);
			$today = date("Y-m-d");
			$curr_date = strtotime($today);
			if ($result["coupon_used"] < $result["coupon_limit"] && $curr_date <= $coupon_exp_date) {
				$array = array();
				foreach($result as $field => $value) {
					if (is_numeric($field)) {
						unset($result[$field]);
					} else {
						if ($result[$field] == "") {
							$result[$field] = null;
						}
					}
				}
				array_push($array, $result);
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["coupon_code_expired"]];
				setResponse($invalid);
			}
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["invalid_coupon_code"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>