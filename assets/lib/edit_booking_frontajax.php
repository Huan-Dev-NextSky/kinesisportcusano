<?php

ini_set('display_errors', '1');

ini_set('display_startup_errors', '1');

error_reporting(E_ALL);

session_start();

include dirname(dirname(dirname(__FILE__))) . '/header.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_connection.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_users.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_order_client_info.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';

include dirname(dirname(dirname(__FILE__))) . "/objects/class_services.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_addon.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_addon_rates.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_methods.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_service_methods_design.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_methods_units.php";

include dirname(dirname(dirname(__FILE__))) . "/objects/class_design_settings.php";

include dirname(dirname(dirname(__FILE__))) . '/objects/class_general.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_front_first_step.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_adminprofile.php';

include dirname(dirname(dirname(__FILE__))) . '/objects/class_frequently_discount.php';



$database = new cleanto_db();

$conn = $database->connect();

$database->conn = $conn;



$frequently_discount = new cleanto_frequently_discount();

$frequently_discount->conn = $conn;



$first_step = new cleanto_first_step();

$first_step->conn = $conn;



$general = new cleanto_general();

$general->conn = $conn;



$objadmin = new cleanto_adminprofile();

$objadmin->conn = $conn;



$user = new cleanto_users();

$order_client_info = new cleanto_order_client_info();

$settings = new cleanto_setting();



$user->conn = $conn;

$order_client_info->conn = $conn;

$settings->conn = $conn;



$objservice = new cleanto_services();

$addons = new cleanto_services_addon();

$addons_rates = new cleanto_services_addon_rates();

$objservice->conn = $conn;

$addons->conn = $conn;

$addons_rates->conn = $conn;



$objservice_method = new cleanto_services_methods();

$objservice_method->conn = $conn;

$objservice_method_design = new cleanto_service_methods_design();

$objservice_method_design->conn = $conn;



$objservice_method_unit = new cleanto_services_methods_units();

$objservice_method_unit->conn = $conn;



$objdesignset = new cleanto_design_settings();

$objdesignset->conn = $conn;



$symbol_position = $settings->get_option('ct_currency_symbol_position');

$decimal = $settings->get_option('ct_price_format_decimal_places');



$lang = $settings->get_option("ct_language");

$label_language_values = array();

$language_label_arr = $settings->get_all_labelsbyid($lang);



if ($language_label_arr[1] != "" && $language_label_arr[3] != "" && $language_label_arr[4] != "" && $language_label_arr[5] != "") {

    $label_decode_front = base64_decode($language_label_arr[1]);

    $label_decode_admin = base64_decode($language_label_arr[3]);

    $label_decode_error = base64_decode($language_label_arr[4]);

    $label_decode_extra = base64_decode($language_label_arr[5]);



    $label_decode_front_unserial = unserialize($label_decode_front);

    $label_decode_admin_unserial = unserialize($label_decode_admin);

    $label_decode_error_unserial = unserialize($label_decode_error);

    $label_decode_extra_unserial = unserialize($label_decode_extra);



    $label_language_arr = array_merge($label_decode_front_unserial, $label_decode_admin_unserial, $label_decode_error_unserial, $label_decode_extra_unserial);



    foreach ($label_language_arr as $key => $value) {

        $label_language_values[$key] = urldecode($value);

    }

} else {

    $default_language_arr = $settings->get_all_labelsbyid("en");



    $label_decode_front = base64_decode($default_language_arr[1]);

    $label_decode_admin = base64_decode($default_language_arr[3]);

    $label_decode_error = base64_decode($default_language_arr[4]);

    $label_decode_extra = base64_decode($default_language_arr[5]);



    $label_decode_front_unserial = unserialize($label_decode_front);

    $label_decode_admin_unserial = unserialize($label_decode_admin);

    $label_decode_error_unserial = unserialize($label_decode_error);

    $label_decode_extra_unserial = unserialize($label_decode_extra);



    $label_language_arr = array_merge($label_decode_front_unserial, $label_decode_admin_unserial, $label_decode_error_unserial, $label_decode_extra_unserial);



    foreach ($label_language_arr as $key => $value) {

        $label_language_values[$key] = urldecode($value);

    }

}

$calculation_policy = $settings->get_option("ct_calculation_policy");

if (isset($_POST['s_m_units_maxlimit_5'])) {

    $objservice_method_unit->services_id = $_POST['service_id'];

    $objservice_method_unit->methods_id = $_POST['method_id'];

    //$maxx_limitts = $objservice_method_unit->get_maxlimit_by_service_methods_ids();

    $maxx_limitts = $objservice_method_unit->getunits_by_service_methods_front();

    /*print_r($maxx_limitts);

    die();*/

    if ($maxx_limitts->num_rows > 0) {

        while ($maxx_limitt = mysqli_fetch_array($maxx_limitts)) {

            $mmnameee = 'ad_unit' . $maxx_limitt['id'];

            ?>

                <div class="ct-list-header">

                    <h3 class="header3"><?php if (isset($maxx_limitt['units_title'])) {echo $maxx_limitt['units_title'];} else {}?></h3>

                </div>

                <div class="ct-address-area-main">

                    <div class="ct-area-type">

                        <span class="area-header"><?php if ($maxx_limitt['limit_title'] != "") {echo $maxx_limitt['limit_title'];} else {echo ucwords($maxx_limitt['units_title']);}?></span>

                        <?php

if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                foreach ($_SESSION['ct_cart']['method'] as $method) {

                    $flag = 0;

                    if ($method['method_id'] == $_POST['method_id'] && $method['service_id'] == $_POST['service_id'] && $method['units_id'] == $maxx_limitt['id']) {

                        $flag = 1;

                        ?>

                                    <input maxlength="5" value="<?php echo $method['s_m_qty']; ?>" type="text" class="ct-area-input ct_area_m_units_rattee ct_area_m_units" id="ct_area_m_units<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-units_id="<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>"  data-method_id="<?php echo $_POST['method_id']; ?>" data-rate="" data-method_name="<?php if (isset($maxx_limitt['units_title'])) {echo $maxx_limitt['units_title'];} else {}?>" data-maxx_limit="<?php echo $maxx_limitt['maxlimit']; ?>" data-minn_limit="<?php echo $maxx_limitt['minlimit']; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"/>

                                <?php

break;

                    }

                }

                if ($flag == 0) {

                    ?>

                                <input maxlength="5" type="text" class="ct-area-input ct_area_m_units_rattee ct_area_m_units" id="ct_area_m_units<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-units_id="<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>"  data-method_id="<?php echo $_POST['method_id']; ?>" data-rate="" data-method_name="<?php if (isset($maxx_limitt['units_title'])) {echo $maxx_limitt['units_title'];} else {}?>" data-maxx_limit="<?php echo $maxx_limitt['maxlimit']; ?>" data-minn_limit="<?php echo $maxx_limitt['minlimit']; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"/>

                            <?php

}

            } else {

                ?>

                            <input maxlength="5" type="text" class="ct-area-input ct_area_m_units_rattee ct_area_m_units" id="ct_area_m_units<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-units_id="<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?>"  data-method_id="<?php echo $_POST['method_id']; ?>" data-rate="" data-method_name="<?php if (isset($maxx_limitt['units_title'])) {echo $maxx_limitt['units_title'];} else {}?>" data-maxx_limit="<?php echo $maxx_limitt['maxlimit']; ?>" data-minn_limit="<?php echo $maxx_limitt['minlimit']; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"/>

                        <?php

}



            ?>

                        <span class="area-type"><?php echo $maxx_limitt['unit_symbol'] ?></span>

                    </div>

                </div>

                <span class="error_of_max_limitss<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?> error"></span>

                <span class="error_of_min_limitss<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?> error"></span>

                <span class="error_of_invalid_area<?php if (isset($maxx_limitt['id'])) {echo $maxx_limitt['id'];} else {}?> error"></span>

            <?php

}

    }



    ?>

