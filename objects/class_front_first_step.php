<?php  
   
class cleanto_first_step
{
	public $day_id;
	public $week_id;
	public $time_interval;
	public $provider_id;
	public $datetime;
	public $staff_id;
	public $table_name="ct_week_days_available";
	public $table_name1="ct_off_days";
	public $table_name2="ct_schedule_breaks";
	public $table_name3="ct_bookings";
	public $table_name4="ct_schedule_offtimes";
    public $conn;
    public $reschedule;
    public $advance_bookingtime;
    public $service_padding_before;
    public $service_padding_after;
    public $timezonediff;
	public $booking_padding_time;
	
	
	/**     
	* Get Week of the month from date	 
	* @param date that need be checked	 
	* @return week number     
	*/
    function get_week_of_month_by_date($date)
    {	
		
        $dmy    = explode('-', $date);
        $weekid = ceil(($dmy[2] + date("w", mktime(0, 0, 0, $dmy[1], 1, $dmy[0]))) / 7);
        if ($weekid == 6) {
            $idweek = $weekid - 1;
        } else {
            $idweek = $weekid;
        }	
        return $idweek;
		
    }
    
	/**     
	* print time slots	 
	* @param Day ID	 
	* @param Week ID	 
	* @param Time Interval default 10	 
	* @param Time provider ID	 
	* @return array time slots result     
	*/
    function time_slots($day_id, $week_id, $time_int,$staff_id)
    {
		$dayid=$day_id;
		$weekid=$week_id;
		$time_interval=$time_int;

    /*if ($_SESSION['staff_id_cal'] == "random") {
        $carbonDate = \Carbon\Carbon::parse($_POST["selected_dates"]);
        $week_id = $carbonDate->weekNumberInMonth;

        $weekday_id = $carbonDate->dayOfWeek + 1;
        $query = "select provider_id from ct_week_days_available where off_day = 'N' AND week_id=$week_id AND weekday_id=$weekday_id";
        $result = mysqli_query($this->conn, $query);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $staff_ids = [];
        foreach ($data as $item) {
            $staff_ids[] = $item["provider_id"];
        }
        $availStaffIDs = implode(",", $staff_ids);

        $selected_Date = date('Y-m-d', strtotime($_POST['selected_dates']));
        $availResultData = array();

        $availQuery = "SELECT MIN(day_start_time),MAX(day_end_time) FROM ct_week_days_available AS a LEFT JOIN ct_off_days AS o ON a.provider_id = o.user_id WHERE a.provider_id IN ($availStaffIDs) AND o.off_date != $selected_Date AND a.off_day='N'";
        $availResult = mysqli_query($this->conn, $availQuery);
        $availData = mysqli_fetch_row($availResult);
        $availResultData['daystart_time'] = $availData[0];
        $availResultData['dayend_time'] = $availData[1];

        return $availResultData;
    }*/

    $staff_id = $staff_id;
		
		$results = array();
		$query="SELECT `day_start_time`,`day_end_time` FROM `".$this->table_name."` WHERE `weekday_id`=" .$dayid . " AND `off_day`='N' AND `week_id`=" .$weekid. " AND provider_id='" .$staff_id."'";			
			$result=mysqli_query($this->conn,$query);
			$value=mysqli_fetch_row($result);	
			if(isset($value[0])){
				$results['daystart_time'] = $value[0];
				$results['dayend_time']   = $value[1];
				return $results;	
			}else{
				return "";
			}			
    }
	
	function random_technician()
    {
        $query = "SELECT * from ct_admin_info where role='staff'";
        $result = mysqli_query($this->conn, $query);
        if($result->num_rows > 0){
            while ($random = mysqli_fetch_array($result)) {
                $randomData[] = $random;
            }
        }

        return $randomData;

    }
	
	function check_off_day($date,$staff_id)
    {
		
		$dates=$date;
		$query="select * from `".$this->table_name1."` where `off_date`='".$dates."' and user_id='".$staff_id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_num_rows($result);	

        if ($value> 0) {
            return true;
        } else {
            return false;
        }
    }
    
