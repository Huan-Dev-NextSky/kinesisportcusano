<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_payment_by_user_type") {
	verifyRequiredParams(array("api_key", "user_type"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$limit = 5;
		$page = $_POST["page"];
		$offset = $limit * $page;
		$pass_array = array();
		if($_POST["user_type"] == "client"){
			$payment->limit = $limit;
			$payment->offset = $offset;
			$all_payments_result = $payment->getallpayment_api();
			if(mysqli_num_rows($all_payments_result) > 0){
				while($row = mysqli_fetch_assoc($all_payments_result)){
					$array = array();
					foreach($row as $field => $value) {
						if ($row[$field] == "") {
							$array[$field] = null;
						}
						if($field == "transaction_id"){
							if($row[$field] == ""){$array[$field] = "-";}
							else{$p_t_id_res = str_split($row[$field],10);$array[$field] = str_replace(","," ",implode(",",$p_t_id_res)); }
						}else if($field == "payment_method"){
							if($row[$field] == "Stripe-payment" || strtolower(trim($row[$field])) == "card-payment" || $row[$field] == "Payway-payment" || strtolower(trim($row[$field])) == "2checkout-payment"){
								$array[$field] = $label_language_values['card_payment'];
							}elseif(strtolower(trim($row[$field])) == "stripe-reccurance"){
								$array[$field] = ucwords("Stripe Reccurance");
							}else{
								$array[$field] = $label_language_values[str_replace(" ", "_", strtolower($row[$field]))];
							}
						}else if($field == "payment_date"){
							$array[$field] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y",strtotime($row[$field])));
						}else if($field == "frequently_discount"){
							$frequently_discount->id = $row[$field];
							$frequently_discount_detail = $frequently_discount->readone();
							$array[$field] = $frequently_discount_detail['discount_typename']." - ".$general->ct_price_format($row['frequently_discount_amount'],$symbol_position,$decimal);
						}else if($field == "amount" || $field == "discount" || $field == "taxes" || $field == "net_amount" || $field == "partial_amount" || $field == "frequently_discount_amount"){
							$array[$field] = $row[$field]==0?"-":$general->ct_price_format($row[$field],$symbol_position,$decimal);
						}else{
							$array[$field] = $value;
						}
					}
					array_push($pass_array,$array);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
				setResponse($valid);
			}else{
				$invalid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["no_payments_available"]];
				setResponse($invalid);
			}
		}else if($_POST["user_type"] == "staff"){
			$staffpayment->limit = $limit;
			$staffpayment->offset = $offset;
			$all_payments_result = $staffpayment->readall_ct_staff_commision_api();
			if(mysqli_num_rows($all_payments_result) > 0){
				while($row = mysqli_fetch_assoc($all_payments_result)){
					$array = array();
					foreach($row as $field => $value) {
						if ($row[$field] == "") {
							$array[$field] = null;
						}
						if($field == "transaction_id"){
							if($row[$field] == ""){$array[$field] = "-";}
							else{$p_t_id_res = str_split($row[$field],10);$array[$field] = str_replace(","," ",implode(",",$p_t_id_res)); }
						}else if($field == "payment_date"){
							$array[$field] = str_replace($english_date_array,$selected_lang_label,date("l, d-M-Y",strtotime($row[$field])));
						}else if($field == "frequently_discount"){
							$frequently_discount->id = $row[$field];
							$frequently_discount_detail = $frequently_discount->readone();
							$array[$field] = $frequently_discount_detail['discount_typename']." - ".$general->ct_price_format($row['frequently_discount_amount'],$symbol_position,$decimal);
						}else if($field == "amt_payable" || $field == "advance_paid" || $field == "net_total"){
							$array[$field] = $row[$field]==0?"-":$general->ct_price_format($row[$field],$symbol_position,$decimal);
						}else{
							$array[$field] = $value;
						}
					}
					array_push($pass_array,$array);
				}
				$valid = ["status" => "true", "statuscode" => 200, "response" => $pass_array];
				setResponse($valid);
			}else{
				$invalid = ["status" => "true", "statuscode" => 200, "response" => $label_language_values["no_payments_available"]];
				setResponse($invalid);
			}
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>