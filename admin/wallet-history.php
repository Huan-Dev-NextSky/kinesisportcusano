<?php 

include(dirname(__FILE__).'/header.php');
include(dirname(__FILE__).'/admin_session_check.php');
include(dirname(dirname(__FILE__))  . "/objects/class_userdetails.php");
include(dirname(dirname(__FILE__))  . "/objects/class_booking.php");
include_once(dirname(dirname(__FILE__))  . '/objects/class_front_first_step.php');
include(dirname(dirname(__FILE__))."/objects/class_rating_review.php");
include(dirname(dirname(__FILE__))."/objects/class_frequently_discount.php");
include(dirname(dirname(__FILE__))."/objects/class_order_client_info.php");
if(!isset($_SESSION['ct_login_user_id'])){
  header('Location:'.SITE_URL."admin/");
}

   
$con = new cleanto_db();
$conn = $con->connect();
$objuserdetails = new cleanto_userdetails();
$objuserdetails->conn = $conn;
$booking = new cleanto_booking();
$booking->conn = $conn;
$setting = new cleanto_setting();
$setting->conn = $conn;
$general=new cleanto_general();
$general->conn=$conn;
$first_step=new cleanto_first_step();
$first_step->conn=$conn;
$rating_review = new cleanto_rating_review();
$rating_review->conn = $conn;
$frequently_discount=new cleanto_frequently_discount();
$frequently_discount->conn=$conn;
$objocinfo=new cleanto_order_client_info();
$objocinfo->conn=$conn;
$symbol_position=$setting->get_option('ct_currency_symbol_position');
$decimal=$setting->get_option('ct_price_format_decimal_places');
$getdateformat=$setting->get_option('ct_date_picker_date_format');
$time_format = $setting->get_option('ct_time_format');
$date_format=$setting->get_option('ct_date_picker_date_format');
$getmaximumbooking = $setting->get_option('ct_max_advance_booking_time');
$currency_symbol = $setting->get_option('ct_currency_symbol');

$t_zone_value = $setting->get_option('ct_timezone');
$server_timezone = date_default_timezone_get();
if(isset($t_zone_value) && $t_zone_value!=''){
  $offset= $first_step->get_timezone_offset($server_timezone,$t_zone_value);
  $timezonediff = $offset/3600;
}else{
  $timezonediff =0;
}


if($setting->get_option('ct_stripe_payment_form_status') == 'on'){  ?>

  <script src="https://js.stripe.com/v2/" type="text/javascript"></script>  

   <?php  } 
		 
		 
if(is_numeric(strpos($timezonediff,'-'))){
  $timediffmis = str_replace('-','',$timezonediff)*60;
  $currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
}else{
  $timediffmis = str_replace('+','',$timezonediff)*60;
  $currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
}

?>

