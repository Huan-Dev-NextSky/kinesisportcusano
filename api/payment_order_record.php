<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_payment_order_rec") {
	verifyRequiredParams(array("api_key", "user_id", "type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$limit = 5;
		$page = $_POST["page"];
		if($page == 1){
			$offset = 0;
		}else{
			$offset = $limit * $page;
		}
		if ($_POST["type"] == "client") {
			$user->user_id = $_POST["user_id"];
			$user->limit = $limit;
			$user->offset = $offset;
			$userdata = $user->get_payment_order_record();
			$array = array();
			if (mysqli_num_rows($userdata) > 0) {
				while ($row = mysqli_fetch_assoc($userdata)) {
					$order_date = strtotime($row["order_date"]);
					$row["order_date_format"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $order_date));
					$booking_date_time = strtotime($row["booking_date_time"]);
					$row["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_time));
					$row["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_time));
					$payment_date = strtotime($row["payment_date"]);
					$row["payment_date_format"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $payment_date));
					array_push($array, $row);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			} else {
				$valid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_orders_details"]];
				setResponse($valid);
			}
		} elseif ($_POST["type"] == "staff") {
			$user->user_id = $_POST["user_id"];
			$user->limit = $limit;
			$user->offset = $offset;
			$userdata = $user->get_staff_payment_order_record();
			$array = array();
			if (mysqli_num_rows($userdata) > 0) {
				while ($row = mysqli_fetch_assoc($userdata)) {
					$order_date = strtotime($row["order_date"]);
					$row["order_date_format"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $order_date));
					$booking_date_time = strtotime($row["booking_date_time"]);
					$row["appointment_date"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $booking_date_time));
					$row["appointment_time"] = str_replace($english_date_array,$selected_lang_label,date("h:i A", $booking_date_time));
					$payment_date = strtotime($row["payment_date"]);
					$row["payment_date_format"] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y", $payment_date));
					array_push($array, $row);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $array];
				setResponse($valid);
			} else {
				$valid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_orders_details"]];
				setResponse($valid);
			}
		}
	}
}

?>