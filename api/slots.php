<?php
include "includes.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_slotssss") {
	verifyRequiredParams(array("api_key", "selected_date", "staff_id"));
	if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
		$selected_date = $_POST["selected_date"];
		$staff_id = $_POST["staff_id"];
		$t_zone_value = $objsettings->get_option("ct_timezone");
		$server_timezone = date_default_timezone_get();
		if (isset($t_zone_value) && $t_zone_value != "") {
			$offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
			$timezonediff = $offset / 3600;
		} else {
			$timezonediff = 0;
		}
		if (is_numeric(strpos($timezonediff, "-"))) {
			$timediffmis = str_replace("-", "", $timezonediff) * 60;
			$currDateTime_withTZ = strtotime("-".$timediffmis." minutes", strtotime(date("Y-m-d H:i:s")));
		} else {
			$timediffmis = str_replace("+", "", $timezonediff) * 60;
			$currDateTime_withTZ = strtotime("+".$timediffmis." minutes", strtotime(date("Y-m-d H:i:s")));
		}
		$select_time = date("Y-m-d", strtotime($selected_date));
		$start_date = str_replace($english_date_array,$selected_lang_label,date($select_time, $currDateTime_withTZ)); /** Get Google Calendar Bookings **/
		$providerCalenderBooking = array();
		if ($gc_hook->gc_purchase_status() == "exist") {
			$gc_hook->google_cal_TwoSync_hook();
		} /** Get Google Calendar Bookings **/
		$time_interval = $objsettings->get_option("ct_time_interval");
		$time_slots_schedule_type = $objsettings->get_option("ct_time_slots_schedule_type");
		$advance_bookingtime = $objsettings->get_option("ct_min_advance_booking_time");
		$ct_service_padding_time_before = $objsettings->get_option("ct_service_padding_time_before");
		$ct_service_padding_time_after = $objsettings->get_option("ct_service_padding_time_after");
		$booking_padding_time = $objsettings->get_option("ct_booking_padding_time");
    $reschedule = "No";
    $client_order_id = 0;
		$time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type, $start_date, $time_interval, $staff_id, $client_order_id, $reschedule, $advance_bookingtime, $ct_service_padding_time_before, $ct_service_padding_time_after, $timezonediff, $booking_padding_time);
		$gc_slot_counter = 0;
		$allbreak_counter = 0;
		$allofftime_counter = 0;
		$slot_counter = 0;
		$arr_of_slots = array();
		$week_day_avail_count = $week_day_avail->get_data_for_front_cal();
		if (mysqli_num_rows($week_day_avail_count) > 0) {
			if ($time_schedule["off_day"] != true && isset($time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) > 0 && $allbreak_counter != sizeof((array)$time_schedule["slots"]) && $allofftime_counter != sizeof((array)$time_schedule["slots"])) {
				foreach($time_schedule["slots"] as $slot) { /* Checking in GC booked Slots START */
					$curreslotstr = strtotime(date(date("Y-m-d H:i:s", strtotime($select_time." ".$slot)), $currDateTime_withTZ));
					$gccheck = "N";
					if (sizeof((array)$providerCalenderBooking) > 0) {
						for ($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
							if ($curreslotstr >= $providerCalenderBooking[$i]["start"] && $curreslotstr < $providerCalenderBooking[$i]["end"]) {
								$gccheck = "Y";$gc_slot_counter++;
							}
						}
					} /* Checking in GC booked Slots END */
					$ifbreak = "N"; /* Need to check if the appointment slot come under break time. */
					foreach($time_schedule["breaks"] as $daybreak) {
						if (strtotime($slot) >= strtotime($daybreak["break_start"]) && strtotime($slot) < strtotime($daybreak["break_end"])) {
							$ifbreak = "Y";
						}
					} /* if yes its break time then we will not show the time for booking  */
					if ($ifbreak == "Y") {
						$allbreak_counter++;
						continue;
					}
					$ifofftime = "N";
					foreach($time_schedule["offtimes"] as $offtime) {
						if (strtotime($selected_date." ".$slot) >= strtotime($offtime["offtime_start"]) && strtotime($selected_date." ".$slot) < strtotime($offtime["offtime_end"])) {
							$ifofftime = "Y";
						}
					} /* if yes its offtime time then we will not show the time for booking  */
					if ($ifofftime == "Y") {
						$allofftime_counter++;
						continue;
					}
					$complete_time_slot = mktime(date("H", strtotime($slot)), date("i", strtotime($slot)), date("s", strtotime($slot)), date("n", strtotime($time_schedule["date"])), date("j", strtotime($time_schedule["date"])), date("Y", strtotime($time_schedule["date"])));
					if ($objsettings->get_option("ct_hide_faded_already_booked_time_slots") == "on" && (in_array($complete_time_slot, $time_schedule["booked"])) || $gccheck == "Y") {
						continue;
					}
					if ((in_array($complete_time_slot, $time_schedule["booked"]) || $gccheck == "Y") && ($objsettings->get_option("ct_allow_multiple_booking_for_same_timeslot_status") != "Y")) {} else {
						if ($objsettings->get_option("ct_time_format") == 24) {
							$slot_time = date("H:i", strtotime($slot));
							$slotdbb_time = date("H:i", strtotime($slot));
							$ct_time_selected = date("H:i", strtotime($slot));
						} else {
							$slot_time = str_replace($english_date_array,$selected_lang_label,date("h:i A", strtotime($slot)));
							$slotdbb_time = date("H:i", strtotime($slot));
							$ct_time_selected = str_replace($english_date_array,$selected_lang_label,date("h:iA", strtotime($slot)));
						}
						array_push($arr_of_slots, date("H:i", strtotime($slot)));
					}
					$slot_counter++;
				}
				if (sizeof((array)$arr_of_slots) > 0) {
					$array = array();
					array_push($array, $arr_of_slots);
					$valid = ["status" => "true", "statuscode" => 200, "response" => $arr_of_slots];
					setResponse($valid);
				}
				if ($allbreak_counter != 0 && $allofftime_counter != 0) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
					setResponse($invalid);
				}
				if ($gc_slot_counter == sizeof((array)$time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) != 0) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
					setResponse($invalid);
				}
				if ($allbreak_counter == sizeof((array)$time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) != 0) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
					setResponse($invalid);
				}
				if ($allofftime_counter > sizeof((array)$time_schedule["offtimes"]) && sizeof((array)$time_schedule["slots"]) == $allofftime_counter) {
					$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
					setResponse($invalid);
				}
			} else {
				$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
				setResponse($invalid);
			}
		} else {
			$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["availability_is_not_configured_from_admin_side"]];
			setResponse($invalid);
		}
	} else {
		$invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
		setResponse($invalid);
	}
}
if (isset($_POST["action"]) && $_POST["action"] == "get_slots") {
    verifyRequiredParams(array("api_key", "selected_date", "staff_id"));
    if (isset($_POST["api_key"]) && $_POST["api_key"] == $objsettings->get_option("ct_api_key")) {
        $selected_date = $_POST["selected_date"];
        $staff_id = $_POST["staff_id"];
        $t_zone_value = $objsettings->get_option("ct_timezone");
        $server_timezone = date_default_timezone_get();
        if (isset($t_zone_value) && $t_zone_value != "") {
            $offset = $first_step->get_timezone_offset($server_timezone, $t_zone_value);
            $timezonediff = $offset / 3600;
        } else {
            $timezonediff = 0;
        }
        if (is_numeric(strpos($timezonediff, "-"))) {
            $timediffmis = str_replace("-", "", $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("-".$timediffmis." minutes", strtotime(date("Y-m-d H:i:s")));
        } else {
            $timediffmis = str_replace("+", "", $timezonediff) * 60;
            $currDateTime_withTZ = strtotime("+".$timediffmis." minutes", strtotime(date("Y-m-d H:i:s")));
        }
        $select_time = date("Y-m-d", strtotime($selected_date));
        $start_date = str_replace($english_date_array, $selected_lang_label, date($select_time, $currDateTime_withTZ));

        /** Get Google Calendar Bookings **/
        $providerCalenderBooking = array();
        if ($gc_hook->gc_purchase_status() == "exist") {
            $gc_hook->google_cal_TwoSync_hook();
        }
        /** Get Google Calendar Bookings **/

        $time_interval = $objsettings->get_option("ct_time_interval");
        $time_slots_schedule_type = $objsettings->get_option("ct_time_slots_schedule_type");
        $advance_bookingtime = $objsettings->get_option("ct_min_advance_booking_time");
        $ct_service_padding_time_before = $objsettings->get_option("ct_service_padding_time_before");
        $ct_service_padding_time_after = $objsettings->get_option("ct_service_padding_time_after");
        $booking_padding_time = $objsettings->get_option("ct_booking_padding_time");
        $reschedule = "No";
        $client_order_id = 0;

        $time_schedule = $first_step->get_day_time_slot_by_provider_id($time_slots_schedule_type, $start_date, $time_interval, $staff_id, $client_order_id, $reschedule, $advance_bookingtime, $ct_service_padding_time_before, $ct_service_padding_time_after, $timezonediff, $booking_padding_time);

        $gc_slot_counter = 0;
        $allbreak_counter = 0;
        $allofftime_counter = 0;
        $slot_counter = 0;
        $arr_of_slots = array();
        $week_day_avail_count = $week_day_avail->get_data_for_front_cal();
        
        if (mysqli_num_rows($week_day_avail_count) > 0) {
            if ($time_schedule["off_day"] != true && isset($time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) > 0 && $allbreak_counter != sizeof((array)$time_schedule["slots"]) && $allofftime_counter != sizeof((array)$time_schedule["slots"])) {
                foreach ($time_schedule["slots"] as $slot) {
                    /* Checking in GC booked Slots START */
                    $curreslotstr = strtotime(date(date("Y-m-d H:i:s", strtotime($select_time . " " . $slot)), $currDateTime_withTZ));
                    $gccheck = "N";
                    if (sizeof((array)$providerCalenderBooking) > 0) {
                        for ($i = 0; $i < sizeof((array)$providerCalenderBooking); $i++) {
                            if ($curreslotstr >= $providerCalenderBooking[$i]["start"] && $curreslotstr < $providerCalenderBooking[$i]["end"]) {
                                $gccheck = "Y";
                                $gc_slot_counter++;
                            }
                        }
                    }
                    /* Checking in GC booked Slots END */
                    $ifbreak = "N"; /* Need to check if the appointment slot come under break time. */
                    foreach ($time_schedule["breaks"] as $daybreak) {
                        if (strtotime($slot) >= strtotime($daybreak["break_start"]) && strtotime($slot) < strtotime($daybreak["break_end"])) {
                            $ifbreak = "Y";
                        }
                    }
                    /* if yes its break time then we will not show the time for booking  */
                    if ($ifbreak == "Y") {
                        $allbreak_counter++;
                        continue;
                    }
                    $ifofftime = "N";
                    foreach ($time_schedule["offtimes"] as $offtime) {
                        if (strtotime($selected_date . " " . $slot) >= strtotime($offtime["offtime_start"]) && strtotime($selected_date . " " . $slot) < strtotime($offtime["offtime_end"])) {
                            $ifofftime = "Y";
                        }
                    }
                    /* if yes its offtime time then we will not show the time for booking  */
                    if ($ifofftime == "Y") {
                        $allofftime_counter++;
                        continue;
                    }
                    $complete_time_slot = mktime(date("H", strtotime($slot)), date("i", strtotime($slot)), date("s", strtotime($slot)), date("n", strtotime($time_schedule["date"])), date("j", strtotime($time_schedule["date"])), date("Y", strtotime($time_schedule["date"])));
                    if ($objsettings->get_option("ct_hide_faded_already_booked_time_slots") == "on" && (in_array($complete_time_slot, $time_schedule["booked"])) || $gccheck == "Y") {
                        continue;
                    }
                    if ((in_array($complete_time_slot, $time_schedule["booked"]) || $gccheck == "Y") && ($objsettings->get_option("ct_allow_multiple_booking_for_same_timeslot_status") != "Y")) {
                        continue;
                    } else {
                        if ($objsettings->get_option("ct_time_format") == 24) {
                            $slot_time = date("H:i", strtotime($slot));
                        } else {
                            $slot_time = date("h:i A", strtotime($slot));
                        }
                        array_push($arr_of_slots, $slot_time);
                    }
                    $slot_counter++;
                }
                if (sizeof((array)$arr_of_slots) > 0) {
                    $valid = ["status" => "true", "statuscode" => 200, "response" => $arr_of_slots];
                    setResponse($valid);
                }
                if ($allbreak_counter != 0 && $allofftime_counter != 0) {
                    $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
                    setResponse($invalid);
                }
                if ($gc_slot_counter == sizeof((array)$time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) != 0) {
                    $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
                    setResponse($invalid);
                }
                if ($allbreak_counter == sizeof((array)$time_schedule["slots"]) && sizeof((array)$time_schedule["slots"]) != 0) {
                    $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
                    setResponse($invalid);
                }
                if ($allofftime_counter > sizeof((array)$time_schedule["offtimes"]) && sizeof((array)$time_schedule["slots"]) == $allofftime_counter) {
                    $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
                    setResponse($invalid);
                }
            } else {
                $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["none_of_time_slot_available_please_check_another_dates"]];
                setResponse($invalid);
            }
        } else {
            $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["availability_is_not_configured_from_admin_side"]];
            setResponse($invalid);
        }
    } else {
        $invalid = ["status" => "false", "statuscode" => 404, "response" => $label_language_values["api_key_mismatch"]];
        setResponse($invalid);
    }
}
?>
