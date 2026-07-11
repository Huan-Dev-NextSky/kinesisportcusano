<?php     
/*$service_array = array("method" => array());
$_SESSION['ct_cart'] = $service_array;
$_SESSION['freq_dis_amount'] = '';
$_SESSION['ct_details'] = '';
$_SESSION['time_duration'] = 0;*/

/*include(dirname(__FILE__) . "/objects/class_services.php");
include(dirname(__FILE__) . "/objects/class_users.php");
include(dirname(__FILE__) . '/objects/class_frequently_discount.php');
include(dirname(__FILE__) . '/objects/class_service_methods_design.php');
include(dirname(__FILE__) . '/objects/class_front_first_step.php');*/

/* NAME */
/*$objservice = new cleanto_services();
$objservice->conn = $conn;
$user = new cleanto_users();
$user->conn = $conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$frequently_discount = new cleanto_frequently_discount();
$frequently_discount->conn = $conn;
$objservice_method_design = new cleanto_service_methods_design();
$objservice_method_design->conn = $conn; 

$first_step=new cleanto_first_step();
$first_step->conn=$conn;*/
?>
  
  <?php   
  $ct_cart_scrollable_position = 'relative !important';
  
    echo "<style>
  /* primary color */
    .cleanto{
      color: " . $settings->get_option('ct_text_color') . " !important;
    }
    .cleanto .ct-link.ct-mybookings{
      color:" . $settings->get_option('ct_text_color_on_bg') . " !important;
      background:" . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto .ct-link.ct-mybookings:hover{
      color:" . $settings->get_option('ct_text_color_on_bg') . " !important;
      background:" . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .ct-main-left .ct-list-header .ct-logged-in-user a.ct-link,
    .cleanto .ct-complete-booking-main .ct-link,
    .cleanto .ct-discount-coupons a.ct-apply-coupon.ct-link{
      color: " . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .ct-link:hover,
    .cleanto .ct-main-left .ct-list-header .ct-logged-in-user a.ct-link:hover,
    .cleanto .ct-complete-booking-main .ct-link:hover,
    .cleanto .ct-discount-coupons a.ct-apply-coupon.ct-link:hover{
      color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto a,
    .cleanto .ct-link,
    .cleanto .ct-addon-count .ct-btn-group .ct-btn-text{
      color: " . $settings->get_option('ct_text_color') . " !important;
    }
    .cleanto a.ct-back-to-top i.icon-arrow-up,
    .cleanto .calendar-wrapper .calendar-header a.next-date:hover .icon-arrow-right:before,
    .cleanto .calendar-wrapper .calendar-header a.previous-date:hover .icon-arrow-left:before{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    .cleanto .calendar-body .ct-week:hover a span,
    .cleanto .ct-extra-services-list ul.addon-service-list li .ct-addon-ser:hover .addon-price{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    .cleanto #ct-type-2 .service-selection-main .ct-services-dropdown .ct-service-list:hover,
    .cleanto #ct-type-method .ct-services-method-dropdown .ct-service-method-list:hover,
    .cleanto .common-selection-main .common-data-dropdown .data-list:hover{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
      background:" . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .selected-is:hover,
    .cleanto #ct-type-2 .service-is:hover,
    .cleanto #ct-type-method .service-method-is:hover{
      border-color:" . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .ct-extra-services-list ul.addon-service-list li .ct-addon-ser:hover span:before{
      border-top-color:" . $settings->get_option('ct_primary_color') . " !important;
    }
    
    .cleanto .calendar-wrapper .calendar-header a.next-date:hover,
    .cleanto .calendar-wrapper .calendar-header a.previous-date:hover,
    .cleanto .calendar-body .ct-week:hover,
    #ct .bar:before, .bar:after{
      background:" . $settings->get_option('ct_secondary_color') . " !important;
    }
  
    .cleanto .calendar-body .dates .ct-week.by_default_today_selected.active_today span,
    .cleanto .calendar-body .ct-show-time .time-slot-container ul li.time-slot,
    .cleanto .calendar-body .dates .ct-week.active span {
      color:" . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    
    .cleanto .ct-custom-checkbox  ul.ct-checkbox-list label:hover span,
    .cleanto .ct-custom-radio ul.ct-radio-list label:hover span{
      border:1px solid " . $settings->get_option('ct_secondary_color') . " !important;
    }
    #ct-login .ct-main-forget-password .ct-info-btn,
    .cleanto button,
    .cleanto #ct-front-forget-password .ct-front-forget-password .ct-info-btn,  
    .cleanto .ct-button{
      color:" . $settings->get_option('ct_text_color_on_bg') . " !important;
      background:" . $settings->get_option('ct_primary_color') . " !important;
      border: 2px solid " . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .ct-display-coupon-code .ct-coupon-value{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
      background:" . $settings->get_option('ct_secondary_color') . " !important;
    }
    /* for front date legends */
    
    .cleanto .calendar-body .ct-show-time .time-slot-container .ct-slot-legends .ct-selected-new{
      background: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    /* seconday color */
    .nicescroll-cursors{
      background-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
        
      .cleanto .calendar-body .dates .ct-week.active{
        background: " . $settings->get_option('ct_secondary_color') . " !important;
      }
  /* background color all css  HOVER */
    
    .cleanto .ct-selected,
    .cleanto .ct-selected-data,
    .cleanto .edit-ct-provider-list ul.edit-provders-list li input[type='radio']:checked + lable span,
    .cleanto .ct-list-services ul.services-list li input[type='radio']:checked + lable span,
    .cleanto .ct-extra-services-list ul.addon-service-list li input[type='checkbox']:checked label span,
    .cleanto #ct-tslots .ct-date-time-main .time-slot-selection-main .time-slot.ct-selected,
    .cleanto .ct-button:hover,
    .cleanto-login .ct-main-forget-password .ct-info-btn:hover,
    .cleanto #ct-front-forget-password .ct-front-forget-password .ct-info-btn:hover,
    .cleanto  input[type='submit']:hover,
    .cleanto  input[type='reset']:hover,
    .cleanto  input[type='button']:hover,
    .cleanto  button:hover{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
      background: " . $settings->get_option('ct_secondary_color') . " !important;
      border-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto .ct-discount-list ul.ct-discount-often li input[type='radio']:checked + .ct-btn-discount{
      border-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto #ct-price-scroll{
      border-color: " . $settings->get_option('ct_primary_color') . " !important;
      box-shadow: 0px 0px 1px " . $settings->get_option('ct_primary_color') . " !important;
      position: ".$ct_cart_scrollable_position.";
    }
    
    .cleanto .ct-cart-wrapper .ct-cart-label-total-amount,
    .cleanto .ct-cart-wrapper .ct-cart-total-amount{
      color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    
    .cleanto .ct-list-services ul.services-list li input[type='radio']:checked + .ct-service ,
    .cleanto .edit-ct-provider-list ul.edit-provders-list li input[type='radio']:checked + .ct-provider ,
    .cleanto .ct-extra-services-list ul.addon-service-list li input[type='checkbox']:checked + .ct-addon-ser {
      border-color: " . $settings->get_option('ct_secondary_color') . " !important;
      box-shadow: 0 0 10px 1px " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto .ct-extra-services-list ul.addon-service-list li input[type='checkbox']:checked + .ct-addon-ser span:before{
      border-top-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    
    
    
    .cleanto .border-c:hover,
    .cleanto .ct-list-services ul.services-list li .ct-service:hover,
    .cleanto .ct-list-services ul.addon-service-list li .ct-addon-ser:hover,
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bedroom-box .ct-bedroom-btn:hover,
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bathroom-box .ct-bathroom-btn:hover,
    .cleanto #ct-duration-main.ct-service-duration .ct-duration-list .duration-box .ct-duration-btn:hover,
    .cleanto .ct-extra-services-list .ct-addon-extra-count .ct-common-addon-list .ct-addon-box .ct-addon-btn:hover,
    .cleanto .ct-discount-list ul.ct-discount-often li .ct-btn-discount:hover,
    .cleanto .ct-custom-radio ul.ct-radio-list label:hover span,
    .cleanto .ct-custom-checkbox  ul.ct-checkbox-list label:hover span{
      border-color: " . $settings->get_option('ct_primary_color') . " !important;
      
    }
    
    
    .cleanto .ct-custom-checkbox  ul.ct-checkbox-list input[type='checkbox']:checked + label span{
      border: 1px solid " . $settings->get_option('ct_secondary_color') . " !important;
      background: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto .ct-custom-radio ul.ct-radio-list input[type='radio']:checked + label span{
      border:5px solid " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bedroom-box .ct-bedroom-btn.ct-bed-selected,
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bathroom-box .ct-bathroom-btn.ct-bath-selected,
    .cleanto #ct-duration-main.ct-service-duration .ct-duration-list .duration-box .ct-duration-btn.duration-box-selected,
    .cleanto .ct-extra-services-list .ct-addon-extra-count .ct-common-addon-list .ct-addon-box .ct-addon-selected{
      background: " . $settings->get_option('ct_secondary_color') . " !important;
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
      border-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    
    .cleanto .ct-button.ct-btn-abs,
    .cleanto .panel-login .panel-heading .col-xs-6,
    .cleanto a.ct-back-to-top {
      background-color: " . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto a.ct-back-to-top:hover{
      background-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    
    .cleanto .calendar-body .dates .ct-week.by_default_today_selected{
      background-color: " . $settings->get_option('ct_primary_color') . " !important;
    }
    .cleanto .calendar-body .dates .ct-week.by_default_today_selected a span{
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    
    .cleanto .calendar-body .dates .ct-week.selected_date.active{
      background-color: " . $settings->get_option('ct_secondary_color') . " !important;
      border-bottom: thin solid " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .cleanto .calendar-body .ct-show-time .time-slot-container ul li.time-slot:hover,
    .cleanto .calendar-body .ct-show-time .time-slot-container ul li.time-slot.ct-booked{
      background-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    
    
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bedroom-box .ct-bedroom-btn.ct-bed-selected,
    .cleanto #ct-meth-unit-type-2.ct-meth-unit-count .bathroom-box .ct-bathroom-btn.ct-bath-selected,
    .cleanto #ct-duration-main.ct-service-duration .ct-duration-list .duration-box .ct-duration-btn.duration-box-selected,
    .cleanto .ct-extra-services-list .ct-addon-extra-count .ct-common-addon-list .ct-addon-box .ct-addon-selected{
      /* background: " . $settings->get_option('ct_secondary_color') . " !important; */
    }
    
    
    
    /* hover inputs */
    .cleanto input[type='text']:hover,
    .cleanto input[type='password']:hover,
    .cleanto input[type='email']:hover,
    .cleanto input[type='url']:hover,
    .cleanto input[type='tel']:hover,
    .cleanto input[type='number']:hover,
    .cleanto input[type='range']:hover,
    .cleanto input[type='date']:hover,
    .cleanto textarea:hover,
    .cleanto select:hover,
    .cleanto input[type='search']:hover,
    .cleanto input[type='submit']:hover,
    .cleanto input[type='button']:hover{
      border-color: " . $settings->get_option('ct_primary_color') . " !important;
    }
    
    /* Focus inputs */
    .cleanto input[type='text']:focus,
    .cleanto input[type='password']:focus,
    .cleanto input[type='email']:focus,
    .cleanto input[type='url']:focus,
    .cleanto input[type='tel']:focus,
    .cleanto input[type='number']:focus,
    .cleanto input[type='range']:focus,
    .cleanto input[type='date']:focus,
    .cleanto textarea:focus,
    .cleanto select:focus,
    .cleanto input[type='search']:focus,
    .cleanto input[type='submit']:focus,
    .cleanto input[type='button']:focus{
      border-color: " . $settings->get_option('ct_secondary_color') . " !important;
      /* box-shadow: 0 0 0 1.5px " . $settings->get_option('ct_secondary_color') . " inset !important; */
    }
    .cleanto .ct-tooltip-link {color: " . $settings->get_option('ct_secondary_color') . " !important;}
      /* for custom css option */
    ".$settings->get_option('ct_custom_css')."
    
    .cleanto .ct_method_tab-slider--nav .edit_ct_method_tab-slider-tabs {
      background: " . $settings->get_option('ct_primary_color') . " !important;
    }
    
    .cleanto .ct_method_tab-slider--nav .ct_method_tab-slider-trigger {
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    .cleanto .ct_method_tab-slider--nav .ct_method_tab-slider-trigger.active {
      color: " . $settings->get_option('ct_text_color_on_bg') . " !important;
    }
    .ct-list-services ul.services-list li input[type=\"radio\"]:checked + .ct-service::after{
      background-color: " . $settings->get_option('ct_secondary_color') . " !important;
    }
    .rating-md{
      font-size: 1.5em !important ;
      display: table;
      margin: auto;
    }
  </style>";
    ?>
    <script>
        jQuery(document).ready(function () {
            var $sidebar = jQuery("#ct-price-scroll"),
                $window = jQuery(window),
                offset = $sidebar.offset(),
                topPadding = 250;
            fulloffset = jQuery("#ct").offset();

            $window.scroll(function () {
                var color = jQuery('#color_box').val();
                jQuery("#ct-price-scroll").css({'box-shadow': '0px 0px 1px ' + color + '', 'position': 'absolute'});
            });
        });
    </script>
    <script type="text/javascript">
        function myFunction() {
            var input = document.getElementById('coupon_val')
            var div = document.getElementById('display_code');
            div.innerHTML = input.value;
        }
    </script>
  
<div class="ct-wrapper cleanto mb" id="ct"> <!-- main wrapper -->
    <div class="ct-main-wrapper">
      <div class="container">
        <!-- left side main booking form -->
      <div class="ct-main-left ct-sm-12 ct-md-12 ct-xs-12 mt-10 br-5 np">
                 <div class="panel-group" id="accordion">
          <div class="panel panel-default">
            <div class="panel-heading active">
              <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#ct-services-pm" class="choose_services_tab"><?php  echo $label_language_values['choose_service']; ?></a>
              </h4>
            </div>
            <div id="ct-services-pm" class="panel-collapse collapse in">
              <div class="panel-body">
                <div class="ct-list-services ct-common-box">
                  <div class="ct-list-header"></div>
                </div>
              
                <div class="ct-list-services ct-common-box fl hide_allsss">
                  
                  <input id="total_cart_count" type="hidden" name="total_cart_count" value='1'/>
                  <!-- area based select cleaning -->
                  <?php  
                  if ($settings->get_option('ct_service_default_design') == 1) {
                    ?>
                    <!-- 1.box style services selection radio selection -->
                    <ul class="services-list">
                      <?php  
                      $services_data = $objservice->readall_for_frontend_services();
                      if (mysqli_num_rows($services_data) > 0) {
                        while ($s_arr = mysqli_fetch_array($services_data)) {
                          ?>
                          <li 
                          <?php   if($settings->get_option('ct_company_service_desc_status') != "" &&  $settings->get_option('ct_company_service_desc_status') == "Y"){ ?>
                          
                          
                          title='<?php  echo $s_arr['description'];?>' class="ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 remove_service_class edit_ser_details ct-tooltip-services tooltipstered mb-20 edit_ser_details<?php  echo $s_arr['id'];?>"
                          <?php   } else {
                            echo "class='ct-sm-6 ct-md-4 ct-lg-3 ct-xs-12 remove_service_class edit_ser_details edit_ser_details".$s_arr['id']."'";                   
                          }  ?>
                            data-servicetitle="<?php  echo $s_arr['title']; ?>"
                            data-id="<?php  echo $s_arr['id']; ?>">
                            <input type="radio" name="service-radio"
                                 id="ct-service-<?php  echo $s_arr['id']; ?>"
                                 class="make_service_disable"/>
                            <?php 
                    if($settings->get_option('ct_service_design') == "ct_square"){
                          ?>
                    <label class="ct-service ct-service-square-main ct-service-embed border-c" for="ct-service-<?php  echo $s_arr['id']; ?>">
                            <?php } else{
                              
                              ?>
                              
                    <label class="ct-service ct-service-embed border-c" for="ct-service-<?php  echo $s_arr['id']; ?>">
                          <?php  
                            }
                              if ($s_arr['image'] == '') {

                                  $s_image = 'default_service.png';

                              } else {

                                  $s_image = $s_arr['image'];
                              }
                              ?>
                          <?php 
                        if($settings->get_option('ct_service_design') == "ct_square"){
                      ?>
                        <div class="ct-service-img-square">
                        <img class="ct-image" src="<?php  echo SITE_URL; ?>assets/images/services/<?php  echo $s_image; ?>"/>
                        </div>
                      <?php 
                        } else {
                      ?>
                        <div class="ct-service-img">
                        <img class="ct-image" src="<?php  echo SITE_URL; ?>assets/images/services/<?php  echo $s_image; ?>"/>
                        </div>
                      <?php 
                        }
                      ?>
                        
                     <div class="service-name fl ta-c"><?php  echo $s_arr['title']; ?></div>

                    </label>
                            
                            
                          </li>
                        <?php  
                        } ?>
                       <?php    } else {
                        ?>
                        <li class="ct-sm-12 ct-md-12 ct-xs-12 ct-no-service-box"><?php  echo $label_language_values['please_configure_first_cleaning_services_and_settings_in_admin_panel']; ?>
                        </li>
                      <?php  
                      }
                      ?>
                    </ul>
                    <!--  1 end box style service selection -->
                    <?php  
                    if (mysqli_num_rows($services_data) === 1){
                      $ser_arry = mysqli_fetch_array($services_data)
                      ?>
                      <script>
                      /** Make Service Selected **/
                      jQuery(document).ready(function() {
                        jQuery('.edit_ser_details').trigger('click');
                      });
                      </script>
                      <?php  
                    }
                  } else {
                    ?>
                    <input type="radio" style="display:none;" name="service-radio" id="ct-service-0" value='off' class="make_service_disable"/>
                    <!-- 2. sevice dropdown selection -->
                  <?php  
                    $services_data = $objservice->readall_for_frontend_services();
                    if (mysqli_num_rows($services_data) > 0) {
                      ?>
                      <label class="service_not_selected_error_d2" id="service_not_selected_error_d2"><?php  echo $label_language_values['please_select_service']; ?></label>
                      <div class="services-list-dropdown fl" id="ct-type-2">
                      <div class="service-selection-main">
                        <div class="service-is" title="<?php  echo $label_language_values['choose_your_service'];?>">
                          <div class="ct-service-list" id="ct_selected_service">
                            <i class="icon-settings service-image icons"></i>

                            <h3 class="service-name ser_name_for_error"><?php  echo $label_language_values['cleaning_service']; ?></h3>
                          </div>
                        </div>
                        <div class="ct-services-dropdown remove_service_data"> <?php  
                        if($services_data->num_rows > 0){
                          while ($s_arr = mysqli_fetch_array($services_data)) { ?>
                            <div class="ct-service-list select_service remove_service_class edit_ser_details"
                               data-servicetitle="<?php  echo $s_arr['title']; ?>"
                               data-id="<?php  echo $s_arr['id']; ?>">
                              <?php  
                              if ($s_arr['image'] == '') {
                                $s_image = 'default_service.png';
                              } else {
                                $s_image = $s_arr['image'];
                              }
                              ?>
                              <img class="service-image"
                                 src="<?php  echo SITE_URL; ?>assets/images/services/<?php  echo $s_image; ?>"
                                 title="<?php  echo $label_language_values['service_image']; ?>"/>

                              <h3 class="service-name"><?php  echo $s_arr['title']; ?></h3>
                            </div>
                          <?php   } }
                        ?></div>
                      </div> </div><?php 
                      if (mysqli_num_rows($services_data) === 1){
                          $st_arry = mysqli_fetch_array($services_data)
                          ?>
                          <script>
                          /** Make Service Selected **/
                          jQuery(document).ready(function() {
                            jQuery('.select_service').trigger('click');
                          });
                          </script>
                          <?php  
                        }
                    } else {
                      ?>
                      <div class="ct-sm-12 ct-md-12 ct-xs-12 ct-no-service-box"><?php  echo $label_language_values['please_configure_first_cleaning_services_and_settings_in_admin_panel']; ?></div>
                    <?php  
                    }
                    ?>
                  <!-- 2. end service dropdown selection -->
                  <?php  
                  }
                  ?>
                  <div class="ct-scroll-meth-unit"></div> 
                  <!-- <label class="method_not_selected_error show_methods_after_service_selection" id="method_not_selected_error"><?php  echo $label_language_values['please_select_method']; ?></label> -->

                  <div class="edit-services-method-list-dropdown fl show_methods_after_service_selection edit_show_single_service_method" id="ct-type-method">
                    <div class="service-method-selection-main">
                      <div class="ct-services-method-dropdown s_method_names">
                      </div>
                    </div>
                  </div>
                  <label class="empty_cart_error" id="empty_cart_error"></label>
                  <label class="no_units_in_cart_error" id="no_units_in_cart_error"></label>
                  <input type='hidden' id="no_units_in_cart_err" value=''>
                  <input type='hidden' id="no_units_in_cart_err_count" value=''>
                  <!-- hrs selected  -->
                  <div class="ct-service-duration ct-md-12 ct-sm-12 edit_s_m_units_design_1" id="ct-duration-main">
                    <div class="ct-inner-box border-c">

                      <div class="fl ct-md-12 mt-5 mb-15 np duration_hrs">
                      </div>
                      <!-- end duration hrs  -->
                    </div>
                  </div>
                  <!-- 1. bedroom and bathroom counting dropdown -->
                  <div class="ct-meth-unit-count ct-md-12 ct-sm-12 np ct-hidden fl edit_s_m_units_design_2"
                     id="ct-meth-unit-type-1">
                    <div class="ct-inner-box border-c ser_design_2_units">

                    </div>
                  </div>
                  <!-- 1.end dropdown list bathroom bedroom -->
                  <!-- 2. boxed bathroom bedroom  -->
                  <div class="ct-meth-unit-count ct-md-12 ct-sm-12 np edit_s_m_units_design_3" id="ct-meth-unit-type-2">
                    <div class="ct-inner-box border-c ser_design_3_units">

                    </div>
                  </div>
                  <!-- 2. end boxed bathroom bedroom -->

                  <div class="ct-meth-unit-count ct-md-12 ct-sm-12 edit_s_m_units_design_4" id="ct-meth-unit-type-3">
                    <div class="ct-inner-box border-c ">
                      <div class="fl ct-bedrooms ct-btn-group ct-md-12 mt-5 mb-15 np">
                        <div class="ct-inner-box border-c ser_design_4_units">

                        </div>
                      </div>
                    </div>
                  </div>


                </div>
                <!-- end service list -->


                <!-- Module third area based -->
                <div class="ct-list-services ct-common-box edit_s_m_units_design_5 ser_design_5_units">

                </div>
                <!-- end area based -->
                <!-- end module third area based -->

                <div class="ct-extra-services-list ct-common-box edit_add_on_lists edit_hide_allsss_addons">

                </div>
              <!-- how often discount -->
                <input type="hidden" name="frequently_discount_radio" class="edit_frequently_discount_radio" value="1" />
                <input type="hidden" name="payment_method" class="payment_method" value="" />
                <div class="edit-ct-provider-list ct-common-box">
                  <div class="ct-list-header">
                  <h3 class="header3 show_select_staff_title" style="display:none;"><?php  echo $label_language_values['please_select_provider'];?></h3>
                    <ul class="edit-provders-list"></ul>
                  </div>
                </div>
                <div class="ct-complete-booking-main ct-sm-12 ct-md-12 mb-30 ct-xs-12 hide_allsss">

                  <div class="ct-list-header ct-hidden">
                    <p class="ct-sub-complete-booking"></p>
                  </div>
                  <label class="ct_all_booking_errors ct-md-12 mt-30" style="display: none;"></label>
                  <div class="ta-center fl">
                    <a href="javascript:void(0)" type='submit' data-currency_symbol="<?php  echo $settings->get_option('ct_currency_symbol'); ?>" id='edit_complete_bookings' class="ct-button ct-btn-big ct_remove_id" data-order_id=""><?php  echo $label_language_values['complete_booking'];?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- start booking details -->
          <div class="ct-main-right ct-sm-8 ct-md-6 ct-xs-12 mt-30 mb-30 br-5 hide_allsss">
            <div class="fl">
              <div class="main-inner-container border-c ct-price-scroll" id="ct-price-scroll">
                <div class="ct-step-heading"><h3 class="header3"><?php  echo $label_language_values['booking_summary']; ?></h3></div>
                <div class="ct-cart-wrapper f-l" id="">
                  <div class="ct-summary hideservice_name">
                    <div class="ct-image">
                      <img src="<?php  echo SITE_URL; ?>/assets/images/icon-service.png" alt="">
                    </div>
                    <p class="ct-text sel-service"></p>
                  </div>
                  <div class="ct-summary hidedatetime_value">
                    <div class="ct-image">
                      <img src="<?php  echo SITE_URL; ?>/assets/images/icon-calendar.png" alt="">
                    </div>
                    <p class="ct-text sel-datetime"><span class='cart_date' data-date_val=""></span><span class="space_between_date_time"> @ </span><span class='cart_time' data-time_val=""></span></p>
                  </div>
                  <?php  
                  if($settings->get_option("ct_recurrence_booking_status") == "Y"){
                    $count_f_dis = $frequently_discount->readall_front();
                    if (mysqli_num_rows($count_f_dis) > 0) {
                      ?>
                      <div class="ct-summary">
                        <div class="ct-image f_dis_img">
                          <img src="<?php  echo SITE_URL; ?>/assets/images/icon-frequency.png" alt="">
                        </div>
                        <p class="ct-text sel-datetime f_discount_name"></p>
                      </div>
                    <?php     
                    }
                  }
                  ?>
                  <div class="ct-summary hideduration_value <?php   if ($settings->get_option('ct_show_time_duration') == 'N') {echo "force_hidden";} ?>">
                    <div class="ct-image total_time_duration">
                      <img src="<?php  echo SITE_URL; ?>/assets/images/icon-timer.png" alt="">
                    </div>
                    <p class="ct-text total_time_duration_text"></p>
                  </div>
                  <div class="ct-form-rown ct-addons-list-main mbi-30">
                    <div class="step_heading f-l"><h6 class="header6 ct-item-list"><?php  echo $label_language_values['cart_items']; ?></h6>
                    </div>
                    <div class="cart-items-main f-l">
                      <label class="edit_cart_empty_msg"><?php  echo $label_language_values['cart_is_empty']; ?></label>
                      <ul class="ct-addon-items-list edit_cart_service_item_listing">

                      </ul>
                    </div>
                  </div>
                  <div class="ct-form-rown">
                    <div class="ct-cart-label-common ofh"><?php  echo $label_language_values['sub_total']; ?></div>
                    <div class="ct-cart-amount-common ofh">
                      <span class="ct-sub-total cart_sub_total"></span>
                    </div>
                  </div>
                  <?php  
                  if($settings->get_option("ct_recurrence_booking_status") == "Y"){
                    $count_f_dis = $frequently_discount->readall_front();
                    if (mysqli_num_rows($count_f_dis) > 0) {
                        ?>
                        <div class="ct-form-rown freq_discount_display">
                            <div class="ct-cart-label-common ofh"><?php  echo ucwords(strtolower($label_language_values['frequently_discount'])); ?></div>
                            <div class="ct-cart-amount-common ofh">
                                <span class="ct-frequently-discount frequent_discount"></span>
                            </div>
                        </div>
                    <?php     
                    }
                  }
                  if ($settings->get_option('ct_show_coupons_input_on_checkout') == 'on') {
                    ?>
                    <div class="ct-form-rown coupon_display">
                      <div class="ct-cart-label-common ofh"><?php  echo $label_language_values['coupon_discount']; ?></div>
                      <div class="ct-cart-amount-common ofh">
                        <span class="ct-coupon-discount cart_discount"></span>
                      </div>
                    </div>
                  <?php  
                  }
                  ?>
                  <?php  
                  if ($settings->get_option('ct_tax_vat_status') == 'Y') {
                    ?>
                    <div class="ct-form-rown">
                      <div class="ct-cart-label-common ofh"><?php  echo $label_language_values['tax']; ?></div>
                      <div class="ct-cart-amount-common ofh">
                        <span class="ct-tax-amount cart_tax"></span>
                      </div>
                    </div>
                  <?php  
                  }
                  
                  if ($settings->get_option('ct_partial_deposit_status') == 'Y') {

                    ?>

                    <div class="ct-form-rown partial_amount_hide_on_load mb-15">

                      <div class="ct-partial-amount-wrapper border-c border-2">

                        <div class="ct-partial-amount-message">

                          <?php   echo $settings->get_option('ct_partial_deposit_message'); ?>

                        </div>

                        <div class="ct-form-rown">

                          <div class="ct-cart-label-common ofh"><?php  echo $label_language_values['partial_deposit']; ?></div>

                          <div class="ct-cart-amount-common ofh">

                            <span class="ct-partial-deposit partial_amount"></span>

                          </div>

                        </div>

                        <div class="ct-form-rown">

                          <div class="ct-cart-label-common ofh"><?php  echo $label_language_values['remaining_amount']; ?></div>

                          <div class="ct-cart-amount-common ofh">

                            <span class="ct-remaining-amount remain_amount"></span>

                          </div>

                        </div>

                      </div>

                    </div>

                  <?php  

                  }

                  ?>
                  <div class="ct-clear"></div>
                  <div id="ct-line"></div>
                  <div class="ct-form-rown">
                    <div class="ct-cart-label-total-amount ofh"><?php  echo $label_language_values['total']; ?></div>
                    <div class="ct-cart-total-amount ofh">
                      <span class="ct-total-amount cart_total"></span>
                    </div>
                  </div>

                  <div class="ct-clear"></div>
                  <!-- discount coupons -->
                </div>
                <!-- cart wrapper end here -->


              </div>
            </div>
          </div><!-- booking details -->
        </div>
        
                
            </div>
            <!-- left side end -->
        </div>
        <!-- end container -->
    </div>
</div>
<script>
  
    var baseurlObj = {'base_url': '<?php  echo BASE_URL;?>'};
    var siteurlObj = {'site_url': '<?php  echo SITE_URL;?>'};
    var ajaxurlObj = {'ajax_url': '<?php  echo AJAX_URL;?>'};
    var fronturlObj = {'front_url': '<?php  echo FRONT_URL;?>'};
    var termsconditionObj = {'terms_condition': '<?php  echo $settings->get_option('ct_allow_terms_and_conditions');?>'};
    var privacypolicyObj = {'privacy_policy': '<?php  echo $settings->get_option('ct_allow_privacy_policy');?>'};
    <?php  
    
  if($settings->get_option('ct_thankyou_page_url') == ''){
        $thankyou_page_url = SITE_URL.'front/thankyou.php';
  }else{
      $thankyou_page_url = $settings->get_option('ct_thankyou_page_url');
  }
  $phone = explode(",",$settings->get_option('ct_bf_phone'));
  $check_password = explode(",",$settings->get_option('ct_bf_password'));
  $check_fn = explode(",",$settings->get_option('ct_bf_first_name'));
  $check_ln = explode(",",$settings->get_option('ct_bf_last_name'));
  $check_addresss = explode(",",$settings->get_option('ct_bf_address'));
  $check_zip_code = explode(",",$settings->get_option('ct_bf_zip_code'));
  $check_city = explode(",",$settings->get_option('ct_bf_city'));
  $check_state = explode(",",$settings->get_option('ct_bf_state'));
  $check_notes = explode(",",$settings->get_option('ct_bf_notes'));
   
    ?>
  var thankyoupageObj = {'thankyou_page': '<?php  echo $thankyou_page_url;?>'};
    
  var phone_status = {'statuss' : '<?php  echo $phone[0];?>','required' : '<?php  echo $phone[1];?>','min' : '<?php  echo $phone[2];?>','max' : '<?php  echo $phone[3];?>'};  
  
  var check_password = {'statuss' : '<?php  echo $check_password[0];?>','required' : '<?php  echo $check_password[1];?>','min' : '<?php  echo $check_password[2];?>','max' : '<?php  echo $check_password[3];?>'};
    
  var check_fn = {'statuss' : '<?php  echo $check_fn[0];?>','required' : '<?php  echo $check_fn[1];?>','min' : '<?php  echo $check_fn[2];?>','max' : '<?php  echo $check_fn[3];?>'};
    
  var check_ln = {'statuss' : '<?php  echo $check_ln[0];?>','required' : '<?php  echo $check_ln[1];?>','min' : '<?php  echo $check_ln[2];?>','max' : '<?php  echo $check_ln[3];?>'};
    
  var check_addresss = {'statuss' : '<?php  echo $check_addresss[0];?>','required' : '<?php  echo $check_addresss[1];?>','min' : '<?php  echo $check_addresss[2];?>','max' : '<?php  echo $check_addresss[3];?>'};
    
  var check_zip_code = {'statuss' : '<?php  echo $check_zip_code[0];?>','required' : '<?php  echo $check_zip_code[1];?>','min' : '<?php  echo $check_zip_code[2];?>','max' : '<?php  echo $check_zip_code[3];?>'};
    
  var check_city = {'statuss' : '<?php  echo $check_city[0];?>','required' : '<?php  echo $check_city[1];?>','min' : '<?php  echo $check_city[2];?>','max' : '<?php  echo $check_city[3];?>'};
    
  var check_state = {'statuss' : '<?php  echo $check_state[0];?>','required' : '<?php  echo $check_state[1];?>','min' : '<?php  echo $check_state[2];?>','max' : '<?php  echo $check_state[3];?>'};
  
  var check_notes = {'statuss' : '<?php  echo $check_notes[0];?>','required' : '<?php  echo $check_notes[1];?>','min' : '<?php  echo $check_notes[2];?>','max' : '<?php  echo $check_notes[3];?>'}; 
    <?php  
  $nacode = explode(',',$settings->get_option("ct_company_country_code"));
  $allowed = $settings->get_option("ct_phone_display_country_code");
  ?>
  var countrycodeObj = {'numbercode': '<?php  echo $nacode[0];?>', 'alphacode': '<?php  echo $nacode[1];?>', 'countrytitle': '<?php  echo $nacode[2];?>', 'allowed': '<?php  echo $allowed;?>'};
 
    var subheaderObj = {'subheader_status': '<?php  echo $settings->get_option('ct_subheaders');?>'};
    
  var appoint_details = {'status':'<?php  echo $settings->get_option('ct_appointment_details_display');?>'};

  <?php   
  $t_zone_value = $settings->get_option('ct_timezone');
  $server_timezone = date_default_timezone_get();
  if(isset($t_zone_value) && $t_zone_value!=''){
    $offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
    $timezonediff = $offset/3600;  
  }else{
    $timezonediff =0;
  }
  if(is_numeric(strpos($timezonediff,'-'))){
    $timediffmis = str_replace('-','',$timezonediff)*60;
    $currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
  }else{
    $timediffmis = str_replace('+','',$timezonediff)*60;
    $currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
  } 
  $current_date = date('Y-m-d',$currDateTime_withTZ);

  $advance_booking_time = $settings->get_option('ct_max_advance_booking_time');

  $advance_date = date('Y-m-d', strtotime("-1 day",strtotime("+".$advance_booking_time." months", $currDateTime_withTZ)));
  ?>
  var current_date = '<?php  echo $current_date; ?>';
  var advance_date = '<?php  echo $advance_date; ?>';
</script>
</body>
</html>