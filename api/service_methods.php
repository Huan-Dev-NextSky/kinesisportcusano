<?php 
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "get_methods_of_selected_service") {
	verifyRequiredParams(array("api_key", "service_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$objservice_method->service_id = $_POST["service_id"];
		$res = $objservice_method->methodsbyserviceid_front();
		$total_count = mysqli_num_rows($res);
		if ($total_count == 0) {
			$response = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_services_found"]];
			setResponse($response);
		}
		elseif($total_count == 1) {
			$i = 0;
			$array = array();
			$data = mysqli_fetch_assoc($res);
			$data["name"] = "method_".$i;
			array_push($array, $data);
			$response = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($response);
		} else {
			$i = 0;
			$array = array();
			while ($data = mysqli_fetch_assoc($res)) {
				foreach($data as $field => $value) {
					if ($data[$field] == "") {
						$data[$field] = null;
					}
				}
				$data["name"] = "method_".$i;
				array_push($array, $data);
				$i++;
			}
			$response = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($response);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>