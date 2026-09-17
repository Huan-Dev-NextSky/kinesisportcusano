					<form class="ct-customer-booking-modals">
						<div class="table-responsive" style="<?php echo !empty($ct_customer_show_booking_table) ? '' : 'display:none;'; ?>">                    
							<table id="user-profile-booking-table" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0"  width="100%">   
								<thead>                        
									<tr>													
										<th><?php echo $label_language_values['order']; ?>#</th>													
										<th><?php echo $label_language_values['order_date']; ?></th>													
										<th><?php echo $label_language_values['order_time']; ?></th>													
										<th><?php echo $label_language_values['show_all_bookings']; ?></th>	
										<th><?php echo $label_language_values['actions']; ?></th>                        
									</tr>                        
								</thead>                        
								<tbody class="my-app-tb">                        
							<?php
if (isset($_SESSION['ct_login_user_id']))
{
    $id = $_SESSION['ct_login_user_id'];
    $objuserdetails->id = $id;
    $details = $objuserdetails->get_user_details();
    if($details->num_rows > 0){
   		 while ($dd = mysqli_fetch_array($details)) { ?>                            
	    	<tr>                                
				<td><?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?></td>                                
				<?php if ($time_format == 12) { ?> 
                <td><?php echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($dd['booking_date_time']))); ?></td>
                 <?php }  else  { ?>                                    
                <td><?php echo str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($dd['booking_date_time']))); ?></td>         
            	<?php  } ?> <?php if ($time_format == 12) { ?>
            	<td><?php echo str_replace($english_date_array, $selected_lang_label, date(" h:i A", strtotime($dd['booking_date_time']))); ?></td>                    
            	<?php  }  else  { ?>                                    
            	<td><?php echo date(" H:i", strtotime($dd['booking_date_time'])); ?></td>       
            	<?php  } ?>                                
            	<td><a href="#user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-toggle="modal" data-target="#user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="ct-my-booking-user btn btn-info myappointment_popup"><i class="fa fa-eye"></i><?php echo $label_language_values['my_appointments']; ?></a></td>                                
            	<td><?php if ($dd["recurrence_status"] == "Y" && strtotime($dd['booking_date_time']) >= $currDateTime_withTZ)
		        {
		            $frequently_discount->id = $dd['frequently_discount'];
		            $frequently_discount_detail = $frequently_discount->readone();
		            $objocinfo->order_id = $dd['order_id'];
		            $oc_detail = $objocinfo->readone_order_client();
		            $objocinfo->recurring_id = $oc_detail["recurring_id"];
		            $count_rec = $objocinfo->count_recurring_id();
		            $count_rec_status = $objocinfo->get_one_rec_status();

            if ($count_rec > 0 && mysqli_num_rows($count_rec_status) == 0)
            { ?>							
            	<button type="button" data-toggle="popover_sent_req" rel="popover" data-placement="left" title="<?php echo $label_language_values['cancel_recurrence']; ?>" class="btn btn-success badges_show" data-order_id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"><i class="fa fa-times" aria-hidden="true"></i>							
            	<span class="badge br-10 hide_badges badge_<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"><?php echo $oc_detail["recurring_id"]; ?>&nbsp;/&nbsp;<?php echo $frequently_discount_detail['discount_typename']; ?></span>					
            	</button>							
            	<div id="popover-request-recurrence" style="display: none;">						<div class="arrow"></div>								
            	<table class="form-horizontal" cellspacing="0">									
            		<tbody>									
            			<tr>										
            				<td>											
            					<a data-recurring_id="<?php echo $oc_detail["recurring_id"]; ?>" class="btn btn-danger btn-sm recurring_request" ><?php echo $label_language_values['yes']; ?></a>											
            					<button type="button" id="ct-close-popover-request-recurrence" class="btn btn-default btn-sm" href="javascript:void(0)"><?php echo $label_language_values['cancel']; ?></button>										
            				</td>									
            			</tr>									
            		</tbody>								
            	</table>							
            	</div><?php
            }
        } ?>            
        <?php
        $payment_status_row = isset($dd['payment_status']) ? $dd['payment_status'] : '';
        if ($payment_status_row === 'Completed') { ?>
        <a target="_blank" href="<?php echo BASE_URL; ?>/assets/lib/download_invoice_client.php?iid=<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="btn btn-primary"><i class="fa fa-download"></i><?php echo $label_language_values['download_invoice']; ?>
        </a>
        <?php } ?>
        <?php $rating_review->order_id = $dd['order_id'];
        $rating = $rating_review->select_one();
        $bt = date("Y-m-d H:i:s", strtotime($dd['booking_date_time']));
        $booking_status = $dd['booking_status'];
        if ($dd['staff_ids'] != '' && $rating == 0 && $booking_status == "CO")
        { ?>																		
        	<button type="button" class="btn btn-info" data-toggle="modal" data-id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"  data-target="#rating_model<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"><?php echo $label_language_values['rating_and_review']; ?></button>
        	<div class="modal fade" id="rating_model<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" role="dialog">	<div class="modal-dialog">		
        		<div class="modal-content">			
        			<div class="modal-header">				
        				<button type="button" class="close" data-dismiss="modal">&times;</button>
        				<h4 class="modal-title"><?php echo $label_language_values['rating_and_review']; ?></h4>			
        				</div>			
        				<div class="modal-body">				
        					<input id="ratings<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" name="ratings<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="rating" data-min="0" data-max="5" data-step="0.1" value="0" /><br />				
        					<label class="control-label"><?php echo $label_language_values['review']; ?></label>				
        					<textarea class="form-control custom_textarea_feedback" id="review_note<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" name="review_note<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>"></textarea><br />				
        					<button type="button" data-staff_id="<?php echo $dd['staff_ids']; ?>" data-id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" id="rating_review_submit" class="btn btn-success"><?php echo $label_language_values['submit']; ?></button>			
        					</div>			
        					<div class="modal-footer">				
        						<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $label_language_values['close']; ?></button>	
        					</div>		
        				</div>	
        			</div>
        		</div>						
        	<?php  } ?>                                
        	</td>                            
        	</tr> <?php } }?>                        
        </tbody>                    
      </table>                
    </div>                

    <?php $details = $objuserdetails->get_user_details();
    if($details->num_rows > 0){
    while ($dd = mysqli_fetch_array($details)) { ?>                    
    	<?php
			$servicename = "";
			$ssi = 1;
			$ss = $booking->get_services_ofbookings($dd['order_id']);
			if ($ss->num_rows > 0) {
				while ($ss1 = mysqli_fetch_array($ss)) {
					$servicename .= $ss1['title'];
					if ($ssi < mysqli_num_rows($ss)) {
						$servicename .= ", ";
					}
					$ssi++;
				}
			}
			$units = "None";
			$methodname = "None";
			$hh = $booking->get_methods_ofbookings($dd['order_id']);
			$count_methods = mysqli_num_rows($hh);
			$hh1 = $booking->get_methods_ofbookings($dd['order_id']);
			if ($count_methods > 0) {
				while ($jj = mysqli_fetch_array($hh1)) {
					if ($units == "None") {
						$units = $jj['units_title'] . "-" . $jj['qtys'];
					} else {
						$units = $units . "," . $jj['units_title'] . "-" . $jj['qtys'];
					}
					$methodname = $jj['method_title'];
				}
			}
			$addons = "None";
			$hh = $booking->get_addons_ofbookings($dd['order_id']);
			if ($hh->num_rows > 0) {
				while ($jj = mysqli_fetch_array($hh)) {
					if ($addons == "None") {
						$addons = $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
					} else {
						$addons = $addons . "," . $jj['addon_service_name'] . "-" . $jj['addons_service_qty'];
					}
				}
			}
			$servicemethodname = "";
			$smi = 1;
			$sm = $booking->get_services_methods_ofbookings($dd['order_id']);
			if ($sm->num_rows > 0) {
				while ($ss1 = mysqli_fetch_array($sm)) {
					$servicemethodname .= $ss1['method_title'];
					if ($smi < mysqli_num_rows($sm)) {
						$servicemethodname .= ", ";
					}
					$smi++;
				}
			}
			if ($servicemethodname === "") {
				$servicemethodname = $methodname;
			}
			if ($dd['booking_status'] == 'A') {
				$booking_stats = '<i class="fa fa-info-circle"></i><em>' . $label_language_values['pending'] . '</em>';
				$booking_status_class = 'ct-status-A';
			} elseif ($dd['booking_status'] == 'C') {
				$booking_stats = '<i class="fa fa-check"></i><em>' . $label_language_values['confirmed'] . '</em>';
				$booking_status_class = 'ct-status-C';
			} elseif ($dd['booking_status'] == 'R') {
				$booking_stats = '<i class="fa fa-ban"></i><em>' . $label_language_values['rejected'] . '</em>';
				$booking_status_class = 'ct-status-R';
			} elseif ($dd['booking_status'] == 'RS') {
				$booking_stats = '<i class="fa fa-pencil-square-o"></i><em>' . $label_language_values["rescheduled"] . '</em>';
				$booking_status_class = 'ct-status-RS';
			} elseif ($dd['booking_status'] == 'CC') {
				$booking_stats = '<i class="fa fa-times"></i><em>' . $label_language_values['cancelled_by_client'] . '</em>';
				$booking_status_class = 'ct-status-CC';
			} elseif ($dd['booking_status'] == 'CS') {
				$booking_stats = '<i class="fa fa-times-circle-o"></i><em>' . $label_language_values['cancelled_by_service_provider'] . '</em>';
				$booking_status_class = 'ct-status-CS';
			} elseif ($dd['booking_status'] == 'CO') {
				$booking_stats = '<i class="fa fa-thumbs-o-up"></i><em>' . $label_language_values['completed'] . '</em>';
				$booking_status_class = 'ct-status-CO';
			} else {
				$booking_stats = '<i class="fa fa-thumbs-o-down"></i><em>' . $label_language_values['mark_as_no_show'] . '</em>';
				$booking_status_class = 'ct-status-NS';
			}
			if ($time_format == 12) {
				$appt_when_date = str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($dd['booking_date_time'])));
				$appt_when_time = str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($dd['booking_date_time'])));
			} else {
				$appt_when_date = str_replace($english_date_array, $selected_lang_label, date($getdateformat, strtotime($dd['booking_date_time'])));
				$appt_when_time = date("H:i", strtotime($dd['booking_date_time']));
			}
			$cust_price = $general->ct_price_format($dd['total_payment'], $symbol_position, $decimal);
			$doctor_names = '';
			if (!empty($dd['staff_ids'])) {
				$staff_id_list = array_filter(array_map('trim', explode(',', (string)$dd['staff_ids'])));
				$doctor_name_list = array();
				foreach ($staff_id_list as $staff_id_one) {
					if ($staff_id_one === '' || $staff_id_one === '0') {
						continue;
					}
					$staff_row = $booking->get_staff_detail_for_email($staff_id_one);
					if (is_array($staff_row) && !empty($staff_row['fullname'])) {
						$doctor_name_list[] = $staff_row['fullname'];
					}
				}
				$doctor_names = implode(', ', $doctor_name_list);
			}
			$doctor_label = 'Doctor';
			$cust_payment_method = '';
			if (!empty($dd['payment_method'])) {
				$cust_payment_method = trim((string)$dd['payment_method']);
			}
			$cust_notes = '';
			$notes_row = $objuserdetails->get_user_notes((int)$dd['order_id']);
			if (is_array($notes_row) && !empty($notes_row[0])) {
				$personal_raw = $notes_row[0];
				$decoded = @base64_decode($personal_raw, true);
				if ($decoded === false) {
					$decoded = $personal_raw;
				}
				$personal_info = @unserialize($decoded);
				if ($personal_info === false) {
					$personal_info = @unserialize($personal_raw);
				}
				if (is_array($personal_info) && isset($personal_info['notes'])) {
					$cust_notes = trim(str_replace('\\', '', (string)$personal_info['notes']));
				}
			}
			$payment_label = isset($label_language_values['payment']) ? $label_language_values['payment'] : 'Payment';
			$notes_label = isset($label_language_values['notes']) ? $label_language_values['notes'] : 'Notes';
		?>
    	<div id="user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="user-booking-details modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    		<div class="modal-dialog modal-md">
    			<div class="modal-content">
    				<div class="modal-header">
    					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    					<h4 class="modal-title"><i class="fa fa-calendar-check-o"></i> <?php echo $label_language_values['booking_details']; ?></h4>
    				</div>
    				<div class="modal-body">
						<div class="ct-cust-bd-card ct-cust-bd-meta">
							<div class="ct-cust-bd-status-row">
								<div class="ct-cust-bd-status <?php echo htmlspecialchars($booking_status_class); ?>"><?php echo $booking_stats; ?></div>
								<?php if (isset($dd['change_request_status']) && $dd['change_request_status'] === 'CANCEL_REQUESTED') { ?>
									<span class="ct-cust-bd-pending"><i class="fa fa-hourglass-half"></i> Cancellation Requested</span>
								<?php } elseif (isset($dd['change_request_status']) && $dd['change_request_status'] === 'RESCHEDULE_REQUESTED') { ?>
									<span class="ct-cust-bd-pending ct-cust-bd-pending-info"><i class="fa fa-hourglass-half"></i> Reschedule Requested</span>
								<?php } ?>
							</div>
							<?php if ($dd['booking_status'] == 'R' && !empty($dd['reject_reason'])) { ?>
								<div class="ct-cust-bd-reason"><span><?php echo $label_language_values['reason']; ?></span><?php echo htmlspecialchars($dd['reject_reason']); ?></div>
							<?php } ?>
							<div class="ct-cust-bd-chips">
								<span class="ct-cust-bd-chip"><i class="fa fa-calendar"></i> <?php echo $appt_when_date; ?></span>
								<span class="ct-cust-bd-chip"><i class="fa fa-clock-o"></i> <?php echo $appt_when_time; ?></span>
							</div>
						</div>
						<div class="ct-cust-bd-card ct-cust-bd-service">
							<h6 class="ct-bd-section-title"><i class="fa fa-briefcase"></i> <?php echo isset($label_language_values['services']) ? $label_language_values['services'] : 'Services'; ?></h6>
							<ul class="list-unstyled ct-cust-bd-grid">
								<li>
									<label><?php echo $label_language_values['service']; ?></label>
									<span><?php echo htmlspecialchars($servicename !== '' ? $servicename : '—'); ?></span>
								</li>
								<li>
									<label><?php echo $label_language_values['price']; ?></label>
									<span><?php echo $cust_price; ?></span>
								</li>
								<?php if ($doctor_names !== '') { ?>
								<li>
									<label><?php echo htmlspecialchars($doctor_label); ?></label>
									<span><?php echo htmlspecialchars($doctor_names); ?></span>
								</li>
								<?php } ?>
								<li>
									<label><?php echo htmlspecialchars($payment_label); ?></label>
									<span><?php echo htmlspecialchars($cust_payment_method !== '' ? $cust_payment_method : '—'); ?></span>
								</li>
								<li>
									<label><?php echo htmlspecialchars($notes_label); ?></label>
									<span><?php echo htmlspecialchars($cust_notes !== '' ? $cust_notes : '—'); ?></span>
								</li>
							</ul>
						</div>
						<?php
						$cust_actions_html = '';
						ob_start();
						?>
							<div class="ct-cust-bd-action-btns">
        <?php $t_zone_value = $setting->get_option('ct_timezone');
        $server_timezone = date_default_timezone_get();
        if (isset($t_zone_value) && $t_zone_value != '')
        {
            $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
            $timezonediff = $offset / 3600;
        }
        else
        {
            $timezonediff = 0;
        }
        if (is_numeric(strpos($timezonediff, '-')))
        {
            $timediffmis = str_replace('-', '', $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
        }
        else
        {
            $timediffmis = str_replace('+', '', $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
        }
        $current_times = date('Y-m-d H:i:s', $currDateTime_withTZ); 
        $td = date('Y-m-d H:i:s', strtotime($current_times));
        $bt = date("Y-m-d H:i:s", strtotime($dd['booking_date_time']));
        /* Past bookings: no actions (status already shown at top) */
        if ($bt < $td)
        {
            echo '';
        }
        else
        {
            if ($dd['booking_status'] == 'A' || $dd['booking_status'] == 'C' || $dd['booking_status'] == 'RS')
            {
                $booking_start_datetime = strtotime(date('Y-m-d H:i:s', strtotime($dd['booking_date_time'])));
                $reschedule_buffer_time = $setting->get_option('ct_reshedule_buffer_time');
                $cancellation_buffer_time = $setting->get_option('ct_cancellation_buffer_time');
                $allow_customer_cancel = ($setting->get_option('ct_allow_customer_cancel') === 'N') ? 'N' : 'Y';
                $allow_customer_reschedule = ($setting->get_option('ct_allow_customer_reschedule') === 'N') ? 'N' : 'Y';
                $t_zone_value = $setting->get_option('ct_timezone');
                $server_timezone = date_default_timezone_get();
                if (isset($t_zone_value) && $t_zone_value != '')
                {
                    $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
                    $timezonediff = $offset / 3600;
                }
                else
                {
                    $timezonediff = 0;
                }
                if (is_numeric(strpos($timezonediff, '-')))
                {
                    $timediffmis = str_replace('-', '', $timezonediff) * 60;
                    $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
                }
                else
                {
                    $timediffmis = str_replace('+', '', $timezonediff) * 60;
                    $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
                }
                $current_times = date('Y-m-d H:i:s', $currDateTime_withTZ);
                $current_time = strtotime($current_times);
                $remain_times = $booking_start_datetime - $current_time;
                $time_in_min = round($remain_times / 60);
                $pendingReq = isset($dd['change_request_status']) ? $dd['change_request_status'] : 'NONE';
                $hasPendingReq = ($pendingReq === 'CANCEL_REQUESTED' || $pendingReq === 'RESCHEDULE_REQUESTED');
                if ($hasPendingReq) { ?>
					<span class="ct-cust-status-pill ct-cust-pill-pending"><i class="fa fa-hourglass-half"></i> Request Pending</span>
				<?php } else {
                if ($allow_customer_reschedule === 'Y' && $time_in_min > $reschedule_buffer_time)
                { ?>
                <a href="javascript:void(0)" data-total_price="<?php echo $general->ct_price_format($dd['total_payment'],$symbol_position,$decimal);?>" class="btn btn-info ct-small-btn display_myappointment_data ct-cust-reschedule-open" data-order_id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" title="Reschedule"><i class="fa fa-repeat"></i> <?php echo $label_language_values['reschedule']; ?></a>
                <?php }
                else if ($allow_customer_reschedule === 'Y')
                {
                    if ($booking_start_datetime > $current_time)
                    { ?>
                    	<span class="btn btn-default disabled ct-cust-disabled-action"><i class="fa fa-repeat"></i> <?php echo $label_language_values['cannot_reschedule_now']; ?></span>
                    <?php
                    }
                    else
                    {
                        echo '';
                    }
                } ?>
                <?php if ($allow_customer_cancel === 'Y' && $time_in_min > $cancellation_buffer_time)
                { ?>
                	<a href="javascript:void(0)" id="ct-user-cancel-appointment<?php echo $dd['order_id'] ?>" data-id="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-order_id="<?php echo (int)$dd['order_id']; ?>" class="btn btn-danger ct-cust-cancel-toggle" title="<?php echo $label_language_values['booking_cancel_reason']; ?>?"><i class="fa fa-ban"></i> <?php echo $label_language_values['cancel']; ?></a>
                	<?php }
                else if ($allow_customer_cancel === 'Y')
                {
                    if ($booking_start_datetime > $current_time)
                    { ?>
                    	<span class="btn btn-default disabled ct-cust-disabled-action"><i class="fa fa-ban"></i> <?php echo $label_language_values['cannot_cancel_now']; ?></span>
                    	<?php
                    }
                    else
                    {
                        echo '';
                    }
                }
                } ?>
                <div class="ct-cust-inline-panel ct-cust-cancel-panel" id="ct-cust-cancel-panel<?php echo (int)$dd['order_id']; ?>" style="display:none;">
                	<div class="ct-cust-inline-title"><?php echo $label_language_values['booking_cancel_reason']; ?></div>
                	<textarea class="form-control" id="reason_cancel<?php echo $dd['order_id'] ?>" name="" placeholder="<?php echo $label_language_values['booking_cancel_reason']; ?>" required="required"></textarea>
                	<div class="ct-cust-inline-actions">
                		<a href="javascript:void(0)" data-id="<?php echo $dd['order_id'] ?>" data-gc_event="<?php echo $dd['gc_event_id']; ?>" data-gc_staff_event="<?php echo $dd['gc_staff_event_id']; ?>" data-pid="<?php echo $dd['staff_ids']; ?>" class="btn btn-danger btn-sm mybtncancel_booking_user_details"><?php echo $label_language_values['yes']; ?></a>
                		<a href="javascript:void(0)" class="btn btn-default btn-sm ct-cust-cancel-dismiss"><?php echo $label_language_values['cancel']; ?></a>
                	</div>
                </div>
            <?php
            }
            else
            {
                echo '';
            }
        } ?>
							</div>
						<?php
						$cust_actions_html = trim(ob_get_clean());
						/* Only show ACTIONS when there are real buttons (not status pills) */
						$cust_actions_has_content = (strpos($cust_actions_html, 'btn ') !== false);
						if ($cust_actions_has_content) { ?>
						<div class="ct-cust-bd-card ct-cust-bd-actions">
							<label><?php echo $label_language_values['actions']; ?></label>
							<?php echo $cust_actions_html; ?>
						</div>
						<?php } ?>
    				</div>
    			</div>
    		</div>
    	</div>
    <?php } } } ?>                