	function get_random_tech_data($date, $weekid, $dayid)
    {
        $dates = $date;
        $staff_ids = explode(",", $_SESSION['provider_sec']);

        $data_array = [];
        $break_array = [];

        $off_day_false_check = 0;

        foreach ($staff_ids as $staff_id) {
            if ($staff_id != '') {
                /* For day off */
                echo $query = "select * from `" . $this->table_name1 . "` where `off_date`='" . $dates . "' and user_id='" . $staff_id . "'";
                $result = mysqli_query($this->conn, $query);
                $value = mysqli_num_rows($result);
                if ($value > 0) {

                } else {
                    $off_day_false_check = 1;
                }

            }
        }

        if ($off_day_false_check == 1) {
            $data_array['off_day'] = false;
        } else {
            $data_array['off_day'] = true;
        }
        /*$data_array['breaks'] = $break_array;*/

        return $data_array;
    }
	
	function get_day_breaks($week_id, $day_id, $provider_schedule_type,$staff_id)
	{
		$provider_schedule_type;
		$weekid=$week_id;
		$dayid=$day_id;
		$providerschedule_type=$provider_schedule_type;
		$return_arr = array();
        if ($providerschedule_type == 'W'){
			$query="SELECT `break_start`,`break_end` FROM `".$this->table_name2."` WHERE `week_id`='1' AND `weekday_id`='" . $dayid . "' AND provider_id='" . $staff_id . "'";
			$result=mysqli_query($this->conn,$query);	
			
        }else{
			
			$query="SELECT `break_start`,`break_end` FROM `".$this->table_name2."` WHERE `week_id`='".$weekid."' AND `weekday_id`='" . $dayid . "' AND provider_id='" . $staff_id . "'";
			$result=mysqli_query($this->conn,$query);	
			
        }

			$counter = 0;
            if($result->num_rows > 0){
                while($breaks=mysqli_fetch_array($result)){
                    $return_arr[$counter]['break_start'] = $breaks['break_start'];
                    $return_arr[$counter]['break_end']   = $breaks['break_end'];
                    $counter++;
                }
            }
        return $return_arr;
		
    }
    
	
	function get_already_booked_slots($selected_date,$cur_time_interval,$service_padding_before,$service_padding_after,$booking_padding_time,$staff_id,$client_order_id,$reschedule){
	 
		$use_date=$selected_date;
		$return_arr = $another_booking_slots = $same_booking_slots = array();
		$query = "select `booking_date_time`, `order_id` from `ct_bookings` where CAST(`booking_date_time` as date)='" . $use_date . "' and FIND_IN_SET('" . $staff_id . "', staff_ids) and (`booking_status`='A' OR `booking_status`='C' OR `booking_status`='RS') group by `booking_date_time`, `order_id` order by `booking_date_time`";
    $value = mysqli_query($this->conn, $query);
		
		if($reschedule == "Yes"){
            if($value->num_rows > 0){
                while ($row = mysqli_fetch_array($value)) {
                    if($row['order_id'] == $client_order_id){
                        /* creating a temprory storage variable for booked datetime */
                        $service_duration = $cur_time_interval;
                        $loop_tmp_storage = strtotime($row['booking_date_time']);
                        if ($service_padding_before != '') {
                            $loop_tmp_storage = strtotime("-$service_padding_before minutes", $loop_tmp_storage);
                            $service_duration = $service_duration + $service_padding_before;
                        }
                        if ($service_padding_after != '') {
                            $service_duration = $service_duration + $service_padding_after;
                        }
                        if ($booking_padding_time != '') {
                                $service_duration = $service_duration + $booking_padding_time;
                        }
                        $same_booking_slots[] = $loop_tmp_storage;
                        if ($service_duration > $cur_time_interval) {
                            $times_greater = ceil($service_duration / $cur_time_interval - 1);
                            for ($tg = 1; $tg <= $times_greater; $tg++) {
                                    $same_booking_slots[] = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                                    $loop_tmp_storage = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                            }
                        }
                    }else{
                        /* creating a temprory storage variable for booked datetime */
                        $service_duration = $cur_time_interval;
                        $loop_tmp_storage = strtotime($row['booking_date_time']);
                        if ($service_padding_before != '') {
                            $loop_tmp_storage = strtotime("-$service_padding_before minutes", $loop_tmp_storage);
                            $service_duration = $service_duration + $service_padding_before;
                        }
                        if ($service_padding_after != '') {
                            $service_duration = $service_duration + $service_padding_after;
                        }
                        if ($booking_padding_time != '') {
                            $service_duration = $service_duration + $booking_padding_time;
                        }
                        $another_booking_slots[] = $loop_tmp_storage;
                        if ($service_duration > $cur_time_interval) {
                            $times_greater = ceil($service_duration / $cur_time_interval - 1);
                            for ($tg = 1; $tg <= $times_greater; $tg++) {
                                $another_booking_slots[] = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                                $loop_tmp_storage = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                            }
                        }
                    }
                        
                }
            }
			$return_arr['same_booking_slots'] = $same_booking_slots;
			$return_arr['another_booking_slots'] = $another_booking_slots;
			return $return_arr;
		}else{
            if($value->num_rows > 0){
                while ($row = mysqli_fetch_array($value)) {
                    /* creating a temprory storage variable for booked datetime */
                    $service_duration = $cur_time_interval;
                    $loop_tmp_storage = strtotime($row['booking_date_time']);
                    if ($service_padding_before != '') {
                        $loop_tmp_storage = strtotime("-$service_padding_before minutes", $loop_tmp_storage);
                        $service_duration = $service_duration + $service_padding_before;
                    }
                    if ($service_padding_after != '') {
                        $service_duration = $service_duration + $service_padding_after;
                    }
                    if ($booking_padding_time != '') {
                        $service_duration = $service_duration + $booking_padding_time;
                    }
                    $another_booking_slots[] = $loop_tmp_storage;
                    if ($service_duration > $cur_time_interval) {
                        $times_greater = ceil($service_duration / $cur_time_interval - 1);
                        for ($tg = 1; $tg <= $times_greater; $tg++) {
                                $another_booking_slots[] = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                                $loop_tmp_storage = strtotime("+$cur_time_interval minutes", $loop_tmp_storage);
                        }
                    }
                }
            }
			$return_arr['same_booking_slots'] = "";
			$return_arr['another_booking_slots'] = $another_booking_slots;
			return $return_arr;	
		}	
	
	}
	 
	
	/* Get temporary booked slot on behalf of selected service's duration */
    function get_temp_booked_slots($selected_date, $cur_time_interval, $staff_id,$providerschedule_type)
    {
    	
        $use_date = $selected_date;
        if (!isset($_SESSION['time_duration'])) {
          $time_duration = 0;
        }else{
          $time_duration = $_SESSION['time_duration'];
        }
        $query = "select `booking_date_time`,`order_id` from `ct_bookings` where CAST(`booking_date_time` as date)='" . $use_date . "' and FIND_IN_SET('" . $staff_id . "', staff_ids) and (`booking_status`='A' OR `booking_status`='C' OR `booking_status`='UC') group by `booking_date_time`,`order_id` order by `booking_date_time`";

        $pre_start_datetime = '';
        $curr_start_datetime = '';
        $pre_end_datetime = '';
        $curr_end_datetime = '';

        $tem_booked = [];

        $i = 0;

        $value = mysqli_query($this->conn, $query);
        if($value->num_rows > 0){
            while ($row = mysqli_fetch_array($value)) {
            
                $oci_query = "SELECT `order_duration` FROM `ct_order_client_info` WHERE `order_id` = '" . $row['order_id'] . "'";
                $oci_value = mysqli_query($this->conn, $oci_query);
                $oci_result = mysqli_fetch_array($oci_value);

                /*$bti_query = "SELECT `option_value` FROM `ct_settings` WHERE `option_name` = 'ct_break_time_gap'";
                $bti_value = mysqli_query($this->conn, $bti_query);
                $bti_result = mysqli_fetch_array($bti_value);

                if (!isset($bti_result['option_value'])) {
                    $bti_result['option_value'] = '0';
                }*/
                
                $booking_start_str = strtotime($row['booking_date_time']);
                $booking_end_str = strtotime($row['booking_date_time']) + ($oci_result['order_duration'] * 60);

                if ($i == 0) {
                    $first_booking_starttime = $booking_start_str;
                }

                if ($pre_start_datetime == '' && $curr_start_datetime == '') {
                    $pre_start_datetime = '';
                    $curr_start_datetime = $booking_start_str;
                    $pre_end_datetime = '';
                    $curr_end_datetime = $booking_end_str;
                } else {
                    $pre_start_datetime = $curr_start_datetime;
                    $curr_start_datetime = $booking_start_str;
                    $pre_end_datetime = $curr_end_datetime;
                    $curr_end_datetime = $booking_end_str;

                    $diff_cal = $curr_start_datetime - $pre_end_datetime;
                    $diff_mins = $diff_cal / 60;

                    if ($time_duration > $diff_mins) {
                        for ($i = $pre_end_datetime; $i < $curr_start_datetime; $i = $i + $cur_time_interval * 60) {
                            $tem_booked[] = $i;
                        }
                    } else {
                        $remain_slots = $diff_mins / $cur_time_interval;
                        $next_remain_slots = ($diff_mins - $time_duration) / $cur_time_interval;

                        $final_remain_slots = ($remain_slots - $next_remain_slots) - 1;
                        $final_mins = $final_remain_slots * $cur_time_interval;

                        $final_strtotime = $final_mins * 60;

                        $start_strtotime = $curr_start_datetime - $final_strtotime;

                        if ($curr_start_datetime != $start_strtotime) {
                            for ($i = $start_strtotime; $i < $curr_start_datetime; $i = $i + $cur_time_interval * 60) {
                                $tem_booked[] = $i;
                            }
                        }
                    }
                }

                $i++;
            }
        }

        $date = date('Y-m-d', strtotime($selected_date)); 

        $date_day = date('l', strtotime($selected_date));
        if ($providerschedule_type == 'weekly') {
            $week_id = 1;
        } else {
        	$week_id = $this->get_week_of_month_by_date(date('Y-m-d', strtotime($date)));
        }
        $weekday_id = date('N', strtotime($selected_date));
        
        $a_query = "SELECT * FROM `ct_week_days_available` WHERE `weekday_id`='" . $weekday_id . "' and `week_id`='" . $week_id . "' and `provider_id`='" . $staff_id . "'";

        $a_result = mysqli_query($this->conn, $a_query);
        $avail = mysqli_fetch_array($a_result);

        $start_availability = $selected_date . ' ' . $avail['day_start_time'];
        $end_availability = $selected_date . ' ' . $avail['day_end_time'];

        if ($curr_start_datetime != '') {
            $diff_cal = $first_booking_starttime - strtotime($start_availability);
            $diff_mins = $diff_cal / 60;

            $remain_slots = $diff_mins / $cur_time_interval;
            $next_remain_slots = ($diff_mins - $time_duration) / $cur_time_interval;

            $final_remain_slots = ($remain_slots - $next_remain_slots) - 1;
            $final_mins = $final_remain_slots * $cur_time_interval;

            $final_strtotime = $final_mins * 60;

            $start_strtotime = $first_booking_starttime - $final_strtotime;

            if ($first_booking_starttime != $start_strtotime) {
                for ($i = $start_strtotime; $i < $first_booking_starttime; $i = $i + $cur_time_interval * 60) {
                    if ($i >= strtotime($start_availability)) {
                        $tem_booked[] = $i;
                    }
                }
            }
        }

        if ($curr_end_datetime != '') {
            $diff_cal = strtotime($end_availability) - $curr_end_datetime;
            $diff_mins = $diff_cal / 60;

            $remain_slots = $diff_mins / $cur_time_interval;
            $next_remain_slots = ($diff_mins - $time_duration) / $cur_time_interval;

            $final_remain_slots = ($remain_slots - $next_remain_slots) - 1;
            $final_mins = $final_remain_slots * $cur_time_interval;

            $final_strtotime = $final_mins * 60;

            $start_strtotime = strtotime($end_availability) - $final_strtotime;

            if (strtotime($end_availability) != $start_strtotime) {
                for ($i = $start_strtotime; $i < strtotime($end_availability); $i = $i + $cur_time_interval * 60) {
                    if ($i >= $curr_end_datetime) {
                        $tem_booked[] = $i;
                    }
                }
            }
        } else {
            $diff_cal = strtotime($end_availability) - strtotime($start_availability);
            $diff_mins = $diff_cal / 60;

            $remain_slots = $diff_mins / $cur_time_interval;
            $next_remain_slots = ($diff_mins - $time_duration) / $cur_time_interval;

            $final_remain_slots = ($remain_slots - $next_remain_slots) - 1;
            $final_mins = $final_remain_slots * $cur_time_interval;

            $final_strtotime = $final_mins * 60;

            $start_strtotime = strtotime($end_availability) - $final_strtotime;

            if (strtotime($end_availability) != $start_strtotime) {
                for ($i = $start_strtotime; $i < strtotime($end_availability); $i = $i + $cur_time_interval * 60) {
                    if ($i >= $curr_end_datetime) {
                        $tem_booked[] = $i;
                    }
                }
            }
        }

        return array_unique($tem_booked);
    }

	
	/* Get Provider offtimes **/
	function get_provider_offtime($staff_id)
	{
        $return_arr = array();
        
 	    $query="SELECT `start_date_time`,`end_date_time` FROM `".$this->table_name4."` where provider_id = '".$staff_id."'" ;
		$result=mysqli_query($this->conn,$query);	
		$counter = 0;
        if($result->num_rows > 0){
            while($offtimes=mysqli_fetch_array($result)){
                $return_arr[$counter]['offtime_start'] = $offtimes['start_date_time'];
                $return_arr[$counter]['offtime_end']   = $offtimes['end_date_time'];
                $counter++;
            }
        }
        return $return_arr;
    }
		
