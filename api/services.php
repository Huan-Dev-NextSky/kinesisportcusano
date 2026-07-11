<?php 
include "includes.php";
if (isset($_POST["action"]) && $_POST["action"] == "get_all_services") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$readall = $objservices->readall_for_frontend_services();
		$array = array();
		if (mysqli_num_rows($readall) > 0) {
			while ($data = mysqli_fetch_assoc($readall)) {
				foreach($data as $field => $value) {
					if ($data[$field] == "") {
						$data[$field] = null;
					}elseif($field == "image"){
						$image = $data[$field];
						$whole_url = SITE_URL."assets/images/services/".$image;
						$data[$field] = $whole_url;
					}
				}
				array_push($array, $data);
			}
			$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
			setResponse($valid);
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_services_found"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>