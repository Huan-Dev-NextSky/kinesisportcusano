<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "stripe_payment_method") {
	verifyRequiredParams(array("api_key", "full_name", "email", "card_number", "card_month", "card_year", "card_cvv", "amount"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		if($objsettings->get_option("ct_stripe_payment_form_status") != "off"){
			require_once(dirname(dirname(__FILE__)) . "/assets/stripe/stripe.php");
			$secret_key = $objsettings->get_option("ct_stripe_secretkey");
			$currency = $objsettings->get_option("ct_currency");
			\Stripe\Stripe::setApiKey($secret_key);
			$error = "";
			$success = "";
			try {
				$objtoken = new \Stripe\Token;
				$token = $objtoken::Create(array(
						"card" => array(
						"number" => $_POST["card_number"],
						"exp_month" => $_POST["card_month"],
						"exp_year" => $_POST["card_year"],
						"cvc" => $_POST["card_cvv"]
					)
				));
				$token_id = $token->id;
				$objcharge = new \Stripe\Charge;
				$striperesponse = $objcharge::Create(array(
					"amount" => round($_POST["amount"]*100),
					"currency" => $currency,
					"source" => $token_id,
					"description"=>$_POST["full_name"]
				));
				$transaction_id = $striperesponse->id;
				$valid = ["status" => "true", "statuscode" => 200, "response" => $transaction_id];
				setResponse($valid);
			}catch (Exception $e) {
				$error = $e->getMessage();
				$invalid = ["status" => "false", "statuscode" => 404, "response" => "Message Is - ".$error];
				setResponse($invalid);
				die;
			}
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["please_enable_stripe"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}


?>