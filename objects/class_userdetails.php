<?php   

class cleanto_userdetails {
	public $id;
	public $firstname;
	public $lastname;
	public $password;
	public $phone;
	public $address;
	public $city;
	public $state;
	public $zip;
	public $dob;
	public $sms_opt_in;
	public $external_customer_id;
	public $email;
	public $limit;
	public $offset;
	public $order_id;
	public $add_amount;
	public $wallet_status;
	public $wallet_trans_id;
	public $wallet_method;
	public $lastmodify;
	public $update_money;
	public $tablename="ct_users";
	public $tableadmin="ct_admin_info";
  public $tablewallet="ct_wallet_history";
	public $reason;
	public $conn;
	/*Function for Read Only one data matched with Id*/
	public function readone(){
		$query="select `id`, `user_email`, `user_pwd`, `first_name`, `last_name`, `phone`, `zip`, `address`, `city`, `state`, `notes`, `vc_status`, `p_status`, `contact_status`, `status`, `usertype`, `cus_dt`, `stripe_id`, `referal_code`, `wallet_amount`, `external_customer_id`, `dob`, `sms_opt_in` from `".$this->tablename."` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value;
	}
	public function readone_assoc(){
		$query="select * from `".$this->tablename."` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_assoc($result);
		return $value;
	}
	/*Function for Update user profile*/
	public function update_profile(){
		$address = mysqli_real_escape_string($this->conn,$this->address);
		$dobSql = !empty($this->dob) ? "`dob`='" . mysqli_real_escape_string($this->conn, $this->dob) . "'," : "";
		$smsOptSql = !empty($this->sms_opt_in) ? "`sms_opt_in`='" . mysqli_real_escape_string($this->conn, $this->sms_opt_in) . "'," : "";
		$query="update `".$this->tablename."` set `first_name`='".mysqli_real_escape_string($this->conn, $this->firstname)."'
		,`phone`='".mysqli_real_escape_string($this->conn, $this->phone)."'
		,`last_name`='".mysqli_real_escape_string($this->conn, $this->lastname)."'
		,`address`='".$address."'
		,`city`='".mysqli_real_escape_string($this->conn, $this->city)."'
		,`state`='".mysqli_real_escape_string($this->conn, $this->state)."'
		,`zip`='".mysqli_real_escape_string($this->conn, $this->zip)."'
		,`user_pwd`='".mysqli_real_escape_string($this->conn, $this->password)."'
		, {$dobSql} {$smsOptSql} `id`='".$this->id."'
		where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* GET USER DETAIL FOR MY_APPOINTMENT PAGE */
	public function get_user_details(){
		$query="select DISTINCT `b`.`order_id`, `p`.`frequently_discount`, `p`.`recurrence_status`, `p`.`payment_status`, `p`.`payment_method`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`, `b`.`change_request_status`, `b`.`cancel_reason`, `b`.`reschedule_reason`, `b`.`reschedule_requested_date`, `s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_users` as `u` where `b`.`client_id` = `u`.`id` and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`id` = $this->id  group by `b`.`order_id` order by `b`.`order_id` desc";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}

	/* CUSTOMER REQUEST CANCELLATION (Pending Root Admin confirmation) */
	public function request_cancel_booking($orderid, $reason, $lastmodify){
		$reasonEsc = mysqli_real_escape_string($this->conn, $reason);
		$lastmodifyEsc = mysqli_real_escape_string($this->conn, $lastmodify);
		$query = "UPDATE `ct_bookings` SET `change_request_status` = 'CANCEL_REQUESTED', `cancel_reason` = '{$reasonEsc}', `lastmodify` = '{$lastmodifyEsc}' WHERE `order_id` = " . (int)$orderid;
		return mysqli_query($this->conn, $query);
	}

	/* CUSTOMER REQUEST RESCHEDULE (Pending Root Admin confirmation) */
	public function request_reschedule_booking($orderid, $newDate, $reason, $lastmodify){
		$newDateEsc = mysqli_real_escape_string($this->conn, $newDate);
		$reasonEsc = mysqli_real_escape_string($this->conn, $reason);
		$lastmodifyEsc = mysqli_real_escape_string($this->conn, $lastmodify);
		$query = "UPDATE `ct_bookings` SET `change_request_status` = 'RESCHEDULE_REQUESTED', `reschedule_requested_date` = '{$newDateEsc}', `reschedule_reason` = '{$reasonEsc}', `lastmodify` = '{$lastmodifyEsc}' WHERE `order_id` = " . (int)$orderid;
		return mysqli_query($this->conn, $query);
	}

	/** Whether the customer opted in to booking SMS (default Y if unknown). */
	public function is_sms_opted_in($user_id){
		$user_id = (int)$user_id;
		if ($user_id <= 0) {
			return true;
		}
		$res = mysqli_query($this->conn, "SELECT `sms_opt_in` FROM `".$this->tablename."` WHERE `id` = {$user_id} LIMIT 1");
		if ($res && ($row = mysqli_fetch_assoc($res))) {
			return (!isset($row['sms_opt_in']) || $row['sms_opt_in'] === '' || $row['sms_opt_in'] === 'Y');
		}
		return true;
	}
	/* This Function For API */
	public function get_user_details_api(){
		$query="select DISTINCT `p`.`order_id`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`, `b`.`change_request_status`, `b`.`cancel_reason`, `b`.`reschedule_reason`, `b`.`reschedule_requested_date`, `s`.`title`,`p`.`net_amount` as `total_payment`,`p`.`payment_status`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_users` as `u` where `b`.`client_id` = `u`.`id` and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`id` = '".$this->id."'  order by `b`.`order_id` desc limit ".$this->limit." offset ".$this->offset;
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* GET APPOINTMENTS ASSIGNED STAFF for API*/
	public function get_staff_details_api(){
		$query  = "select DISTINCT `p`.`order_id`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`,`s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_admin_info` as `u` where `b`.`staff_ids` = '".$this->id."' and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`role`= 'doctor'  order by `b`.`booking_date_time` desc";
		$result = mysqli_query($this->conn, $query);
		return $result;
	}
	/* GET NOTES FO THE USER FOR RESCHEDULE */
	public function get_user_notes($orderid){
		$query="select `client_personal_info` from `ct_order_client_info` where `order_id` = $orderid";
		$result=mysqli_query($this->conn,$query);
		$value = mysqli_fetch_row($result);
		return $value;
	}
	/* update the booking datails of user */
	public function reschedule_booking($finaldate,$orderid,$bookingstatus,$readstatus,$lastmodify){
		$query ="UPDATE `ct_bookings` SET `booking_date_time` = '".$finaldate."',`booking_status` = '".$bookingstatus."',`read_status` = '".$readstatus."',`lastmodify` = '".$lastmodify."' where `order_id` = $orderid";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* UPDATE CLIENT PERSONAL INFO IN ORDER CLIENT INFO AFTER RESCHEDULE */
	public function update_notes($orderid,$client_personal_info){
		$query ="UPDATE `ct_order_client_info` SET `client_personal_info` = '".$client_personal_info."' where `order_id` = $orderid";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* UPDATE STATUS OF BOOKING IF USER CANCEL BY ITES OWN */
	public function update_booking_of_user($orderid,$reason,$lastmodify){
		$query ="UPDATE `ct_bookings` SET `booking_status` = 'CC' ,`reject_reason`='".$reason."',`lastmodify` = '".$lastmodify."' where `order_id` = $orderid";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	public function check_customer_email_existing(){
	  echo $query="select * from `".$this->tablename."` where `user_email`='".$this->email."'";
		$result=mysqli_query($this->conn,$query);
		$value = mysqli_num_rows($result);
		return $value;
	}
	 
	 public function check_admin_email_existing(){
	  $query="select * from `".$this->tableadmin."` where `email`='".$this->email."'";
		$result=mysqli_query($this->conn,$query);
		$value = mysqli_num_rows($result);
		return $value;
	}

  /* Jay Wankhede */
  public function get_user_referral_code(){
    $query="SELECT `u`.`first_name` , `c`.`referral_coupon`, `c`.`coupon_used` FROM `ct_referral_coupon` as `c` INNER JOIN `ct_users` as `u` ON `u`.`id` = `c`.`friend_referral_id` WHERE client_id='".$this->id."'";
    $result = mysqli_query($this->conn,$query);
    return $result;
  }

  public function get_user_wallet_details(){
    $query="select `wallet_amount`,`user_email` from `".$this->tablename."` where `id`='".$this->id."'";
    $result=mysqli_query($this->conn,$query);
    $value = mysqli_fetch_row($result);
    return $value;
  }

  public function add_wallet_history(){
   $query="INSERT INTO `".$this->tablewallet."` (`client_id`, `wallet_amount`, `wallet_amount_status`, `wallet_trans_id`, `wallet_method`,`lastmodify`) VALUES ('".$this->id."','".$this->add_amount."','".$this->wallet_status."','".$this->wallet_trans_id."','".$this->wallet_method."','".$this->lastmodify."')";
    $result=mysqli_query($this->conn,$query);
    return $result;
  }

  public function update_wallet_amount(){
    $query="update `".$this->tablename."` set `wallet_amount`='".$this->update_money."'
    where `user_email`='".$this->email."' ";
    $result=mysqli_query($this->conn,$query);
    return $result;
  } 
  
  public function update_wallet_amount_withid(){
    $query="update `".$this->tablename."` set `wallet_amount`='".$this->update_money."'
    where `id`='".$this->id."' ";
    $result=mysqli_query($this->conn,$query);
    return $result;
  }

  public function get_wallet_history_details(){
    $query="select * from `".$this->tablewallet."` where `client_id`='".$this->id."' ORDER BY `id` DESC";
    $result=mysqli_query($this->conn,$query);
    return $result;
  }
	
	
  public function get_user_name(){
    $query="select `first_name` from `".$this->tablename."` where `user_email`='".$this->email."'";
    $result=mysqli_query($this->conn,$query);
    $value = mysqli_fetch_row($result);
	return $temp= isset($value[0])? $value[0] : '' ;
  }
  
  public function get_customer_booking(){
	$query="select DISTINCT `p`.`order_id`, `p`.`frequently_discount`, `p`.`recurrence_status`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`,`s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids`,`u`.`first_name`,`u`.`last_name`,`p`.`taxes`,`p`.`payment_status`,`u`.`payment_method_id`,`b`.`client_id` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_users` as `u` where `b`.`client_id` = `u`.`id` and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `b`.`order_id` = '".$this->order_id."'  order by `b`.`order_id` desc"; 
	$result=mysqli_query($this->conn,$query);
	return $result;
  }
}
?>