    /* A new function for new design */
    /* A new function for new design */
    function get_day_time_slot_by_provider_id($provider_schedule_type, $start_date, $time_int,$staff_id, $client_order_id,$reschedule,$advance_bookingtime=0,$service_padding_before=0,$service_padding_after=0,$timezonediff=0,$booking_padding_time=0)    
    {
		/*if(!isset($_SESSION)) { session_start(); }								
		if ($_SESSION['staff_id_cal'] == "random") {
        $cal_starting_date = $start_date;
        $time_interval = $time_int;
        $carbonDate = \Carbon\Carbon::parse($_POST["selected_dates"]);
        $week_id = $carbonDate->weekNumberInMonth;
        $weekday_id = $carbonDate->dayOfWeek + 1;
        $query = "select provider_id from ct_week_days_available where off_day = 'N' AND week_id=$week_id AND weekday_id=$weekday_id";
        $result = mysqli_query($this->conn, $query);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $staff_ids = [];

        foreach ($data as $item) {
            $staff_ids[] = $item["provider_id"];
        }
        $availStaffIDs = implode(",", $staff_ids);

        if (isset($_POST['selected_dates'])) {
            $selected_Date = date('Y-m-d', strtotime($_POST['selected_dates']));
            $availQuery = "SELECT distinct a.provider_id FROM ct_week_days_available AS a LEFT JOIN ct_off_days AS o ON a.provider_id = o.user_id WHERE a.provider_id IN ($availStaffIDs) AND o.off_date != '$selected_Date' AND a.off_day='N' group by a.provider_id";
            $availResult = mysqli_query($this->conn, $availQuery);
            $availData = $availResult->fetch_all(MYSQLI_ASSOC);
            $alltech = $this->random_technician();

            $techData = array_column($alltech,'id');

            foreach ($availData as $availItems) {
                $staffIDS[] = $availItems['provider_id'];

            }
            $staffIDS = array_intersect($techData,$staffIDS);
        }

        $staff_id = $staffIDS[array_rand($staffIDS)];
				
	      $same_another_slots = array();
	      $same_another_slots = $this->get_already_booked_slots($cal_starting_date, $time_interval, $service_padding_before, $service_padding_after, $booking_padding_time, $staff_id, $client_order_id,$reschedule);
	      $day_time_slots = $same_another_slots['another_booking_slots'];
			
        $availdataQuery = "SELECT distinct a.provider_id FROM ct_week_days_available AS a LEFT JOIN ct_off_days AS o ON a.provider_id = o.user_id LEFT JOIN ct_bookings AS b ON a.provider_id=b.staff_ids WHERE a.provider_id =$staff_id AND o.off_date != '$selected_Date' AND a.off_day='N' AND b.booking_date_time NOT IN(";
        for($i=0;$i<count($day_time_slots);$i++){
            $availdataQuery .="'".date('Y-m-d H:i:s',$day_time_slots[$i])."',";
        }
        $availdataQuery .= ")";
        $dataquery = str_replace(",)",")",$availdataQuery);
        $dataResult = mysqli_query($this->conn, $dataquery);
        while($staffRow = mysqli_fetch_array($dataResult)){
            $providerID = $staffRow['provider_id'];
        }

            $staff_id = $providerID;
            $_SESSION['staff_id'] = $staff_id;
    }*/
		
		if(is_numeric(strpos($timezonediff,'-'))){
			$timediffmis = str_replace('-','',$timezonediff)*60;
			$currDateTime_withTZ= strtotime("-".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));
		}else{
			$timediffmis = str_replace('+','',$timezonediff)*60;
			$currDateTime_withTZ = strtotime("+".$timediffmis." minutes",strtotime(date('Y-m-d H:i:s')));	
		}
		
