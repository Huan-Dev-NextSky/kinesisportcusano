<?php
session_start();
include dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_users.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_booking.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_frequently_discount.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_coupon.php';
include dirname(dirname(dirname(__FILE__))) . '/objects/class_general.php';
$database = new cleanto_db();
$conn = $database->connect();
$database->conn = $conn;

$settings = new cleanto_setting();
$settings->conn = $conn;

$first_step = new cleanto_first_step();
$first_step->conn = $conn;

$user = new cleanto_users();
$user->conn = $conn;
$booking = new cleanto_booking();
$booking->conn = $conn;

$frequently_discount = new cleanto_frequently_discount();
$frequently_discount->conn = $conn;

$coupon = new cleanto_coupon();
$coupon->conn = $conn;

$general = new cleanto_general();
$general->conn = $conn;

$symbol_position = $settings->get_option('ct_currency_symbol_position');
$decimal = $settings->get_option('ct_price_format_decimal_places');

$lang = $settings->get_option("ct_language");
$label_language_values = array();
$language_label_arr = $settings->get_all_labelsbyid($lang);

if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "") {
    $default_language_arr = $settings->get_all_labelsbyid("en");
    if ($language_label_arr[1] != '') {
        $label_decode_front = base64_decode($language_label_arr[1]);
    } else {
        $label_decode_front = base64_decode($default_language_arr[1]);
    }
    $label_decode_front_unserial = unserialize($label_decode_front);
    $label_language_arr = $label_decode_front_unserial;
    foreach ($label_language_arr as $key => $value) {
        $label_language_values[$key] = urldecode($value);
    }
} else {
    $default_language_arr = $settings->get_all_labelsbyid("en");
    $label_decode_front = base64_decode($default_language_arr[1]);
    $label_decode_front_unserial = unserialize($label_decode_front);
    $label_language_arr = $label_decode_front_unserial;
    foreach ($label_language_arr as $key => $value) {
        $label_language_values[$key] = urldecode($value);
    }
}
if (isset($_POST['action']) && $_POST['action'] == 'update_status_confirm_book') {
    $booking->booking_id = $_POST['data_id'];
/* $booking->order_id=$_POST['id']; */
    $booking->booking_status = "C";
    $result = $booking->confirm_booking();
    if ($result) {
        echo "Status Updated";
    } else {
        echo "Status Not Updated";
    }

}

if (isset($_POST['action']) && $_POST['action'] == 'reject_booking') {
    $t_zone_value = $settings->get_option('ct_timezone');
    $server_timezone = date_default_timezone_get();
    if (isset($t_zone_value) && $t_zone_value != '') {
        $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
        $timezonediff = $offset / 3600;
    } else {
        $timezonediff = 0;
    }
    if (is_numeric(strpos($timezonediff, '-'))) {
        $timediffmis = str_replace('-', '', $timezonediff) * 60;
        $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
    } else {
        $timediffmis = str_replace('+', '', $timezonediff) * 60;
        $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
    }
    $current_time = date('Y-m-d H:i:s', $currDateTime_withTZ);
    $booking->order_id = $_POST['booking_id'];
    $booking->reject_reason = $_POST['reject_reason_book'];
    $booking->lastmodify = $current_time;
    $update_status1 = $booking->update_reject_status();
    if ($update_status1) {
        echo 'booking Rejected';
    } else {
        echo "failed";
    }

}

/*Delete Appointments*/
if (isset($_POST['action']) && $_POST['action'] == 'update_delete_book') {
    /*Get Order id from booking id */
    $booking->booking_id = $_POST['booking_id'];
    $booking_details = $booking->readone();
    $order_id = $booking_details[1];
    /*Check occurance of order id in booking table */
    $booking->order_id = $order_id;
    $cnb = $booking->count_order_id_bookings();
    var_dump($cnb);
    if ($cnb['ordercount'] > 1) {
        $booking->booking_id = $_POST['booking_id'];
        $delete_myapp = $booking->delete_booking();
    } else {
        $booking->order_id = $order_id;
        $delete_myapp = $booking->delete_appointments();
    }
    if ($delete_myapp) {
        echo "Cancel My appointment";
    }
}

/* Count recurring record */
if (isset($_POST['action']) && $_POST['action'] == 'delete_all_rec') {
    /*Get Order id from booking id */
    $booking->recurring_id = $_POST['recurring_id'];
    $recurrence_totrow = $booking->count_recurrence();

    if ($recurrence_totrow > 1) {
        echo "Yes";
    } else {
        echo "No";
    }
}

