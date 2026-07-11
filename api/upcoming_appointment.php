<?php
include "includes.php";
if(isset($_POST["action"]) && $_POST["action"] == "get_all_upcoming_appointment") {
	verifyRequiredParams(array("api_key","user_id","type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		
		/* $limit = 5;
		$page = $_POST["page"];
		if($page == 1){
			$offset = 0;
		}else{
			$offset = $limit * $page;
		} */
		$type = $_POST["type"];
		$user_id = $_POST["user_id"];
		$booking->type = $type;
		$booking->user_id = $user_id;
		/* $booking->limit = $limit;
		$booking->offset = $offset; */
		$booking->booking_start_datetime = $today_date." 00:00:00";
		$all_upcomming_appointment = $booking->get_all_upcoming_bookings_api();
		
		$array = array();
		$pass_array = array();
		if (mysqli_num_rows($all_upcomming_appointment) > 0) {
			while ($row = mysqli_fetch_assoc($all_upcomming_appointment)) {
				
				$array["order_id"] = $row["order_id"];
				$client_id = $row["client_id"];
				$staff_ids = explode(",", $row["staff_ids"]);
				if($type == "user"){
					if($client_id != $user_id){
						continue;
					}
				}elseif($type == "staff"){
					if(!in_array($user_id, $staff_ids)){
						continue;
					}
				}
				
				$order_detail = $booking->get_detailsby_order_id($row["order_id"]);
				
				$client_info = unserialize(base64_decode($order_detail["client_personal_info"]));
				
				$array["booking_date_time"] = $order_detail["booking_date_time"];
				$array["booking_status"] = $order_detail["booking_status"];
				$array["reject_reason"] = $order_detail["reject_reason"];
				$array["title"] = $order_detail["service_title"];
				$array["total_payment"] = $order_detail["net_amount"];
				$array["gc_event_id"] = $order_detail["gc_event_id"];
				$array["gc_staff_event_id"] = $order_detail["gc_staff_event_id"];
				$array["staff_ids"] = $order_detail["staff_ids"];
				
				if ($order_detail["staff_ids"] != "") {
					$staff_names = "";
					$exploded_staff_ids = explode(",", $order_detail["staff_ids"]);
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
					$array["staff_names"] = $staff_names;
				} else {
					$array["staff_names"] = null;
				}
				$units = null;
				$methodname = null;
				$hh = $booking->get_methods_ofbookings($row["order_id"]);
				$count_methods = mysqli_num_rows($hh);
				$hh1 = $booking->get_methods_ofbookings($row["order_id"]);
				
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
				$hh = $booking->get_addons_ofbookings($row["order_id"]);
				while ($jj = mysqli_fetch_array($hh)) {
					if ($addons == null) {
						$addons = $jj["addon_service_name"]."-".$jj["addons_service_qty"];
					} else {
						$addons = $addons.",".$jj["addon_service_name"]."-".$jj["addons_service_qty"];
					}
				}
				
				$array["method_name"] = $methodname;
				$array["units"] = $units;
				$array["addons"] = $addons;
				$booking_date_timestamp = strtotime($array["booking_date_time"]);
				$array["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_timestamp));
				$array["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_timestamp));
				
				foreach($client_info as $field => $value) {
					if ($client_info[$field] == "") {
						$array[$field] = null;
					} else {
						$array[$field] = $value;
					}
				}
				
				foreach($array as $field => $value) {
					if ($array[$field] == "") {
						$array[$field] = null;
					} else {
						$array[$field] = $value;
					}
				}
				
				array_push($pass_array, $array);
				
			}
			if(!empty($pass_array)){
				$invalid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
				setResponse($invalid);				
			}else{
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_upcomming_appointment"]];
				setResponse($invalid);
			}
		} else {
			
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_upcomming_appointment"]];
			setResponse($invalid);
		}
	} else {
		
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>