<?php if (isset($_SESSION['ct_login_user_id']))
	{
    $details = $objuserdetails->get_user_details();
    if($details->num_rows > 0){
    while ($dd = mysqli_fetch_array($details))
    { 

    	?>                        
    	<div id="update-user-booking-details<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">                            
    		<div class="vertical-alignment-helper">                                
    			<div class="modal-dialog modal-md vertical-align-center">                                    
    				<div class="modal-content">                                        
    					<div class="modal-header">                                            
    					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>                                            
    					<h4 class="modal-title"><?php echo $label_language_values['appointment_details']; ?></h4>                                        
    				</div>                                        
    				<div class="modal-body">                                            
    					<div class="tab-content">                                                
    						<div class="tab-pane fade in active">                                                    
    				<table>                                                        
    					<tbody>                                                        
    						<tr>                                                            
    							<td><label for="ct-service-duration"><?php echo $label_language_values['amount']; ?></label></td>                                                            
    							<td>                                                                
    								<div class="cta-col6 ct-w-50 ">                                                                    
    									<div class="form-control booking_total_payment" readonly="readonly">                                                                    
    									</div>                                                                
    								</div>                     
    							</td>                 
    							</tr>                 
    							<tr>                                                            
								<td><label for="ct-service-duration"><?php echo $label_language_values['date_and_time']; ?></label>
								</td>            
								<td>                                                                
								<div class="cta-col6 ct-w-50"><?php $dates = date("Y-m-d", strtotime($dd['booking_date_time']));
						        $slot_timess = date('H:i', strtotime($dd['booking_date_time']));
						        $get_staff_id = $booking->get_staff_ids_from_bookings($dd['order_id']);
						        if ($get_staff_id == "")
						        {
						            $staff_id = 1;
						        }
						        else
						        {
						            $staff_id = $get_staff_id;
						        } ?>                                                        
						        <input class="exp_cp_date form-control" id="expiry_date<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-staffid="<?php echo $staff_id; ?>" value=	"<?php echo $dates; ?>" data-date-format="yyyy/mm/dd" data-provide="datepicker" />                                                                                                                
						         </div>                                                       
						         <div class="cta-col6 ct-w-50 float-right mytime_slots_booking" data-order="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>">
		<?php $t_zone_value = $setting->get_option('ct_timezone');
        $server_timezone = date_default_timezone_get();
        if (isset($t_zone_value) && $t_zone_value != '')
        {
            $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
            $timezonediff = $offset / 3600;
        }
        else
        {
            $timezonediff = 0;
        }
        if (is_numeric(strpos($timezonediff, '-')))
        {
            $timediffmis = str_replace('-', '', $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("-" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
        }
        else
        {
            $timediffmis = str_replace('+', '', $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("+" . $timediffmis . " minutes", strtotime(date('Y-m-d H:i:s')));
        }
        $select_time = date('Y-m-d', strtotime($dates));
        $start_date = date($select_time, $currDateTime_withTZ);
        $time_interval = $setting->get_option('ct_time_interval');
        $time_slots_schedule_type = $setting->get_option('ct_time_slots_schedule_type');
        $advance_bookingtime = $setting->get_option('ct_min_advance_booking_time');
        $ct_service_padding_time_before = $setting->get_option('ct_service_padding_time_before');
        $ct_service_padding_time_after = $setting->get_option('ct_service_padding_time_after');
        $booking_padding_time = $setting->get_option('ct_booking_padding_time');
        $reschedule = "No";
        $client_order_id = 0;
        $time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type, $start_date, $time_interval, $staff_id, $client_order_id, $reschedule, $advance_bookingtime, $ct_service_padding_time_before, $ct_service_padding_time_after, $timezonediff, $booking_padding_time);
        $allbreak_counter = 0;
        $allofftime_counter = 0;
        $slot_counter = 0; ?>                                                                    
		<select class="selectpicker mydatepicker_appointment form-control myuser_reschedule_time" id="myuser_reschedule_time<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-order="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" data-size="10" style="" >                                                                        
		<?php if ($time_schedule['off_day'] != true && isset($time_schedule['slots']) && sizeof((array)$time_schedule['slots']) > 0 && $allbreak_counter != sizeof((array)$time_schedule['slots']) && $allofftime_counter != sizeof((array)$time_schedule['slots']))
        {
            foreach ($time_schedule['slots'] as $slot)
            {
                $ifbreak = 'N'; /* Need to check if the appointment slot come under break time. */
                foreach ($time_schedule['breaks'] as $daybreak)
                {
                    if (strtotime($slot) >= strtotime($daybreak['break_start']) && strtotime($slot) < strtotime($daybreak['break_end']))
                    {
                        $ifbreak = 'Y';
                    }
                } /* if yes its break time then we will not show the time for booking  */
                if ($ifbreak == 'Y')
                {
                    $allbreak_counter++;
                    continue;
                }
                $ifofftime = 'N';
                foreach ($time_schedule['offtimes'] as $offtime)
                {
                    if (strtotime($dates . ' ' . $slot) >= strtotime($offtime['offtime_start']) && strtotime($dates . ' ' . $slot) < strtotime($offtime['offtime_end']))
                    {
                        $ifofftime = 'Y';
                    }
                } /* if yes its offtime time then we will not show the time for booking  */
                if ($ifofftime == 'Y')
                {
                    $allofftime_counter++;
                    continue;
                }
                $complete_time_slot = mktime(date('H', strtotime($slot)) , date('i', strtotime($slot)) , date('s', strtotime($slot)) , date('n', strtotime($time_schedule['date'])) , date('j', strtotime($time_schedule['date'])) , date('Y', strtotime($time_schedule['date'])));
                if ($setting->get_option('ct_hide_faded_already_booked_time_slots') == 'on' && in_array($complete_time_slot, $time_schedule['booked']))
                {
                    continue;
                }
                if (in_array($complete_time_slot, $time_schedule['booked']) && ($setting->get_option('ct_allow_multiple_booking_for_same_timeslot_status') != 'Y'))
                { ?>                                                                                    <?php if ($setting->get_option('ct_hide_faded_already_booked_time_slots') == "on")
                    { ?>                                                                            
                    	<option value="<?php echo date("H:i", strtotime($slot)); ?>" <?php if (date("H:i", strtotime($slot)) == $slot_timess)
                        {
                            echo "selected";
                        } ?> class="time-slot br-2 ct-booked" >                                                                                            
                        <?php if ($setting->get_option('ct_time_format') == 24)
                        {
                            echo date("H:i", strtotime($slot));
                        }
                        else
                        {
                            echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($slot)));
                        } ?>                                                                                       
                         </option> <?php } ?> 
              <?php
                }
                else
                {
                    if ($setting->get_option('ct_time_format') == 24)
                    {
                        $slot_time = date("H:i", strtotime($slot));
                    }
                    else
                    {
                        $slot_time = str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($slot)));
                    } ?>                                                                                    <option value="<?php echo date("H:i", strtotime($slot)); ?>" <?php if (date("H:i", strtotime($slot)) == $slot_timess)
                    {
                        echo "selected";
                    } ?> class="time-slot br-2 <?php if (in_array($complete_time_slot, $time_schedule['booked']))
                    {
                        echo ' ct-booked';
                    }
                    else
                    {
                        echo ' time_slotss';
                    } ?>" <?php if (in_array($complete_time_slot, $time_schedule['booked']))
                    {
                        echo '';
                    }
                    else
                    {
                        echo 'data-slot_date_to_display="' . date($date_format, strtotime($dates)) . '" data-slot_date="' . $dates . '" data-slot_time="' . $slot_time . '"';
                    } ?>><?php if ($setting->get_option('ct_time_format') == 24)
                    {
                        echo date("H:i", strtotime($slot));
                    }
                    else
                    {
                        echo str_replace($english_date_array, $selected_lang_label, date("h:i A", strtotime($slot)));
                    } ?></option>                                                                                <?php
                }
                $slot_counter++;
            }
            if ($allbreak_counter == sizeof((array)$time_schedule['slots']) && sizeof((array)$time_schedule['slots']) != 0)
            { ?>                                                                                
            	<option  class="time-slot"><?php echo "Sorry Not Available "; ?></option>                                                                            
            	<?php
            }
        }
        else
        { ?>                                                                            
        	<option class="time-slot"><?php echo "Sorry Not Available"; ?></option>                                                                        
        	<?php
        } ?>                                                                    
        </select>                                                                
        </div>                                                            
        </td>                                                       
        </tr>                                                        
        <tr>
        	<td>Reason</td>
        	<td><textarea class="form-control my_user_notes_reschedule<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" placeholder="Reason for reschedule"></textarea></td>
        	</tr>
        	</tbody>
        	</table>
        	</div>
        	</div>
        	</div>
        	<div class="modal-footer">
        		<div class="cta-col12 ct-footer-popup-btn" style="width: 0%;padding: 5px;">
        			<div class="cta-col6"><button type="button" data-order="<?php if(isset($dd['order_id'])){ echo $dd['order_id']; }else{ } ?>" class="btn btn-info my_user_btn_for_reschedule" data-gc_event="<?php echo $dd['gc_event_id']; ?>" data-gc_staff_event="<?php echo $dd['gc_staff_event_id']; ?>" data-pid="<?php echo $dd['staff_ids']; ?>">Submit request</button>                                                
        				</div>                                            
        			</div>                                        
        		</div>                                    
        	</div>                                
        </div>                            
      </div>                        
   </div> <?php } } } ?>
   </div>        
 </form>    
 </div>
</div>
