<?php
include (dirname(dirname(dirname(__FILE__))) . "/objects/class_connection.php");
include (dirname(dirname(dirname(__FILE__))) . "/header.php");
include (dirname(dirname(dirname(__FILE__))) . "/objects/class_services_methods_units.php");
include (dirname(dirname(dirname(__FILE__))) . "/objects/class_services_methods_units_rates.php");
include (dirname(dirname(dirname(__FILE__))) . "/objects/class_service_methods_design.php");
include (dirname(dirname(dirname(__FILE__))) . "/objects/class_setting.php");
include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_addon.php";
include dirname(dirname(dirname(__FILE__))) . "/objects/class_services.php";
include dirname(dirname(dirname(__FILE__))) . "/objects/class_services_addon_rates.php";
$con = new cleanto_db();
$conn = $con->connect();

$objservice_addon = new cleanto_services_addon();
$objservice_addon_rate = new cleanto_services_addon_rates();
$objservice_addon->conn = $conn;
$objservice_addon_rate->conn = $conn;
$objservice = new cleanto_services();
$objservice->conn = $conn;


$objservice_method_unit = new cleanto_services_methods_units();
$objservice_method_unit_rate = new cleanto_services_methods_units_rates();
$objservice_method_unit->conn = $conn;
$objservice_method_unit_rate->conn = $conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$method_default_design = $settings->get_option('ct_method_default_design');
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
   if ($language_label_arr[3] != '') {
      $label_decode_admin = base64_decode($language_label_arr[3]);
   } else {
      $label_decode_admin = base64_decode($default_language_arr[3]);
   }
   if ($language_label_arr[4] != '') {
      $label_decode_error = base64_decode($language_label_arr[4]);
   } else {
      $label_decode_error = base64_decode($default_language_arr[4]);
   }
   if ($language_label_arr[5] != '') {
      $label_decode_extra = base64_decode($language_label_arr[5]);
   } else {
      $label_decode_extra = base64_decode($default_language_arr[5]);
   }
   $label_decode_front_unserial = unserialize($label_decode_front);
   $label_decode_admin_unserial = unserialize($label_decode_admin);
   $label_decode_error_unserial = unserialize($label_decode_error);
   $label_decode_extra_unserial = unserialize($label_decode_extra);

   $label_language_arr = array_merge(
      $label_decode_front_unserial,
      $label_decode_admin_unserial,
      $label_decode_error_unserial,
      $label_decode_extra_unserial
   );

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

   $label_language_arr = array_merge(
      $label_decode_front_unserial,
      $label_decode_admin_unserial,
      $label_decode_error_unserial,
      $label_decode_extra_unserial
   );

   foreach ($label_language_arr as $key => $value) {
      $label_language_values[$key] = urldecode($value);
   }
}

