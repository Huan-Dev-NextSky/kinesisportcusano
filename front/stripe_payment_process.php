<?php   
    ob_start();
    session_start();

    include(dirname(dirname(__FILE__)).'/header.php');
    include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
    include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
    include(dirname(dirname(__FILE__)).'/objects/class_services.php');
    require_once(dirname(dirname(__FILE__)).'/assets/stripe_3d/vendor/autoload.php');

    $con = new cleanto_db();
    $conn = $con->connect();

    $setting = new cleanto_setting();
    $setting->conn = $conn;

    $service = new cleanto_services();
    $service->conn = $conn;
    $site_url = SITE_URL;
    header('Content-Type: application/json');

    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str);

    if (sizeof($_POST) < 1 || sizeof($_POST) == 0) {
        header('Content-Type: application/json');

        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);

        $_POST = (array) $json_obj;
    }else{
        $_POST = $_POST;
    }

    $partialdeposite_status = $setting->get_option('ct_partial_deposit_status');

    if($partialdeposite_status=='Y'){
        $stripe_amt = number_format($_POST['partial_amount'],2,".",',');
    }else{
        $stripe_amt = number_format($_POST['net_amount'],2,".",',');
    }

    if($_POST['existing_username']!=''){ 
        $emails=$_POST['existing_username']; 
    }else{ 
        $emails=$_POST['email']; 
    }
    
    $intent = null;
    try {
        \Stripe\Stripe::setApiKey($setting->get_option("ct_stripe_secretkey"));

        if (isset($_POST['stripe_payment_method_id'])) {

            $customer = \Stripe\Customer::create([
                'email' => $emails,
                'name' => $_POST['firstname'].' '.$_POST['lastname'],
            ]);

            $customer_detail = $json_obj->customer_detail;

            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $_POST['stripe_payment_method_id'],
                'amount' => round(((double)$stripe_amt)*100),
                'currency' => $setting->get_option('ct_currency'),
                "customer" => $customer->id,
                'confirm' => true,
                'description' => 'Paid by '.$_POST['firstname'].' '.$_POST['lastname'],
				'return_url' => $site_url,
            ]);
        }

        if (isset($_POST['stripe_payment_intent_id'])) {
            $intent = \Stripe\PaymentIntent::retrieve(
                $_POST['stripe_payment_intent_id']
            );
            $intent->confirm();
        }

        if ($intent->status == 'requires_source_action' && $intent->next_action->type == 'use_stripe_sdk') {
            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        } else if ($intent->status == 'succeeded') {
            $stripe_trans_id = $intent->id;

            if ($_POST['discount'] == 'undefined' || $_POST['discount'] == '') {
                $_POST['discount'] = 0;
            }else{
                $_POST['discount'] = $_POST['discount'];
            }

            $total_discount =  number_format($_POST['frequent_discount_amount'],2,".",',') + number_format($_POST['discount'],2,".",',');

            $phone = "";
            if (substr($_POST['phone'], 0, 1) === '+') {
                $phone = $_POST['phone'];
            }else{
                $country_codes = explode(',',$setting->get_option("ct_company_country_code"));
                $phone = $country_codes[0].$_POST['phone'];
            }

            if($setting->get_option("ct_tax_vat_status") == 'N'){
                $tax = 0;
            }else{
                $tax = $_POST['taxes'];
            }
            $i=0;
            $service->id = $_SESSION['ct_cart']['method'][$i]['service_id'];
            $service_name = $service->get_service_name_for_mail();

            $email = addslashes($_POST['email']);
            $firstname = addslashes($_POST['firstname']);
            $lastname = addslashes($_POST['lastname']);
            $address = addslashes($_POST['address']);
            $zipcode = addslashes($_POST['zipcode']);
            $city = addslashes($_POST['city']);
            $state = addslashes($_POST['state']);
            $user_address = addslashes($_POST['user_address']);
            $user_zipcode = addslashes($_POST['user_zipcode']);
            $coupon_code = addslashes($_POST['coupon_code']);
            $user_city = addslashes($_POST['user_city']);
            $user_state = addslashes($_POST['user_state']);
            $notes = addslashes($_POST['notes']);
            $staff_id = addslashes($_POST['staff_id']);

            if (isset($_POST['user_coupon_val'])) {
                $user_coupon_val = $_POST['user_coupon_val'];
            }else{
                $user_coupon_val = 0;
            }

            $random_string = substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 10)),1,10);


            $app_address ="";
            $app_city ="";
            $app_zip ="";
            $app_state ="";
            if($_POST['address']!=""){ $app_address = $_POST['address'].", " ; } 
            if($_POST['city']!=""){ $app_city = $_POST['city'].", " ; } 
            if($_POST['zipcode']!=""){ $app_zip = $_POST['zipcode'].", " ; } 
            if($_POST['state']!=""){ $app_state = $_POST['state'] ; } 

            $temper = $app_address.$app_city.$app_zip.$app_state;
            $full_address = rtrim($temper,", ");

            $uniqueServices = [];
            $uniqueMethods = [];
            $commaSeparatedMethodNames = '';
            $commaSeparatedAddonName = '';
            $service_name = '';
            $method_name = [];
            
            for ($i = 0; $i < count($_SESSION['ct_cart']['method']); $i++) {
                $serviceId = $_SESSION['ct_cart']['method'][$i]['service_id'];
                $methodId = $_SESSION['ct_cart']['method'][$i]['method_id'];
                if($_SESSION['ct_cart']['method'][$i]['type'] == 'method_units'){
                    $methodName = $_SESSION['ct_cart']['method'][$i]['method_name'];
                    $commaSeparatedMethodNames .= $methodName . ', ';
                }else if($_SESSION['ct_cart']['method'][$i]['type'] == 'addon'){
                    $addonName = $_SESSION['ct_cart']['method'][$i]['method_name'];
                    $commaSeparatedAddonName .= $addonName . ', ';
                }
                
                $uniqueServices[$serviceId] = true;
                $uniqueMethods[$methodId] = true;
            }
            $service_ids = array_keys($uniqueServices);
            $method_ids = array_keys($uniqueMethods);
            foreach ($service_ids as $value) {
                $service->id = $value;
                $service_names = $service->get_service_name_gc();
                $service_name .= $service_names . ', ';
            }
            
            foreach ($method_ids as $value) {
                $service->id = $value;
                $method_names = $service->get_method_name_gc();
                $method_name[] = $method_names;
            }
            $uniquemethod_names = array_values(array_unique($method_name));
            if (count($uniquemethod_names) > 1) {
                $uniquemethod_name = implode(", ", $uniquemethod_names);
            } else {
                $uniquemethod_name = $uniquemethod_names[0] ?? ''; // Use the first element or an empty string if the array is empty
            }

            $service_name = rtrim($service_name, ', ');
            $uniquemethod_name = is_array($uniquemethod_name) ? '' : rtrim($uniquemethod_name, ', ');
            $commaSeparatedUnitNames = rtrim($commaSeparatedMethodNames, ', ');
            $commaSeparatedAddonNames = rtrim($commaSeparatedAddonName, ', ');

            $array_value = array('existing_username' => $_POST['existing_username'], 'existing_password' => $_POST['existing_password'], 'password' => $_POST['password'], 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'phone' => $phone, 'user_address' => $user_address, 'user_zipcode' => $user_zipcode, 'user_city' => $user_city, 'user_state' => $user_state, 'address' => $address, 'zipcode' => $zipcode, 'city' => $city, 'state' => $state, 'notes' => $notes, 'vc_status' => $_POST['vc_status'],'staff_id' => $staff_id, 'p_status' => $_POST['p_status'], 'contact_status' => $_POST['contact_status'], 'payment_method' => $_POST['payment_method'], 'amount' => $_POST['amount'], 'discount' => abs(number_format($total_discount, 2, ".", ',')), 'taxes' => $tax, 'partial_amount' => $_POST['partial_amount'], 'net_amount' => $_POST['net_amount'], 'booking_date_time' => $_POST['booking_date_time'], 'frequently_discount' => $_POST['frequently_discount'], 'frequent_discount_amount' => abs($_POST['frequent_discount_amount']), 'action' => "complete_booking", 'coupon_discount' => $_POST['discount'], 'guest_user_status' => $_POST['guest_user_status'],'is_login_user' => $_POST['is_login_user'],'service_name' => $service_name,'coupon_code'=> $coupon_code,
            'user_coupon_val'=> $user_coupon_val,'recurrence_booking_status'=> $_POST['recurrence_booking'],'random_string'=> $random_string,'service_names'=> $service_name,'uniquemethod_name'=> $uniquemethod_name,'commaSeparatedUnitNames'=> $commaSeparatedUnitNames,'full_address'=> $full_address,'commaSeparatedAddonNames'=> $commaSeparatedAddonNames);

            $_SESSION['ct_details']=$array_value;

            $_SESSION['ct_details']['stripe_trans_id'] = $stripe_trans_id;

            $_SESSION['ct_details']['stripe_3d_payment'] = '1';

            echo json_encode(['transaction_id' => $stripe_trans_id]);
            die;
        } else {
            echo json_encode(['error' => 'Invalid PaymentIntent status']);
            die;
        }

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        die;
    }
?>