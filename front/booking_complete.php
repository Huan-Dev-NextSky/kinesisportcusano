<?php

require_once (dirname(dirname(__FILE__)) . '/assets/quickbooks/vendor/autoload.php');
require_once (dirname(dirname(__FILE__)) . '/assets/xero/vendor/autoload.php');
require_once (dirname(dirname(__FILE__)) . '/assets/xero/storage.php');
$a = session_id();
if (empty($a))
  session_start();

include (dirname(dirname(__FILE__)) . "/header.php");
include (dirname(dirname(__FILE__)) . "/objects/class_connection.php");
include (dirname(dirname(__FILE__)) . "/objects/class_setting.php");
include (dirname(dirname(__FILE__)) . "/objects/class_services.php");
include (dirname(dirname(__FILE__)) . "/objects/class_front_first_step.php");
include (dirname(dirname(__FILE__)) . "/objects/class_users.php");
include (dirname(dirname(__FILE__)) . "/objects/class_order_client_info.php");
include (dirname(dirname(__FILE__)) . "/objects/class_coupon.php");
include (dirname(dirname(__FILE__)) . "/objects/class_booking.php");
include (dirname(dirname(__FILE__)) . "/objects/class_frequently_discount.php");
include (dirname(dirname(__FILE__)) . "/objects/class_payments.php");
include (dirname(dirname(__FILE__)) . "/objects/class.phpmailer.php");
include (dirname(dirname(__FILE__)) . "/objects/class_general.php");
include (dirname(dirname(__FILE__)) . "/objects/class_email_template.php");
include (dirname(dirname(__FILE__)) . "/objects/class_adminprofile.php");
include (dirname(dirname(__FILE__)) . "/objects/plivo.php");
include (dirname(dirname(__FILE__)) . "/assets/twilio/Services/Twilio.php");
include (dirname(dirname(__FILE__)) . "/objects/class_dashboard.php");
include (dirname(dirname(__FILE__)) . "/objects/class_nexmo.php");
include (dirname(dirname(__FILE__)) . "/objects/class_gc_hook.php");
include (dirname(dirname(__FILE__)) . '/objects/class_payment_hook.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Data\IPPLinkedTxn;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPPayment;
use XeroAPI\XeroPHP\AccountingObjectSerializer;

$database = new cleanto_db();
$conn = $database->connect();
$database->conn = $conn;

$objdashboard = new cleanto_dashboard();
$objdashboard->conn = $conn;

$objadminprofile = new cleanto_adminprofile();
$objadminprofile->conn = $conn;

$gc_hook = new cleanto_gcHook();
$gc_hook->conn = $conn;

$first_step = new cleanto_first_step();
$first_step->conn = $conn;

$email_template = new cleanto_email_template();
$email_template->conn = $conn;

$general = new cleanto_general();
$general->conn = $conn;

$user = new cleanto_users();
$user->conn = $conn;

$order_client_info = new cleanto_order_client_info();
$order_client_info->conn = $conn;

$settings = new cleanto_setting();
$settings->conn = $conn;

$coupon = new cleanto_coupon();
$coupon->conn = $conn;

$booking = new cleanto_booking();
$booking->conn = $conn;

$frequently_discount = new cleanto_frequently_discount();
$frequently_discount->conn = $conn;

$payment = new cleanto_payments();
$payment->conn = $conn;

$service = new cleanto_services();
$service->conn = $conn;

$payment_hook = new cleanto_paymentHook();
$payment_hook->conn = $conn;
$payment_hook->payment_extenstions_exist();
$purchase_check = $payment_hook->payment_purchase_status();

$symbol_position = $settings->get_option("ct_currency_symbol_position");
$decimal = $settings->get_option("ct_price_format_decimal_places");
$company_email = $settings->get_option("ct_email_sender_address");
if ($settings->get_option("ct_smtp_authetication") == "true") {
  $mail_SMTPAuth = "1";
  if ($settings->get_option("ct_smtp_hostname") == "smtp.gmail.com") {
    $mail_SMTPAuth = "Yes";
  }
} else {
  $mail_SMTPAuth = "Yes";
  if ($settings->get_option("ct_smtp_hostname") == "smtp.gmail.com") {
    $mail_SMTPAuth = "No";
  }
}
$mail = new cleanto_phpmailer();
$mail->Host = $settings->get_option("ct_smtp_hostname");
$mail->Username = $settings->get_option("ct_smtp_username");
$mail->Password = $settings->get_option("ct_smtp_password");
$mail->Port = $settings->get_option("ct_smtp_port");
$mail->SMTPSecure = $settings->get_option("ct_smtp_encryption");
$mail->SMTPAuth = $mail_SMTPAuth;
$mail->CharSet = "UTF-8";
if ($settings->get_option("ct_smtp_hostname") != "" && $settings->get_option("ct_email_sender_name") != "" && $settings->get_option("ct_email_sender_address") != "" && $settings->get_option("ct_smtp_username") != "" && $settings->get_option("ct_smtp_password") != "" && $settings->get_option("ct_smtp_port") != "") {
  $mail->IsSMTP();
} else {
  $mail->IsMail();
}

/*NEED VARIABLE FOR EMAIL*/
$company_city = $settings->get_option("ct_company_city");
$company_state = $settings->get_option("ct_company_state");
$company_zip = $settings->get_option("ct_company_zip_code");
$company_country = $settings->get_option("ct_company_country");
$company_phone = strlen($settings->get_option("ct_company_phone")) < 6 ? "" : $settings->get_option("ct_company_phone");
$company_address = $settings->get_option("ct_company_address");

/*** complete checkout code ***/

/*  set admin name */
$get_admin_name_result = $objadminprofile->readone_adminname();
$get_admin_name = $get_admin_name_result["fullname"];
if ($get_admin_name == "") {
  $get_admin_name = "Admin";
}
$admin_email = $settings->get_option("ct_admin_optional_email");
/* set business logo and logo alt */
if ($settings->get_option("ct_company_logo") != null && $settings->get_option("ct_company_logo") != "") {
  $business_logo = SITE_URL . "assets/images/services/" . $settings->get_option("ct_company_logo");
  $business_logo_alt = $settings->get_option("ct_company_name");
} else {
  $business_logo = "";
  $business_logo_alt = $settings->get_option("ct_company_name");
}
$client_phone = "";

if (isset($_SESSION["ct_details"]) && $_SESSION["ct_details"] != "") {
  $lang = $settings->get_option("ct_language");
  $label_language_values = array();
  $language_label_arr = $settings->get_all_labelsbyid($lang);
  if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "" || $language_label_arr[6] != "") {
    $default_language_arr = $settings->get_all_labelsbyid("en");
    if ($language_label_arr[1] != "") {
      $label_decode_front = base64_decode($language_label_arr[1]);
    } else {
      $label_decode_front = base64_decode($default_language_arr[1]);
    }
    $label_decode_front_unserial = unserialize($label_decode_front);
    $label_language_values = array_merge($label_decode_front_unserial);
    foreach ($label_language_values as $key => $value) {
      $label_language_values[$key] = urldecode($value);
    }
  } else {
    $default_language_arr = $settings->get_all_labelsbyid("en");
    $label_decode_front = base64_decode($default_language_arr[1]);
    $label_decode_front_unserial = unserialize($label_decode_front);
    $label_language_values = array_merge($label_decode_front_unserial);
    foreach ($label_language_values as $key => $value) {
      $label_language_values[$key] = urldecode($value);
    }
  }
  $english_date_array = array("January", "Jan", "February", "Feb", "March", "Mar", "April", "Apr", "May", "June", "Jun", "July", "Jul", "August", "Aug", "September", "Sep", "October", "Oct", "November", "Nov", "December", "Dec", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "su", "mo", "tu", "we", "th", "fr", "sa", "AM", "PM");
  $selected_lang_label = array(ucfirst(strtolower($label_language_values["january"])), ucfirst(strtolower("jan")), ucfirst(strtolower($label_language_values["february"])), ucfirst(strtolower("feb")), ucfirst(strtolower($label_language_values["march"])), ucfirst(strtolower("mar")), ucfirst(strtolower($label_language_values["april"])), ucfirst(strtolower("apr")), ucfirst(strtolower($label_language_values["may"])), ucfirst(strtolower($label_language_values["june"])), ucfirst(strtolower("jun")), ucfirst(strtolower($label_language_values["july"])), ucfirst(strtolower("jul")), ucfirst(strtolower($label_language_values["august"])), ucfirst(strtolower("aug")), ucfirst(strtolower($label_language_values["september"])), ucfirst(strtolower("sep")), ucfirst(strtolower($label_language_values["october"])), ucfirst(strtolower("oct")), ucfirst(strtolower($label_language_values["november"])), ucfirst(strtolower("nov")), ucfirst(strtolower($label_language_values["december"])), ucfirst(strtolower("dec")), ucfirst(strtolower($label_language_values["sun"])), ucfirst(strtolower($label_language_values["mon"])), ucfirst(strtolower($label_language_values["tue"])), ucfirst(strtolower($label_language_values["wed"])), ucfirst(strtolower($label_language_values["thu"])), ucfirst(strtolower($label_language_values["fri"])), ucfirst(strtolower($label_language_values["sat"])), ucfirst(strtolower($label_language_values["su"])), ucfirst(strtolower($label_language_values["mo"])), ucfirst(strtolower($label_language_values["tu"])), ucfirst(strtolower($label_language_values["we"])), ucfirst(strtolower($label_language_values["th"])), ucfirst(strtolower($label_language_values["fr"])), ucfirst(strtolower($label_language_values["sa"])), strtoupper($label_language_values["am"]), strtoupper($label_language_values["pm"]));

  $t_zone_value = $settings->get_option("ct_timezone");
  $server_timezone = date_default_timezone_get();
  if (isset($t_zone_value) && $t_zone_value != "") {
    $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
    $timezonediff = $offset / 3600;
  } else {
    $timezonediff = 0;
  }
  if (is_numeric(strpos($timezonediff, "-"))) {
    $timediffmis = str_replace("-", "", $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date("Y-m-d H:i:s")));
  } else {
    $timediffmis = str_replace("+", "", $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date("Y-m-d H:i:s")));
  }
  $current_time = date("Y-m-d H:i:s", $currDateTime_withTZ);

  $appointment_auto_confirm = $settings->get_option("ct_appointment_auto_confirm_status");
  if ($appointment_auto_confirm == "Y") {
    $booking_status = "C";
  } else {
    $booking_status = "A";
  }

  $coupon->coupon_code = $_SESSION["ct_details"]["coupon_code"];
  $result = $coupon->checkcode();
  if ($result) {
    $coupon->inc = $result["coupon_used"] + 1;
    $coupon_exp_date = strtotime($result["coupon_expiry"]);
    $today = date("Y-m-d", $currDateTime_withTZ);
    $curr_date = strtotime($today);
    if ($result["coupon_used"] < $result["coupon_limit"] && $curr_date <= $coupon_exp_date) {
      $coupon->update_coupon_limit();
    }
  }


  $user_coupon_val = $_SESSION["ct_details"]["user_coupon_val"];
  $result = $coupon->checkcouponcode($user_coupon_val);
  if ($result > 0) {
    $coupon->inc = $result["coupon_used"] + 1;
    if ($result["coupon_used"] < $result["coupon_limit"]) {
      $coupon->update_user_coupon_limit($user_coupon_val);
    }
  }

  $freq_discount = "";
  if (isset($_SESSION["ct_details"]["frequently_discount"])) {
    $freq_discount = $_SESSION["ct_details"]["frequently_discount"];
  }
  $zipcode = "";
  if (isset($_SESSION["ct_details"]["zipcode"])) {
    $zipcode = $_SESSION["ct_details"]["zipcode"];
  }
  $address = "";
  if (isset($_SESSION["ct_details"]["address"])) {
    $address = $_SESSION["ct_details"]["address"];
  }
  $city = "";
  if (isset($_SESSION["ct_details"]["city"])) {
    $city = ucwords($_SESSION["ct_details"]["city"]);
  }
  $state = "";
  if (isset($_SESSION["ct_details"]["state"])) {
    $state = ucwords($_SESSION["ct_details"]["state"]);
  }
  $notes = "";
  if (isset($_SESSION["ct_details"]["notes"])) {
    $notes = $_SESSION["ct_details"]["notes"];
  }
  $vc_status = "";
  if (isset($_SESSION["ct_details"]["vc_status"])) {
    $vc_status = $_SESSION["ct_details"]["vc_status"];
  }
  $p_status = "";
  if (isset($_SESSION["ct_details"]["p_status"])) {
    $p_status = $_SESSION["ct_details"]["p_status"];
  }
  $contact_status = "";
  if (isset($_SESSION["ct_details"]["contact_status"])) {
    $contact_status = $_SESSION["ct_details"]["contact_status"];
  }
  $staff_id = "";
  if (isset($_SESSION["ct_details"]["staff_id"])) {
    $staff_id = $_SESSION["ct_details"]["staff_id"];
  }
  $contact_status_email = $_SESSION["ct_details"]["contact_status"];

  $payment_method = ucwords($_SESSION["ct_details"]["payment_method"]);
  $transaction_id = "";
  $payment_status = "Pending";
  if (isset($_POST["transaction_id"])) {
    $payment_status = "Completed";
    $transaction_id = $_POST["transaction_id"];
  } elseif (isset($_SESSION["ct_details"]["stripe_trans_id"]) && $_SESSION["ct_details"]["payment_method"] == "stripe-payment") {
    $payment_status = "Completed";
    $transaction_id = $_SESSION["ct_details"]["stripe_trans_id"];
  } elseif (isset($_SESSION["ct_details"]["twocheckout_trans_id"]) && $_SESSION["ct_details"]["payment_method"] == "2checkout-payment") {
    $payment_status = "Completed";
    $transaction_id = $_SESSION["ct_details"]["twocheckout_trans_id"];
  } elseif (isset($_SESSION["ct_details"]["ext_payment_token"]) && $_SESSION["ct_details"]["payment_method"] == "payway-payment") {
    $payment_status = "Completed";
    $transaction_id = $_SESSION["ct_details"]["ext_payment_token"];
  } elseif (isset($_SESSION["ct_details"]["paypal_transaction_id"]) && $_SESSION["ct_details"]["payment_method"] == "paypal") {
    $payment_status = "Completed";
    $transaction_id = $_SESSION["ct_details"]["paypal_transaction_id"];
  }

  /* Payment Extension method */
  if (sizeof((array) $purchase_check) > 0) {
    foreach ($purchase_check as $key => $val) {
      if ($key == 'ct_mollie_purchase_status') {
        if ($_SESSION["ct_details"]["payment_method"] == "mollie") {
          $payment_status = "Completed";
          $transaction_id = $_SESSION["ct_details"]["transaction_id"];
        }
      } else {
        $payment_hook->payment_transation_hook($key);
      }
    }
  }

  $last_order_id = $booking->last_booking_id();
  if ($last_order_id == "0" || $last_order_id == null) {
    $orderid = 1000;
  } else {
    $orderid = $last_order_id + 1;
  }
  $email_order_id = $orderid;

  $last_recurring_id = $order_client_info->last_recurring_id();
  if ($last_recurring_id == "0" || $last_recurring_id == null) {
    $rec_id = 1;
  } else {
    $rec_id = $last_recurring_id + 1;
  }
  $booking_date_time = date("Y-m-d H:i:s", strtotime($_SESSION["ct_details"]["booking_date_time"]));

  if ($staff_id == "" && $_SESSION["staff_id_cal"] != "") {
    $provider_sec = $_SESSION["provider_sec"];
    $staff_idss = explode(",", $provider_sec);


    foreach ($staff_idss as $staff_key => $staff_value) {
      if ($staff_value !== "") {
        /* $service_provider = $_SESSION["ct_cart"]["method"][0]["service_id"]; */
        $objadminprofile->staff_value = $staff_value;
        $objadminprofile->booking_date_time = $booking_date_time;
        $service_provider_list = $objadminprofile->get_staff_id_acc_datetime();
        if ($service_provider_list == 0) {

          $check_staff_id = $staff_value;
          break;

        }
      } else {
        $check_staff_id = "";
      }
    }
  } else {
    $check_staff_id = $staff_id;
  }

  $client_id = 0;
  $stripe_cus_id = "";
  if ($_SESSION["ct_details"]["guest_user_status"] == "on") {
  } elseif ($_SESSION["ct_details"]["is_login_user"] == "Y") {
    $client_id = $_SESSION["ct_login_user_id"];
    $user->user_id = $client_id;
    $user->user_email = $_SESSION["ct_details"]["email"];
    $user->first_name = ucwords($_SESSION["ct_details"]["firstname"]);
    $user->last_name = ucwords($_SESSION["ct_details"]["lastname"]);
    $user->phone = $_SESSION["ct_details"]["phone"];
    $user->address = $_SESSION["ct_details"]["user_address"];
    $user->zip = $_SESSION["ct_details"]["user_zipcode"];
    $user->city = ucwords($_SESSION["ct_details"]["user_city"]);
    $user->state = ucwords($_SESSION["ct_details"]["user_state"]);
    $user->notes = mysqli_real_escape_string($conn, $_SESSION["ct_details"]["notes"]);
    $user->vc_status = $_SESSION["ct_details"]["vc_status"];
    $user->p_status = $_SESSION["ct_details"]["p_status"];
    $user->contact_status = mysqli_real_escape_string($conn, $_SESSION["ct_details"]["contact_status"]);
    $one_user_detail = $user->readone();
    $user->user_pwd = $one_user_detail["user_pwd"];
    $user->status = "E";
    $user->usertype = serialize(array("client"));
    $update_user = $user->update_user();
    $stripe_cus_id = $one_user_detail["stripe_id"];
  } else {
    $user->user_email = $_SESSION["ct_details"]["email"];
    $user->user_pwd = md5($_SESSION["ct_details"]["password"]);
    $user->first_name = ucwords($_SESSION["ct_details"]["firstname"]);
    $user->last_name = ucwords($_SESSION["ct_details"]["lastname"]);
    $user->phone = $_SESSION["ct_details"]["phone"];
    $user->zip = $_SESSION["ct_details"]["user_zipcode"];
    $user->address = $_SESSION["ct_details"]["user_address"];
    $user->city = ucwords($_SESSION["ct_details"]["user_city"]);
    $user->state = ucwords($_SESSION["ct_details"]["user_state"]);
    $user->notes = mysqli_real_escape_string($conn, $_SESSION["ct_details"]["notes"]);
    $user->vc_status = $_SESSION["ct_details"]["vc_status"];
    $user->p_status = $_SESSION["ct_details"]["p_status"];
    $user->contact_status = mysqli_real_escape_string($conn, $_SESSION["ct_details"]["contact_status"]);
    $user->status = "E";
    $user->usertype = serialize(array("client"));
    $user->stripe_id = "";

    if (isset($_SESSION["referral_detail"]["user_referral_code"]) && !empty($_SESSION["referral_detail"]["user_referral_code"])) {
      if ($settings->get_option('ct_refs_type') == 'F') {
        $discount_values = $settings->get_option('ct_refs_value');
        $user->update_wallet_amt = 0 + $discount_values;
      }

    } else {
      $user->update_wallet_amt = 0;
    }

    $client_id = $user->add_user();
    unset($_SESSION["ct_adminid"]);
    $_SESSION["ct_login_user_id"] = $client_id;
    $_SESSION["ct_useremail"] = $_SESSION["ct_details"]["email"];
  }

  if ($_SESSION["ct_details"]["guest_user_status"] == "off" && $settings->get_option("ct_stripe_create_plan") == "Y" && $_SESSION["ct_details"]["payment_method"] == "stripe-payment" && $_SESSION["ct_details"]["recurrence_booking_status"] == "Y") {
    require_once (dirname(dirname(__FILE__)) . "/assets/stripe_3d/vendor/autoload.php");
    $secret_key = $settings->get_option("ct_stripe_secretkey");

    try {
      \Stripe\Stripe::setApiKey($secret_key);
      $objcustomer = new \Stripe\Customer;
      if ($stripe_cus_id == "") {
        $create_customer = $objcustomer::Create(
          array(
            "email" => $_SESSION["ct_details"]["email"],
            "description" => "This id name is " . ucwords($_SESSION["ct_details"]["firstname"]) . " " . ucwords($_SESSION["ct_details"]["lastname"])
          )
        );

        $stripe_cus_id = $create_customer->id;
        $user->user_id = $client_id;
        $user->stripe_id = $stripe_cus_id;
        $user->update_user_stripe_id();
      } else {
        try {
          $stripe = new \Stripe\StripeClient($secret_key);

          $checkCustomer = $stripe->customers->retrieve(
            $stripe_cus_id,
            []
          );

          $objcustomer::Update($stripe_cus_id, array());
        } catch (Exception $e) {
          $create_customer = $objcustomer::Create(
            array(
              "email" => $_SESSION["ct_details"]["email"],
              "description" => "This id name is " . ucwords($_SESSION["ct_details"]["firstname"]) . " " . ucwords($_SESSION["ct_details"]["lastname"])
            )
          );

          $stripe_cus_id = $create_customer->id;
          $user->user_id = $client_id;
          $user->stripe_id = $stripe_cus_id;
          $user->update_user_stripe_id();
        }
      }
      $frequently_discount->id = $_SESSION["ct_details"]["frequently_discount"];
      $frequently_discount_detail = $frequently_discount->readone();
      $product_name = $frequently_discount_detail["discount_typename"];
      $stripe_product_id = $frequently_discount_detail["stripe_plan_id"];
      $days = $frequently_discount_detail["days"];

      $objplan = new \Stripe\Plan;
      $one_plan_create = $objplan::Create(
        array(
          "amount" => ((float) $_SESSION["ct_details"]["net_amount"]) * 100,
          "interval" => "day",
          "product" => $stripe_product_id,
          "currency" => $settings->get_option("ct_currency"),
          "interval_count" => $days,
          "nickname" => $product_name . " For " . $days . " days"
        )
      );
      $stripe_plan_id = $one_plan_create->id;

      $start_date = date_create(date("Y-m-d", strtotime($current_time)));
      $end_date = date_create(date("Y-m-d", strtotime($booking_date_time)));
      $diff = date_diff($start_date, $end_date);
      $total_difference = $diff->format("%a");

      $objsubscription = new \Stripe\Subscription;

      $create_subscription = $objsubscription::Create(
        array(
          "customer" => $stripe_cus_id,
          "items" => array(
            array("plan" => $stripe_plan_id, ),
          ),
          "collection_method" => "charge_automatically",
          "trial_period_days" => $total_difference,
          "payment_behavior" => "default_incomplete"
        )
      );
      $payment_method = "Stripe-Reccurance";
      $transaction_id = $create_subscription->id;
      $payment_status = "Pending";
    } catch (Exception $e) {
      echo "error:-" . $e->getMessage();
      die();
    }
  }

  if (count((array) $_SESSION["ct_cart"]) != 0) {
    if ($_SESSION["ct_details"]["recurrence_booking_status"] == "Y") {
      $frequently_discount->id = $_SESSION["ct_details"]["frequently_discount"];
      $frequently_discount_detail = $frequently_discount->readone();
      $days = $frequently_discount_detail["days"];

      $cart_date_strtotime = strtotime($booking_date_time);
      $end_3_month_strtotime = strtotime("+3 months", $cart_date_strtotime);
      $cust_datediff = $end_3_month_strtotime - $cart_date_strtotime;
      $total_days = abs(floor($cust_datediff / (60 * 60 * 24))) + 1;

      for ($j = 0; $j < $total_days; $j += $days) {
        $booking_date_time = date("Y-m-d H:i:s", strtotime("+" . $j . " days", $cart_date_strtotime));
        $booking->order_id = $orderid;
        for ($i = 0; $i < (count((array) $_SESSION["ct_cart"]["method"])); $i++) {
          $booking->service_id = $_SESSION["ct_cart"]["method"][$i]["service_id"];
          if ($_SESSION["ct_cart"]["method"][$i]["type"] == "method_units") {
            $booking->client_id = $client_id;
            $booking->order_date = $current_time;
            $booking->booking_date_time = $booking_date_time;
            //$booking->booking_date_time_end=$booking_date_time_end;																								 
            $booking->method_id = $_SESSION["ct_cart"]["method"][$i]["method_id"];
            $booking->method_unit_id = $_SESSION["ct_cart"]["method"][$i]["units_id"];
            $booking->method_unit_qty = $_SESSION["ct_cart"]["method"][$i]["s_m_qty"];
            $booking->method_unit_qty_rate = $_SESSION["ct_cart"]["method"][$i]["s_m_rate"];
            $booking->booking_status = $booking_status;
            $booking->lastmodify = $current_time;
            $booking->read_status = "U";
            $booking->staff_id = $check_staff_id;
            $add_booking = $booking->add_booking();
          } elseif ($_SESSION["ct_cart"]["method"][$i]["type"] == "addon") {
            $booking->addons_service_id = $_SESSION["ct_cart"]["method"][$i]["units_id"];
            $booking->addons_service_qty = $_SESSION["ct_cart"]["method"][$i]["s_m_qty"];
            $booking->addons_service_rate = $_SESSION["ct_cart"]["method"][$i]["s_m_rate"];
            $add_booking = $booking->add_addons_booking();
          }
        }


        $payment->order_id = $orderid;
        if ($_SESSION["ct_details"]["guest_user_status"] == "off" && $settings->get_option("ct_stripe_create_plan") == "Y" && $_SESSION["ct_details"]["payment_method"] == "stripe-payment" && $_SESSION["ct_details"]["recurrence_booking_status"] == "Y") {
          $payment->payment_method = $payment_method;
          $payment->transaction_id = $transaction_id;
          $payment->payment_status = $payment_status;
          $payment->payment_date = $booking_date_time;
        } else {
          if ($j == 0) {
            $payment->payment_method = $payment_method;
            $payment->transaction_id = $transaction_id;
            $payment->payment_status = $payment_status;
            $payment->payment_date = $current_time;
          } else {
            $payment->payment_method = ucwords("pay at venue");
            $payment->transaction_id = "";
            $payment->payment_status = "Pending";
            $payment->payment_date = $booking_date_time;
          }
        }

        $payment->amount = $_SESSION["ct_details"]["amount"];
        $payment->discount = abs($_SESSION["ct_details"]["coupon_discount"]);
        $payment->taxes = $_SESSION["ct_details"]["taxes"];
        $payment->partial_amount = $_SESSION["ct_details"]["partial_amount"];
        $payment->lastmodify = $current_time;
        $payment->net_amount = $_SESSION["ct_details"]["net_amount"];
        $payment->frequently_discount = $_SESSION["ct_details"]["frequently_discount"];
        $payment->frequently_discount_amount = abs($_SESSION["ct_details"]["frequent_discount_amount"]);
        $payment->recurrence_status = "Y";
        $add_payment = $payment->add_payments();

        $order_client_info->order_id = $orderid;
        $order_client_info->client_name = ucwords($_SESSION["ct_details"]["firstname"]) . " " . ucwords($_SESSION["ct_details"]["lastname"]);
        $order_client_info->client_email = $_SESSION["ct_details"]["email"];
        $order_client_info->client_phone = $_SESSION["ct_details"]["phone"];
        $order_client_info->client_personal_info = base64_encode(serialize(array("zip" => $zipcode, "address" => $address, "city" => $city, "state" => $state, "notes" => $notes, "vc_status" => $vc_status, "p_status" => $p_status, "contact_status" => $contact_status)));
        $order_client_info->order_duration = $_SESSION["time_duration"];
        $order_client_info->recurring_id = $rec_id;
        $add_guest_user = $order_client_info->add_order_client();

        if ($settings->get_option("ct_allow_multiple_booking_for_same_timeslot_status") == "N") {
          $count_j = $j + $days;
          $next_booking_date_time = date("Y-m-d H:i:s", strtotime("+" . $count_j . " days", $cart_date_strtotime));
          $check_for_booking_date_time = $booking->check_for_booking_date_time($next_booking_date_time, $check_staff_id);
          if (!$check_for_booking_date_time) {
            $j += $days;
            $booking_date_time = date("Y-m-d H:i:s", strtotime("+" . $j . " days", $cart_date_strtotime));
            $orderid++;
            continue;
          }
        }
        if ($gc_hook->gc_purchase_status() == "exist") {
          echo $gc_hook->gc_add_booking_ajax_hook();
          echo $gc_hook->gc_add_staff_booking_ajax_hook();
        }
        $orderid++;
      }
    } else {
      $booking->order_id = $orderid;
      for ($i = 0; $i < (count((array) $_SESSION["ct_cart"]["method"])); $i++) {
        $booking->service_id = $_SESSION["ct_cart"]["method"][$i]["service_id"];
        if ($_SESSION["ct_cart"]["method"][$i]["type"] == "method_units") {
          $booking->client_id = $client_id;
          $booking->order_date = $current_time;
          $booking->booking_date_time = $booking_date_time;
          $booking->method_id = $_SESSION["ct_cart"]["method"][$i]["method_id"];
          $booking->method_unit_id = $_SESSION["ct_cart"]["method"][$i]["units_id"];
          $booking->method_unit_qty = $_SESSION["ct_cart"]["method"][$i]["s_m_qty"];
          $booking->method_unit_qty_rate = $_SESSION["ct_cart"]["method"][$i]["s_m_rate"];
          $booking->booking_status = $booking_status;
          $booking->lastmodify = $current_time;
          $booking->read_status = "U";
          $booking->staff_id = $check_staff_id;
          $add_booking = $booking->add_booking();
        } elseif ($_SESSION["ct_cart"]["method"][$i]["type"] == "addon") {
          $booking->addons_service_id = $_SESSION["ct_cart"]["method"][$i]["units_id"];
          $booking->addons_service_qty = $_SESSION["ct_cart"]["method"][$i]["s_m_qty"];
          $booking->addons_service_rate = $_SESSION["ct_cart"]["method"][$i]["s_m_rate"];
          $add_booking = $booking->add_addons_booking();
        }
      }

      $payment->order_id = $orderid;
      $payment->payment_method = $payment_method;
      $payment->transaction_id = $transaction_id;
      $payment->payment_status = $payment_status;
      $payment->amount = $_SESSION["ct_details"]["amount"];
      $payment->discount = abs($_SESSION["ct_details"]["coupon_discount"]);
      $payment->taxes = $_SESSION["ct_details"]["taxes"];
      $payment->partial_amount = $_SESSION["ct_details"]["partial_amount"];
      $payment->payment_date = $current_time;
      $payment->lastmodify = $current_time;
      $payment->net_amount = $_SESSION["ct_details"]["net_amount"];
      $payment->frequently_discount = $_SESSION["ct_details"]["frequently_discount"];
      $payment->frequently_discount_amount = abs($_SESSION["ct_details"]["frequent_discount_amount"]);
      $payment->recurrence_status = "N";
      $add_payment = $payment->add_payments();

      $order_client_info->order_id = $orderid;
      $order_client_info->client_name = ucwords($_SESSION["ct_details"]["firstname"]) . " " . ucwords($_SESSION["ct_details"]["lastname"]);
      $order_client_info->client_email = $_SESSION["ct_details"]["email"];
      $order_client_info->client_phone = $_SESSION["ct_details"]["phone"];
      $order_client_info->client_personal_info = base64_encode(serialize(array("zip" => $zipcode, "address" => $address, "city" => $city, "state" => $state, "notes" => $notes, "vc_status" => $vc_status, "p_status" => $p_status, "contact_status" => $contact_status)));
      $order_client_info->order_duration = $_SESSION["time_duration"];
      $order_client_info->recurring_id = $rec_id;
      $add_guest_user = $order_client_info->add_order_client();
      if ($gc_hook->gc_purchase_status() == "exist") {
        echo $gc_hook->gc_add_booking_ajax_hook();
        echo $gc_hook->gc_add_staff_booking_ajax_hook();
      }
    }
  }
  if ($staff_id) {
    $booking->staff_id = $check_staff_id;
    $booking->order_id = $orderid;
    $status_insert_id = $booking->staff_status_insert();
  }
  $orderid = $email_order_id;
  /*** Email Code Start ***/
  $order_client_info->order_id = $orderid;
  $admin_infoo = $order_client_info->readone_for_email();
  $service_name = $_SESSION["ct_details"]["service_name"];
  /* methods */
  $units = $label_language_values["none"];
  $methodname = $label_language_values["none"];
  $hh = $booking->get_methods_ofbookings($orderid);
  $count_methods = mysqli_num_rows($hh);
  $hh1 = $booking->get_methods_ofbookings($orderid);
  if ($count_methods > 0) {
    while ($jj = mysqli_fetch_array($hh1)) {
      if ($units == "None") {
        $units = $jj["units_title"] . "-" . $jj["qtys"];
      } else {
        $units = $units . "," . $jj["units_title"] . "-" . $jj["qtys"];
      }
      $methodname = $jj["method_title"];
    }
  }
  /* ADDONS */
  $addons = $label_language_values["none"];
  $hh = $booking->get_addons_ofbookings($orderid);
  if ($hh->num_rows > 0) {
    while ($jj = mysqli_fetch_array($hh)) {
      if ($addons == "None") {
        $addons = $jj["addon_service_name"] . "-" . $jj["addons_service_qty"];
      } else {
        $addons = $addons . "," . $jj["addon_service_name"] . "-" . $jj["addons_service_qty"];
      }
    }
  }

  $company_name = $settings->get_option("ct_email_sender_name");
  if ($company_name == "") {
    $company_name = $settings->get_option("ct_company_name");
  }
  $setting_date_format = $settings->get_option("ct_date_picker_date_format");
  $setting_time_format = $settings->get_option("ct_time_format");

  $booking_date = str_replace($english_date_array, $selected_lang_label, date($setting_date_format, strtotime($_SESSION["ct_details"]["booking_date_time"])));
  if ($setting_time_format == 12) {
    $booking_time = str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($_SESSION["ct_details"]["booking_date_time"])));
  } else {
    $booking_time = date("H:i", strtotime($_SESSION["ct_details"]["booking_date_time"]));
  }

  $price = $general->ct_price_format($_SESSION["ct_details"]["net_amount"], $symbol_position, $decimal);
  $c_address = $_SESSION["ct_details"]["address"];
  $client_city = $_SESSION["ct_details"]["city"];
  $client_state = $_SESSION["ct_details"]["state"];
  $client_zip = $_SESSION["ct_details"]["zipcode"];
  $client_email = "";
  if ($_SESSION["ct_details"]["is_login_user"] == "Y") {
    $client_email = $_SESSION["ct_useremail"];
  } else {
    $client_email = $_SESSION["ct_details"]["email"];
  }

  $subject = ucwords($service_name) . " " . $label_language_values["on"] . " " . $booking_date;
  if ($vc_status == "Y") {
    $vc_status_v = "Yes";
  } else if ($vc_status == "N") {
    $vc_status_v = "No";
  } else {
    $vc_status_v = "N/A";
  }
  if ($p_status == "Y") {
    $p_status_v = "Yes";
  } elseif ($p_status == "N") {
    $p_status_v = "No";
  } else {
    $p_status_v = "N/A";
  }
  $cemail = $_SESSION["ct_details"]["email"];

  if ($appointment_auto_confirm == "Y") {
    $email_template->email_template_type = "C";
  } else {
    $email_template->email_template_type = "A";
  }
  $clientemailtemplate = $email_template->readone_client_email_template();
  if ($clientemailtemplate["email_message"] != "") {
    $clienttemplate = base64_decode($clientemailtemplate["email_message"]);
  } else {
    $clienttemplate = base64_decode($clientemailtemplate["default_message"]);
  }
  $staffemailtemplate = $email_template->readone_staff_email_template();
  if ($staffemailtemplate["email_message"] != "") {
    $stafftemplate = base64_decode($staffemailtemplate["email_message"]);
  } else {
    $stafftemplate = base64_decode($staffemailtemplate["default_message"]);
  }
  $adminemailtemplate = $email_template->readone_admin_email_template();
  if ($adminemailtemplate["email_message"] != "") {
    $admintemplate = base64_decode($adminemailtemplate["email_message"]);
  } else {
    $admintemplate = base64_decode($adminemailtemplate["default_message"]);
  }

  $client_phone_info = $client_phone_no = $client_fname = $client_lname = $client_notes = "";
  $client_phone_no = $_SESSION["ct_details"]["phone"];
  $client_phone_length = strlen($client_phone_no);
  if ($client_phone_length > 6) {
    $client_phone_info = $client_phone_no;
  } else {
    $client_phone_info = "N/A";
  }

  $client_first_name = ucwords(stripslashes($_SESSION["ct_details"]["firstname"]));
  $client_last_name = ucwords(stripslashes($_SESSION["ct_details"]["lastname"]));
  $client_name = $client_fname = $client_lname = "";
  if ($client_first_name == "" && $client_last_name == "") {
    $client_fname = "User";
    $client_lname = "";
    $client_name = $client_fname . " " . $client_lname;
  } elseif ($client_first_name != "" && $client_last_name != "") {
    $client_fname = $client_first_name;
    $client_lname = $client_last_name;
    $client_name = $client_fname . " " . $client_lname;
  } elseif ($client_first_name != "") {
    $client_fname = $client_first_name;
    $client_lname = "";
    $client_name = $client_fname . " " . $client_lname;
  } elseif ($client_last_name != "") {
    $client_fname = "";
    $client_lname = $client_last_name;
    $client_name = $client_fname . " " . $client_lname;
  }
  $client_notes = stripslashes($notes);
  if ($client_notes == "") {
    $client_notes = "N/A";
  }

  if ($contact_status_email == "") {
    $contact_status_email = "N/A";
  }

  $payment_method = $_SESSION["ct_details"]["payment_method"];
  if ($payment_method == "pay at venue") {
    $payment_method = ucwords($label_language_values["pay_locally"]);
  } else {
    $payment_method = ucwords($payment_method);
  }
  $promo_code = $_SESSION["ct_details"]["coupon_code"];

  $staff_id = $_SESSION["ct_details"]["staff_id"];
  $get_staff_name = "";
  $get_staff_email = "";
  $staff_phone = "";
  if (isset($staff_id) && !empty($staff_id)) {
    $objadminprofile->id = $staff_id;
    $staff_details = $objadminprofile->readone();
    $get_staff_name = $staff_details["fullname"];
    $get_staff_email = $staff_details["email"];
    $staff_phone = $staff_details["phone"];
  }

  $searcharray = array("{{service_name}}", "{{booking_date}}", "{{business_logo}}", "{{business_logo_alt}}", "{{client_name}}", "{{methodname}}", "{{units}}", "{{addons}}", "{{firstname}}", "{{lastname}}", "{{client_email}}", "{{phone}}", "{{payment_method}}", "{{vaccum_cleaner_status}}", "{{parking_status}}", "{{notes}}", "{{contact_status}}", "{{admin_name}}", "{{price}}", "{{address}}", "{{app_remain_time}}", "{{reject_status}}", "{{company_name}}", "{{booking_time}}", "{{client_city}}", "{{client_state}}", "{{client_zip}}", "{{client_promocode}}", "{{company_city}}", "{{company_state}}", "{{company_zip}}", "{{company_country}}", "{{company_phone}}", "{{company_email}}", "{{company_address}}", "{{admin_name}}", "{{staff_name}}", "{{staff_email}}");
  $replacearray = array($service_name, $booking_date, $business_logo, $business_logo_alt, stripslashes($client_name), $methodname, $units, $addons, $client_fname, $client_lname, $cemail, $client_phone_info, $payment_method, $vc_status_v, $p_status_v, $client_notes, $contact_status_email, $get_admin_name, $price, stripslashes($c_address), "", "", $company_name, $booking_time, stripslashes($client_city), stripslashes($client_state), $client_zip, $promo_code, stripslashes($company_city), stripslashes($company_state), $company_zip, $company_country, $company_phone, $company_email, stripslashes($company_address), stripslashes($get_admin_name), stripslashes($get_staff_name), stripslashes($get_staff_email));


  if ($staff_id != "") {
    $get_staff_reve = $objadminprofile->get_staff_reve($staff_id);
    $objadminprofile->staff_id = $staff_id;
    $wallet_amount = $objadminprofile->get_previous_staff_wallet();

    $staff_share = $_SESSION["ct_details"]["net_amount"] * $get_staff_reve / 100;

    $objadminprofile->update_wallet_value = $wallet_amount[0] + $staff_share;

    $objadminprofile->update_staff_wallet();
  }




  if ($settings->get_option("ct_client_email_notification_status") == "Y" && $clientemailtemplate["email_template_status"] == "E") {
    if ($mail->SMTPAuth === "Yes") {
      $client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
      $mail->SMTPDebug = 0;
      $mail->IsHTML(true);
      $mail->From = $company_email;
      $mail->FromName = $company_name;
      $mail->Sender = $company_email;
      $mail->AddAddress($client_email, $client_name);
      $mail->Subject = $subject;
      $mail->Body = $client_email_body;
      $mail->send();
      $mail->ClearAllRecipients();
    } else {
      $client_email_body = str_replace($searcharray, $replacearray, $clienttemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($client_email, $subject, $client_email_body, $headers);
    }

  }
  if ($settings->get_option("ct_admin_email_notification_status") == "Y" && $adminemailtemplate["email_template_status"] == "E") {
    if ($mail->SMTPAuth === "Yes") {
      $admin_email_body = str_replace($searcharray, $replacearray, $admintemplate);
      $mail->SMTPDebug = 0;
      $mail->IsHTML(true);
      $mail->From = $company_email;
      $mail->FromName = $company_name;
      $mail->Sender = $company_email;
      $mail->AddAddress($admin_email, $get_admin_name);
      $mail->Subject = $subject;
      $mail->Body = $admin_email_body;
      $mail->send();
      $mail->ClearAllRecipients();
    } else {
      $admin_email_body = str_replace($searcharray, $replacearray, $admintemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($admin_email, $subject, $admin_email_body, $headers);
    }

  }
  if ($settings->get_option("ct_staff_email_notification_status") == "Y" && $clientemailtemplate["email_template_status"] == "E") {
    if ($mail->SMTPAuth === "Yes") {
      $client_email_body = str_replace($searcharray, $replacearray, $stafftemplate);
      $mail->SMTPDebug = 0;
      $mail->IsHTML(true);
      $mail->From = $company_email;
      $mail->FromName = $company_name;
      $mail->Sender = $company_email;
      $mail->AddAddress($get_staff_email, $get_staff_name);
      $mail->Subject = $subject;
      $mail->Body = $client_email_body;
      $mail->send();
      $mail->ClearAllRecipients();
    } else {
      $client_email_body = str_replace($searcharray, $replacearray, $stafftemplate);
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      mail($get_staff_email, $subject, $client_email_body, $headers);
    }

  }
  /*** Email Code End ***/

  /*SMS SENDING CODE Start*/
  $client_phone = $_SESSION["ct_details"]["phone"];
  /* MESSAGEBIRD CODE */
  if ($settings->get_option("ct_sms_messagebird_status") == "Y") {
    if ($settings->get_option('ct_sms_messagebird_send_sms_to_client_status') == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "C");
      $phone = $client_phone;
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
      }
      $messagebird_apikey = $settings->get_option("ct_sms_messagebird_account_apikey");

      $message = str_replace($searcharray, $replacearray, $message);

      require_once (dirname(dirname(__FILE__)) . '/assets/messagebird/vendor/autoload.php');
      $MessageBird = new \MessageBird\Client($messagebird_apikey);

      $Message = new \MessageBird\Objects\Message();
      $Message->originator = 'MessageBird';
      $Message->recipients = $phone;
      $Message->body = $message;

      $res = $MessageBird->messages->create($Message);
      $Balance = $MessageBird->balance->read();
    }

    if ($settings->get_option('ct_sms_messagebird_send_sms_to_admin_status') == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "A");
      $phone = $settings->get_option('ct_sms_messagebird_admin_phone');
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
      }
      $messagebird_apikey = $settings->get_option("ct_sms_messagebird_account_apikey");

      $message = str_replace($searcharray, $replacearray, $message);

      require_once (dirname(dirname(__FILE__)) . '/assets/messagebird/vendor/autoload.php');
      $MessageBird = new \MessageBird\Client($messagebird_apikey);

      $Message = new \MessageBird\Objects\Message();
      $Message->originator = 'MessageBird';
      $Message->recipients = $phone;
      $Message->body = $message;

      $res = $MessageBird->messages->create($Message);

      $Balance = $MessageBird->balance->read();
    }

    if ($settings->get_option('ct_sms_messagebird_send_sms_to_staff_status') == "Y") {
      if (isset($staff_phone) && !empty($staff_phone)) {
        $template = $objdashboard->gettemplate_sms("A", "S");
        $phone = $staff_phone;
        if ($template[4] == "E") {
          if ($template[2] == "") {
            $message = base64_decode($template[3]);
          } else {
            $message = base64_decode($template[2]);
          }
        }
        $messagebird_apikey = $settings->get_option("ct_sms_messagebird_account_apikey");

        $message = str_replace($searcharray, $replacearray, $message);

        require_once (dirname(dirname(__FILE__)) . '/assets/messagebird/vendor/autoload.php');
        $MessageBird = new \MessageBird\Client($messagebird_apikey);

        $Message = new \MessageBird\Objects\Message();
        $Message->originator = 'MessageBird';
        $Message->recipients = $phone;
        $Message->body = $message;

        $res = $MessageBird->messages->create($Message);

        $Balance = $MessageBird->balance->read();
      }
    }
  }
  /* TEXTLOCAL CODE */
  if ($settings->get_option("ct_sms_textlocal_status") == "Y") {
    $textlocal_username = $settings->get_option("ct_sms_textlocal_account_username");
    $textlocal_hash_id = $settings->get_option("ct_sms_textlocal_account_hash_id");
    if ($settings->get_option("ct_sms_textlocal_send_sms_to_client_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "C");
      $phone = $client_phone;
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
      }
      $message = str_replace($searcharray, $replacearray, $message);
      $data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
      $ch = curl_init("http://api.textlocal.in/send/?");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);
    }
    if ($settings->get_option("ct_sms_textlocal_send_sms_to_admin_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "A");
      $company_contry_code = $settings->get_option("ct_company_country_code");
      $contry_code_array = explode(",", $company_contry_code);
      $phone = $contry_code_array[0] . $settings->get_option("ct_sms_textlocal_admin_phone");
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
      }
      $message = str_replace($searcharray, $replacearray, $message);
      $data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
      $ch = curl_init("http://api.textlocal.in/send/?");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);
    }
    if ($settings->get_option("ct_sms_textlocal_send_sms_to_staff_status") == "Y") {
      if (isset($staff_phone) && !empty($staff_phone)) {
        $template = $objdashboard->gettemplate_sms("A", "S");
        $phone = $staff_phone;
        if ($template[4] == "E") {
          if ($template[2] == "") {
            $message = base64_decode($template[3]);
          } else {
            $message = base64_decode($template[2]);
          }
        }
        $message = str_replace($searcharray, $replacearray, $message);
        $data = "username=" . $textlocal_username . "&hash=" . $textlocal_hash_id . "&message=" . $message . "&numbers=" . $phone . "&test=0";
        $ch = curl_init("http://api.textlocal.in/send/?");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
      }
    }
  }
  /*PLIVO CODE*/
  if ($settings->get_option("ct_sms_plivo_status") == "Y") {
    $plivo_sender_number = $settings->get_option("ct_sms_plivo_sender_number");
    $obj_plivo = new Plivo\RestAPI($settings->get_option("ct_sms_plivo_account_SID"), $settings->get_option("ct_sms_plivo_auth_token"), "", "");
    if ($settings->get_option("ct_sms_plivo_send_sms_to_client_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "C");
      $phone = $client_phone;
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $client_sms_body = str_replace($searcharray, $replacearray, $message);
        $params = array(
          "src" => $plivo_sender_number,
          "dst" => $phone,
          "text" => $client_sms_body,
          "method" => "POST"
        );
        $response = $obj_plivo->send_message($params);
      }
    }
    if ($settings->get_option("ct_sms_plivo_send_sms_to_admin_status") == "Y") {
      $admin_phone = $settings->get_option("ct_sms_plivo_admin_phone_number");
      $template = $objdashboard->gettemplate_sms("A", "A");
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $client_sms_body = str_replace($searcharray, $replacearray, $message);
        $params = array(
          "src" => $plivo_sender_number,
          "dst" => $admin_phone,
          "text" => $client_sms_body,
          "method" => "POST"
        );
        $response = $obj_plivo->send_message($params);
      }
    }
    if ($settings->get_option("ct_sms_plivo_send_sms_to_staff_status") == "Y") {
      if (isset($staff_phone) && !empty($staff_phone)) {
        $template = $objdashboard->gettemplate_sms("A", "S");
        $phone = $staff_phone;
        if ($template[4] == "E") {
          if ($template[2] == "") {
            $message = base64_decode($template[3]);
          } else {
            $message = base64_decode($template[2]);
          }
          $client_sms_body = str_replace($searcharray, $replacearray, $message);
          $params = array(
            "src" => $plivo_sender_number,
            "dst" => $phone,
            "text" => $client_sms_body,
            "method" => "POST"
          );
          $response = $obj_plivo->send_message($params);
        }
      }
    }
  }
  /* Twilio Code */
  if ($settings->get_option("ct_sms_twilio_status") == "Y") {
    $twilio_sender_number = $settings->get_option("ct_sms_twilio_sender_number");
    $AccountSid = $settings->get_option("ct_sms_twilio_account_SID");
    $AuthToken = $settings->get_option("ct_sms_twilio_auth_token");
    $obj_twillio = new Services_Twilio($AccountSid, $AuthToken);
    if ($settings->get_option("ct_sms_twilio_send_sms_to_client_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "C");
      $phone = $client_phone;
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $client_sms_body = str_replace($searcharray, $replacearray, $message);
        try {
          $message = $obj_twillio->account->messages->create(
            array(
              "From" => $twilio_sender_number,
              "To" => $phone,
              "Body" => $client_sms_body
            )
          );
        } catch (\Exception $e) {

        }
      }
    }
    if ($settings->get_option("ct_sms_twilio_send_sms_to_admin_status") == "Y") {
      $admin_phone = $settings->get_option("ct_sms_twilio_admin_phone_number");
      $template = $objdashboard->gettemplate_sms("A", "A");
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $client_sms_body = str_replace($searcharray, $replacearray, $message);
        try {
          $message = $obj_twillio->account->messages->create(
            array(
              "From" => $twilio_sender_number,
              "To" => $admin_phone,
              "Body" => $client_sms_body
            )
          );
        } catch (\Exception $e) {

        }
      }
    }
    if ($settings->get_option("ct_sms_twilio_send_sms_to_staff_status") == "Y") {
      if (isset($staff_phone) && !empty($staff_phone)) {
        $template = $objdashboard->gettemplate_sms("A", "S");
        $phone = $staff_phone;
        if ($template[4] == "E") {
          if ($template[2] == "") {
            $message = base64_decode($template[3]);
          } else {
            $message = base64_decode($template[2]);
          }
          $client_sms_body = str_replace($searcharray, $replacearray, $message);
          try {
            $message = $obj_twillio->account->messages->create(
              array(
                "From" => $twilio_sender_number,
                "To" => $phone,
                "Body" => $client_sms_body
              )
            );
          } catch (\Exception $e) {

          }
        }
      }
    }
  }
  /* Nexmo Code */
  if ($settings->get_option("ct_nexmo_status") == "Y") {
    $obj_nexmo = new cleanto_ct_nexmo();
    $obj_nexmo->ct_nexmo_api_key = $settings->get_option("ct_nexmo_api_key");
    $obj_nexmo->ct_nexmo_api_secret = $settings->get_option("ct_nexmo_api_secret");
    $obj_nexmo->ct_nexmo_from = $settings->get_option("ct_nexmo_from");
    if ($settings->get_option("ct_sms_nexmo_send_sms_to_client_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "C");
      $phone = $client_phone;
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $ct_nexmo_text = str_replace($searcharray, $replacearray, $message);
        $res = $obj_nexmo->send_nexmo_sms($phone, $ct_nexmo_text);
      }
    }
    if ($settings->get_option("ct_sms_nexmo_send_sms_to_admin_status") == "Y") {
      $template = $objdashboard->gettemplate_sms("A", "A");
      $phone = $settings->get_option("ct_sms_nexmo_admin_phone_number");
      if ($template[4] == "E") {
        if ($template[2] == "") {
          $message = base64_decode($template[3]);
        } else {
          $message = base64_decode($template[2]);
        }
        $ct_nexmo_text = str_replace($searcharray, $replacearray, $message);
        $res = $obj_nexmo->send_nexmo_sms($phone, $ct_nexmo_text);
      }
    }
    if ($settings->get_option("ct_sms_nexmo_send_sms_to_staff_status") == "Y") {
      if (isset($staff_phone) && !empty($staff_phone)) {
        $template = $objdashboard->gettemplate_sms("A", "S");
        $phone = $staff_phone;
        if ($template[4] == "E") {
          if ($template[2] == "") {
            $message = base64_decode($template[3]);
          } else {
            $message = base64_decode($template[2]);
          }
          $ct_nexmo_text = str_replace($searcharray, $replacearray, $message);
          $res = $obj_nexmo->send_nexmo_sms($phone, $ct_nexmo_text);
        }
      }
    }
  }
  /*SMS SENDING CODE END*/
  if (isset($_SESSION["referral_detail"]["user_referral_code"])) {
    $booking->friend_referral_code = $_SESSION["referral_detail"]["user_referral_code"];
    $booking->random_string = $_SESSION["ct_details"]["random_string"];
    $booking->coupon_limit = 1;
    $booking->coupon_used = 0;
    $add_referral = $booking->add_referral_coupon();
  } else {
    $booking->random_string = 0;
  }

  /** Quickbooks Code Start **/
  if ($settings->get_option('ct_quickbooks_status') == 'Y') {
    $config = include (dirname(dirname(__FILE__)) . '/assets/quickbooks/config.php');

    $accessToken = unserialize($settings->get_option('ct_qb_session_access_token'));

    $dataService = DataService::Configure(
      array(
        'auth_mode' => 'oauth2',
        'ClientID' => $settings->get_option('ct_quickbooks_client_ID'),
        'ClientSecret' => $settings->get_option('ct_quickbooks_client_secret'),
        'RedirectURI' => $config['oauth_redirect_uri'],
        'baseUrl' => $settings->get_option('ct_qb_account'),
        'refreshTokenKey' => $settings->get_option('ct_qb_refresh_token'),
        'QBORealmID' => $settings->get_option('ct_qb_company_id'),
      )
    );

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();

    $tokenArray = (array) $refreshedAccessTokenObj;
    end($tokenArray);
    $realID = prev($tokenArray);

    $settings->set_option('ct_qb_company_id', $realID);
    $settings->set_option('ct_qb_access_token', $refreshedAccessTokenObj->getAccessToken());
    $settings->set_option('ct_qb_refresh_token', $refreshedAccessTokenObj->getRefreshToken());
    $settings->set_option('ct_qb_x_refresh_token_expires_in', $refreshedAccessTokenObj->getRefreshTokenExpiresAt());
    $settings->set_option('ct_qb_expires_in', $refreshedAccessTokenObj->getAccessTokenExpiresAt());

    $_SESSION['sessionAccessToken'] = $refreshedAccessTokenObj;

    $dataService->updateOAuth2Token($refreshedAccessTokenObj);

    $service->id = $_SESSION['ct_cart']['method'][0]['service_id'];
    $service_name = $service->get_service_name_for_mail();

    $DueDate = date("Y-m-d", strtotime($_SESSION["ct_details"]["booking_date_time"]));

    $getItems = "SELECT * FROM ITEM WHERE Name = '" . $service_name . "'";
    $items = $dataService->Query($getItems);

    if ($_SESSION["ct_details"]["is_login_user"] == "Y") {
      $user_email_id = $_SESSION["ct_useremail"];
    } else {
      $user_email_id = $_SESSION["ct_details"]["email"];
    }

    $getCustomer = "SELECT * FROM CUSTOMER WHERE PrimaryEmailAddr = '" . $user_email_id . "'";
    $customer = $dataService->Query($getCustomer);

    if ($customer) {
      $CustomerRef = $customer[0]->Id;
    } else {
      $customerObj = Customer::create([
        "BillAddr" => [
          "Line1" => $_SESSION['ct_details']['address'],
          "City" => $_SESSION['ct_details']['city'],
          //"Country"=>  "USA",
          "PostalCode" => $_SESSION['ct_details']['zipcode']
        ],
        "FullyQualifiedName" => ucwords($_SESSION['ct_details']['firstname']) . ' ' . ucwords($_SESSION['ct_details']['lastname']),
        "DisplayName" => ucwords($_SESSION['ct_details']['firstname']) . ' ' . ucwords($_SESSION['ct_details']['lastname']),
        "PrimaryPhone" => [
          "FreeFormNumber" => $_SESSION['ct_details']['phone']
        ],
        "PrimaryEmailAddr" => [
          "Address" => $user_email_id
        ]
      ]);
      $resultCustomerObj = $dataService->Add($customerObj);
      $CustomerRef = $resultCustomerObj->Id;
    }

    $invoice = Invoice::create([
      "Line" => [
        [
          "Description" => $items[0]->Name,
          "Amount" => $_SESSION['ct_details']['net_amount'],
          "DetailType" => "SalesItemLineDetail",
          "SalesItemLineDetail" => [
            "ItemRef" => [
              "value" => $items[0]->Id,
              "name" => $items[0]->Name
            ]
          ]
        ]
      ],
      "DueDate" => $DueDate,
      "CustomerRef" => [
        "value" => $CustomerRef
      ],
      "BillEmail" => [
        "Address" => $user_email_id
      ]
    ]);
    $resultInvoice = $dataService->Add($invoice);

    if ($_SESSION['ct_details']['partial_amount'] !== '') {
      $qbLinkedInvoice = new IPPLinkedTxn();
      $qbLinkedInvoice->TxnId = $resultInvoice->Id;
      $qbLinkedInvoice->TxnType = 'Invoice';

      $qbLine = new IPPLine();
      $qbLine->Amount = $_SESSION['ct_details']['partial_amount'];
      $qbLine->LinkedTxn = $qbLinkedInvoice;

      $qbPayment = new IPPPayment();
      $qbPayment->CustomerRef = $CustomerRef;
      $qbPayment->TotalAmt = $_SESSION['ct_details']['partial_amount'];
      $qbPayment->Line = array($qbLine);

      $createdQbPayment = $dataService->Add($qbPayment);
    } elseif ($_SESSION['ct_details']['payment_method'] !== 'pay at venue') {
      $qbLinkedInvoice = new IPPLinkedTxn();
      $qbLinkedInvoice->TxnId = $resultInvoice->Id;
      $qbLinkedInvoice->TxnType = 'Invoice';

      $qbLine = new IPPLine();
      $qbLine->Amount = $_SESSION['ct_details']['net_amount'];
      $qbLine->LinkedTxn = $qbLinkedInvoice;

      $qbPayment = new IPPPayment();
      $qbPayment->CustomerRef = $CustomerRef;
      $qbPayment->TotalAmt = $_SESSION['ct_details']['net_amount'];
      $qbPayment->Line = array($qbLine);

      $createdQbPayment = $dataService->Add($qbPayment);
    } elseif ($_SESSION['ct_details']['tip_value'] !== '') {
      $qbLinkedInvoice = new IPPLinkedTxn();
      $qbLinkedInvoice->TxnId = $resultInvoice->Id;
      $qbLinkedInvoice->TxnType = 'Invoice';

      $qbLine = new IPPLine();
      $qbLine->Amount = $_SESSION['ct_details']['tip_value'];
      $qbLine->LinkedTxn = $qbLinkedInvoice;

      $qbPayment = new IPPPayment();
      $qbPayment->CustomerRef = $CustomerRef;
      $qbPayment->TotalAmt = $_SESSION['ct_details']['tip_value'];
      $qbPayment->Line = array($qbLine);

      $createdQbPayment = $dataService->Add($qbPayment);
    }
  }
  /** Quickbooks Code End **/

  /** Xero Code Start **/
  if ($settings->get_option('ct_xero_status') == 'Y' || $settings->get_option('ct_xero_company_name') !== '') {
    $storage = new StorageClass();
    $xeroTenantId = (string) $storage->getSession()['tenant_id'];

    if ($storage->getHasExpired()) {

      $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId' => $settings->get_option('ct_xero_client_ID'),
        'clientSecret' => $settings->get_option('ct_xero_client_secret'),
        'redirectUri' => SITE_URL . 'assets/xero/callback.php',
        'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken' => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
      ]);

      $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $storage->getRefreshToken()
      ]);

      // Save my token, expiration and refresh token
      $storage->setToken(
        $newAccessToken->getToken(),
        $newAccessToken->getExpires(),
        $xeroTenantId,
        $newAccessToken->getRefreshToken(),
        $newAccessToken->getValues()["id_token"]
      );
    }

    $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string) $storage->getSession()['token']);
    $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
      new GuzzleHttp\Client(),
      $config
    );

    $apiResponse = $apiInstance->getOrganisations($xeroTenantId);

    $settings->set_option('ct_xero_company_name', $apiResponse->getOrganisations()[0]->getName());
    $settings->set_option('ct_xero_oauth2state', $_SESSION['oauth2state']);
    $settings->set_option('ct_xero_oauth2_token', $_SESSION['oauth2']['token']);
    $settings->set_option('ct_xero_oauth2_expires', $_SESSION['oauth2']['expires']);
    $settings->set_option('ct_xero_oauth2_tenant_id', $xeroTenantId);
    $settings->set_option('ct_xero_oauth2_refresh_token', $_SESSION['oauth2']['refresh_token']);
    $settings->set_option('ct_xero_oauth2_id_token', $_SESSION['oauth2']['id_token']);

    try {
      $getAccountsResponse = $apiInstance->getAccounts($xeroTenantId);
      $getAccount = $getAccountsResponse->getAccounts()[2];
      $AccountID = $getAccountsResponse->getAccounts()[2]->getAccountId();
    } catch (Exception $e) {

    }

    try {
      $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
      $contact->setName($_SESSION['ct_details']['firstname'] . ' ' . $_SESSION['ct_details']['lastname'])
        ->setFirstName($_SESSION['ct_details']['firstname'])
        ->setLastName($_SESSION['ct_details']['lastname'])
        ->setEmailAddress($_SESSION['ct_details']['email']);

      $arr_contacts = [];
      array_push($arr_contacts, $contact);

      $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
      $contacts->setContacts($arr_contacts);

      $getContactRespoce = $apiInstance->updateOrCreateContacts($xeroTenantId, $contacts, false);
      $getContact = $getContactRespoce->getContacts()[0];
      $ContactID = $getContactRespoce->getContacts()[0]->getContactId();
    } catch (\XeroAPI\XeroPHP\ApiException $e) {

    }

    try {
      $lineitems[] = array(
        "Description" => "Services",
        "Quantity" => "",
        "UnitAmount" => "",
        "AccountCode" => "200",
        "TaxType" => "NONE",
        "TaxAmount" => $_SESSION['ct_details']['taxes'],
        "LineAmount" => $_SESSION['ct_details']['net_amount'] - $_SESSION['ct_details']['taxes']
      );

      $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
      $invoice->setReference('Cleanto')
        ->setDate(date('Y-m-d'))
        ->setDueDate($_SESSION['ct_details']['booking_date_time'])
        ->setContact($getContact)
        ->setLineItems($lineitems)
        ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
        ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
        ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);

      $arr_invoices = [];
      array_push($arr_invoices, $invoice);

      $invoices = new XeroAPI\XeroPHP\Models\Accounting\Invoices;
      $invoices->setInvoices($arr_invoices);

      $getInvoiceRespoce = $apiInstance->createInvoices($xeroTenantId, $invoices, false);
      $getInvoice = $getInvoiceRespoce->getInvoices()[0];
    } catch (Exception $e) {

    }

    if ($_SESSION['ct_details']['partial_amount'] !== '') {
      try {
        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setAccount($getAccount)
          ->setInvoice($getInvoice)
          ->setCurrencyRate(1)
          ->setAmount($_SESSION['ct_details']['partial_amount']);

        $arr_payments = [];
        array_push($arr_payments, $payment);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $payments->setPayments($arr_payments);

        $getPaymentRespoce = $apiInstance->createPayments($xeroTenantId, $payments, false);
        $getPayment = $getPaymentRespoce->getPayments()[0];
      } catch (Exception $e) {

      }
    } elseif ($_SESSION['ct_details']['payment_method'] !== 'pay at venue') {
      try {
        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setAccount($getAccount)
          ->setInvoice($getInvoice)
          ->setCurrencyRate(1)
          ->setAmount($_SESSION['ct_details']['net_amount']);

        $arr_payments = [];
        array_push($arr_payments, $payment);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $payments->setPayments($arr_payments);

        $getPaymentRespoce = $apiInstance->createPayments($xeroTenantId, $payments, false);
        $getPayment = $getPaymentRespoce->getPayments()[0];
      } catch (Exception $e) {

      }
    } elseif ($_SESSION['ct_details']['tip_value'] !== '') {
      try {
        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setAccount($getAccount)
          ->setInvoice($getInvoice)
          ->setCurrencyRate(1)
          ->setAmount($_SESSION['ct_details']['tip_value']);

        $arr_payments = [];
        array_push($arr_payments, $payment);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $payments->setPayments($arr_payments);

        $getPaymentRespoce = $apiInstance->createPayments($xeroTenantId, $payments, false);
        $getPayment = $getPaymentRespoce->getPayments()[0];
      } catch (Exception $e) {

      }
    }

  }
  /** Xero Code End **/
  if (isset($_SESSION["ct_details"]["payment_method"]) && ($_SESSION["ct_details"]["payment_method"] == "mollie")) {
    if ($settings->get_option("ct_thankyou_page_url") == "") {
      $thankyou_page_url = SITE_URL . "front/thankyou.php";
    } else {
      $thankyou_page_url = $settings->get_option("ct_thankyou_page_url");
    }
    ?>
    <script>window.location = "<?php echo $thankyou_page_url; ?>"; </script>
    <?php
  }
  if (isset($_SESSION["ct_details"]["payment_method"]) && ($_SESSION["ct_details"]["payment_method"] == "paypal")) {
    if ($settings->get_option("ct_thankyou_page_url") == "") {
      $thankyou_page_url = SITE_URL . "front/thankyou.php";
    } else {
      $thankyou_page_url = $settings->get_option("ct_thankyou_page_url");
    }
    ?>
    <script>window.location = "<?php echo $thankyou_page_url; ?>"; </script>
    <?php
  }
  if (isset($_SESSION["ct_details"]["payment_method"]) && ($_SESSION["ct_details"]["payment_method"] == "Wallet-payment")) {
    if ($settings->get_option("ct_thankyou_page_url") == "") {
      $thankyou_page_url = SITE_URL . "front/thankyou.php";
    } else {
      $thankyou_page_url = $settings->get_option("ct_thankyou_page_url");
    }
    ?>
    <?php
    $wallet_array = array();
    $wallet_array['status'] = "success";
    echo json_encode($wallet_array);
  } else {
    if ($_SESSION['ct_details']['payment_method'] == "paytm") {
      header('location:' . SITE_URL . 'extension/paytm/paytm_payment_process.php');
    } else {
      echo "ok";
      unset($_SESSION["referral_detail"]["user_referral_code"]);
    }
  }
}

/*start of this code use for paytam extention payment getway */
if (isset($_POST['payment_method_data']) && $_POST['payment_method_data'] == "Paytm") {
  $t_zone_value = $settings->get_option("ct_timezone");
  $server_timezone = date_default_timezone_get();
  if (isset($t_zone_value) && $t_zone_value != "") {
    $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
    $timezonediff = $offset / 3600;
  } else {
    $timezonediff = 0;
  }
  if (is_numeric(strpos($timezonediff, "-"))) {
    $timediffmis = str_replace("-", "", $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date("Y-m-d H:i:s")));
  } else {
    $timediffmis = str_replace("+", "", $timezonediff) * 60;
    $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date("Y-m-d H:i:s")));
  }
  $current_time = date("Y-m-d H:i:s", $currDateTime_withTZ);

  $last_order_id = $booking->last_booking_id();

  $payment->payment_method = $_POST['payment_method_data'];
  $payment->transaction_id = $_POST['transaction_id'];
  $payment->order_id = $last_order_id;
  $payment->lastmodify = $current_time;
  $update_payment = $payment->update_payment_transaction_id();

  echo "ok";
  unset($_SESSION["referral_detail"]["user_referral_code"]);
  exit(0);
}
/*end of this code use for paytam extention payment getway */