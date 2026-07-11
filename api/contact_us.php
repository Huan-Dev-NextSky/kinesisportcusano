<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_contact_us") {
	verifyRequiredParams(array("api_key", "ct_company_address"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$array = array();
		$arr["comapny_add"] = $objsettings->get_option($_POST["ct_company_address"]);
		$arr["comapny_email"] = $objsettings->get_option($_POST["ct_company_email"]);
		$arr["comapny_phone"] = $objsettings->get_option($_POST["ct_company_phone"]);
		array_push($array, $arr);
		$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
		setResponse($valid);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>