<?php

} elseif (isset($_POST['action']) && $_POST['action'] == 'get_existing_user_data') {

    $user->user_id = $_POST['existing_userid'];

    $existing_login = $user->readone();

    $u_msg = array();

    $u_msg['status'] = "Login Sucessfull";

    $u_msg['id'] = $existing_login[0];

    $u_msg['email'] = $existing_login[1];

    $u_msg['password'] = $existing_login[2];

    $u_msg['firstname'] = $existing_login[3];

    $u_msg['lastname'] = $existing_login[4];

    $u_msg['phone'] = $existing_login[5];

    $u_msg['zip'] = $existing_login[6];

    $u_msg['address'] = $existing_login[7];

    $u_msg['city'] = $existing_login[8];

    $u_msg['state'] = $existing_login[9];

    $u_msg['notes'] = $existing_login[10];

    $u_msg['vc_status'] = $existing_login[11];

    $u_msg['p_status'] = $existing_login[12];

    $u_msg['contact_status'] = $existing_login[13];

    echo json_encode($u_msg);die();

}



/* get methods in dropdown on click of service */

elseif (isset($_POST['operationgetmethods'])) {

    unset($_SESSION['staff_id_cal']);



    /* $service_array = array("method"=>array());

    $_SESSION['ct_cart'] = $service_array; */

    $objservice_method->service_id = $_POST['service_id'];

    $res = $objservice_method->methodsbyserviceid_front();



    $json_array = array();

    if (mysqli_num_rows($res) <= 1) {

        $arr = mysqli_fetch_array($res);

        $json_array['m_html'] = "<div class='ct_method_tab-slider--nav ct_method_tab-slider--nav_dynamic" . $arr['id'] . "'><ul class='edit_ct_method_tab-slider-tabs ct_methods_slide'><li class='ct_method_tab-slider-trigger  ct_method_tab-slider-trigger_dynamic" . $arr['id'] . " edit_s_m_units_design ser_mthd_units dis_metd_name" . $arr['id'] . "'  data-id='" . $arr['id'] . "' data-maindivid='" . $arr['id'] . "' data-methoddss='" . $arr['method_title'] . "' data-service_id='" . $_POST['service_id'] . "'>" . $arr['method_title'] . "</li></ul></div>";

        $json_array['status'] = 'single';

        echo json_encode($json_array);

    } else {

        $html = "";

        $ig = 1;

        $total_count = mysqli_num_rows($res);

        if ($total_count > 0) {

            while ($arr = mysqli_fetch_array($res)) {

                if ($ig == 1) {

                    $main_id_of_div = $arr['id'];

                    $html .= '<div class="ct_method_tab-slider--nav ct_method_tab-slider--nav_dynamic' . $arr['id'] . '" data-id="' . $arr['id'] . '"><ul class="edit_ct_method_tab-slider-tabs ct_methods_slide">';

                }

                $html .= "<li class='ct_method_tab-slider-trigger ct_method_tab-slider-trigger_dynamic" . $arr['id'] . " edit_s_m_units_design ser_mthd_units dis_metd_name" . $arr['id'] . "'  data-id='" . $arr['id'] . "'  data-maindivid='" . $main_id_of_div . "' data-methoddss='" . $arr['method_title'] . "' data-service_id='" . $_POST['service_id'] . "'>" . $arr['method_title'] . "</li>";

                if ($ig == $total_count && $total_count <= 3) {

                    $ig = 0;

                    $html .= "</ul></div>";

                } elseif ($ig == 3 && $total_count >= 3) {

                    $ig = 0;

                    $html .= "</ul></div>";

                }

                $ig++;

            }

        }

        $json_array['m_html'] = $html;

        $json_array['status'] = 'multiple';

        echo json_encode($json_array);

    }

} elseif (isset($_POST['staff_select_according_service'])) {
    @ob_clean();
    ob_start();
  
    if ($settings->get_option('ct_staff_zipcode') == 'Y') {
        $ct_postal_input = $_POST['ct_postal_input'];
        $objadmin->staff_select_according_zipcode = $ct_postal_input;
        $service_provider_list = $objadmin->get_zipcode_acc_provider();
    } else {
        $service_provider = $_POST['service_id'];
        $objadmin->staff_select_according_service = $service_provider;
        $service_provider_list = $objadmin->get_service_acc_provider();
    }
  
    unset($_SESSION['provider_sec']);
    $_SESSION['provider_sec'] = "";
  
    if (!empty($service_provider_list)) {
        $_SESSION['provider_sec'] = implode(",", $service_provider_list);
    }
  
    if ($_SESSION['provider_sec'] == '') {
        $status_found = "not found";
    } else {
        $status_found = "found";
    }
  
    $search_status = array();
    $search_status['staff_id'] = $_SESSION['provider_sec'];
    $search_status['found_status'] = $status_found;
    echo json_encode($search_status);
  } 

/* get add-on on click of service */

