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
	public $tablename="ct_users";
	public $tableadmin="ct_admin_info";
  public $tablewallet="ct_wallet_history";
	public $reason;
	public $conn;
	/*Function for Read Only one data matched with Id*/
	public function readone(){
		$query="select * from `".$this->tablename."` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value;
	}
	/*Function for Update service-Not Used in this*/
	public function update_profile(){
		$address = mysqli_real_escape_string($this->conn,$this->address);
		echo $query="update `".$this->tablename."` set `first_name`='".$this->firstname."'
		,`phone`='".$this->phone."'
		,`last_name`='".$this->lastname."'
		,`address`='".$address."'
		,`city`='".$this->city."'
		,`state`='".$this->state."'
		,`zip`='".$this->zip."'
		,`user_pwd`='".$this->password."'
		where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* GET USER DETAIL FOR MY_APPOINTMENT PAGE */
	public function get_user_details(){
		$query="select DISTINCT `b`.`order_id`, `p`.`frequently_discount`, `p`.`recurrence_status`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`,`s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_users` as `u` where `b`.`client_id` = `u`.`id` and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`id` = $this->id  group by `b`.`order_id` order by `b`.`order_id` desc";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* This Function For API */
	public function get_user_details_api(){
		$query="select DISTINCT `p`.`order_id`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`,`s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_users` as `u` where `b`.`client_id` = `u`.`id` and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`id` = '".$this->id."'  order by `b`.`order_id` desc limit ".$this->limit." offset ".$this->offset;
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* GET APPOINTMENTS ASSIGNED STAFF for API*/
	public function get_staff_details_api(){
		$query  = "select DISTINCT `p`.`order_id`, `b`.`booking_date_time`, `b`.`booking_status`, `b`.`reject_reason`,`s`.`title`,`p`.`net_amount` as `total_payment`,`b`.`gc_event_id`,`b`.`gc_staff_event_id`,`b`.`staff_ids` from `ct_bookings` as `b`,`ct_payments` as `p`,`ct_services` as `s`,`ct_admin_info` as `u` where `b`.`staff_ids` = '".$this->id."' and `b`.`service_id` = `s`.`id` and `b`.`order_id` = `p`.`order_id` and `u`.`role`= 'staff'  order by `b`.`booking_date_time` desc";
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