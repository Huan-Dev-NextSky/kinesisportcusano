<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "check_partialdeposit") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		if ($objsettings->get_option("ct_partial_deposit_status") == "Y") {
			$array = array();
			$pd_arr = array();
			$pd_arr["partial_deposit_status"] = $objsettings->get_option("ct_partial_deposit_status");
			$pd_arr["partial_deposit_amount"] = $objsettings->get_option("ct_partial_deposit_amount");
			$pd_arr["partial_deposit_type"] = $objsettings->get_option("ct_partial_type");
			array_push($array, $pd_arr);
			$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($valid);
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["partial_deposit_is_disabled"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>