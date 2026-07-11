<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_appointment_detail") {
	verifyRequiredParams(array("api_key", "order_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$appointment_detail = array();
		$order_id = $_POST["order_id"];
		$book_detail = $booking->get_booking_details_appt_api($order_id);
		$appointment_detail["id"] = $order_id;
		$appointment_detail["booking_price"] = $book_detail[2];
		$appointment_detail["start_date"] = date("d-m-Y", strtotime($book_detail[1]));
		$appointment_detail["start_time"] = date("H:i", strtotime($book_detail[1]));
		$appointment_detail["booking_date_time"] = $book_detail[1];
		$units = "";
		$methodname = "";
		$hh = $booking->get_methods_ofbookings($order_id);
		$count_methods = mysqli_num_rows($hh);
		$hh1 = $booking->get_methods_ofbookings($order_id);
		if ($count_methods > 0) {
			while ($jj = mysqli_fetch_array($hh1)) {
				if ($units == "") {
					$units = $jj["units_title"]."-".$jj["qtys"];
				} else {
					$units = $units.",".$jj["units_title"]."-".$jj["qtys"];
				}
				$methodname = $jj["method_title"];
			}
		}
		$addons = "";
		$hh = $booking->get_addons_ofbookings($order_id);
		while ($jj = mysqli_fetch_array($hh)) {
			if ($addons == "") {
				$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
			} else {
				$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
			}
		}
		$appointment_detail["method_title"] = $methodname;
		$appointment_detail["unit_title"] = $units;
		$appointment_detail["addons_title"] = $addons;
		$appointment_detail["service_title"] = $book_detail[8];
		$appointment_detail["gc_event_id"] = $book_detail[9];
		$appointment_detail["gc_staff_event_id"] = $book_detail["gc_staff_event_id"];
		$staff_names = "";
		if ($book_detail["staff_ids"] != "") {
			$exploded_staff_ids = explode(",", $book_detail["staff_ids"]);
			$i = 1;
			foreach($exploded_staff_ids as $id) {
				$objadmin->id = $id;
				$staffdata = $objadmin->readone();
				if ($i = 1) {
					$staff_names.= $staffdata["fullname"];
				} else {
					$staff_names.= ", ".$staffdata["fullname"];
				}
				$i++;
			}
		}
		$appointment_detail["staff_names"] = $staff_names;
		$appointment_detail["staff_ids"] = $book_detail["staff_ids"];
		$ccnames = explode(" ", $book_detail[3]);
		$cnamess = array_filter($ccnames);
		$client_name = array_values($cnamess);
		if (sizeof((array)$client_name) > 0) {
			if ($client_name[0] != "") {
				$client_first_name = $client_name[0];
			} else {
				$client_first_name = "";
			}
			if (isset($client_name[1]) && $client_name[1] != "") {
				$client_last_name = $client_name[1];
			} else {
				$client_last_name = "";
			}
		} else {
			$client_first_name = "";
			$client_last_name = "";
		}
		if ($client_first_name != "" || $client_last_name != "") {
			$appointment_detail["client_name"] = $client_first_name." ".$client_last_name;
		} else {
			$appointment_detail["client_name"] = "";
		}
		$fetch_phone = strlen($book_detail[7]);
		if ($fetch_phone >= 6) {
			$appointment_detail["client_phone"] = $book_detail[7];
		} else {
			$appointment_detail["client_phone"] = "";
		}
		$appointment_detail["client_email"] = $book_detail[4];
		$temppp = unserialize(base64_decode($book_detail[5]));
		$tem = str_replace("\\", "", $temppp);
		if ($tem["notes"] != "") {
			$finalnotes = $tem["notes"];
		} else {
			$finalnotes = "";
		}
		$vc_status = $tem["vc_status"];
		if ($vc_status == "N") {
			$final_vc_status = "no";
		}
		elseif($vc_status == "Y") {
			$final_vc_status = "yes";
		} else {
			$final_vc_status = "-";
		}
		$p_status = $tem["p_status"];
		if ($p_status == "N") {
			$final_p_status = "no";
		}
		elseif($p_status == "Y") {
			$final_p_status = "yes";
		} else {
			$final_p_status = "-";
		}
		if ($tem["address"] != "" || $tem["city"] != "" || $tem["zip"] != "" || $tem["state"] != "") {
			$app_address = "";
			$app_city = "";
			$app_zip = "";
			$app_state = "";
			if ($tem["address"] != "") {
				$app_address = $tem["address"].", ";
			}
			if ($tem["city"] != "") {
				$app_city = $tem["city"].", ";
			}
			if ($tem["zip"] != "") {
				$app_zip = $tem["zip"].", ";
			}
			if ($tem["state"] != "") {
				$app_state = $tem["state"];
			}
			$temper = $app_address.$app_city.$app_zip.$app_state;
			$temss = rtrim($temper, ", ");
			$appointment_detail["client_address"] = $temss;
		} else {
			$appointment_detail["client_address"] = "";
		}
		$appointment_detail["vaccum_cleaner"] = $final_vc_status;
		$appointment_detail["parking"] = $final_p_status;
		$appointment_detail["client_notes"] = $finalnotes;
		$appointment_detail["contact_status"] = $tem["contact_status"];
		$appointment_detail["global_vc_status"] = $global_vc_status;
		$appointment_detail["global_p_status"] = $global_p_status;
		$appointment_detail["payment_type"] = $book_detail[6];
		$appointment_detail["booking_status"] = $book_detail[0];
		$booking_day = date("Y-m-d", strtotime($book_detail[1]));
		$current_day = date("Y-m-d");
		if ($current_day > $booking_day) {
			$appointment_detail["past"] = "yes";
		} else {
			$appointment_detail["past"] = "no";
		}
		$get_staff_services = $objadmin->readall_staff_booking();
		$booking->order_id = $order_id;
		$get_staff_assignid = explode(",", $booking->fetch_staff_of_booking());
		$array = array();
		foreach($appointment_detail as $field => $value) {
			if ($appointment_detail[$field] == "") {
				$appointment_detail[$field] = null;
			}
		}
		array_push($array, $appointment_detail);
		$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
		setResponse($valid);
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>