<div id="cta-user-appointments">
  <div class="panel-body">
    <div class="tab-content">
      <?php
        if(isset($_SESSION['ct_login_user_id'])){
          $id = $_SESSION['ct_login_user_id'];
          $objuserdetails->id = $id;
          $wallet_details = $objuserdetails->get_user_wallet_details();
        }
      ?>
        <h4 class="header4" style="margin-bottom: 10px;"><?php echo $label_language_values['wallet_amount'];?> ( <?php echo  $currency_symbol.$wallet_details[0]; ?>  )</h4>
        <button type="button" class="btn btn-info amt-add ml-15" data-toggle="modal" data-target="#myModal_payment_add"><?php echo $label_language_values['add_amount'];?></button> 
        <h4 class="header4"><?php echo $label_language_values['my_appointments'];?>
         <a href="<?php echo SITE_URL; ?>" class="btn btn-success pull-right" target="_BLANK"><?php echo $label_language_values['book_appointment'];?></a>
        </h4>

        <form>
              <div class="table-responsive">
                <table id="user-profile-booking-table" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                  <thead>
                  <tr>
                    <th><?php echo "Status";?></th>
                    <th><?php echo "Date/Time";?></th>
                    <th><?php echo "Wallet Method";?></th>
                    <th><?php echo "Transaction Id";?></th>
                    <th><?php echo $label_language_values['wallet_amount'];?></th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php 
                    if(isset($_SESSION['ct_login_user_id'])){
                    $id = $_SESSION['ct_login_user_id'];
                    $objuserdetails->id = $id;
                    $details = $objuserdetails->get_wallet_history_details();
                    if($details->num_rows > 0){
                    while($dd = mysqli_fetch_array($details)){
                       $bt = date("Y-m-d H:i:s",strtotime($dd['booking_date_time'])); 
                        ?>
                    <tr>
                      <td><?php echo ($dd['wallet_amount_status']=="C")?'Credit':'Debit';?></td>
                      <td><?php if(isset($dd['lastmodify'])){ echo $dd['lastmodify']; }else{ } ?></td>
                      <td><?php if(isset($dd['wallet_method'])){ echo $dd['wallet_method']; }else{ } ?></td>
                      <td><?php if(isset($dd['wallet_trans_id'])){ echo $dd['wallet_trans_id']; }else{ } ?></td>
                      <td style="font-weight:bold;color:<?php echo ($dd['wallet_amount_status']=="C")?'Green':'Red';?>">
                      <?php if(isset($dd['wallet_amount_status']) == "C"){
                          echo "<i class='fa fa-arrow-down' aria-hidden='true'></i>".$currency_symbol.$dd['wallet_amount'];
                      }else{
                          echo "<i class='fa fa-arrow-up' aria-hidden='true'></i>".$currency_symbol.$dd['wallet_amount'];     
                      } ?>
                      </td>
                    </tr>
                    <?php 
                    } }
                    ?>
                  </tbody>
                </table>
              </div>
                <?php 
                  $details = $objuserdetails->get_user_details();
                  if($details->num_rows > 0){
                  while($dd = mysqli_fetch_array($details)){
                ?>
                  <div id="user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="user-booking-details modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title"><?php echo $label_language_values['my_appointments'];?></h4>
                        </div>
                        <div class="modal-body">
                          <div class="table-responsive">
                            <table id="user-all-bookings-details" class="table table-striped table-bordered responsive nowrap" cellspacing="0" width="100%">
                              <thead>
                              <tr >
                                <th><?php echo $label_language_values['order'];?>#</th>
                                <th><?php echo $label_language_values['service'];?></th>
                                <th style="width: 140px;"><?php echo $label_language_values['booking_date_and_time'];?></th>
                                <th style="width: 230px;"><?php echo $label_language_values['more_details'];?></th>
                                <th><?php echo $label_language_values['status'];?></th>
                                <th><?php echo $label_language_values['actions'];?></th>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td><?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?></td>
                                <td><?php echo $dd['title'];?></td>
                                <?php 
                                if($time_format == 12){
                                  ?>
                                  <td><?php echo str_replace($english_date_array,$selected_lang_label,date($getdateformat." h:i A",strtotime($dd['booking_date_time'])));?></td>
                                <?php 
                                }else{
                                  ?>
                                  <td><?php echo str_replace($english_date_array,$selected_lang_label,date($getdateformat." H:i",strtotime($dd['booking_date_time'])));?></td>
                                <?php 
                                }
                                ?>
                                <td>
                                  <?php 
                                  /* methods */
                                  $units = "None";
                                  $methodname="None";
                                  $hh = $booking->get_methods_ofbookings($dd['order_id']);
                                  
                                  $hh = $booking->get_methods_ofbookings($dd['order_id']);
                                  $count_methods = mysqli_num_rows($hh);
                                  $hh1 = $booking->get_methods_ofbookings($dd['order_id']);
                                  if($count_methods > 0){
                                    while($jj = mysqli_fetch_array($hh1)){
                                      if($units == "None"){
                                        $units = $jj['units_title']."-".$jj['qtys'];
                                      }
                                      else
                                      {
                                        $units = $units.",".$jj['units_title']."-".$jj['qtys'];
                                      }
                                      $methodname = $jj['method_title'];
                                    }
                                  }
                                  $addons = "None";
                                  $hh = $booking->get_addons_ofbookings($dd['order_id']);
                                  while($jj = mysqli_fetch_array($hh)){
                                    if($addons == "None"){
                                      $addons = $jj['addon_service_name']."-".$jj['addons_service_qty'];
                                    }
                                    else
                                    {
                                      $addons = $addons.",".$jj['addon_service_name']."-".$jj['addons_service_qty'];
                                    }
                                  }
                                  ?>
                                  <b><?php echo $label_language_values['methods'];?></b> - <?php  echo $methodname;?>
                                  <br>
                                  <b><?php echo $label_language_values['units'];?></b> - <?php  echo $units;?>
                                  <br>
                                  <b><?php echo $label_language_values['add_ons'];?></b> - <?php  echo $addons;?>
                                </td>
                                <td class="txt-success"><?php
                                  if($dd['booking_status']=='A')
                                  {
                                  $booking_stats=$label_language_values['active'];
                                  }
                                  elseif($dd['booking_status']=='C')
                                  {
                                    $booking_stats='<i class="fa fa-check txt-success">'.$label_language_values['confirm'].'</i>';
                                  }
                                  elseif($dd['booking_status']=='R')
                                  {
                                    $booking_stats='<i class="fa fa-ban txt-danger">'.$label_language_values['reject'].'</i><br><b class="txt-danger">Reason : '.$dd['reject_reason'].'</b>';
                                  }
                                  elseif($dd['booking_status']=='RS')
                                  {
                                    $booking_stats='<i class="fa fa-pencil-square-o txt-info">'.$label_language_values["rescheduled"].'</i>';
                                  }
                                  elseif($dd['booking_status']=='CC')
                                  {
                                    $booking_stats='<i class="fa fa-times txt-primary">'.$label_language_values['cancel_by_client'].'</i>';
                                  }
                                  elseif($dd['booking_status']=='CS')
                                  {
                                    $booking_stats='<i class="fa fa-times-circle-o txt-info">'.$label_language_values['cancelled_by_service_provider'].'</i>';
                                  }
                                  elseif($dd['booking_status']=='CO')
                                  {
                                    $booking_stats='<i class="fa fa-thumbs-o-up txt-success">'.$label_language_values['completed'].'</i>';
                                  }
                                  else
                                  {
                                    $dd['booking_status']=='MN';
                                    $booking_stats='<i class="fa fa-thumbs-o-down txt-danger">'.$label_language_values['mark_as_no_show'].'</i>';
                                  }
                                  ?>
                                  <?php  echo $booking_stats;?>
                                </td>
                                <td>
                                  <?php   
                                  $t_zone_value = $setting->get_option('ct_timezone');
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
                                  $current_times = date('Y-m-d H:i:s',$currDateTime_withTZ);
                                  $td = date('Y-m-d H:i:s',strtotime($current_times));
                                  $bt = date("Y-m-d H:i:s",strtotime($dd['booking_date_time'])); 
                                  if($bt<$td)
                                  {
                                    ?>
                                    <a  class="btn btn-danger"  rel="popover"  ><i class="fa fa-check"></i><?php echo $label_language_values['completed'];?></a>
                                  <?php 
                                  }
                                  else
                                  {
                                    if($dd['booking_status'] == 'A' || $dd['booking_status'] == 'C' || $dd['booking_status'] == 'RS'){
                                      $booking_start_datetime=strtotime(date('Y-m-d H:i:s',strtotime($dd['booking_date_time'])));
                                      $reschedule_buffer_time=$setting->get_option('ct_reshedule_buffer_time');
                                      $cancellation_buffer_time=$setting->get_option('ct_cancellation_buffer_time');
                                      $t_zone_value = $setting->get_option('ct_timezone');
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
                                      $current_times = date('Y-m-d H:i:s',$currDateTime_withTZ);
                                      $current_time = strtotime($current_times);
                                      $remain_times=$booking_start_datetime - $current_time;
                                      $time_in_min=round($remain_times / 60 );
                                      if($time_in_min > $reschedule_buffer_time){
                                        ?>
                                        <a data-toggle="modal" href="javascript:void(0)" data-total_price="<?php echo $general->ct_price_format($dd['total_payment'],$symbol_position,$decimal);?>" data-target="#update-user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"  class="btn btn-success display_myappointment_data" data-order_id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"><i class="fa fa-repeat"></i><?php echo $label_language_values['reschedule'];?></a>
                                      <?php 
                                      }else{
                                        if($booking_start_datetime > $current_time){
                                          ?>
                                          <a href="javascript:void(0)" class="btn btn-success"><i class="fa fa-repeat"></i><?php echo $label_language_values['cannot_reschedule_now'];?></a>
                                        <?php 
                                        }else{
                                          echo '';
                                        }
                                      }
                                      ?>
                                      <?php 
                                      if($time_in_min > $cancellation_buffer_time){
                                        ?>
                                        <a id="ct-user-cancel-appointment<?php  echo $dd['order_id']?>" data-id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="btn btn-danger cancel_appointment"  rel="popover" data-placement='left' title="<?php echo $label_language_values['booking_cancel_reason'];?>?"><i class="fa fa-ban"></i><?php echo $label_language_values['cancel'];?></a>
                                      <?php 
                                      }else{
                                        if($booking_start_datetime > $current_time){
                                          ?>
                                          <a class="btn btn-danger" href="javascript:void(0)"><i class="fa fa-ban"></i><?php echo $label_language_values['cannot_cancel_now'];?></a>
                                        <?php 
                                        }else{
                                          echo '';
                                        }
                                      }
                                      ?>
                                      <div id="popover-user-cancel-appointment<?php  echo $dd['order_id']?>" style="display: none;">
                                        <div class="arrow"></div>
                                        <table class="form-horizontal" cellspacing="0">
                                          <tbody>
                                          <tr>
                                            <td>
                                              <textarea class="form-control" id="reason_cancel<?php  echo $dd['order_id']?>" name="" placeholder="<?php echo $label_language_values['booking_cancel_reason'];?>" required="required" ></textarea>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <a data-id="<?php echo $dd['order_id']?>" data-gc_event="<?php if(isset($dd['gc_event_id'])){ echo $dd['gc_event_id']; }else{ } ?>" data-gc_staff_event="<?php if(isset($dd['gc_staff_event_id'])){ echo $dd['gc_staff_event_id']; }else{ } ?>" data-pid="<?php if(isset($dd['staff_ids'])){ echo $dd['staff_ids']; }else{ } ?>" value="Delete" class="btn btn-danger btn-sm mybtncancel_booking_user_details"><?php echo $label_language_values['yes'];?></a>
                                              <a id="ct-close-user-cancel-appointment" class="btn btn-default btn-sm" href="javascript:void(0)"><?php echo $label_language_values['cancel'];?></a>
                                            </td>
                                          </tr>
                                          </tbody>
                                        </table>
                                      </div><!-- end pop up -->
                                    <?php 
                                    }else{
                                      echo '';
                                    }
                                  }
                                  ?>
                                </td>
                              </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php 
                  }
                } }
                ?>
          <?php 
          if(isset($_SESSION['ct_login_user_id'])){
            $details = $objuserdetails->get_user_details();
            while($dd = mysqli_fetch_array($details)){
          ?>
          <div id="update-user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="vertical-alignment-helper">
              <div class="modal-dialog modal-md vertical-align-center">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"><?php echo $label_language_values['appointment_details'];?></h4>
                  </div>
                  <div class="modal-body">
                    <div class="tab-content">
                      <div class="tab-pane fade in active">
                        <table>
                          <tbody>
                            <tr>
                              <td><label for="ct-service-duration"><?php echo $label_language_values['amount'];?></label></td>
                              <td>
                                <div class="cta-col6 ct-w-50 ">
                                  <div class="form-control booking_total_payment" readonly="readonly">
                                  </div>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td><label for="ct-service-duration"><?php echo $label_language_values['date_and_time'];?></label></td>
                              <td>
                                <div class="cta-col6 ct-w-50">
                                  <?php  $dates = date("Y-m-d",strtotime($dd['booking_date_time']));
                                  $slot_timess = date('H:i',strtotime($dd['booking_date_time']));

                                  $get_staff_id = $booking->get_staff_ids_from_bookings($dd['order_id']); 
                                  
                                  if($get_staff_id==""){
                                    $staff_id=1;
                                  }else{
                                    $staff_id = $get_staff_id;
                                  }

                                  ?>
                                  <input class="exp_cp_date form-control" id="expiry_date<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-staffid="<?php echo $staff_id; ?>" value= "<?php echo $dates;?>" data-date-format="yyyy/mm/dd" data-provide="datepicker" />
                                </div>
                                <div class="cta-col6 ct-w-50 float-right mytime_slots_booking">
                                  <?php 
                                  $t_zone_value = $setting->get_option('ct_timezone');
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
                                  $select_time=date('Y-m-d',strtotime($dates));
                                  $start_date = date($select_time,$currDateTime_withTZ);
                                  $time_interval = $setting->get_option('ct_time_interval');
                                  $time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
                                  $advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
                                  $ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
                                  $ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
                                  $booking_padding_time = $setting->get_option('ct_booking_padding_time');
                                  $reschedule = "No";
                                  $client_order_id = 0;
                                  $time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type,$start_date,$time_interval,$staff_id,$client_order_id,$reschedule,$advance_bookingtime,$ct_service_padding_time_before,$ct_service_padding_time_after,$timezonediff,$booking_padding_time);
                                  $allbreak_counter = 0;
                                  $allofftime_counter = 0;
                                  $slot_counter = 0;
                                  ?>
                                  <select class="selectpicker mydatepicker_appointment   form-control" id="myuser_reschedule_time" data-size="10" style="" >
                                      <?php 
                                      if($time_schedule['off_day']!=true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots'])>0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots'])){
                                          foreach($time_schedule['slots']  as $slot) {
                                              $ifbreak = 'N';
                                              /* Need to check if the appointment slot come under break time. */
                                              foreach($time_schedule['breaks'] as $daybreak) {
                                                  if(strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end'])) {
                                                      $ifbreak = 'Y';
                                                  }
                                              }
                                              /* if yes its break time then we will not show the time for booking  */
                                              if($ifbreak=='Y') { $allbreak_counter++; continue; }
                                              $ifofftime = 'N';
                                              foreach($time_schedule['offtimes'] as $offtime) {
                                                  if(strtotime($dates.' '.$slot) >= strtotime($offtime['offtime_start']) && strtotime($dates.' '.$slot) < strtotime($offtime['offtime_end'])) {
                                                      $ifofftime = 'Y';
                                                  }
                                              }
                                              /* if yes its offtime time then we will not show the time for booking  */
                                              if($ifofftime=='Y') { $allofftime_counter++; continue; }
                                              $complete_time_slot = mktime(date('H',strtotime($slot)),date('i',strtotime($slot)),date('s',strtotime($slot)),date('n',strtotime($time_schedule['date'])),date('j',strtotime($time_schedule['date'])),date('Y',strtotime($time_schedule['date'])));
                                              if($setting->get_option('ct_hide_faded_already_booked_time_slots')=='on' && in_array($complete_time_slot,$time_schedule['booked'])) {
                                                  continue;
                                              }
                                              if( in_array($complete_time_slot,$time_schedule['booked']) && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status')!='Y') ) { ?>
                                                  <?php 
                                                  if($setting->get_option('ct_hide_faded_already_booked_time_slots')=="on"){
                                                      ?>
                                                      <option value="<?php echo date("H:i",strtotime($slot));?>" <?php  if(date("H:i",strtotime($slot)) == $slot_timess){ echo "selected";}?> class="time-slot br-2 ct-booked" >
                                                          <?php 
                                                          if($setting->get_option('ct_time_format')==24){
                                                              echo date("H:i",strtotime($slot));
                                                          }else{
                                                             echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
                                                          }?>
                                                      </option>
                                                  <?php 
                                                  }
                                                  ?>
                                              <?php 
                                              } else {
                                                  if($setting->get_option('ct_time_format')==24){
                                                      $slot_time = date("H:i",strtotime($slot));
                                                  }else{
                                                      $slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));
                                                  }
                                                  ?>
                                                  <option value="<?php echo date("H:i",strtotime($slot));?>" <?php  if(date("H:i",strtotime($slot)) == $slot_timess){ echo "selected";}?> class="time-slot br-2 <?php  if(in_array($complete_time_slot,$time_schedule['booked'])){ echo' ct-booked';}else{ echo ' time_slotss'; }?>" <?php  if(in_array($complete_time_slot,$time_schedule['booked'])){echo ''; }else{ echo 'data-slot_date_to_display="'.date($date_format,strtotime($dates)).'" data-slot_date="'.$dates.'" data-slot_time="'.$slot_time.'"'; } ?>><?php if($setting->get_option('ct_time_format')==24){echo date("H:i",strtotime($slot));}else{echo str_replace($english_date_array,$selected_lang_label,date("h:i A",strtotime($slot)));}?></option>
                                              <?php 
                                              } $slot_counter++;
                                          }
                                          if($allbreak_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots'])!=0){ ?>
                                              <option  class="time-slot"><?php echo "Sorry Not Available ";?></option>
                                          <?php  }
                                      } else {?>
                                          <option class="time-slot"><?php echo "Sorry Not Available";?></option>
                                      <?php  } ?>
                                  </select>
                                </div>
                              </td>
                            </tr>
                            <?php 
                            $userinfo =  $objuserdetails->get_user_notes($dd['order_id']);
                            $temppp= unserialize(base64_decode($userinfo[0]));
                            $tem = str_replace('\\','',$temppp);
                            $finalnotes = $tem['notes'];
                            ?>
                            <tr>
                                <td><?php echo $label_language_values['notes'];?></td>
                                <td><textarea class="form-control my_user_notes_reschedule<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"><?php echo $finalnotes;?></textarea></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <div class="cta-col12 ct-footer-popup-btn">
                      <div class="cta-col6">
                        <button type="button" data-order="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="btn btn-info my_user_btn_for_reschedule" data-gc_event="<?php if(isset($dd['gc_event_id'])){ echo $dd['gc_event_id']; }else{ } ?>" data-gc_staff_event="<?php if(isset($dd['gc_staff_event_id'])){ echo $dd['gc_staff_event_id']; }else{ } ?>" data-pid="<?php if(isset($dd['staff_ids'])){ echo $dd['staff_ids']; }else{ } ?>"><?php echo $label_language_values['update_appointment'];?></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
