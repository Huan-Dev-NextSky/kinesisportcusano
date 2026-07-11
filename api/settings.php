<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_setting") {
	verifyRequiredParams(array("api_key", "option_name"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$array = array();
		$arr = array();
		$arr["option_value"] = $objsettings->get_option($_POST["option_name"]);
		array_push($array, $arr);
		$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
		setResponse($valid);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>