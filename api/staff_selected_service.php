<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_staff_of_selected_service") {
	verifyRequiredParams(array("api_key", "service_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$service_provider = $_POST["service_id"];
		$objadmin->staff_select_according_service = $service_provider;
		$service_provider_list = $objadmin->get_service_acc_provider_api();
		$provider_sec = "";
		$array = array();
		$i = 1;
		while ($row = mysqli_fetch_array($service_provider_list)) {
			array_push($array,$row);
		}
		if (empty($array)) {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_staff_found"]];
			setResponse($invalid);
		} else {
			$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($valid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>