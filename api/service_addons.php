<?php
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "get_addons_of_selected_service") {
	verifyRequiredParams(array("api_key", "service_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$addons->service_id = $_POST["service_id"];
		$addons_data = $addons->readall_from_service();
		if (mysqli_num_rows($addons_data) > 0) {
			$array = array();
			$i = 0;
			while ($data = mysqli_fetch_assoc($addons_data)) {
				foreach($data as $field => $value) {
					if ($data[$field] == "") {
						$data[$field] = null;
					}elseif($field == "image"){						
						$image = $data[$field]; 
						$whole_url = SITE_URL."assets/images/services/".$image;
						$data[$field] = $whole_url;
					}
				}
				$data["checked"] = false;
				$data["name"] = "addon-".$i;
				$i++;
				array_push($array, $data);
			}
			$response = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($response);
		} else {
			$response = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["extra_services_not_available"]];
			setResponse($response);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>