if (isset($_POST['getservice_method_units'])) {
   $objservice_method_unit->services_id = $_POST['service_id'];
   $objservice_method_unit->methods_id = $_POST['method_id'];
   $res = $objservice_method_unit->getunits_by_service_methods();
   $i = 1;  /* ?>    
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap-toggle.min.css" type="text/css" media="all">
<script src="<?php echo BASE_URL; ?>/assets/js/bootstrap-toggle.min.js" type="text/javascript" ></script>         
<?php  */
   if ($res->num_rows > 0) {
      while ($arrs = mysqli_fetch_array($res)) {
         $i++; ?>
         <div class="col-sm-12 col-md-12 col-xs-12">
            <li class="panel panel-default ct-clean-services-panel mysortlist_units"
               data-id="<?php if (isset($arrs['id'])) {
                  echo $arrs['id'];
               } else {
               } ?>"
               id="service_method_units_<?php if (isset($arrs['id'])) {
                  echo $arrs['id'];
               } else {
               } ?>">
               <div class="panel-heading">
                  <h4 class="panel-title">
                     <div class="cta-col8 col-sm-8 col-md-9 np">
                        <div class="pull-left"> <i class="fa fa-th-list"></i> </div>
                        <span class="ct-clean-service-title-name"
                           id="method_unit_name<?php if (isset($arrs['id'])) {
                              echo $arrs['id'];
                           } else {
                           } ?>"><?php echo $arrs['units_title']; ?></span>
                     </div>
                     <div class="pull-right cta-col4 col-sm-4 col-md-3 np">
                        <!--                 <div class="cta-col3"><a data-id="<?php /* if(isset($arrs['id'])){ echo $arrs['id']; }else{ } */ ?>" class="quantity-rules-btn pull-left btn-circle btn-info btn-sm myqtypriceload" title="Quantity Rules"> <i class="fa fa-money"></i></a>                </div>-->
                        <div class="cta-col4 cta-smu-endis">
                           <label for="sevice-endis-<?php if (isset($arrs['id'])) {
                              echo $arrs['id'];
                           } else {
                           } ?>">
                              <input class='myservices_methods_units_status' data-toggle="toggle" data-size="small"
                                 type='checkbox' data-id="<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>" <?php if ($arrs['status'] == 'E') {
                                     echo "checked";
                                  } else {
                                     echo "";
                                  } ?>
                                 id="sevice-endis-<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>"
                                 data-on="<?php echo $label_language_values['enable']; ?>"
                                 data-off="<?php echo $label_language_values['disable']; ?>" data-onstyle='success'
                                 data-offstyle='danger' />
                           </label>
                        </div>
                        <div class="pull-right cta-smu-del-toggle">
                           <div class="cta-col1">
                              <?php $t = $objservice_method_unit->method_unit_isin_use($arrs['id']);
                              if ($t > 0) { ?>
                                 <a data-toggle="popover" class="delete-clean-service-btn pull-right btn-circle btn-danger btn-sm"
                                    rel="popover" data-placement='top'
                                    title="<?php echo $label_language_values['unit_is_booked']; ?>"> <i class="fa fa-ban"></i></a>
                              <?php } else { ?>
                                 <a id="ct-delete-service-unit<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>"
                                    data-toggle="popover"
                                    class="delete-clean-service-unit-btn pull-right btn-circle btn-danger btn-sm" rel="popover"
                                    data-placement='left' title="<?php echo $label_language_values['delete_this_service_unit']; ?>">
                                    <i class="fa fa-trash" title="<?php echo $label_language_values['delete_service_unit']; ?>"></i>
                                 </a>
                                 <div id="popover-delete-service" style="display: none;">
                                    <div class="arrow"></div>
                                    <table class="form-horizontal" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td>
                                                <a data-service_method_unitid="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                   value="<?php echo $label_language_values['delete']; ?>"
                                                   class="btn btn-danger btn-sm service-methods-units-delete-button"><?php echo $label_language_values['yes']; ?>
                                                </a>
                                                <button id="ct-close-popover-delete-service-unit" class="btn btn-default btn-sm"
                                                   href="javascript:void(0)"
                                                   data-service_method_unitid="<?php if (isset($arrs['id'])) {
                                                      echo $arrs['id'];
                                                   } else {
                                                   } ?>"><?php echo $label_language_values['cancel']; ?>
                                                </button>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </div>
                              <?php } ?>
                           </div>
                           <div class="ct-show-hide pull-right">
                              <input type="checkbox" name="ct-show-hide" class="ct-show-hide-checkbox"
                                 id="sp<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>">
                              <!--Added Serivce Id-->
                              <label class="ct-show-hide-label"
                                 for="sp<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>"></label>
                           </div>
                        </div>
                     </div>
                  </h4>
               </div>
               <div id="detailmes_sp<?php if (isset($arrs['id'])) {
                  echo $arrs['id'];
               } else {
               } ?>"
                  class="servicemeth_details panel-collapse collapse">
                  <div class="panel-body">
                     <div class="ct-service-collapse-div col-sm-12 col-md-6 col-lg-6 col-xs-12">
                        <form id="service_method_unit_price<?php if (isset($arrs['id'])) {
                           echo $arrs['id'];
                        } else {
                        } ?>"
                           method="" type="" class="slide-toggle">
                           <table class="ct-create-service-table">
                              <tbody>
                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['unit_name']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"><input type="text" name="unitname"
                                             id="txtedtunitname<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             class="form-control mytxtservice_method_uniteditname<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             value="<?php echo $arrs['units_title'] ?>" /></div>
                                    </td>
                                 </tr>
                                 <tr>
                                    <?php $duration = $arrs['uduration'];
                                    $intval = intval($duration / 60);
                                    $modulas = fmod($duration, 60); ?>
                                    <td><label for="txtedtunithours"><?php echo $label_language_values['duration']; ?></label>
                                    </td>
                                    <td>
                                       <div class="form-inline col-xs-12">
                                          <div class="input-group"> <span class="input-group-addon"><span
                                                   class="glyphicon glyphicon-time"></span></span> <input placeholder="00"
                                                size="2" maxlength="2" type="text" name="unithours"
                                                id="txtedtunithours<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                class="form-control mytxtservice_method_unitedithours<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                value="<?php echo $intval; ?>" /> <span
                                                class="input-group-addon"><?php echo $label_language_values['hours']; ?></span>
                                          </div>
                                          <div class="input-group cta_mt_10"> <input placeholder="05" size="2" maxlength="2"
                                                type="text" name="unitmints"
                                                id="txtedtunitmints<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                class="form-control mytxtservice_method_uniteditmints<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                value="<?php echo $modulas; ?>" /> <span
                                                class="input-group-addon"><?php echo $label_language_values['minutes']; ?></span>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>

                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['base_price']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12">
                                          <div class="input-group"> <span class="input-group-addon"><span
                                                   class="unit-price-currency"><?php echo $settings->get_option('ct_currency_symbol'); ?></span></span>
                                             <input type="text" name="baseprice"
                                                id="txtedtunitbaseprice<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                class="form-control mytxtservice_method_uniteditbase_price<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                placeholder="US Dollar" value="<?php echo $arrs['base_price']; ?>"> </div>
                                       </div>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['min_limit']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"><input type="text" name="txtminlimit"
                                             class="form-control mytxt_service_method_editminlimit<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             id="txtedtunitminlimit<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             value="<?php echo $arrs['minlimit']; ?>" /></div>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['max_limit']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"><input type="text" name="txtmaxlimit"
                                             class="form-control mytxt_service_method_editmaxlimit<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             id="txtedtunitmaxlimit<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             value="<?php echo $arrs['maxlimit']; ?>" /></div>
                                    </td>
                                 </tr>
                                 <tr style="display:none;">
                                    <td><label for=""><?php echo $label_language_values['half_section']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"> <label class="ctoggle-large"
                                             for="sevice-endis-<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>">
                                             <input class='myservices_methods_one_status' data-toggle="toggle" data-size="small"
                                                type='checkbox'
                                                data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" <?php if ($arrs['half_section'] == 'E') {
                                                    echo "checked";
                                                 } else {
                                                    echo "";
                                                 } ?>
                                                id="sevice-endis-<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                data-on="<?php echo $label_language_values['enable']; ?>"
                                                data-off="<?php echo $label_language_values['disable']; ?>" data-onstyle='success'
                                                data-offstyle='danger' /> </label> </div>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['optional_label']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"><input type="text" name="txtmaxlimit_title"
                                             class="form-control mytxt_service_method_editmaxlimit_title<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             id="txtedtunitmaxlimit_title<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             value="<?php echo $arrs['limit_title']; ?>" /></div>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><label for=""><?php echo $label_language_values['optional_unit_symbol']; ?></label></td>
                                    <td>
                                       <div class="col-xs-12"><input type="text" name="txtsymbol"
                                             class="form-control mytxt_service_method_symbol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             id="txtedtunitsymbol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             value="<?php echo $arrs['unit_symbol']; ?>"
                                             placeholder="<?php echo $label_language_values['sqft']; ?>" /></div>
                                    </td>
                                 </tr>



                                 <style>
                                    .cta-addons-imagelist-dropdown {
                                       position: relative;
                                       display: inline-block;
                                    }

                                    .cta-addons-selection-main {
                                       cursor: pointer;
                                    }

                                    .cta-addons-selection-main img {
                                       width: 50px;
                                       height: 50px;
                                    }

                                    .cta-addons-dropdown {
                                       display: none;
                                       position: absolute;
                                       background-color: #f9f9f9;
                                       min-width: 200px;
                                       box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                                       z-index: 1;
                                    }

                                    .cta-addons-list {
                                       padding: 12px 16px;
                                       display: flex;
                                       align-items: center;
                                       cursor: pointer;
                                    }

                                    .cta-addons-list img {
                                       width: 30px;
                                       height: 30px;
                                       margin-right: 10px;
                                    }

                                    .cta-addons-list h3 {
                                       margin: 0;
                                       font-size: 16px;
                                    }

                                    .cta-addons-list:hover {
                                       background-color: #ddd;
                                    }
                                 </style>

                                 <tr>
                                    <td><label
                                          for="ct-service-desc"><?php echo $label_language_values['service_image']; ?></label>
                                    </td>
                                    <td style="display: flex; align-items: center; justify-content: space-around;">
                                       <div class="ct-clean-service-addons-image-uploader">
                                          <?php if (@$arrs['image'] == '') {
                                             $imagepath = "../assets/images/default_service.png";
                                          } else {
                                             $imagepath = "../assets/images/services/" . @$arrs['image'];
                                          } ?> <img data-imagename=""
                                             id="pcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>addonimage"
                                             src="<?php echo $imagepath; ?>" class="ct-clean-service-addons-image br-100"
                                             height="100" width="100"> <?php if (@$arrs['image'] == '') { ?> <label
                                                for="ct-upload-imagepcaol<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                class="ct-clean-service-addons-img-icon-label ser_addons<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>">
                                                <i class="ct-camera-icon-common br-100 fa fa-camera"></i> <i
                                                   class="pull-left fa fa-plus-circle fa-2x"></i> </label> <?php
                                             } ?> <input
                                             data-us="pcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             data-id="<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             class="hide ct-upload-images" type="file" name=""
                                             id="ct-upload-imagepcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>" />
                                          <label
                                             for="ct-upload-imagepcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             class="ct-clean-service-addons-img-icon-label addon_ser_cam cam_btn_addon<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>">
                                             <i class="ct-camera-icon-common br-100 fa fa-camera"></i> <i
                                                class="pull-left fa fa-plus-circle fa-2x"></i> </label>
                                          <?php if (@$arrs['image'] !== '') { ?>
                                             <a data-pcaolid="<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                                id="ct-remove-service-addons-imagepcaol<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                class="pull-left br-100 btn-danger bt-remove-service-addons-img btn-xs addons_del_btn addons_del_icon<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                rel="popover" data-placement='left'
                                                title="<?php echo $label_language_values['remove_image']; ?>"> <i
                                                   class="fa fa-trash"
                                                   title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a> <?php
                                          } ?>
                                          <a data-pcaolid="<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                             id="ct-remove-service-addons-imagepcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             class="pull-left br-100 btn-danger bt-remove-service-addons-img btn-xs new_addons_del del_btn_addon<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             rel="popover" data-placement='left'
                                             title="<?php echo $label_language_values['remove_image']; ?>"> <i
                                                class="fa fa-trash"
                                                title="<?php echo $label_language_values['remove_service_image']; ?>"></i></a>
                                          <div
                                             id="popover-ct-remove-service-addons-imagepcaol<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             style="display: none;">
                                             <div class="arrow"></div>
                                             <table class="form-horizontal" cellspacing="0">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <a href="javascript:void(0)" id="" value="Delete"
                                                            class="btn btn-danger btn-sm delete_image_addons"
                                                            data-pcaolid="<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"
                                                            type="submit"><?php echo $label_language_values['yes']; ?></a>
                                                         <a href="javascript:void(0)" id="ct-close-popover-service-addon-image"
                                                            class="btn btn-default btn-sm" href="javascript:void(0)"
                                                            data-pcaolid="<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"><?php echo $label_language_values['cancel']; ?></a>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       <label class="error_image"></label> <span
                                          class="cta-addon-img-icon" style="font-size: 20px;"><?php echo $label_language_values['or']; ?></span>
                                       <div class="cta-addons-imagelist-dropdown fl" style="border: 1px solid #ddd;
                                                   border-radius: 5px;">
                                          <div class="cta-addons-selection-main">
                                             <div class="cta-addon-is update_id"
                                                data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>"
                                                title="<?php echo $label_language_values['choose_your_method_image']; ?>">
                                                <?php if ($arrs['predefine_image'] != "") { ?>
                                                   <div class="cta-addons-list"
                                                      id="addonid_<?php if (isset($arrs['id'])) {
                                                         echo $arrs['id'];
                                                      } else {
                                                      } ?>"
                                                      data-name="<?php echo $arrs['predefine_image']; ?>"
                                                      data-p_i_name="<?php echo $arrs['predefine_image_title']; ?>">
                                                      <img class="cta-addons-image"
                                                         src='../assets/images/unit-images/<?php echo $arrs['predefine_image']; ?>'
                                                         title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                      <h3 class="cta-addons-name"><?php echo $arrs['predefine_image_title']; ?></h3>
                                                   </div>
                                                   <?php
                                                } else { ?>
                                                   <div class="cta-addons-list"
                                                      id="addonid_<?php if (isset($arrs['id'])) {
                                                         echo $arrs['id'];
                                                      } else {
                                                      } ?>"
                                                      data-name="" data-p_i_name="">
                                                      <i class="cta-addons-image icon-puzzle icons"></i>
                                                      <h3 class="cta-addons-name">
                                                         <?php echo $label_language_values['select_method_image']; ?></h3>
                                                   </div>
                                                   <?php
                                                } ?>
                                             </div>
                                             <div
                                                class="cta-addons-dropdown display_update_<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>">
                                                <div class="cta-addons-list select_addons"
                                                   data-id="<?php if (isset($arrs['id'])) {
                                                      echo $arrs['id'];
                                                   } else {
                                                   } ?>"
                                                   data-name="bed.png"
                                                   data-p_i_name="<?php echo $label_language_values['Bedroom']; ?>">
                                                   <img class="cta-addons-image"
                                                      src="../assets/images/unit-images/bed.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Bedroom']; ?></h3>
                                                      
                                                </div>
                                                <div class="cta-addons-list select_addons"
                                                   data-id="<?php if (isset($arrs['id'])) {
                                                      echo $arrs['id'];
                                                   } else {
                                                   } ?>"
                                                   data-name="bathroom.png"
                                                   data-p_i_name="<?php echo $label_language_values['Bathroom']; ?>">
                                                   <img class="cta-addons-image"
                                                      src="../assets/images/unit-images/bathroom.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Bathroom']; ?></h3>
                                                      
                                                </div>
                                                <div class="cta-addons-list select_addons"
                                                   data-id="<?php if (isset($arrs['id'])) {
                                                      echo $arrs['id'];
                                                   } else {
                                                   } ?>"
                                                   data-name="area.png"
                                                   data-p_i_name="<?php echo $label_language_values['Kitchen']; ?>">
                                                   <img class="cta-addons-image"
                                                      src="../assets/images/unit-images/area.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Kitchen']; ?></h3>
                                                       
                                                </div>
                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="toilet.png"
                                                   data-p_i_name="<?php echo $label_language_values['Toilet']; ?>">
                                                   <img class="cta-addons-image" src="../assets/images/unit-images/toilet.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Toilet']; ?>
                                                   </h3>

                                                </div>



                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="living.png"
                                                   data-p_i_name="<?php echo $label_language_values['living_room']; ?>">
                                                   <img class="cta-addons-image" src="../assets/images/unit-images/living.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />
                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['living_room']; ?>
                                                   </h3>
                                                </div>
                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="diningroom.png"
                                                   data-p_i_name="<?php echo $label_language_values['dining_room']; ?>">
                                                   <img class="cta-addons-image"
                                                      src="../assets/images/unit-images/diningroom.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />

                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['dining_room']; ?>
                                                   </h3>

                                                </div>
                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="balcony.png"
                                                   data-p_i_name="<?php echo $label_language_values['Balcony']; ?>">
                                                   <img class="cta-addons-image" src="../assets/images/unit-images/balcony.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />

                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Balcony']; ?>
                                                   </h3>
                                                </div>
                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="gym.png"
                                                   data-p_i_name="<?php echo $label_language_values['Gym']; ?>">
                                                   <img class="cta-addons-image" src="../assets/images/unit-images/gym.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />

                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Gym']; ?>
                                                   </h3>


                                                </div>


                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="home-theater.png"
                                                   data-p_i_name="<?php echo $label_language_values['home_theater_room']; ?>">
                                                   <img class="cta-addons-image"
                                                      src="../assets/images/unit-images/home-theater.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />

                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['home_theater_room']; ?>
                                                   </h3>
                                                </div>


                                                <div class="cta-addons-list select_addons" data-id="<?php if (isset($arrs['id'])) {
                                                   echo $arrs['id'];
                                                } else {
                                                } ?>" data-name="backyard.png"
                                                   data-p_i_name="<?php echo $label_language_values['Yard']; ?>">
                                                   <img class="cta-addons-image" src="../assets/images/unit-images/backyard.png"
                                                      title="<?php echo $label_language_values['service_method_image']; ?>" />

                                                   <h3 class="cta-addons-name">
                                                      <?php echo $label_language_values['Yard']; ?>
                                                   </h3>


                                                </div>

                                             </div>
                                          </div>
                                       </div>
                                       <div
                                          id="ct-image-upload-popuppcaol<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                          class="ct-image-upload-popup modal fade" tabindex="-1" role="dialog">
                                          <div class="vertical-alignment-helper">
                                             <div class="modal-dialog modal-md vertical-align-center">
                                                <div class="modal-content">
                                                   <div class="modal-header">
                                                      <div class="col-md-12 col-xs-12"> <a
                                                            data-us="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"
                                                            class="btn btn-success ct_upload_img5"
                                                            data-imageinputid="ct-upload-imagepcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"
                                                            data-id="<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"><?php echo $label_language_values['crop_and_save']; ?></a>
                                                         <button type="button" class="btn btn-default hidemodal"
                                                            data-dismiss="modal"
                                                            aria-hidden="true"><?php echo $label_language_values['cancel']; ?></button>
                                                      </div>
                                                   </div>
                                                   <div class="modal-body"> <img
                                                         id="ct-preview-imgpcaol<?php if (isset($arrs['id'])) {
                                                            echo $arrs['id'];
                                                         } else {
                                                         } ?>" />
                                                   </div>
                                                   <div class="modal-footer">
                                                      <div class="col-md-12 np">
                                                         <div class="col-md-4 col-xs-12"> <label
                                                               class="pull-left"><?php echo $label_language_values['file_size']; ?></label>
                                                            <input type="text" class="form-control"
                                                               id="pcaolfilesize<?php if (isset($arrs['id'])) {
                                                                  echo $arrs['id'];
                                                               } else {
                                                               } ?>"
                                                               name="filesize" /> </div>
                                                         <div class="col-md-4 col-xs-12"> <label class="pull-left">H</label>
                                                            <input type="text" class="form-control"
                                                               id="pcaol<?php if (isset($arrs['id'])) {
                                                                  echo $arrs['id'];
                                                               } else {
                                                               } ?>h"
                                                               name="h" /> </div>
                                                         <div class="col-md-4 col-xs-12"> <label class="pull-left">W</label>
                                                            <input type="text" class="form-control"
                                                               id="pcaol<?php if (isset($arrs['id'])) {
                                                                  echo $arrs['id'];
                                                               } else {
                                                               } ?>w"
                                                               name="w" /> </div>
                                                         <!-- hidden crop params --> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>x1"
                                                            name="x1" /> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>y1"
                                                            name="y1" /> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>x2"
                                                            name="x2" /> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>y2"
                                                            name="y2" /> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>id"
                                                            name="id"
                                                            value="<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>" />
                                                         <input
                                                            id="pcaolctimage<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>"
                                                            type="hidden" name="ctimage" /> <input type="hidden"
                                                            id="lastrecordid"
                                                            value="addon_<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>">
                                                         <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>ctimagename"
                                                            class="pcaolimg" name="ctimagename"
                                                            value="<?php echo $arrs['image']; ?>" /> <input type="hidden"
                                                            id="pcaol<?php if (isset($arrs['id'])) {
                                                               echo $arrs['id'];
                                                            } else {
                                                            } ?>newname"
                                                            value="addon_" />
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>


                                 <tr>
                                    <td></td>
                                    <td>
                                       <div class="col-xs-12"><a
                                             data-id="<?php if (isset($arrs['id'])) {
                                                echo $arrs['id'];
                                             } else {
                                             } ?>"
                                             class="btn btn-success ct-btn-width mybtnservice_method_unitupdate"><?php echo $label_language_values['update']; ?></a>
                                       </div>
                                    </td>
                                 </tr>










                              </tbody>
                           </table>
                        </form>
                     </div>
                     <div
                        class="manage-unit-price-container<?php if (isset($arrs['id'])) {
                           echo $arrs['id'];
                        } else {
                        } ?> col-sm-12 col-md-6 col-lg-6 col-xs-12 mt-20"
                        style="display:<?php if ($settings->get_option('ct_calculation_policy') == "M") {
                           echo "none";
                        } else {
                           echo "block";
                        } ?>">
                        <div class="manage-unit-price-main col-sm-12 col-md-12 col-lg-12 col-xs-12">
                           <h4><?php echo $label_language_values['service_unit_price_rules']; ?></h4>
                           <ul>
                              <li class="form-group">
                                 <label class="col-sm-2 col-xs-12 np"
                                    for="addon_qty_6"><?php echo $label_language_values['base_price']; ?></label>
                                 <div class="col-xs-12 col-sm-2"> <input class="form-control" placeholder="1" value="1" id=""
                                       type="text" readonly="readonly" /></div>
                                 <div class="price-rules-select">
                                    <select class="form-control" id="">
                                       <option selected="" readonly value="=">= </option>
                                    </select>
                                 </div>
                                 <div class="col-xs-12 col-sm-3"> <input class="pull-left form-control" readonly
                                       value="<?php echo $arrs['base_price']; ?>"
                                       placeholder="<?php echo $label_language_values['price']; ?>" type="text" /> </div>
                              </li>
                           </ul>
                           <ul class="myunitspricebyqty<?php if (isset($arrs['id'])) {
                              echo $arrs['id'];
                           } else {
                           } ?>">
                              <?php $objservice_method_unit_rate->units_id = $arrs['id'];
                              $result = $objservice_method_unit_rate->getallrates_byunitid();
                              if ($result->num_rows > 0) {
                                 while ($r = mysqli_fetch_array($result)) { ?>
                                    <li class="form-group myunitqty_price_row<?php if (isset($r['id'])) {
                                       echo $r['id'];
                                    } else {
                                    } ?>">
                                       <form id="myeditform_units<?php if (isset($r['id'])) {
                                          echo $r['id'];
                                       } else {
                                       } ?>">
                                          <label class="col-sm-2 col-xs-12 np"
                                             for="addon_qty_6"><?php echo $label_language_values['Quantity']; ?></label>
                                          <div class="col-xs-12 col-sm-2"> <input
                                                id="myeditqty_units<?php if (isset($r['id'])) {
                                                   echo $r['id'];
                                                } else {
                                                } ?>"
                                                name="edtqty"
                                                class="form-control myloadedqty_units<?php if (isset($r['id'])) {
                                                   echo $r['id'];
                                                } else {
                                                } ?>"
                                                placeholder="1" value="<?php echo $r['units']; ?>" type="text" /> </div>
                                          <div class="price-rules-select">
                                             <select
                                                class="form-control myloadedrules_units<?php if (isset($r['id'])) {
                                                   echo $r['id'];
                                                } else {
                                                } ?>">
                                                <option <?php if ($r['rules'] == 'E') { ?>selected<?php } ?> value="E">=</option>
                                                <option <?php if ($r['rules'] == 'G') { ?>selected<?php } ?> value="G"> &gt; </option>
                                             </select>
                                          </div>
                                          <div class="col-xs-12 col-sm-3"> <input
                                                id="myeditprice_units<?php if (isset($r['id'])) {
                                                   echo $r['id'];
                                                } else {
                                                } ?>"
                                                name="edtprice"
                                                class="pull-left form-control myloadedprice_units<?php if (isset($r['id'])) {
                                                   echo $r['id'];
                                                } else {
                                                } ?>"
                                                value="<?php echo $r['rates']; ?>" placeholder="Price" type="text" /> </div>
                                          <a href="javascript:void(0);" data-id="<?php if (isset($r['id'])) {
                                             echo $r['id'];
                                          } else {
                                          } ?>"
                                             class="btn btn-circle btn-success  pull-left update-addon-rule myloadedbtnsave_units"><i
                                                class="fa fa-thumbs-up"></i></a> <a href="javascript:void(0);"
                                             data-id="<?php if (isset($r['id'])) {
                                                echo $r['id'];
                                             } else {
                                             } ?>"
                                             class="btn btn-circle btn-danger pull-left delete-addon-rule myloadedbtndelete_units"><i
                                                class="fa fa-trash"></i></a>
                                       </form>
                                    </li>
                                 <?php }
                              } ?>
                              <li class="form-group">
                                 <form id="mynewaddedform_units<?php if (isset($arrs['id'])) {
                                    echo $arrs['id'];
                                 } else {
                                 } ?>">
                                    <label class="col-sm-2 col-xs-12 np"
                                       for="addon_qty_6"><?php echo $label_language_values['Quantity']; ?></label>
                                    <div class="col-xs-12 col-sm-2"> <input required
                                          class="form-control mynewqty<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                          name="mynewssqty"
                                          id="mynewaddedqty_units<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                          placeholder="1" value="" type="text" /> </div>
                                    <div class="price-rules-select">
                                       <select
                                          class="form-control mynewrule<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>">
                                          <option selected value="E">=</option>
                                          <option value="G"> &gt; </option>
                                       </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-3"> <input name="mynewssprice"
                                          id="mynewaddedprice_units<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                          required
                                          class="pull-left form-control mynewprice<?php if (isset($arrs['id'])) {
                                             echo $arrs['id'];
                                          } else {
                                          } ?>"
                                          value="" placeholder="<?php echo $label_language_values['price_per_unit']; ?>"
                                          type="text" /> </div>
                                    &nbsp; <a href="javascript:void(0);"
                                       data-id="<?php if (isset($arrs['id'])) {
                                          echo $arrs['id'];
                                       } else {
                                       } ?>" data-inspector="0"
                                       class="btn btn-circle btn-success add-addon-price-rule form-group new-manage-price-list myaddnewatyrule_units"><?php echo $label_language_values['add_new']; ?></a>
                                 </form>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <!-- end manage unit price container -->
               </div>
         </div>
         </li>
         </div>
      <?php }
   } ?>
   <div class="col-sm-12 col-md-12 col-xs-12">
      <li>
         <!-- add new clean service pop up -->
         <div class="panel panel-default ct-clean-services-panel ct-add-new-price-unit">
            <div class="panel-heading">
               <h4 class="panel-title">
                  <div class="cta-col6"> <span class="ct-service-title-name"></span> </div>
                  <div class="pull-right cta-col6">
                     <div class="pull-right">
                        <div class="ct-show-hide pull-right">
                           <input type="checkbox" name="ct-show-hide" checked="checked" class="ct-show-hide-checkbox"
                              id="sp0"><!--Added Serivce Id--> <label class="ct-show-hide-label" for="sp0"></label>
                        </div>
                     </div>
                  </div>
               </h4>
            </div>
            <div id="" class="panel-collapse collapse in detail_sp0">
               <div class="panel-body">
                  <div class="ct-service-collapse-div col-sm-12 col-md-12 col-xs-12 np">
                     <form id="service_method_unitaddform" method="" type="" class="slide-toggle">
                        <table class="ct-create-service-table">
                           <tbody>
                              <tr>
                                 <td class="td-width"><label
                                       for=""><?php echo $label_language_values['unit_name']; ?></label></td>
                                 <td>
                                    <div class="col-xs-12"><input type="text"
                                          class="form-control mytxt_service_method_unitname" name="unitprice"
                                          id="txtunitnamess" /></div>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="td-width"><label
                                       for=""><?php echo $label_language_values['base_price']; ?></label></td>
                                 <td>
                                    <div class="col-xs-12">
                                       <div class="input-group"> <span class="input-group-addon"><span
                                                class="unit-price-currency"><?php echo $settings->get_option('ct_currency_symbol'); ?></span></span>
                                          <input type="text" class="form-control mytxt_service_method_unitbaseprice"
                                             id="txtbasepricess" name="baseprice" placeholder="US Dollar"> </div>
                                       <label for="txtbasepricess" generated="true" class="error"></label>
                                    </div>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="td-width"></td>
                                 <td>
                                    <div class="col-xs-12"> <a
                                          class="btn btn-success ct-btn-width mybtnservice_method_unitsave"><?php echo $label_language_values['save']; ?></a>
                                    </div>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </li>
   </div>