elseif (isset($_POST['get_service_addons'])) {



    $addons->service_id = $_POST['service_id'];

    $addons_data = $addons->readall_from_service();

    if (mysqli_num_rows($addons_data) > 0) {

        ?>

		<script>

		jQuery(document).ready(function() {

			jQuery('.ct-tooltip-addon').tooltipster({

				animation: 'grow',

				delay: 20,

				theme: 'tooltipster-shadow',

				trigger: 'hover'

			});

		});

		</script>

        <div class="ct-list-header">

            <h3 class="header3"><?php echo $label_language_values['extra_services']; ?></h3>

			 <?php if ($settings->get_option("ct_front_tool_tips_status") == 'on' && $settings->get_option("ct_front_tool_tips_addons_services") != '') {?>

			<a class="ct-tooltip-addon" href="#" data-toggle="tooltip" title="<?php echo $settings->get_option("ct_front_tool_tips_addons_services"); ?>."><i class="fa fa-info-circle fa-lg"></i></a>

			<?php }?>

        </div>

        <?php

if ($settings->get_option('ct_addons_default_design') == 1) {

            ?>



            <ul class="addon-service-list fl remove_addonsss">

                <?php

if (mysqli_num_rows($addons_data) > 0) {

                while ($adonsdata = mysqli_fetch_array($addons_data)) {

                    $addons_rates->addon_service_id = $adonsdata['service_id'];

                    $addonrates_data = $addons_rates->readone_from_serviceid();

                    /* CHANGED BY ME FROM Y TO N */

                    if (isset($adonsdata['multipleqty']) == 'N') {

                        $mmnameee = 'ad_unit' . $adonsdata['id'];



                        ?>

                            <li class="ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 mb-15">

                              <?php

if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                            foreach ($_SESSION['ct_cart']['method'] as $addon) {

                                $flag = 0;

                                if (isset($addon['units_id']) == isset($adonsdata['id']) && isset($addon['method_id']) == '0') {

                                    $flag = 1;

                                    ?>

                                      <script type="text/javascript">

                                        var ID = '<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>';

                                        jQuery('.edit_add_addon_in_cart_for_multipleqty').each(function() {

                                            if (jQuery(this).attr('data-status') == 1) {

                                              if (jQuery(this).attr('data-selected') == 1) {

                                                  jQuery('#edit-ct-addon-'+ID).prop('checked', true);



                                              }

                                            }

                                        });

                                      </script>

                                      <input type="checkbox" name="addon-checkbox" class="addon-checkbox edit_add_addon_in_cart_for_multipleqty" data-status="2" data-duration_value="-1" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-rate="<?php if (isset($adonsdata['base_price'])) {echo $adonsdata['base_price'];} else {}?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>" data-units_id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>" />

                                   

                                   

                                   

                                   <?php

break;

                                }

                            }

                            if ($flag == 0) {

                                ?>

                                    <input type="checkbox" name="addon-checkbox" data-selected="<?php echo $flag; ?>" class="addon-checkbox edit_add_addon_in_cart_for_multipleqty" data-status="2" data-duration_value="-1" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-rate="<?php if (isset($adonsdata['base_price'])) {echo $adonsdata['base_price'];} else {}?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>" data-units_id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>" />

                                  

                                  

                                  

                                  

                                  

                                  

                                  <?php

}

                        } else {

                            ?>

                                  <input type="checkbox" name="addon-checkbox" data-selected="0" class="addon-checkbox edit_add_addon_in_cart_for_multipleqty" data-status="2" data-duration_value="-1" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-rate="<?php if (isset($adonsdata['base_price'])) {echo $adonsdata['base_price'];} else {}?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>" data-units_id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>" />

                               

                               

                               

                               <?php

}

                        ?>

                                <label class="ct-addon-ser border-c ct_addon_ser<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" for="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>"><span></span>

                                    <div class="addon-price"><?php echo $general->ct_price_format($adonsdata['base_price'], $symbol_position, $decimal); ?></div>

                                    <div class="ct-addon-img">

                                                                                <img src="

                                                                                <?php

                                        if ($adonsdata['i'] == '' && $adonsdata['predefine_image'] == '') {

                                                                    echo SITE_URL . '/assets/images/services/default.png';

                                                                } elseif ($adonsdata['image'] == '') {

                                                                    echo SITE_URL . '/assets/images/addons-images/' . $adonsdata['predefine_image'];

                                                                } else {

                                                                    echo SITE_URL . '/assets/images/services/' . $adonsdata['image'];

                                                                }?>" />

                                    </div>



																		<div class="addon-name fl ta-c"><?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?></div>

                                </label>



                            </li>

                        <?php

                        } else {

                        $mmnameee = 'ad_unit' . $adonsdata['id'];

                        ?>

                            <li class="ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 mb-15">

                              <?php

                                if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                                foreach ($_SESSION['ct_cart']['method'] as $addon) {

                                $flag = 0;

                                if ($addon['units_id'] == $adonsdata['id'] && $addon['method_id'] == '0') {

                                    $flag = 1;

                                    ?>

                                      <script type="text/javascript">

                                        jQuery('.addons_servicess').each(function() {

                                          if (jQuery(this).attr('data-status') == 2) {

                                            if (jQuery(this).attr('data-selected') == 1) {

                                                jQuery(this).trigger('click');

                                            }

                                          }

                                        });

                                      </script>

                                       <input type="checkbox" name="addon-checkbox" class="addon-checkbox addons_servicess" data-status="2" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>"/>

                                        <?php

                                        break;

                                        }

                                        }

                                        if ($flag == 0) {

                                            ?>

                                            <input type="checkbox" name="addon-checkbox" data-selected="<?php echo $flag; ?>" class="addon-checkbox addons_servicess" data-status="2" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>"/>

                                        <?php

                                        }

                                        } else {

                                            ?>

                                        <input type="checkbox" name="addon-checkbox" data-selected="0" class="addon-checkbox addons_servicess" data-status="2" data-id="<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" id="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>" data-service_id="<?php if (isset($adonsdata['service_id'])) {echo $adonsdata['service_id'];} else {}?>" data-method_id="0" data-method_name="<?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?>"/>

                                        <?php

                                        }

                                 ?>



                                <label class="ct-addon-ser border-c ct_addon_ser<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>" for="edit-ct-addon-<?php if (isset($adonsdata['id'])) {echo $adonsdata['id'];} else {}?>"><span></span>

                                    <div class="addon-price"><?php echo $general->ct_price_format($adonsdata['base_price'], $symbol_position, $decimal); ?></div>

                                    <div class="ct-addon-img">

                                        <img src="

                                        <?php 

                                        if ($adonsdata['image'] == '' && $adonsdata['predefine_image'] == '') {

                            echo SITE_URL . '/assets/images/services/default.png';

                        } elseif ($adonsdata['image'] == '') {

                            echo SITE_URL . '/assets/images/addons-images/' . $adonsdata['predefine_image'];

                        } else {

                            echo SITE_URL . '/assets/images/services/' . $adonsdata['image'];

                        }?>" /></div>



																		<div class="addon-name fl ta-c"><?php if (isset($adonsdata['addon_service_name'])) {echo $adonsdata['addon_service_name'];} else {}?></div>

                                </label>



                            </li>

                        <?php

}

                }

            } else {

                ?>

                    <p class="ct-sub"><?php echo $label_language_values['extra_services_not_available']; ?></p>

                <?php

}

            ?>

            </ul>

            <div class="addons_counting">

            </div>

        <?php

} else {

            ?>

<ul class="addon-service-list fl remove_addonsss">

                <?php 

                if (mysqli_num_rows($addons_data) > 0) {

                    while ($adonsdata = mysqli_fetch_array($addons_data)) {

                        $addons_rates->addon_service_id = $adonsdata['service_id'];

                        $addonrates_data = $addons_rates->readone_from_serviceid();

                        $multipleqty = isset($adonsdata['multipleqty']) ? $adonsdata['multipleqty'] : 'N';

                        $mmnameee = 'ad_unit' . (isset($adonsdata['id']) ? $adonsdata['id'] : '');

                        $adonsdata_id = isset($adonsdata['id']) ? $adonsdata['id'] : '';

                        $adonsdata_base_price = isset($adonsdata['base_price']) ? $adonsdata['base_price'] : 0;

                        $adonsdata_service_id = isset($adonsdata['service_id']) ? $adonsdata['service_id'] : '';

                        $adonsdata_addon_service_name = isset($adonsdata['addon_service_name']) ? $adonsdata['addon_service_name'] : '';



                        // Determine the image path

                        $image_path = '';

                        if (!empty($adonsdata['image'])) {

                            $image_path = SITE_URL . '/assets/images/services/' . $adonsdata['image'];

                        } elseif (!empty($adonsdata['predefine_image'])) {

                            $image_path = SITE_URL . '/assets/images/addons-images/' . $adonsdata['predefine_image'];

                        } else {

                            $image_path = SITE_URL . '/assets/images/services/default.png';

                        }

                        ?>

            <li class="ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 mb-15 <?php echo $multipleqty == 'N' ? '' : 'add_addon_class_selected'; ?>">

                <?php

                $flag = 0;

                if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                    foreach ($_SESSION['ct_cart']['method'] as $addon) {

                        if ($addon['units_id'] == $adonsdata_id && $addon['method_id'] == '0' && $addon['service_id'] == $adonsdata_service_id) {

                            $flag = 1;

                            ?>

                            <input type="checkbox" name="addon-checkbox" class="addon-checkbox <?php echo $multipleqty == 'N' ? 'edit_add_addon_in_cart_for_multipleqty' : 'addons_servicess_2'; ?>" data-status="1" data-duration_value="-1" data-id="<?php echo $adonsdata_id; ?>" id="edit-ct-addon-<?php echo $adonsdata_id; ?>" data-rate="<?php echo $adonsdata_base_price; ?>" data-service_id="<?php echo $adonsdata_service_id; ?>" data-method_id="0" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-units_id="<?php echo $adonsdata_id; ?>" data-type="addon" data-mnamee="<?php echo $mmnameee; ?>" checked />

                            <?php

                            break;

                        }

                    }

                }

                if ($flag == 0) {

                    ?>

                    <input type="checkbox" name="addon-checkbox" class="addon-checkbox <?php echo $multipleqty == 'N' ? 'edit_add_addon_in_cart_for_multipleqty' : 'addons_servicess_2'; ?>" data-status="2" data-duration_value="-1" data-id="<?php echo $adonsdata_id; ?>" id="edit-ct-addon-<?php echo $adonsdata_id; ?>" data-rate="<?php echo $adonsdata_base_price; ?>" data-service_id="<?php echo $adonsdata_service_id; ?>" data-method_id="0" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-units_id="<?php echo $adonsdata_id; ?>" data-type="addon" data-mnamee="<?php echo $mmnameee; ?>" />

                    <?php

                }

                ?>

                <label class="ct-addon-ser border-c ct_addon_ser<?php echo $adonsdata_id; ?>" for="edit-ct-addon-<?php echo $adonsdata_id; ?>"><span></span>

                    <div class="addon-price"><?php echo $general->ct_price_format($adonsdata_base_price, $symbol_position, $decimal); ?></div>

                    <?php if (!empty($image_path)) : ?>

                        <div class="ct-addon-img">

                            <img src="<?php echo $image_path; ?>" />

                        </div>

                    <?php endif; ?>

                    <div class="addon-name fl ta-c"><?php echo $adonsdata_addon_service_name; ?></div>

                </label>

                <?php

                if ($multipleqty != 'N') {

                    if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                        foreach ($_SESSION['ct_cart']['method'] as $addon) {

                            if ($addon['units_id'] == $adonsdata_id && $addon['method_id'] == '0') {

                                $flag = 1;

                                $s_m_qty = isset($addon['s_m_qty']) ? $addon['s_m_qty'] : 1;

                                $uniitt_rate = $adonsdata_base_price;

                                ?>

                                <div class="ct-addon-count border-c add_minus_buttonn add_minus_buttonid<?php echo $adonsdata_id; ?>" data-id='cc'>

                                    <div class="ct-btn-group">

                                        <button data-ids="<?php echo $adonsdata_id; ?>" id="minus<?php echo $adonsdata_id; ?>" class="edit_minus ct-btn-left ct-small-btn" type="button" data-units_id="<?php echo $adonsdata_id; ?>" data-duration_value="" data-mnamee="<?php echo $mmnameee; ?>" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-rate="<?php echo $uniitt_rate; ?>" data-method_id="0" data-type="addon">-</button>

                                        <input type="text" value="<?php echo $s_m_qty; ?>" class="ct-btn-text addon_qty data_addon_qtyrate qtyyy_<?php echo $mmnameee; ?>" data-rate="<?php echo $uniitt_rate; ?>" />

                                        <button data-ids="<?php echo $adonsdata_id; ?>" id="add<?php echo $adonsdata_id; ?>" data-db-qty="<?php echo $adonsdata['maxqty']; ?>" data-mnamee="<?php echo $mmnameee; ?>" class="edit_add ct-btn-right float-right ct-small-btn" type="button" data-units_id="<?php echo $adonsdata_id; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="0" data-duration_value="" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-rate="<?php echo $uniitt_rate; ?>" data-type="addon">+</button>

                                    </div>

                                </div>

                                <?php

                                break;

                            }

                        }

                    }

                    if ($flag == 0) {

                        $uniitt_rate = $adonsdata_base_price;

                        ?>

                        <div class="ct-addon-count border-c edit_add_minus_button add_minus_buttonid<?php echo $adonsdata_id; ?>" data-id='bb'>

                            <div class="ct-btn-group">

                                <button data-ids="<?php echo $adonsdata_id; ?>" id="minus<?php echo $adonsdata_id; ?>" class="edit_minus ct-btn-left ct-small-btn" type="button" data-units_id="<?php echo $adonsdata_id; ?>" data-duration_value="" data-mnamee="<?php echo $mmnameee; ?>" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-rate="<?php echo $uniitt_rate; ?>" data-method_id="0" data-type="addon">-</button>

                                <input type="text" value="-1" class="ct-btn-text addon_qty data_addon_qtyrate qtyyy_<?php echo $mmnameee; ?>" data-rate="<?php echo $uniitt_rate; ?>" />

                                <button data-ids="<?php echo $adonsdata_id; ?>" id="add<?php echo $adonsdata_id; ?>" data-db-qty="<?php echo $adonsdata['maxqty']; ?>" data-mnamee="<?php echo $mmnameee; ?>" class="edit_add ct-btn-right float-right ct-small-btn" type="button" data-units_id="<?php echo $adonsdata_id; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="0" data-duration_value="" data-method_name="<?php echo $adonsdata_addon_service_name; ?>" data-rate="<?php echo $uniitt_rate; ?>" data-type="addon">+</button>

                            </div>

                        </div>

                        <?php

                    }

                }

                ?>

            </li>

            <?php

        }

    } else {

        ?>

        <p class="ct-sub"><?php echo $label_language_values['extra_services_not_available']; ?></p>

        <?php 

    }

    ?>

