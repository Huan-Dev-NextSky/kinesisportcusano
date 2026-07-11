<?php 
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "check_postal_code") {
	verifyRequiredParams(array("api_key", "postal_code"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$postal_code_list = $objsettings->get_option_postal();
		if ($postal_code_list == "") {
			$response = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["postal_code_not_found"]];
			setResponse($response);
		} else {
			$res = explode(",", strtolower($postal_code_list));
			$check = 1;
			$p_code = strtolower($_POST["postal_code"]);
			for ($i = 0; $i <= (count((array)$res) - 1); $i++) {
				if ($res[$i] == $p_code) {
					$j = 10;
					$response = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["postal_code_found"]];
					setResponse($response);
					break;
				}
				elseif(substr($p_code, 0, strlen($res[$i])) === $res[$i]) {
					$j = 10;
					$response = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["postal_code_found"]];
					setResponse($response);
					break;
				} else {
					$j = 20;
				}
			}
			if ($j == 20) {
				$response = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["postal_code_not_found"]];
				setResponse($response);
			}
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>