<?php } elseif (isset($_POST['deleteid'])) {
   $objservice_method_unit->id = $_POST['deleteid'];
   $objservice_method_unit->delete_services_method_unit();
} elseif (isset($_POST['pos']) && isset($_POST['ids'])) {
   echo "yes in ";
   echo count((array) $_POST['ids']);
   for ($i = 0; $i < count((array) $_POST['ids']); $i++) {
      $objservice_method_unit->position = $_POST['pos'][$i];
      $objservice_method_unit->id = $_POST['ids'][$i];
      $objservice_method_unit->updateposition();
   }
} elseif (isset($_POST['changestatus'])) {
   $objservice_method_unit->id = $_POST['id'];
   $objservice_method_unit->status = $_POST['changestatus'];
   $objservice_method_unit->changestatus();
} elseif (isset($_POST['changesinfotatus'])) {
   $objservice_method_unit->id = $_POST['id'];
   $objservice_method_unit->status = $_POST['changesinfotatus'];
   $objservice_method_unit->changesinfotatus();
} elseif (isset($_POST['operationinsert'])) {
   $objservice_method_unit->methods_id = $_POST['method_id'];
   $objservice_method_unit->units_title = filter_var($_POST['unit_name']);
   $t = $objservice_method_unit->check_same_title();
   $cnt = mysqli_num_rows($t);
   if ($cnt == 0) {
      $objservice_method_unit->services_id = $_POST['service_id'];
      $objservice_method_unit->methods_id = $_POST['method_id'];
      $objservice_method_unit->units_title = filter_var(mysqli_real_escape_string($conn, ucwords($_POST['unit_name'])));
      $objservice_method_unit->base_price = $_POST['base_price'];
      $objservice_method_unit->maxlimit = 1;
      $objservice_method_unit->status = 'D';
      $objservice_method_unit->add_services_method_unit();
      $objservice_method_unit_rate->assign_design(
         "ct_service_methods_design",
         $_POST['method_id'],
         3
      );
   } else {
      echo "1";
   }
} elseif (isset($_POST['operationedit'])) {
   chmod(dirname(dirname(dirname(__FILE__))) . "/assets/images/services", 0777);
   $objservice_method_unit->id = $_POST['id'];
   $hours = $_POST['units_hours'];
   $mintues = $_POST['units_mints'];
   if ($_POST['units_hours'] > 0 || $_POST['units_hours'] != '') {
      if ($mintues == "") {
         $mintues = 0;
      }
      $objservice_method_unit->duration = ($hours * 60) + $mintues;
   } else {
      if ($hours == "") {
         $hours = 0;
      }
      $objservice_method_unit->duration = $hours + $mintues;
   }
   $objservice_method_unit->units_title = filter_var(mysqli_real_escape_string($conn, ucwords($_POST['units_title'])));
   $objservice_method_unit->base_price = $_POST['base_price'];
   $objservice_method_unit->minlimit = $_POST['minlimit'];
   $objservice_method_unit->maxlimit = $_POST['maxlimit'];
   $objservice_method_unit->maxlimit_title = ucwords($_POST['maxlimit_title']);
   $objservice_method_unit->unit_symbol = $_POST['unit_symbol'];

   $objservice_method_unit->image = $_POST['image'];
   $objservice_method_unit->predefine_image = $_POST['predefineimage'];
   $objservice_method_unit->predefine_image_title = $_POST['predefineimage_title'];
   $objservice_method_unit->update_services_method_unit();
} elseif (isset($_POST['operationgetmethods'])) {
   $objservice_method->service_id = $_POST['service_id'];
   $res = $objservice_method->methodsbyserviceid();
   while ($arr = mysqli_fetch_array($res)) {
      echo "Method : " . $arr['method_title'] . " Id " . $arr['id'];
   }
} elseif (isset($_POST['checkunits_ofservice_methods'])) {
   $objservice_method_unit->services_id = $_POST['service_id'];
   $objservice_method_unit->methods_id = $_POST['method_id'];
   $res = $objservice_method_unit->getunits_by_service_methods();
   $t = mysqli_num_rows($res);
   if ($t < 1) {
      echo $t . "records";
   } else {
      if ($t == 1) {
      } else { ?>
         <div class="ct-custom-radio">
            <ul class="ct-radio-list">
               <?php $t = $objservice_method_unit->get_setting_design_methods($_POST['method_id']); ?>
               <li class="fln w100">
                  <input <?php if ($t == 2) {
                     echo "checked";
                  } ?> type="radio" id="v1" class="cta-radio design_radio_btn_units"
                     name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="2" />
                  <label for="v1"><span></span><?php echo $label_language_values['service_unit_front_dropdown_view']; ?></label>
                  <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-1.jpg"
                     style="height:  100%;width: 100%;">
               </li>
               <li class="fln w100">
                  <input <?php if ($t == 3) {
                     echo "checked";
                  } ?> type="radio" id="v2" class="cta-radio design_radio_btn_units"
                     name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="3" />
                  <label for="v2"><span></span><?php echo $label_language_values['service_unit_front_block_view']; ?></label>
                  <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-2.jpg"
                     style="height: 100%;width: 100%;">
               </li>
               <li class="fln w100">
                  <input <?php if ($t == 4) {
                     echo "checked";
                  } ?> type="radio" id="v3" class="cta-radio design_radio_btn_units"
                     name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="4" />
                  <label
                     for="v3"><span></span><?php echo $label_language_values['service_unit_front_increase_decrease_view']; ?></label>
                  <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-3.jpg"
                     style="height: 100%;width: 100%;">
               </li>
            </ul>
         </div>
      <?php }
   }
} elseif (isset($_POST['operation_getallqtyprice'])) {
   /* get all service by unit id */
   $objservice_method_unit_rate->units_id = $_POST['unit_id'];
   $result = $objservice_method_unit_rate->getallrates_byunitid();
   if ($result->num_rows > 0) {
      while ($r = mysqli_fetch_array($result)) { ?>
         <li class="form-group myunitqty_price_row<?php if (isset($r['id'])) {
            echo $r['id'];
         } else {
         } ?>">
            <form id="myeditform_units<?php if (isset($r['id'])) {
               echo $r['id'];
            } else {
            } ?>">
               <label class="col-sm-2 col-xs-12 np" for="addon_qty_6"><?php echo $label_language_values['Quantity']; ?></label>
               <div class="col-xs-12 col-sm-2">
                  <input id="myeditqty_units<?php if (isset($r['id'])) {
                     echo $r['id'];
                  } else {
                  } ?>" name="edtqty"
                     class="form-control myloadedqty_units<?php if (isset($r['id'])) {
                        echo $r['id'];
                     } else {
                     } ?>" placeholder="1"
                     value="<?php echo $r['units']
                     ; ?>" type="text" />
               </div>
               <div class="price-rules-select">
                  <select class="form-control myloadedrules_units<?php if (isset($r['id'])) {
                     echo $r['id'];
                  } else {
                  } ?>">
                     <option <?php if ($r['rules'] == 'E') { ?>selected<?php } ?> value="E">=</option>
                     <option <?php if ($r['rules'] == 'G') { ?>selected<?php } ?> value="G"> &gt; </option>
                  </select>
               </div>
               <div class="col-xs-12 col-sm-3">
                  <input id="myeditprice_units<?php if (isset($r['id'])) {
                     echo $r['id'];
                  } else {
                  } ?>" name="edtprice"
                     class="pull-left form-control myloadedprice_units<?php if (isset($r['id'])) {
                        echo $r['id'];
                     } else {
                     } ?>"
                     value="<?php echo $r['rates']; ?>" placeholder="<?php echo $label_language_values['price']; ?>"
                     type="text" />
               </div>
               <a href="javascript:void(0);" data-id="<?php if (isset($r['id'])) {
                  echo $r['id'];
               } else {
               } ?>"
                  class="btn btn-circle btn-success  pull-left update-addon-rule myloadedbtnsave_units"><i
                     class="fa fa-thumbs-up"></i></a>
               <a href="javascript:void(0);" data-id="<?php if (isset($r['id'])) {
                  echo $r['id'];
               } else {
               } ?>"
                  class="btn btn-circle btn-danger pull-left delete-addon-rule myloadedbtndelete_units"><i
                     class="fa fa-trash"></i></a>
            </form>
         </li>
      <?php }
   } ?>
   <li class="form-group">
      <form id="mynewaddedform_units<?php echo $_POST['unit_id']; ?>">
         <label class="col-sm-2 col-xs-12 np" for="addon_qty_6"><?php echo $label_language_values['Quantity']; ?></label>
         <div class="col-xs-12 col-sm-2">
            <input required class="form-control mynewqty<?php echo $_POST['unit_id']; ?>" name="mynewssqty"
               id="mynewaddedqty_units<?php echo $_POST['unit_id']; ?>" placeholder="1" value="" type="text" />
         </div>
         <div class="price-rules-select">
            <select class="form-control mynewrule<?php echo $_POST['unit_id']; ?>">
               <option selected value="E">=</option>
               <option value="G"> &gt; </option>
            </select>
         </div>
         <div class="col-xs-12 col-sm-3">
            <input name="mynewssprice" id="mynewaddedprice_units<?php echo $_POST['unit_id']; ?>" required
               class="pull-left form-control mynewprice<?php echo $_POST['unit_id']; ?>" value=""
               placeholder="<?php echo $label_language_values['price_per_unit']; ?>" type="text" />
         </div>
         &nbsp; <a href="javascript:void(0);" data-id="<?php echo $_POST['unit_id']; ?>" data-inspector="0"
            class="btn btn-circle btn-success add-addon-price-rule form-group new-manage-price-list myaddnewatyrule_units"><?php echo $label_language_values['add_new']; ?></a>
      </form>
   </li>