		$providerschedule_type=$provider_schedule_type;
		
		$cal_starting_date=$start_date;
		$time_interval=$time_int;		
		
        $day_time_slots = array();
        
		/* showing time schedule for ONE DAY ONLY days */
        /* Get Week number of month for starting date (between 1 to 5) */
        if ($providerschedule_type == 'weekly') {
            $week_id = 1;
        } else {
        	$week_id = $this->get_week_of_month_by_date(date('Y-m-d', strtotime($cal_starting_date)));
        }
    	
		/* if calendar starting date is missing then it will take starting date to current date */
        if ($cal_starting_date == '') {
            $day_id                 = date('N', $currDateTime_withTZ);
            
			/*  add Date as heading of the day column */
            $day_time_slots['date'] = date('Y-m-d', $currDateTime_withTZ);
        } else {
            $day_id                 = date('N', strtotime($cal_starting_date));
           
			/* add Date as heading of the day column */
            $day_time_slots['date'] =date('Y-m-d', strtotime($cal_starting_date));
		
        }
        
		/* check if the day is off day */
        $day_time_slots['off_day'] = $this->check_off_day($day_time_slots['date'],$staff_id);
		
		/* function return day start time and day end time of given provider */
        $time_intervals            = $this->time_slots($day_id, $week_id, $time_interval,$staff_id);
		