</ul>



        <?php

}

    } else {

        echo "Extra Services Not Available";

    }

} elseif (isset($_POST['get_service_addons_qtys'])) {

    $addons->id = $_POST['addon_id'];

    $addon_dataaa = $addons->readone();

    ?>

    <div class="addon-services-count remove_addonsss remove_addon_intensive<?php echo $_POST['addon_id']; ?>">

        <div class="ct-addon-extra-count fl ct-md-12 mb-15 mt-5 np">

            <h5 class="header5"><?php echo $addon_dataaa["addon_service_name"]; ?></h5>

            <div class="ct-common-addon-list">

                <?php

$mmnameee = 'ad_unit' . $addon_dataaa['id'];

    if ($addon_dataaa["maxqty"] == 0) {

        ?>

                    <span class="ct-addon-box">

        						  <a class="br-3"><?php echo $label_language_values['no_intensive']; ?></a>

        					  </span>

                <?php

} else {

        $fe = 0;

        $fg = 0;

        $strate = 1;

        for ($i = 1; $i <= $addon_dataaa["maxqty"]; $i++) {

            $addons_rates->maxlimit = $i;

            $addons_rates->addon_service_id = $addon_dataaa['id'];

            $unt_ratess = $addons_rates->get_rate_by_service_addon_ids();



            if (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'G') {

                $strate = $unt_ratess['rate'];

                $fg = 1;

                $fe = 0;

            }

            if ($fg == 1) {

                if (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'E') {

                    ?>

                                <span class="ct-addon-box">

            									<a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rate'] * $i : $unt_ratess['rates']; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>" ><?php echo $i; ?></a>

            								</span>

                            <?php

} else {

                    ?>

                            <span class="ct-addon-box">

									            <a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $strate * $i : $strate; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

								            </span>

                            <?php

}

            } elseif (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'E') {

                ?>

                            <span class="ct-addon-box">

              								<a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rate'] * $i : $unt_ratess['rate']; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

              							</span>

                        <?php

} else {

                if ($calculation_policy == "M") {

                    $base_rates = $addon_dataaa['base_price'] * $i;

                } else {

                    $base_rates = $addon_dataaa['base_price'];

                }



                ?>

                        <?php

if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                    foreach ($_SESSION['ct_cart']['method'] as $method) {

                        $flag = 0;

                        if ($method['s_m_qty'] == $i && $method['service_id'] == $addon_dataaa['service_id'] && $method['units_id'] == $addon_dataaa['id']) {

                            $flag = 1;

                            ?>

                                    <span class="ct-addon-box">

                                      <a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo $base_rates; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

                                    </span>

                                  <?php

break;

                        }

                    }

                    if ($flag == 0) {

                        ?>

                                <span class="ct-addon-box">

                                  <a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo $base_rates; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

                                </span>

                             <?php

}

                } else {

                    ?>

                          <span class="ct-addon-box">

                            <a href="javascript:void(0);" class="br-3 ct-addon-btn edit_add_item_in_cart" data-rate="<?php echo $base_rates; ?>" data-duration_value="<?php echo $i; ?>" data-units_id="<?php echo $addon_dataaa['id']; ?>" data-service_id="<?php echo $addon_dataaa['service_id']; ?>" data-method_id="0" data-method_name="<?php echo $addon_dataaa['addon_service_name']; ?>" data-type="<?php echo "addon"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

                          </span>

                        <?php

}

                ?>



                      <?php

}

        }

        ?>



              <?php

}

    ?>

            </div>

        </div>

    </div>

