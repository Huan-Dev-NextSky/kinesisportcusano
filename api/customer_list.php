<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "all_customer_list") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$all_customer_result = $user->readall();
		$pass_array =array();
		if(mysqli_num_rows($all_customer_result) > 0){
			while($row = mysqli_fetch_assoc($all_customer_result)){
				$array = $row;
				foreach($array as $field => $value) {
					if ($array[$field] == "") {
						$array[$field] = null;
					}else {
						$array[$field] = $value;
					}
				}
				if($array["cus_dt"] != ""){
					$array["cus_dt"] = date("l, d-M-Y",strtotime($array["cus_dt"]));
				}
				$array["booking_count"] = $user->get_users_totalbookings($array["id"]);
				array_push($pass_array,$array);
			}
			$valid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
			setResponse($valid);
		}else{
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_users_available"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>