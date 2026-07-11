<?php  
	
class cleanto_coupon{
	
	public $coupon_id;
	public $coupon_code;
	public $coupon_type;
	public $coupon_limit;
	public $coupon_value;
	public $coupon_expiry;
	public $status;
	public $conn;
  public $update_wallet_amt;
  public $res_user_update_wallet_amt;
  public $user_coupon_val;
  public $inc;
  public $today_date;
	public $table_name="ct_coupons";
	
	/* 
	* Function for add Coupon in setting 
	*
	*/
	
	public function add_coupon(){
		$query="insert into `".$this->table_name."` (`id`,`coupon_code`,`coupon_type`,`coupon_limit`,`coupon_used`,`coupon_value`,`coupon_expiry`,`status`) values(NULL,'".$this->coupon_code."','".$this->coupon_type."','".$this->coupon_limit."','0','".$this->coupon_value."','".$this->coupon_expiry."','".$this->status."')";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_insert_id($this->conn);
		return $value;
	}
	
	/* 
	* Function for Update Coupon in setting 
	*
	*/
	
	public function update_coupon(){
		$query="update `".$this->table_name."` set `coupon_code`='".$this->coupon_code."',`coupon_type`='".$this->coupon_type."',`coupon_limit`='".$this->coupon_limit."',`coupon_used`='0',`coupon_value`='".$this->coupon_value."',`coupon_expiry`='".$this->coupon_expiry."',`status`='".$this->status."' where `id`='".$this->coupon_id."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	
	/* 
	*Function for Delete Coupon in setting
	*
	*/
	
	public function delete_coupon(){
		$query="delete from `".$this->table_name."` where `id`='".$this->coupon_id."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	
	/* 
	* Function for Read All Coupon in setting
	*
	*/
	
	public function readall(){
		$query="select * from `".$this->table_name."`";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	
	/* 
	* Function for Read One Coupon in setting
	* 
	*/
	
	public function readone(){
		$query="select * from `".$this->table_name."` where `id`='".$this->coupon_id."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	
	
	/* 
	* Function for Update Status of Coupon in setting
	*
	*/
	
	public function updatestatus(){
		$query="update `".$this->table_name."` set `status`='".$this->coupon_status."' where `id`='".$this->coupon_id."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/**/
	public function checkcode(){
    $query="select * from `".$this->table_name."` where `coupon_code`='".$this->coupon_code."'";
    $result=mysqli_query($this->conn,$query);
    $value=mysqli_fetch_array($result);
    return $value;
  }

  public function checkcouponcode($user_coupon_val){
    $query="select * from ct_referral_coupon where `referral_coupon`='".$user_coupon_val."'";
    $result=mysqli_query($this->conn,$query);
    $value=mysqli_fetch_array($result);
    return $value;
  }

  public function check_referral_code($referral_code){
    $query="select * from `ct_users` where `referal_code`='".$referral_code."'";
    $result=mysqli_query($this->conn,$query);
    $value=mysqli_fetch_array($result);
    return $value;
  }
   
	   public function getdate(){
		   $query="select `coupon_expire` from `".$this->table_name."` where `coupon_code`='".$this->coupon_code."'";
		   $result=mysqli_query($this->conn,$query);
		   $value=mysqli_fetch_array($result);
		   return $value;
	   }
	   /**/
	   public function update_coupon_limit(){
		   $query="update `".$this->table_name."` set `coupon_used`='".$this->inc."' where `coupon_code`='".$this->coupon_code."'";
		   $result=mysqli_query($this->conn,$query);
		   return $result;
	   }

     /**/
     public function update_user_coupon_limit($user_coupon_val){
      $query="update `ct_referral_coupon` set `coupon_used`='".$this->inc."' where `referral_coupon`='".$user_coupon_val."'";
       $result=mysqli_query($this->conn,$query);
       return $result;
     }


	   /**/
	   public function update_coupon_used(){
		  $query="update `".$this->table_name."` set `coupon_used`='".$this->dec."' where `coupon_code`='".$this->coupon_code."'";
		  $result=mysqli_query($this->conn,$query);
		  return $result;
	   }


    public function get_login_user_wallet($client_id){
      $query="select `wallet_amount` from `ct_users` where `id`='".$client_id."'";
      $result=mysqli_query($this->conn,$query);
      $value=mysqli_fetch_array($result);
      return $value;
    }

    public function update_login_user_wallet($client_id){
      $query="update `ct_users` set `wallet_amount`='".$this->update_wallet_amt."' WHERE id='".$client_id."'";
      $result=mysqli_query($this->conn,$query);
      return $result;
    }

    public function update_refernce_user_wallet($res_is){
      $query="update `ct_users` set `wallet_amount`='".$this->res_user_update_wallet_amt."' where `id`='".$res_is."'";
      $result=mysqli_query($this->conn,$query);
      return $result;
    }
	public function special_offer() {
			$query="SELECT * FROM `ct_special_offer` WHERE `coupon_date` = '".$this->today_date."'";
			$result=mysqli_query($this->conn,$query);
			return $result;
		}
}
?>