<?php

} elseif (isset($_POST['select_s_m_units_design'])) {

    /* $service_array = array("method"=>array());

    $_SESSION['ct_cart'] = $service_array; */

    echo $design_values = $objservice_method_design->get_service_methods_design($_POST['service_methods_id']);

} elseif (isset($_POST['s_m_units_maxlimit'])) {

    $objservice_method_unit->services_id = $_POST['service_id'];

    $objservice_method_unit->methods_id = $_POST['method_id'];

    $unt_values = $objservice_method_unit->get_maxlimit_by_service_methods_ids();

    $mmnameee = 'ad_unit' . $unt_values['id'];



    $fe = 0;

    $fg = 0;

    $strate = 1;

    ?>

    <h5 class="header5"><?php if ($unt_values['limit_title'] != "") {echo $unt_values['limit_title'];} else {echo $unt_values['units_title'];}?></h5>

    <div class="ct-duration-list">

        <?php

for ($i = 1; $i <= $unt_values['maxlimit']; $i++) {

        $objservice_method_unit->maxlimit = $i;

        $objservice_method_unit->units_id = $unt_values['id'];

        $unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();



        if (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'G') {

            $strate = $unt_ratess['rates'];

            $fg = 1;

            $fe = 0;

        }

        if ($fg == 1) {

            if (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'E') {

                ?>

                    <span class="duration-box">

						<a href="javascript:void(0);" class="br-3 ct-duration-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rates'] * $i : $unt_ratess['rates']; ?>" data-units_id="<?php echo $unt_values['id']; ?>" data-duration_value="<?php echo $i; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values['units_title'] ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

					</span>

                    <?php

/* print the rate given in db*/

            } else {

                /* print the rate of the previous one */

                ?>

                    <span class="duration-box">

						<a href="javascript:void(0);" class="br-3 ct-duration-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $strate * $i : $strate; ?>" data-units_id="<?php echo $unt_values['id']; ?>" data-duration_value="<?php echo $i; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values['units_title'] ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

					</span>

                <?php

}

        } elseif (isset($unt_ratess['rules']) && $unt_ratess['rules'] == 'E') {

            ?>

                <span class="duration-box">

					<a href="javascript:void(0);" class="br-3 ct-duration-btn edit_add_item_in_cart" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rates'] * $i : $unt_ratess['rates']; ?>" data-units_id="<?php echo $unt_values['id']; ?>" data-duration_value="<?php echo $i; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values['units_title'] ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

				</span>

                <?php

} else {

            if ($calculation_policy == "M") {

                $base_rates = $unt_values['base_price'] * $i;

            } else {

                $base_rates = $unt_values['base_price'];

            }

            ?>

				<span class="duration-box">

					<a href="javascript:void(0);" class="br-3 ct-duration-btn edit_add_item_in_cart" data-rate="<?php echo $base_rates; ?>" data-units_id="<?php echo $unt_values['id']; ?>" data-duration_value="<?php echo $i; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values['units_title'] ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>"><?php echo $i; ?></a>

				</span>

            <?php

}

    }

    ?>

    </div>

<?php

} elseif (isset($_POST['s_m_units_maxlimit_2'])) {

    $objservice_method_unit->services_id = $_POST['service_id'];

    $objservice_method_unit->methods_id = $_POST['method_id'];

    $unt_valuess_2 = $objservice_method_unit->getunits_by_service_methods_front();

    if ($unt_valuess_2->num_rows > 0) {

        while ($unt_values_2 = mysqli_fetch_array($unt_valuess_2)) {

            $mmnameee = 'mt_unit' . $unt_values_2['id'];



            $fe = 0;

            $fg = 0;

            $strate = 1;

            ?>



            <div class="ct-bedrooms ct-btn-group ct-md-6 ct-sm-6 mb-15 ">

                <label> <?php if (isset($unt_values_2['limit_title']) != "") {echo $unt_values_2['limit_title'];} else {echo $unt_values_2['units_title'];}?></label>

                <div class="common-selection-main">

                    <div class="selected-is edit-select-bedrooms" data-mnamee="<?php echo $mmnameee; ?>" data-un_title="<?php echo $unt_values_2['units_title']; ?>" data-un_id="<?php if (isset($unt_values_2['id'])) {echo $unt_values_2['id'];} else {}?>" title="<?php echo $label_language_values['choose_your'] . " " . $unt_values_2['units_title']; ?>">

                    <?php

if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                foreach ($_SESSION['ct_cart']['method'] as $method) {

                    $flag = 0;

                    if ($method['units_id'] == $unt_values_2['id'] && $_POST['method_id'] == $method['method_id']) {

                        $flag = 1;

                        ?>

                            <div class="data-list common-fc-2" id="ct_edit_selected_<?php if (isset($unt_values_2['id'])) {echo $unt_values_2['id'];} else {}?>">

                                <p class="ct-count"><?php echo $method['s_m_qty']; ?></p>

                            </div>

                            <?php

break;

                    }

                }

                if ($flag == 0) {

                    ?>

                        <div class="data-list common-fc-2" id="ct_edit_selected_<?php if (isset($unt_values_2['id'])) {echo $unt_values_2['id'];} else {}?>">

                            <p class="ct-count"><?php echo $label_language_values['choose_your'] . " " . $unt_values_2['units_title']; ?></p>

                        </div>

                        <?php

}

            } else {

                ?>

                    <div class="data-list common-fc-2" id="ct_edit_selected_<?php if (isset($unt_values_2['id'])) {echo $unt_values_2['id'];} else {}?>">

                        <p class="ct-count"><?php echo $label_language_values['choose_your'] . " " . $unt_values_2['units_title']; ?></p>

                    </div>

                    <?php

}

            ?>

                </div>



                <div class="edit-common-data-dropdown ct-edit-<?php if (isset($unt_values_2['id'])) { echo $unt_values_2['id']; } ?>-dropdown">
                        <?php 
                        $hfsec = 1; 

                        for ($i = $hfsec; $i <= $unt_values_2['maxlimit']; $i += $hfsec) {
                            $objservice_method_unit->maxlimit = $i;
                            $objservice_method_unit->units_id = $unt_values_2['id'];
                            $unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();

                            $strate = 0;
                            $fg = 0;
                            $fe = 0;

                            if (isset($unt_ratess) && $unt_ratess['rules'] == 'G') {
                            $strate = $unt_ratess['rates'];
                            $fg = 1;
                        }

                        if ($fg == 1) {
                          if ($unt_ratess['rules'] == 'E') {
                                ?>
                                <div class="data-list edit_select_bedroom edit_add_item_in_cart" data-duration_value="<?php echo $i; ?>" data-units_id="<?php if (isset($unt_values_2['id'])) { echo $unt_values_2['id']; } ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values_2['units_title']; ?>" data-un_title="<?php echo $unt_values_2['units_title']; ?>" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rates'] * $i : $unt_ratess['rates']; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">
                                    <p class="ct-count"><?php echo $i . " " . $unt_values_2['unit_symbol']; ?></p>
                                </div>
                                <?php 
                            } else {
                                ?>
                                <div class="data-list edit_select_bedroom edit_add_item_in_cart" data-duration_value="<?php echo $i; ?>" data-units_id="<?php if (isset($unt_values_2['id'])) { echo $unt_values_2['id']; } ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values_2['units_title']; ?>" data-un_title="<?php echo $unt_values_2['units_title']; ?>" data-rate="<?php echo ($calculation_policy == "M") ? $strate * $i : $strate; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">
                                    <p class="ct-count"><?php echo $i . " " . $unt_values_2['unit_symbol']; ?></p>
                                </div>
                                <?php 
                            }
                        } elseif (isset($unt_ratess) && $unt_ratess['rules'] == 'E') {
                             ?>
                            <div class="data-list edit_select_bedroom edit_add_item_in_cart" data-duration_value="<?php echo $i; ?>" data-units_id="<?php if (isset($unt_values_2['id'])) { echo $unt_values_2['id']; } ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values_2['units_title']; ?>" data-un_title="<?php echo $unt_values_2['units_title']; ?>" data-rate="<?php echo ($calculation_policy == "M") ? $unt_ratess['rates'] * $i : $unt_ratess['rates']; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">
                                <p class="ct-count"><?php echo $i . " " . $unt_values_2['unit_symbol']; ?></p>
                            </div>
                            <?php 
                        } else {
                            $base_rates = ($calculation_policy == "M") ? $unt_values_2['base_price'] * $i : $unt_values_2['base_price'];
                            ?>
                            <div class="data-list edit_select_bedroom edit_add_item_in_cart" data-duration_value="<?php echo $i; ?>" data-units_id="<?php if (isset($unt_values_2['id'])) { echo $unt_values_2['id']; } ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" data-method_name="<?php echo $unt_values_2['units_title']; ?>" data-un_title="<?php echo $unt_values_2['units_title']; ?>" data-rate="<?php echo $base_rates; ?>" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">
                                <p class="ct-count"><?php echo $i . " " . $unt_values_2['unit_symbol']; ?></p>
                            </div>
                            <?php 
                        }
                    }
                    ?>
                </div>



                </div>

            </div>



        <?php

}

    }

} elseif (isset($_POST['s_m_units_maxlimit_3'])) {
    $objservice_method_unit->services_id = $_POST['service_id'];
    $objservice_method_unit->methods_id = $_POST['method_id'];
    $unt_valuess_3 = $objservice_method_unit->getunits_by_service_methods_front();
    if ($unt_valuess_3->num_rows > 0) {
        while ($unt_values_3 = mysqli_fetch_array($unt_valuess_3)) {
            $fe = 0;
            $fg = 0;
            $strate = 1;
?>
            <div class="ct-bedrooms ct-btn-group fl ct-md-12 mt-5 mb-15 np">
                <h5 class="header5">
                    <?php
                    if (isset($unt_values_3['limit_title']) != "") {
                        echo $unt_values_3['limit_title'];
                    } else {
                        if (isset($unt_values_3['units_title'])) {
                            echo $unt_values_3['units_title'];
                        }
                    }
                    ?>
                </h5>
                <div class="ct-bedroom-list">
                    <?php
                    if (isset($unt_values_3['half_section']) && $unt_values_3['half_section'] == 'E') {
                        $plus_value = 0.5;
                    } else {
                        $plus_value = 1;
                    }

                    for ($i = $unt_values_3['minlimit']; $i <= $unt_values_3['maxlimit']; $i += $plus_value) { 
                        $objservice_method_unit->maxlimit = $i;
                        $objservice_method_unit->units_id = $unt_values_3['id'];
                        $unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();

                        if ($calculation_policy == "M") {
                            if (isset($unt_ratess['rates']) && $unt_ratess['rules'] == 'G') {
                                $strate = $unt_ratess['rates'];
                                $fg = 1;
                                $fe = 0;
                            } elseif (isset($unt_ratess['rates']) && $unt_ratess['rules'] == 'E') {
                                $strate = $unt_ratess['rates'];
                                $fg = 0;
                                $fe = 1;
                            } else {
                                // $strate = $unt_values_3['base_price'] * $i;
                                $strate = $unt_values_3['base_price'] ;

                            }
                        } else {
                            if (isset($unt_ratess['rates'])) {
                                $strate = $unt_ratess['rates'];
                            } else {
                                $strate = $unt_values_3['base_price'];
                            }
                        }

                        $item_in_cart = false;
                        if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {
                            foreach ($_SESSION['ct_cart']['method'] as $method) {
                                if ($method['units_id'] == $unt_values_3['id'] && $_POST['method_id'] == $method['method_id'] && $method['s_m_qty'] == $i) {
                                    $item_in_cart = true;
                                    break;
                                }
                            }
                        }
                    ?>
                        <span class="ct-box bedroom-box">
                            <a href="javascript:void(0);" class="br-3 ct-bedroom-btn select_m_u_btn u_<?php echo isset($unt_values_3['id']) ? $unt_values_3['id'] : ''; ?>_btn edit_add_item_in_cart <?php echo $item_in_cart ? 'ct-bed-selected' : ''; ?>"
                                data-units_id="<?php echo isset($unt_values_3['id']) ? $unt_values_3['id'] : ''; ?>"
                                data-duration_value="<?php echo $i; ?>"
                                data-service_id="<?php echo $_POST['service_id']; ?>"
                                data-method_id="<?php echo $_POST['method_id']; ?>"
                                data-method_name="<?php echo isset($unt_values_3['units_title']) ? $unt_values_3['units_title'] : ''; ?>"
                                data-un_title="<?php echo isset($unt_values_3['units_title']) ? $unt_values_3['units_title'] : ''; ?>"
                                data-rate="<?php echo ($calculation_policy == "M") ? $strate * $i : $strate; ?>"
                                data-type="method_units"
                                data-mnamee="mt_unit<?php echo isset($unt_values_3['id']) ? $unt_values_3['id'] : ''; ?>">
                                <?php echo $i . " " . $unt_values_3['unit_symbol']; ?>
                            </a>
                        </span>
                    <?php
                    } 
                    ?>
                </div>
            </div>
<?php
        } 
    }
} elseif(isset($_POST['s_m_units_maxlimit_4'])){
    $objservice_method_unit->services_id = $_POST['service_id'];

    $objservice_method_unit->methods_id = $_POST['method_id'];

    $unt_values_4 = $objservice_method_unit->getunits_by_service_methods_front();

    if ($unt_values_4->num_rows > 0) {

        while ($u_value_4 = mysqli_fetch_array($unt_values_4)) {

            $objservice_method_unit->maxlimit = 1;

            $objservice_method_unit->units_id = $u_value_4['id'];

            $unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();



            if ($unt_ratess) {

                $uniitt_rate = $unt_ratess['rates'];

            } else {

                $uniitt_rate = $u_value_4['base_price'];

            }

            $hfsec = 0;

            if ($u_value_4['half_section'] == "E") {

                $hfsec = 0.5;

            } else {

                $hfsec = 1;

            }

            ?>

            <?php

if (isset($_SESSION['ct_cart']['method']) && count($_SESSION['ct_cart']['method']) > 0) {

                foreach ($_SESSION['ct_cart']['method'] as $addon) {

                    $flag = 0;

                    if ($addon['units_id'] == $u_value_4['id'] && $_POST['method_id'] == $addon['method_id'] && $addon['service_id'] == $_POST['service_id']) {

                        $flag = 1;

                        ?>

                        <div class="ct-bedrooms ct-btn-group ct-md-6 ct-sm-6">

                            <label> <?php if (isset($u_value_4['limit_title']) != "") {echo $u_value_4['limit_title'];} else {echo $u_value_4['units_title'];}?></label>

                            <?php

$mmnameee = 'mt_unit' . $u_value_4['id'];

                        ?>

                            <button id="minus-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" class="edit_minuss ct-btn-left ct-small-btn" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-hfsec="<?php echo $hfsec; ?>" type="button" data-service_id="<?php echo $_POST['service_id']; ?>" data-rate="" data-method_id="<?php echo $_POST['method_id']; ?>" data-type="<?php echo "method_units"; ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>">-</button>

                            <input id="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" type="text" value="<?php echo $addon['s_m_qty']; ?><?php echo " " . $u_value_4['unit_symbol']; ?>" class="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?> ct-btn-text data_qtyrate" data-rate="<?php echo $uniitt_rate; ?>" />

                            <button id="add-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>"  data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-hfsec="<?php echo $hfsec; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" class="edit_addd ct-btn-right float-right ct-small-btn" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-rate="" type="button" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">+</button>

                        </div>

                    <?php

break;

                    }

                }

                if ($flag == 0) {

                    ?>

                    <div class="ct-bedrooms ct-btn-group ct-md-6 ct-sm-6">

                        <label> <?php if ($u_value_4['limit_title'] != "") {echo $u_value_4['limit_title'];} else {echo $u_value_4['units_title'];}?></label>

                        <?php

$mmnameee = 'mt_unit' . $u_value_4['id'];

                    ?>

                        <button id="minus-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" class="edit_minuss ct-btn-left ct-small-btn" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-hfsec="<?php echo $hfsec; ?>" type="button" data-service_id="<?php echo $_POST['service_id']; ?>" data-rate="" data-method_id="<?php echo $_POST['method_id']; ?>" data-type="<?php echo "method_units"; ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>">-</button>

                        <input id="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" type="text" value="0<?php echo " " . $u_value_4['unit_symbol']; ?>" class="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?> ct-btn-text data_qtyrate" data-rate="<?php echo $uniitt_rate; ?>" />

                        <button id="add-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>"  data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-hfsec="<?php echo $hfsec; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" class="edit_addd ct-btn-right float-right ct-small-btn" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-rate="" type="button" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">+</button>

                    </div>

                <?php

}

            } else {

                ?>

                <div class="ct-bedrooms ct-btn-group ct-md-6 ct-sm-6">

                    <label> <?php if ($u_value_4['limit_title'] != "") {echo $u_value_4['limit_title'];} else {echo $u_value_4['units_title'];}?></label>

                    <?php

$mmnameee = 'mt_unit' . $u_value_4['id'];

                ?>

                    <button id="minus-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" class="edit_minuss ct-btn-left ct-small-btn" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-hfsec="<?php echo $hfsec; ?>" type="button" data-service_id="<?php echo $_POST['service_id']; ?>" data-rate="" data-method_id="<?php echo $_POST['method_id']; ?>" data-type="<?php echo "method_units"; ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-mnamee="<?php echo $mmnameee; ?>">-</button>

                    <input id="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" type="text" value="0<?php echo " " . $u_value_4['unit_symbol']; ?>" class="qty<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?> ct-btn-text data_qtyrate" data-rate="<?php echo $uniitt_rate; ?>" />

                    <button id="add-addon-<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-units_id="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>"  data-ids="<?php if (isset($u_value_4['id'])) {echo $u_value_4['id'];} else {}?>" data-db-qty="<?php if (isset($u_value_4['maxlimit'])) {echo $u_value_4['maxlimit'];} else {}?>" data-db-minqty="<?php if (isset($u_value_4['minlimit'])) {echo $u_value_4['minlimit'];} else {}?>" data-hfsec="<?php echo $hfsec; ?>" data-service_id="<?php echo $_POST['service_id']; ?>" data-method_id="<?php echo $_POST['method_id']; ?>" class="edit_addd ct-btn-right float-right ct-small-btn" data-duration_value="" data-method_name="<?php echo $u_value_4['units_title'] ?>" data-unit_symbol="<?php if (isset($u_value_4['unit_symbol'])) {echo $u_value_4['unit_symbol'];} else {}?>" data-rate="" type="button" data-type="<?php echo "method_units"; ?>" data-mnamee="<?php echo $mmnameee; ?>">+</button>

                </div>

            <?php

}

            ?>

        <?php

}

    }

} elseif (isset($_POST['s_m_units_maxlimit_4_ratesss'])) {

    $objservice_method_unit->id = $_POST['units_id'];

    $objservice_method_unit->services_id = $_POST['service_id'];

    $objservice_method_unit->methods_id = $_POST['method_id'];

    $unt_values_4_rate = $objservice_method_unit->get_maxlimit_by_service_methods_ids_baseratess();

    $hfsec = 0;

    if ($unt_values_4_rate['half_section'] == "E") {

        $hfsec = 0.5;

    } else {

        $hfsec = 1;

    }



    $fe = 0;

    $fg = 0;

    $strate = 1;

    $finalvalue = 0;

    $calculation_policy = $settings->get_option("ct_calculation_policy");

    for ($i = $hfsec; $i <= $_POST['qty_vals']; $i += $hfsec) {

        $objservice_method_unit->maxlimit = $i;

        $objservice_method_unit->units_id = $_POST['units_id'];

        $unt_ratess = $objservice_method_unit->get_rate_by_service_methods_ids();



        if (isset($unt_ratess) && $unt_ratess['rules'] == 'G') {

            $strate = $unt_ratess['rates'];

            $fg = 1;

            $fe = 0;

        }

        if ($fg == 1) {

            if ($unt_ratess['rules'] == 'E') {

                if ($calculation_policy == "M") {

                    $finalvalue = $unt_ratess['rate'] * $i;

                } else {

                    $finalvalue = $unt_ratess['rate'];

                }

            } else {

                if ($calculation_policy == "M") {

                    $finalvalue = $strate * $i;

                } else {

                    $finalvalue = $strate;

                }



            }

        } elseif (isset($unt_ratess) && $unt_ratess['rules'] == 'E') {

            if ($calculation_policy == "M") {

                $finalvalue = $unt_ratess['rates'] * $i;

            } else {

                $finalvalue = $unt_ratess['rates'];

            }



        } else {

            if ($calculation_policy == "M") {

                $base_rates = $unt_values_4_rate['base_price'] * $i;

            } else {

                $base_rates = $unt_values_4_rate['base_price'];

            }

            $finalvalue = $base_rates;

        }

    }

    echo $finalvalue;

} elseif (isset($_POST['s_addon_units_maxlimit_4_ratesss'])) {

    $addons->id = $_POST['addon_id'];

    $addon_dataaa = $addons->readone();



    $fe = 0;

    $fg = 0;

    $strate = 1;

    $finalvalue = 0;

    $calculation_policy = $settings->get_option("ct_calculation_policy");

    for ($i = 1; $i <= $_POST['qty_vals']; $i++) {

        $addons_rates->maxlimit = $i;

        $addons_rates->addon_service_id = $addon_dataaa['id'];

        $unt_ratess = $addons_rates->get_rate_by_service_addon_ids();



        if (isset($unt_ratess) && $unt_ratess['rules'] == 'G') {

            $strate = $unt_ratess['rate'];

            $fg = 1;

            $fe = 0;

        }

        if ($fg == 1) {

            if ($unt_ratess['rules'] == 'E') {

                if ($calculation_policy == "M") {

                    $finalvalue = $unt_ratess['rate'] * $i;

                } else {

                    $finalvalue = $unt_ratess['rate'];

                }

            } else {

                if ($calculation_policy == "M") {

                    $finalvalue = $strate * $i;

                } else {

                    $finalvalue = $strate;

                }

            }

        } elseif (isset($unt_ratess) && $unt_ratess['rules'] == 'E') {

            if ($calculation_policy == "M") {

                $finalvalue = $unt_ratess['rate'] * $i;

            } else {

                $finalvalue = $unt_ratess['rate'];

            }

        } else {

            if ($calculation_policy == "M") {

                $base_rates = $addon_dataaa['base_price'] * $i;

            } else {

                $base_rates = $addon_dataaa['base_price'];

            }



            $finalvalue = $base_rates;

        }

    }

    echo $finalvalue;

} elseif (isset($_POST['get_postal_code'])) {

    @ob_clean();

    ob_start();

    $postal_code_list = $settings->get_option_postal();

    if ($postal_code_list == '') {

        echo "not found";

    } else {

        $res = explode(',', strtolower($postal_code_list));

        $check = 1;

        $p_code = strtolower($_POST['postal_code']);



        for ($i = 0; $i <= (count((array) $res) - 1); $i++) {

            if ($res[$i] == $p_code) {

                $j = 10;

                echo "found";

                break;

            } elseif (substr($p_code, 0, strlen($res[$i])) === $res[$i]) {

                $j = 10;

                echo "found";

                break;

            } else {

                $j = 20;

            }

        }

        if ($j == 20) {

            echo "not found";

        }

    }

}