<?php } elseif (isset($_POST['operationdelete_unitprice'])) {
   /* delete the row from the db*/
   $objservice_method_unit_rate->id = $_POST['id'];
   $objservice_method_unit_rate->delete_unitprice();
} elseif (isset($_POST['operation_updateqtyprice_unit'])) {
   $objservice_method_unit_rate->id = $_POST['editid'];
   $objservice_method_unit_rate->units = $_POST['qty'];
   $objservice_method_unit_rate->rules = $_POST['rules'];
   $objservice_method_unit_rate->rates = $_POST['price'];
   $objservice_method_unit_rate->update_unitprice();
} elseif (isset($_POST['operation_insertqtyprice_unit'])) {
   $objservice_method_unit_rate->units = $_POST['qty'];
   $objservice_method_unit_rate->rules = $_POST['rules'];
   $objservice_method_unit_rate->rates = $_POST['price'];
   $objservice_method_unit_rate->units_id = $_POST['unit_id'];
   $objservice_method_unit_rate->insert_unitprice();
} elseif (isset($_POST['assigndesign'])) {
   $id = $_POST['service_method_id'];
   $designid = $_POST['designid'];
   $having = $objservice_method_unit_rate->check_have_design($id);
   if (count((array) $having[0]) > 0) {
      $objservice_method_unit_rate->update_design("ct_service_methods_design", $id, $designid);
   } else {
      $objservice_method_unit_rate->assign_design("ct_service_methods_design", $id, $designid);
   }
} elseif ($_POST['setfrontdesign']) { ?>
   <div class="ct-custom-radio">
      <ul class="ct-radio-list">
         <?php $t = $objservice_method_unit->get_setting_design_methods($_POST['method_id']); ?>
         <li class="fln w100">
            <input <?php if ($t == 2) {
               echo "checked";
            } ?> type="radio" id="v1" class="cta-radio design_radio_btn_units"
               name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="2" />
            <label for="v1"><span></span><?php echo $label_language_values['service_unit_front_dropdown_view']; ?></label>
            <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-1.jpg"
               style="height:  100%;width: 100%;">
         </li>
         <li class="fln w100">
            <input <?php if ($t == 3) {
               echo "checked";
            } ?> type="radio" id="v2" class="cta-radio design_radio_btn_units"
               name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="3" />
            <label for="v2"><span></span><?php echo $label_language_values['service_unit_front_block_view']; ?></label>
            <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-2.jpg"
               style="height: 100%;width: 100%;">
         </li>
         <li class="fln w100">
            <input <?php if ($t == 4) {
               echo "checked";
            } ?> type="radio" id="v3" class="cta-radio design_radio_btn_units"
               name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="4" />
            <label
               for="v3"><span></span><?php echo $label_language_values['service_unit_front_increase_decrease_view']; ?></label>
            <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-3.jpg"
               style="height: 100%;width: 100%;">
         </li>
         <li class="fln w100">
            <input <?php if ($t == 5) {
               echo "checked";
            } ?> type="radio" id="v4" class="cta-radio design_radio_btn_units"
               name="rdbs" data-methodid="<?php echo $_POST['method_id']; ?>" value="5" />
            <label for="v4"><span></span><?php echo "Service Unit Front Square Meter View"; ?></label>
            <img src="<?php echo BASE_URL; ?>/assets/images/democheck/service-unit-design-4.jpg"
               style="height: 100%;width: 70%;">
         </li>
      </ul>
   </div>
<?php } ?>