		/* calculating starting and end time of day into mintues */				
		if(isset($time_intervals['daystart_time'],$time_intervals['dayend_time'])){		
		$min_day_start_time        = (date('G', strtotime($time_intervals['daystart_time'])) * 60) + date('i',strtotime($time_intervals['daystart_time']));
        $min_day_end_time          = (date('G', strtotime($time_intervals['dayend_time'])) * 60) + date('i',strtotime($time_intervals['dayend_time']));
		
		$min_advnce_allow='Y';
		$advancemins='N';
		if($advance_bookingtime>=1440){
			$advancemins='Y';
			$currdatestr = strtotime(date('Y-m-d '.date('H:i:s',$currDateTime_withTZ)));					
			$withadncebooktime = strtotime("+$advance_bookingtime minutes", $currdatestr);
			$withadncebookdate = date('Y-m-d',strtotime("+$advance_bookingtime minutes", $currdatestr));
			$daystarttimeofdate = strtotime(date($withadncebookdate.' '.$time_intervals['daystart_time']));
			$withadncetime = date('H:i:s',$withadncebooktime);
							
			if(strtotime($cal_starting_date)>strtotime($withadncebookdate)){
				$withadncetime = $time_intervals['daystart_time'];
			}
			
			if(strtotime($cal_starting_date)>=strtotime($withadncebookdate)){
				if($withadncebooktime<$daystarttimeofdate){
					$min_day_start_time = (date('G', strtotime($time_intervals['daystart_time'])) * 60) + date('i',strtotime($time_intervals['daystart_time']));								
						$min_advnce_allow='Y';					
				}else{
				
					$min_day_start_time = (date('G', strtotime($withadncetime)) * 60) + date('i',strtotime($withadncetime));						
					if($min_day_start_time%$time_interval!=0){
						$extraminsadd =  $time_interval-($min_day_start_time%$time_interval);
						$min_day_start_time = $min_day_start_time+$extraminsadd;
					}
				
					$min_advnce_allow='Y';
				}
			}else{
				$min_advnce_allow='N';
			}
		}
		
		$starting_min = $min_day_start_time;
		
		
		/* Adding Service Before Padding Time For First Slot */
		if ($service_padding_before != '') {
			  $starting_min =  $starting_min+$service_padding_before;
        } 
		
        /* check if selected date is today  if yes calculate current time's min to avoid past booking */
        $today                     = false;
        $conditional_min_mins      = 0;
		
        if (strtotime($day_time_slots['date']) == strtotime(date('Y-m-d',$currDateTime_withTZ)) && $advancemins=='N') {
            $today                = true;
            /* total mins of current time */
           $conditional_min_mins = date('G',strtotime(date('Y-m-d H:i:s',$currDateTime_withTZ))) * 60 + date('i',strtotime(date('Y-m-d H:i:s',$currDateTime_withTZ))) ;
        } else {
            $today = false;
																																																						  
        }
		
	   /* add minimum advance booking mins with starting mins for slots */
        if (is_numeric($advance_bookingtime) && $advance_bookingtime < 1440) {
            $conditional_min_mins += ((int)$advance_bookingtime);
        }
    
		
        /* check breaks of the day */
        $day_time_slots['breaks'] = $this->get_day_breaks($week_id, $day_id, $providerschedule_type,$staff_id);
				
        /* check already booked timeslots */
		$same_another_slots = array();							
        $same_another_slots = $this->get_already_booked_slots($cal_starting_date,$time_interval,$service_padding_before,$service_padding_after,$booking_padding_time,$staff_id, $client_order_id,$reschedule);
		$day_time_slots['same_booking_slots'] = $same_another_slots['same_booking_slots'];
		$day_time_slots['booked'] = $same_another_slots['another_booking_slots'];
            /* Check temporary booked slot on behalf of selected service's duration */
        $day_time_slots['temp_booked'] = $this->get_temp_booked_slots($cal_starting_date, $time_interval, $staff_id,$providerschedule_type);
		// get all booked time gap interval added by geek_sonu
        $day_time_slots['duration_of_booking'] = $this->get_booking_time_duration($start_date, $staff_id);

		/* Check provider Offtimes */
		$day_time_slots['offtimes'] = $this->get_provider_offtime($staff_id);
		
        /* Converting time into slots based on given daystart time and dayend time */
        if ($time_intervals['daystart_time'] != '' && $time_intervals['dayend_time'] != '' && $min_advnce_allow=='Y') {
					   
            while ($starting_min < $min_day_end_time) {
                if ($today) {
					if ($starting_min > $conditional_min_mins) {						
																																																						
						 
							
                        $day_time_slots['slots'][] = date('G:i:s', mktime(0, $starting_min, 0, 1, 1, date('Y',$currDateTime_withTZ)));

                    }
                } else {					
                    $day_time_slots['slots'][] = date('G:i:s', mktime(0, $starting_min, 0, 1, 1, date('Y',$currDateTime_withTZ)));
						 
                }
                $starting_min = $starting_min + $time_interval;
												   
            }
        } else {
            $day_time_slots['slots'] = array();
        }		}
		
