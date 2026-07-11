<?php
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "get_units_of_selected_method") {
	verifyRequiredParams(array("api_key", "service_id", "method_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$calculation_policy = $objsettings->get_option("ct_calculation_policy");
		$objservice_method_unit->services_id = $_POST["service_id"];
		$objservice_method_unit->methods_id = $_POST["method_id"];
		$unt_values = $objservice_method_unit->getunits_by_service_methods_setdesign();
		if (mysqli_num_rows($unt_values) > 0) {
			$ind = 0;
			$array = array();
			while ($unit_value = mysqli_fetch_assoc($unt_values)) {
				$fe = 0;
				$fg = 0;
				$strate = 1;
				$hfsec = 0;
				if ($unit_value["half_section"] == "E") {
					$hfsec = 0.5;
				} else {
					$hfsec = 1;
				}
				$rate_and_qty_arr = array();
				for ($i = $hfsec; $i <= $unit_value["maxlimit"]; $i += $hfsec) {
					$objservice_method_unit->maxlimit = $i;
					$objservice_method_unit->units_id = $unit_value["id"];
					$unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();
					if ($unt_ratess["rules"] == "G") {
						$strate = $unt_ratess["rates"];
						$fg = 1;
						$fe = 0;
					}
					$qty = $i;
					if ($fg == 1) {
						if ($unt_ratess["rules"] == "E") {
							$rate = ($calculation_policy == "M") ? $unt_ratess["rates"] * $i : $unt_ratess["rates"];
						} else {
							$rate = ($calculation_policy == "M") ? $strate * $i : $strate;
						}
					} elseif ($unt_ratess["rules"] == "E") {
						$rate = ($calculation_policy == "M") ? $unt_ratess["rates"] * $i : $unt_ratess["rates"];
					} else {
						if ($calculation_policy == "M") {
							$base_rates = $unit_value["base_price"] * $i;
						} else {
							$base_rates = $unit_value["base_price"];
						}
						$rate = $base_rates;
					}
					$arr = array();
					$arr["qty"] = $qty;
					$arr["rate"] = $rate;
					array_push($rate_and_qty_arr, $arr);
				}
				foreach($unit_value as $field => $value) {
					if ($unit_value[$field] == "") {
						$unit_value[$field] = null;
					}
				}
				$unit_value["name"] = "unit".$ind;
				$unit_value["rate_and_qty"] = $rate_and_qty_arr;
				array_push($array, $unit_value);
				$ind++;
			}
			$response = ["status" => "true","no_of_dropdown" => $ind, "statuscode" => 200, "response" => $array];
			setResponse($response);
		} else {
			$response = ["status" => "false","no_of_dropdown" => 0, "statuscode" => 404, "response" => $label_language_values["no_units_available"]];
			setResponse($response);
		}
	} else {
		$invalid = ["status" => "false", "no_of_dropdown" => 0, "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>