if (isset($_POST['get_search_staff_detail'])) {

    $staff_list = $_POST['staff_search'];

    $get_staff = explode(",", $staff_list);



/*     print_r($get_staff);

echo sizeof((array)$get_staff); */

    foreach ($get_staff as $value) {

        if ($value != "") {

            $postal_code_staff_detail = $objadmin->get_search_staff_detail_byid($value);



            if ($postal_code_staff_detail[1] != '') {

                $staff_image = "./assets/images/services/" . $postal_code_staff_detail[1];

                $staff_image_mb = "../assets/images/services/" . $postal_code_staff_detail[1];

            } else {

                $staff_image = "./assets/images/user.png";

                $staff_image_mb = "../assets/images/user.png";

            }

            echo '<li class="ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 remove_provider_class edit_provider_select" data-id="' . $value . '">

				<input type="radio" name="provider-radio" data-staff_id ="' . $value . '" id="ct-provider-' . $value . '" class="provider_disable" ' . (($_SESSION['book_staff_id_cal'] = $value) ? 'checked="checked"' : "") . '>

							<label class="ct-provider border-c img-circle" for="ct-provider-' . $value . '">

							<div class="ct-provider-img">

								<img class="ct-image img-circle ct-mb-show" src="' . $staff_image . '">

								<img class="ct-image img-circle ct-mb-hidden" src="' . $staff_image_mb . '">

							</div>



							</label>



							<div class="provider-name fl ta-c">' . $postal_code_staff_detail[0] . '</div>



							</li>';



        }



    }

}



