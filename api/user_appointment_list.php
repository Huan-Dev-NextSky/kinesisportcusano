<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_user_appointments_list") {
	verifyRequiredParams(array("api_key", "user_id", "user_type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		if ($_POST["user_type"] == "client") {
			$limit = 5;
			$page = $_POST["page"];
			$offset = $limit * $page;
			$objuserdetails->id = $_POST["user_id"];
			$objuserdetails->limit = $limit;
			$objuserdetails->offset = $offset;
			$details = $objuserdetails->get_user_details_api();
			$array = array();
			if (mysqli_num_rows($details) == 0) {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_appointments_found"]];
				setResponse($invalid);
			} else {
				while ($data = mysqli_fetch_assoc($details)) {
					if ($data["staff_ids"] != "") {
						$staff_names = "";
						$exploded_staff_ids = explode(",", $data["staff_ids"]);
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
						$data["staff_names"] = $staff_names;
					}
					foreach($data as $field => $value) {
						if ($data[$field] == "") {
							$data[$field] = null;
						}
					}
					$units = null;
					$methodname = null;
					$hh = $booking->get_methods_ofbookings($data["order_id"]);
					$count_methods = mysqli_num_rows($hh);
					$hh1 = $booking->get_methods_ofbookings($data["order_id"]);
					if ($count_methods > 0) {
						while ($jj = mysqli_fetch_array($hh1)) {
							if ($units == null) {
								$units = $jj["units_title"]."-".$jj["qtys"];
							} else {
								$units = $units. ",".$jj["units_title"]."-".$jj["qtys"];
							}
							$methodname = $jj["method_title"];
						}
					}
					$addons = null;
					$hh = $booking->get_addons_ofbookings($data["order_id"]);
					while ($jj = mysqli_fetch_array($hh)) {
						if ($addons == null) {
							$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
						} else {
							$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
						}
					}
					$data["method_name"] = $methodname;
					$data["units"] = $units;
					$data["addons"] = $addons;
					$booking_date_timestamp = strtotime($data["booking_date_time"]);
					$data["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_timestamp));
					$data["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_timestamp));
					array_push($array, $data);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			}
		} elseif($_POST["user_type"] == "staff") {
			$objuserdetails->id = $_POST["user_id"];
			$details = $objuserdetails->get_staff_details_api();
			$array = array();
			if (mysqli_num_rows($details) == 0) {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_appointments_found"]];
				setResponse($invalid);
			} else {
				while ($data = mysqli_fetch_assoc($details)) {
					if ($data["staff_ids"] != "") {
						$staff_names = "";
						$exploded_staff_ids = explode(",", $data["staff_ids"]);
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
						$data["staff_names"] = $staff_names;
					}
					foreach($data as $field => $value) {
						if ($data[$field] == "") {
							$data[$field] = null;
						}
					}
					$units = null;
					$methodname = null;
					$hh = $booking->get_methods_ofbookings($data["order_id"]);
					$count_methods = mysqli_num_rows($hh);
					$hh1 = $booking->get_methods_ofbookings($data["order_id"]);
					if ($count_methods > 0) {
						while ($jj = mysqli_fetch_array($hh1)) {
							if ($units == null) {
								$units = $jj["units_title"]."-".$jj["qtys"];
							} else {
								$units = $units.",".$jj["units_title"]."-".$jj["qtys"];
							}
							$methodname = $jj["method_title"];
						}
					}
					$addons = null;
					$hh = $booking->get_addons_ofbookings($data["order_id"]);
					while ($jj = mysqli_fetch_array($hh)) {
						if ($addons == null) {
							$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
						} else {
							$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
						}
					}
					$data["method_name"] = $methodname;
					$data["units"] = $units;
					$data["addons"] = $addons;
					$booking_date_timestamp = strtotime($data["booking_date_time"]);
					$data["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_timestamp));
					$data["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_timestamp));
					array_push($array, $data);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			}
		} elseif($_POST["user_type"] == "admin") {
			$limit = 5;
			$page = $_POST["page"];
			$offset = $limit * $page;
			$booking->id = $_POST["user_id"];
			$booking->limit = $limit;
			$booking->offset = $offset;
			$details = $booking->get_all_bookings_api();
			$array = array();
			if (mysqli_num_rows($details) == 0) {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_appointments_found"]];
				setResponse($invalid);
			} else {
				while ($data = mysqli_fetch_assoc($details)) {
					if ($data["staff_ids"] != "") {
						$staff_names = "";
						$exploded_staff_ids = explode(",", $data["staff_ids"]);
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
						$data["staff_names"] = $staff_names;
					}
					foreach($data as $field => $value) {
						if ($data[$field] == "") {
							$data[$field] = null;
						}
					}
					$units = null;
					$methodname = null;
					$hh = $booking->get_methods_ofbookings($data["order_id"]);
					$count_methods = mysqli_num_rows($hh);
					$hh1 = $booking->get_methods_ofbookings($data["order_id"]);
					if ($count_methods > 0) {
						while ($jj = mysqli_fetch_array($hh1)) {
							if ($units == null) {
								$units = $jj["units_title"]."-".$jj["qtys"];
							} else {
								$units = $units. ",".$jj["units_title"]."-".$jj["qtys"];
							}
							$methodname = $jj["method_title"];
						}
					}
					$addons = null;
					$hh = $booking->get_addons_ofbookings($data["order_id"]);
					while ($jj = mysqli_fetch_array($hh)) {
						if ($addons == null) {
							$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
						} else {
							$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
						}
					}
					$data["method_name"] = $methodname;
					$data["units"] = $units;
					$data["addons"] = $addons;
					$booking_date_timestamp = strtotime($data["booking_date_time"]);
					$data["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_timestamp));
					$data["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_timestamp));
					array_push($array, $data);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			}
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>