/* Session set for edit booking */
if (isset($_POST['action']) && $_POST['action'] == 'get_edit_booking_info') {
    $service_array = array("method" => array());
    $_SESSION['ct_cart'] = $service_array;
    $_SESSION['freq_dis_amount'] = '';
    $_SESSION['ct_details'] = '';
    $_SESSION['time_duration'] = 0;
    $_SESSION['book_staff_id_cal'] = 1;
    /*Get Order id from booking id */
    $booking_smu_data = $booking->get_edit_smu_by_order_id($_POST['order_id']);
    $booking_sa_data = $booking->get_edit_sa_by_order_id($_POST['order_id']);
    $ser_unique_id = array();
    if ($booking_smu_data->num_rows > 0) {
        while ($smu_data = mysqli_fetch_array($booking_smu_data)) {
            $cartitems = array(
                "service_id" => $smu_data['service_id'],
                "method_id" => $smu_data['method_id'],
                "units_id" => $smu_data['method_unit_id'],
                "s_m_qty" => $smu_data['method_unit_qty'],
                "s_m_rate" => $smu_data['method_unit_qty_rate'],
                "method_name" => $smu_data['units_title'],
                "type" => "method_units",
                "method_type" => "mt_unit" . $smu_data['method_unit_id'],
                "duration" => $smu_data['method_unit_qty'] * $smu_data['uduration'],
            );
            array_push($_SESSION['ct_cart']["method"], $cartitems);
            if (!in_array($smu_data['service_id'], $ser_unique_id)) {
                array_push($ser_unique_id, $smu_data['service_id']);
            }
        }
    }
    if ($booking_sa_data->num_rows > 0) {
        while ($sa_data = mysqli_fetch_array($booking_sa_data)) {
            $cartitems = array(
                "service_id" => $sa_data['service_id'],
                "method_id" => 0,
                "units_id" => $sa_data['addons_service_id'],
                "s_m_qty" => $sa_data['addons_service_qty'],
                "s_m_rate" => $sa_data['addons_service_rate'],
                "method_name" => $sa_data['addon_service_name'],
                "type" => "addon",
                "method_type" => "ad_unit" . $sa_data['addons_service_id'],
                "duration" => $sa_data['addons_service_qty'] * $sa_data['aduration'],
            );
            array_push($_SESSION['ct_cart']["method"], $cartitems);
            if (!in_array($sa_data['service_id'], $ser_unique_id)) {
                array_push($ser_unique_id, $sa_data['service_id']);
            }
        }
    }
    $_SESSION['ser_unique_id'] = $ser_unique_id;
    $booking->order_id = $_POST['order_id'];
    $booking_staff_id = $booking->fetch_staff_of_booking();
    $_SESSION['book_staff_id_cal'] = $booking_staff_id;
}

