<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_all_enabled_payment_gateways") {
	verifyRequiredParams(array("api_key"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$payment_array = array();
		if ($settings->get_option("ct_pay_locally_status") == "on") {
			array_push($payment_array, "pay_locally");
		}
		if ($settings->get_option("ct_bank_transfer_status") == "Y" && ($settings->get_option("ct_bank_name") != "" || $settings->get_option("ct_account_name") != "" || $settings->get_option("ct_account_number") != "" || $settings->get_option("ct_branch_code") != "" || $settings->get_option("ct_ifsc_code") != "" || $settings->get_option("ct_bank_description") != "")) {
			array_push($payment_array, "bank_transfer");
		}
		if ($settings->get_option("ct_paypal_express_checkout_status") == "on") {
			array_push($payment_array, "paypal");
		}
		if ($settings->get_option("ct_authorizenet_status") == "on" && $settings->get_option("ct_stripe_payment_form_status") != "on" && $settings->get_option("ct_2checkout_status") != "Y") {
			array_push($payment_array, "authorizenet");
		}
		if ($settings->get_option("ct_authorizenet_status") != "on" && $settings->get_option("ct_stripe_payment_form_status") == "on" && $settings->get_option("ct_2checkout_status") != "Y") {
			array_push($payment_array, "stripe");
		}
		if ($settings->get_option("ct_authorizenet_status") != "on" && $settings->get_option("ct_stripe_payment_form_status") != "on" && $settings->get_option("ct_2checkout_status") == "Y") {
			array_push($payment_array, "2checkout");
		}
		if (sizeof((array)$purchase_check) > 0) {
			foreach($purchase_check as $key => $val) {
				if ($val == "Y") {
					array_push($payment_array, $key);
				}
			}
		}
		if (sizeof((array)$payment_array) > 0) {
			$valid = ["status" => "true", "statuscode" => 200, "response" => $payment_array];
			setResponse($valid);
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["no_staff_found"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}

?>