<?php  
  }
}
?>
    </div>
    </form>
        
    </div>
    <!-- Modal -->
    <div id="myModal_payment_add" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <?php
              if(isset($_SESSION['ct_login_user_id'])){
                $id = $_SESSION['ct_login_user_id'];
                $objuserdetails->id = $id;
                $wallet_details = $objuserdetails->get_user_wallet_details(); 
              }
            ?>
            <h4 class="modal-title"><?php  echo $label_language_values['available']." ". $label_language_values['wallet_amount']; ?> (<?php echo $currency_symbol.$wallet_details[0]; ?>)</h4>
          </div>
          <div class="modal-body">
					 <div class="ct-custom-radio ct-payment-methods f-l">
                              <ul class="ct-radio-list ct-all-pay-methods" style="margin-bottom: 5px;">
					<?php if ($setting->get_option('ct_paypal_express_checkout_status') == 'on') {

                    ?>

                      <li class="ct-md-3 ct-sm-6 ct-xs-12" id="pay-at-venue">

                        <input type="radio" name="payment-methods" value="paypal" class="input-radio payment_gateway" id="pay-paypal" />

                        <label for="pay-paypal"  class="locally-radio"><span></span><?php  echo $label_language_values['paypal']; ?><img src="<?php  echo SITE_URL; ?>/assets/images/cards/paypal.png" class="ct-paypal-image" alt="PayPal"></label>

                      </li>
          <?php }
  if ($setting->get_option('ct_stripe_payment_form_status') == 'on'){  ?>

                   

                    <li class="ct-md-3 ct-sm-6 ct-xs-12 pl-17" id="card-payment">

                      <input type="radio" name="payment-methods" value="stripe-payment" class="input-radio payment_gateway cccard" id="pay-card" checked="checked"/>

                      <label for="pay-card" class="card-radio"><span></span><?php  echo $label_language_values['card_payment']; ?></label>

                    </li>

                    <?php    } 

					?>
					</ul>
					</div>
						<form name="cart_submit" id="cart_submit" action="#">
					
              <div id="ct-pay-methods" class="payment-method-container f-l">



                                <div class="card-type-center f-l">

                                    <div class="common-payment-style ct_hidden" <?php   

                    if ($setting->get_option('ct_authorizenet_status') == 'on' || $setting->get_option('ct_stripe_payment_form_status') == 'on' || $setting->get_option('ct_2checkout_status') == 'Y') {

                      echo " style='display:block;' ";

                    }

                    elseif(sizeof((array)$purchase_check)>0){

                      $check_pay = 'N';

                      $display_check = '';

                      foreach($purchase_check as $key=>$val){

                        if($val == 'Y'){

                          if($payment_hook->payment_display_cardbox_condition_hook($key) == true){

                            if($display_check == ''){

                              $display_check = " style='display:block;' ";

                              $check_pay = 'Y';

                            }elseif($display_check == " style='display:none;' "){

                              $display_check = " style='display:block;' ";

                              $check_pay = 'Y';

                            }

                          }else{

                            if($display_check == ''){

                              $display_check = " style='display:none;' ";

                              $check_pay = 'Y';

                            }elseif($display_check == " style='display:block;' "){

                              $display_check = " style='display:none;' ";

                              $check_pay = 'Y';

                            }

                          }

                        }

                      }

                      echo $display_check;

                    } ?> >

                                        <div class="payment-inner">

                      <?php   if($setting->get_option('ct_2checkout_status') == 'Y'){ ?>

                      <input id="token" name="token" type="hidden" value="">

                      <?php   } ?>

                                            <div id="card-payment-fields" class="ct-md-12 ct-sm-12" style="padding-top:0px;padding-left:8px;">

                                                <div class="ct-md-12 ct-xs-12 ct-header-bg">

                                                    <h4 class="header4"><?php  echo $label_language_values['card_details']; ?></h4>

                                                    

                                                </div>

                                                <div class="ct-md-12">

                                                    <label id="ct-card-payment-error" class="ct-error-msg ct-payment-error ct-payment-error1"><?php  echo $label_language_values['invalid_card_number']; ?>  <?php  echo $label_language_values['expiry_date_or_csv']; ?></label>  

                        </div>

                                                <div class="ct-md-12 ct-sm-12 ct-xs-12 ct-card-details">

                                                    <div class="ct-form-row ct-md-4 ct-xs-12 ct-sm-12">

                                                        <label><?php  echo $label_language_values['card_number']; ?></label>

                                                        <i class="icon-credit-card icons"></i>

                                                        <input class="cc-number ct-card-number common-fc allownumericwithoutdecimal" name="ct_card_number" id="ct_card_number" maxlength="16" size="16" data-stripe="number" type="tel">

                                                        <span class="card" aria-hidden="true"></span>



                                                    </div>



                                                    <div class="ct-form-row ct-md-6 ct-sm-10 ct-xs-12 ct-exp-mnyr">

                                                      <div class="ex-month-set ex-month-set1">
                                                        
                                                        <label>Exp. Month</label>
                                                        <input data-stripe="exp-month" class="cc-exp-month ct-exp-month common-fc allownumericwithoutdecimal" name="ct_exp_month" id="ct_exp_month" maxlength="2" type="tel" placeholder="<?php    echo date('m'); ?>" />
                                                      </div>

                                                      <div> <label>Exp. Year</label>
                                                        <input data-stripe="exp-year" class="cc-exp-year ct-exp-year common-fc-2 allownumericwithoutdecimal" name="ct_exp_year" id="ct_exp_year" maxlength="4" type="tel" placeholder="<?php    echo date('Y'); ?>" />
                                                      </div>

                                                    </div>

                                                    <div class="ct-form-row ct-md-2 ct-sm-2 ct-xs-12 ct-stripe-cvc">

                                                        <label><?php  echo $label_language_values['cvc']; ?></label>

                                                        <i class="icon-lock icons"></i>

                                                        <input type="password" placeholder="●●●" maxlength="4" size="4" data-stripe="cvc" name="ct_cvc_code" id="ct_cvc_code"  class="cc-cvc ct-cvc-code common-fc allownumericwithoutdecimal" type="tel"/>



                                                    </div>

                                                </div>

                                                <div class="ct-lock-image">

                                                  <div class="float-left pt-5">

                                                    <img src="<?php  echo SITE_URL; ?>/assets/images/cards/card-images.png" class="ct-stripe-image" alt="Stripe" />

                                                  </div>

                                                  <div class="float-left ml-50 pt-5">

                                                    <div class="ct-lock-img"></div>

                                                    <div class="debit-lock-text">SAFE AND SECURE 256-BIT<br/> SSL ENCRYPTED PAYMENT</div>

                                                  </div>

                                                </div>



                                            </div>

                                        </div>

                                    </div>

                                </div>

              </div>  
            <label for="add-money" class=" ml-20"><?php echo $label_language_values['amount']; ?></label>
           <input type="text" style="width:88%;" class="form-control add_amount allownumericwithoutdecimal ml-20 mt-10" required name="add_amount" id="add_amount" Placeholder="<?php  echo "Please Enter Add Amount";  ?>" />

            <a href="javascript:void(0)" data-email="<?php echo $wallet_details[1]; ?>" data-preamount="<?php echo $wallet_details[0]; ?>" class="btn btn-success ld-btn-width add_client_money amt-submit mt-10 ml-20" ><?php echo $label_language_values['add_amount']; ?></a>             
          </div>
					</form>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $label_language_values['close']; ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

  

    var baseurlObj = {'base_url': '<?php  echo BASE_URL;?>','stripe_publishkey':'<?php  echo $setting->get_option('ct_stripe_publishablekey');?>','stripe_status':'<?php  echo $setting->get_option('ct_stripe_payment_form_status');?>'};


    var siteurlObj = {'site_url': '<?php  echo SITE_URL;?>'};

    var ajaxurlObj = {'ajax_url': '<?php  echo AJAX_URL;?>'};

    var fronturlObj = {'front_url': '<?php  echo FRONT_URL;?>'};

</script>