if (isset($_POST['action']) && $_POST['action'] == 'get_service_cart_list') {

    $_SESSION['ct_cart']['method'] = array_values($_SESSION['ct_cart']['method']);
    $json_array = $first_service_id = array();
    $c_rates = 0;
    $final_duration_value = 0;
    for ($i = 0; $i < (count($_SESSION['ct_cart']['method'])); $i++) {
        $c_rates = ($c_rates + $_SESSION['ct_cart']['method'][$i]['s_m_rate']);
        $final_duration_value = ($final_duration_value + $_SESSION['ct_cart']['method'][$i]['duration']);
    }
    $_SESSION['time_duration'] = $final_duration_value;
    $hours = intval($final_duration_value / 60);
    $minutes = fmod($final_duration_value, 60);
    $json_array['duration_text'] = $hours . " " . $label_language_values['hours'] . " " . $minutes . " " . $label_language_values['minutes'];
    ksort($_SESSION['ser_unique_id']);
    $first_service_id = $_SESSION['ser_unique_id'];
    $booking->booking_id = $_POST['order_id'];
    $get_booking_date_time = $booking->readone_order_date_time();
    $booking_date_time_arr = explode(' ', $get_booking_date_time);

    $frequently_discount->order_id = $_POST['order_id'];
    $frequently_discount_id = $frequently_discount->get_frequently_discount_id();
    $frequently_discount->id = $frequently_discount_id;
    $freq_dis_data = $frequently_discount->readone();

    if ($freq_dis_data) {
        if (isset($freq_dis_data['d_type']) && $freq_dis_data['d_type'] == 'F') {
            $freqdis_amount = $freq_dis_data['rates'];
        } elseif (isset($freq_dis_data['d_type']) && $freq_dis_data['d_type'] == 'P') {
            $p_value = $freq_dis_data['rates'] / 100;
            $freqdis_amount = $c_rates * $p_value;
        } else {

        }
    } else {
        $freqdis_amount = 0;
    }

    $total = $c_rates;
    $_SESSION['freq_dis_amount'] = $freqdis_amount;
    $final_subtotal = $total - $_SESSION['freq_dis_amount'];
    if ($settings->get_option('ct_tax_vat_status') == 'Y') {
        if ($settings->get_option('ct_tax_vat_type') == 'F') {
            $flatvalue = $settings->get_option('ct_tax_vat_value');
            $taxamount = $flatvalue;
        } else
        if ($settings->get_option('ct_tax_vat_type') == 'P') {
            $percent = $settings->get_option('ct_tax_vat_value');
            $percentage_value = $percent / 100;
            $taxamount = $percentage_value * $final_subtotal;
        }
    } else {
        $taxamount = 0;
    }

    if ($settings->get_option('ct_partial_deposit_status') == 'Y') {
        $grand_total = $final_subtotal + $taxamount;
        if ($settings->get_option('ct_partial_type') == 'F') {
            $p_deposite_amount = $settings->get_option('ct_partial_deposit_amount');
            $partial_amount = $p_deposite_amount;
            $remain_amount = $grand_total - $partial_amount;
        } elseif ($settings->get_option('ct_partial_type') == 'P') {
            $p_deposite_amount = $settings->get_option('ct_partial_deposit_amount');
            $percentages = $p_deposite_amount / 100;
            $partial_amount = $grand_total * $percentages;
            $remain_amount = $grand_total - $partial_amount;
        } else {
            $partial_amount = 0;
            $remain_amount = 0;
        }
    } else {
        $partial_amount = 0;
        $remain_amount = 0;
    }

    $today_date = date("Y-m-d");
    $coupon->today_date = $today_date;
    $get_offer = $coupon->special_offer();
    /* print_r($get_offer); exit; */
    if ($get_offer->num_rows > 0) {
        while ($spe_text = mysqli_fetch_array($get_offer)) {
            if ($today_date == $spe_text['coupon_date']) {

                if ($spe_text['coupon_type'] == 'F') {
                    $special_off_amount = $spe_text['coupon_value'];
                    $totalss = ($final_subtotal + $taxamount);
                    $total_amount = $totalss - $special_off_amount;
                } else if ($spe_text['coupon_type'] == 'P') {
                    $p_value = $spe_text['coupon_value'] / 100;
                    $special_off_amount = $_POST['s_m_rate'] * $p_value;
                    $totalss = ($final_subtotal + $taxamount);
                    $total_amount = $totalss - $special_off_amount;
                } else {
                    $special_off_amount = 0;

                }

            }
        }
    } else {
        $total_amount = ($final_subtotal + $taxamount);
        $special_off_amount = 0;
    }
    $_SESSION['special_offer_amount'] = $special_off_amount;

    $json_array['total_amount'] = $general->ct_price_format($total_amount, $symbol_position, $decimal);
    $json_array['partial_amount'] = $general->ct_price_format($partial_amount, $symbol_position, $decimal);
    $json_array['remain_amount'] = $general->ct_price_format($remain_amount, $symbol_position, $decimal);
    $json_array['frequent_discount'] = '- ' . $general->ct_price_format($_SESSION['freq_dis_amount'], $symbol_position, $decimal);
    $json_array['special_offer_amount'] = $general->ct_price_format($special_off_amount, $symbol_position, $decimal);
    $json_array['cart_tax'] = $general->ct_price_format($taxamount, $symbol_position, $decimal);
    $json_array['cart_sub_total'] = $general->ct_price_format($total, $symbol_position, $decimal);
    $json_array['recurrence_booking'] = $freq_dis_data['discount_typename'];
    $json_array['booking_date'] = $booking_date_time_arr[0];
    $json_array['booking_time'] = $booking_date_time_arr[1];
    $json_array['frequently_discount_id'] = $frequently_discount_id;
    $json_array['first_service_id'] = $first_service_id[0];
    $booking->order_id = $_POST['order_id'];
    $get_payment_method = $booking->read_net_amt();
    $json_array['payment_method'] = $get_payment_method['payment_method'];
    $json_array['order_id'] = $_POST['order_id'];
    /* calculation end */

    $json_array['s_m_html'] = '';
    $ser_unique_id = $_SESSION['ser_unique_id'];
    foreach ($ser_unique_id as $key => $value) {
        $ser_name = $booking->get_service_name($value);
        $json_array['s_m_html'] .= '<li class="cart_service_item_listing_li' . $value . '" data-id="' . $value . '"><h4 class="cart_toggle_head" data-id="' . $value . '" >' . $ser_name . '<i class="pull-right ct_icon_check_' . $value . ' fa fa-plus"></i></h4><div id="cart_toggle_content_' . $value . '" style="overflow:hidden; display:none;" class="cart_toggle_content"><ul class="ct-addon-items-list cart_item_listing' . $value . '"></ul></div></li>';
    }
    $json_array['unit_addons_arr'] = $_SESSION['ct_cart']["method"];
    echo json_encode($json_array);
}