if (isset($_POST['select_language'])) {

    $_SESSION['current_lang'] = $_POST['set_language'];

}

/**item remove from cart**/



if (isset($_POST['cart_item_remove'])) {

    $json_array = array();

    $final_duration_value = 0;

    for ($i = 0; $i < (count($_SESSION['ct_cart']['method'])); $i++) {

        $method_type = '';

        if ($_SESSION['ct_cart']['method'][$i]['units_id'] == $_POST['cart_unit_id']) {

            unset($_SESSION['ct_cart']["method"][$i]);

        }

    }

    $_SESSION['ct_cart']['method'] = array_values($_SESSION['ct_cart']['method']);

    if (sizeof($_SESSION['ct_cart']['method']) == 0) {

        $json_array['status'] = "empty calculation";

    } else {

        /**calculation start**/

        $c_rates = 0;

        $final_duration_value = 0;

        for ($i = 0; $i < (count($_SESSION['ct_cart']['method'])); $i++) {

            $c_rates = ($c_rates + $_SESSION['ct_cart']['method'][$i]['s_m_rate']);

            $final_duration_value = ($final_duration_value + $_SESSION['ct_cart']['method'][$i]['duration']);

        }



        $hours = intval($final_duration_value / 60);

        $minutes = fmod($final_duration_value, 60);



        $json_array['duration_text'] = $hours . " " . $label_language_values['hours'] . " " . $minutes . " " . $label_language_values['minutes'];



        $frequently_discount->id = $_POST['frequently_discount_id'];

        $freq_dis_data = $frequently_discount->readone();

        if ($freq_dis_data) {

            if ($freq_dis_data['d_type'] == 'F') {

                $freqdis_amount = $freq_dis_data['rates'];

            } else

            if ($freq_dis_data['d_type'] == 'P') {

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

        if ($settings->get_option('sr_tax_vat_status') == 'Y') {

            if ($settings->get_option('sr_tax_vat_type') == 'F') {

                $flatvalue = $settings->get_option('sr_tax_vat_value');

                $taxamount = $flatvalue;

            } else

            if ($settings->get_option('sr_tax_vat_type') == 'P') {

                $percent = $settings->get_option('sr_tax_vat_value');

                $percentage_value = $percent / 100;

                $taxamount = $percentage_value * $final_subtotal;

            }

        } else {

            $taxamount = 0;

        }



        if ($settings->get_option('sr_partial_deposit_status') == 'Y') {

            $grand_total = $final_subtotal + $taxamount;

            if ($settings->get_option('sr_partial_type') == 'F') {

                $p_deposite_amount = $settings->get_option('sr_partial_deposit_amount');

                $partial_amount = $p_deposite_amount;

                $remain_amount = $grand_total - $partial_amount;

            } elseif ($settings->get_option('sr_partial_type') == 'P') {

                $p_deposite_amount = $settings->get_option('sr_partial_deposit_amount');

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



        $json_array['status'] = "cart not empty";

        $json_array['cart_discount'] = $general->ct_price_format(0, $symbol_position, $decimal);

        $json_array['partial_amount'] = $general->ct_price_format($partial_amount, $symbol_position, $decimal);

        $json_array['remain_amount'] = $general->ct_price_format($remain_amount, $symbol_position, $decimal);

        $json_array['frequent_discount'] = '- ' . $general->ct_price_format($_SESSION['freq_dis_amount'], $symbol_position, $decimal);

        $json_array['cart_tax'] = $general->ct_price_format($taxamount, $symbol_position, $decimal);

        $json_array['total_amount'] = $general->ct_price_format(($final_subtotal + $taxamount), $symbol_position, $decimal);

        $json_array['cart_sub_total'] = $general->ct_price_format($total, $symbol_position, $decimal);

        /* calculation end */

    }

    echo json_encode($json_array);

    $_SESSION['time_duration'] = $final_duration_value;

}



if (isset($_POST['open_edit_booking'])) {

    include_once dirname(dirname(dirname(__FILE__))) . '/edit_booking.php';

}



?>