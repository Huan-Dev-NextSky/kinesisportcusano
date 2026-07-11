<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_language_labels") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		echo $lang = $settings->get_option("ct_language");
		$label_language_values = array();
		$language_label_arr = $settings->get_all_labelsbyid($lang);
		if ($language_label_arr[8] != ""){
			$default_language_arr = $settings->get_all_labelsbyid("en");
			if($language_label_arr[8] != ""){
				$label_decode_front = base64_decode($language_label_arr[8]);
			} else {
				$label_decode_front = base64_decode($default_language_arr[8]);
			}
			$label_decode_front_unserial = unserialize($label_decode_front);
			$label_language_values = array_merge($label_decode_front_unserial);
			foreach($label_language_values as $key => $value){
				$label_language_values[$key] = urldecode($value);
			}
		} else {
			$default_language_arr = $settings->get_all_labelsbyid("en");
			$label_decode_front = base64_decode($default_language_arr[8]);
			$label_decode_front_unserial = unserialize($label_decode_front);
			$label_language_values = array_merge($label_decode_front_unserial);
			foreach($label_language_values as $key => $value){
				$label_language_values[$key] = urldecode($value);
			}
		}
		$array = array();
		array_push($array, $label_language_values);
		$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
		setResponse($valid);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>