        return $day_time_slots;		
		
    }

    /* end of function */
	
	function get_timezone_offset($remote_tz, $origin_tz = null) {
		if($origin_tz === null) {
			if(!is_string($origin_tz = date_default_timezone_get())) {
				return false; /* A UTC timestamp was returned -- bail out! */
			}
		}
		$origin_dtz = new DateTimeZone($origin_tz);
		$remote_dtz = new DateTimeZone($remote_tz);
		$origin_dt = new DateTime("now", $origin_dtz);
		$remote_dt = new DateTime("now", $remote_dtz);
		/* $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt); */
		$offset = $origin_dtz->getOffset($remote_dt) - $remote_dtz->getOffset($origin_dt);
		return $offset;
	}
	
	
  //function to get duration of each booking added by geek_sonu
	function get_booking_time_duration($start_date, $staff_id)
	{
	 $return_arr = array();
	 $query = "SELECT `order_id`,`booking_date_time` FROM `ct_bookings` where `booking_date_time` LIKE '%" . $start_date . "%' AND FIND_IN_SET('" . $staff_id . "', staff_ids) group by `order_id`,`booking_date_time` order by `booking_date_time`";
		$result=mysqli_query($this->conn,$query);	
		$counter = 0;
        if($result->num_rows > 0){
            while($offtimes=mysqli_fetch_array($result)){
                $return_arr[$counter]['order_id'] = $offtimes['order_id'];
                $counter++;
            }
        }

		$result11 = array();
		foreach ($return_arr as $recent_orders) {
			$recent_orders_id =  $recent_orders['order_id'];
			$return_arr1 = array();
			$query1="SELECT `order_duration` FROM `ct_order_client_info` WHERE `order_id` = $recent_orders_id";
		   	$result = mysqli_query($this->conn,$query1);
		   	// $result1 =mysqli_fetch_array($result1);
		  	$counter1 = 0;
		  	if(mysqli_num_rows($result) > 0 ){ 
			   	while($res = mysqli_fetch_array($result)){
				
					$result11[] = $res['order_duration'];
					$counter1++;
					// print_r($result1);
				}
													
			}
		
		}									   
		return $result11;
	}
	
	function getWeeks($date, $rollover)
    {
        $cut = substr($date, 0, 8);
        $daylen = 86400;
        $timestamp = strtotime($date);
        $first = strtotime($cut . "00");
        $elapsed = ($timestamp - $first) / $daylen;
        $weeks = 1;
        for ($i = 1; $i <= $elapsed; $i++) {
            $dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
            $daytimestamp = strtotime($dayfind);
            $day = strtolower(date("l", $daytimestamp));
            if ($day == strtolower($rollover)) $weeks++;
        }
        return $weeks;
    }

    public function get_schedule_type_according_provider($staff_id)
    {
        $query = "select * from " . $this->table_name . " where provider_id='" . $staff_id . "' limit 0,1";
        $result = mysqli_query($this->conn, $query);
        $value = mysqli_fetch_row($result);
        return $value;
    }

    function get_weekday_slots($day_id,$week_id,$staffid){
		
		$query = "SELECT `day_start_time`,`day_end_time` FROM `".$this->table_name."` WHERE `off_day`='N' AND `provider_id`='".$staffid."' AND `week_id`='".$week_id."' AND `weekday_id`='".$day_id."'";
		$result = mysqli_query($this->conn,$query);
		return $result;
	}

	function get_day_break($week_id,$day_id,$staff_id){
  		$query="SELECT `break_start`,`break_end` FROM `".$this->table_name2."` WHERE `week_id`='".$week_id."' AND `weekday_id`='" . $day_id . "' AND provider_id='" . $staff_id . "'";
		$result=mysqli_query($this->conn,$query);	
		return $result;
	}
}

?>