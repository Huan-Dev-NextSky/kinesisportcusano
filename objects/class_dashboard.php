<?php  

class cleanto_dashboard{
        /* get all data for popup of the click */
        /* get cleint detail */
        public $recurring_id;
        public $conn;
        public function getclient($id)
        {
            $query = "SELECT * FROM `ct_users` WHERE `id` = $id";
            $result=mysqli_query($this->conn,$query);
			if(!empty($result)){
				$value=mysqli_fetch_row($result);
				return $value;
			}
		}
/* get guest client info */
        public function getguestclient($orderid)
        {
          $query = "SELECT * FROM `ct_order_client_info` WHERE `order_id` = $orderid";
          $result=mysqli_query($this->conn,$query);
          $value=mysqli_fetch_row($result);
          return $value;
        }
        /* get client order for popup */
        public function getclientorder($orderid)
        {
          $query = "SELECT DISTINCT `b`.`booking_date_time`,
					`s`.`title`,
					`p`.`net_amount`,
					`b`.`client_id`,
					`b`.`order_id`,
					`p`.`payment_method`,
					`b`.`booking_status`,
					`b`.`reject_reason`,
					`oci`.`order_duration`,
					`b`.`staff_ids`,
                    `oci`.`recurring_id`
                    
					FROM `ct_bookings` as `b`,`ct_services` as `s`,`ct_payments` as `p`,`ct_order_client_info` as `oci`
                    WHERE `b`.`service_id` = `s`.`id`
                    and `b`.`order_id` = `p`.`order_id`
					and `b`.`order_id` = `oci`.`order_id`
                    and `b`.`order_id` = $orderid ";
            $result = mysqli_query($this->conn, $query);
            $value = mysqli_fetch_row($result);
           
            return $value;
        } 
        /* notificatrion code */
    /* get total no of bookings */
    public function getallbookings_notify(){		
        $query = "SELECT DISTINCT `b`.`read_status`,  `b`.`order_id`, `b`.`booking_status`, `b`.`booking_date_time`, `b`.`lastmodify`, `b`.`client_id`, `s`.`title` FROM `ct_bookings` as `b`,`ct_services` as `s` WHERE `b`.`service_id` = `s`.`id`  ORDER BY `b`.`lastmodify` DESC";
        $result=mysqli_query($this->conn,$query);
        return $result;
    }
/* get total no of bookings */
    public function getallbookingsunread_count(){
        $query = "SELECT DISTINCT `order_id` FROM `ct_bookings` WHERE `read_status` = 'U'  ORDER BY `order_id` DESC";
        $result=mysqli_query($this->conn,$query);
        return $result;
    }
    /* Confirm the booking */
        public function confirm_bookings($orderid,$lastmodify)
        {
            $query="update `ct_bookings` set `booking_status`='C',`lastmodify` = '".$lastmodify."' where `order_id`='".$orderid."' ";
            $result=mysqli_query($this->conn,$query);
            return $result;
        }
        /* function to update the read ststus of the notification */
        public function update_read_status($orderid){
            $query="update `ct_bookings` set `read_status`='R' where `order_id`='".$orderid."' ";
            $result=mysqli_query($this->conn,$query);
            return $result;
        }
        /* reject the order/bookings */
        public function reject_bookings($orderid,$reason,$lastmodify){
            $query="update `ct_bookings` set `booking_status`='R',`reject_reason`='".$reason."',`lastmodify` = '".$lastmodify."' where `order_id`='".$orderid."'";
            $result=mysqli_query($this->conn,$query);
            return $result;
        }
        /*  delete the booking */
        public function delete_booking($orderid)
        {
            /* ct_staff_commission */
            $query5 = "delete from `ct_staff_commission` where `order_id`='".$orderid."'";
            $result5=mysqli_query($this->conn,$query5);
            /* bookings */
            $query1 = "delete from `ct_bookings` where `order_id`='".$orderid."'";
            $result=mysqli_query($this->conn,$query1);
            /* booking_addons */
            $query2 = "delete from `ct_booking_addons` where `order_id`='".$orderid."'";
            $result=mysqli_query($this->conn,$query2);
            /* payments */
            $query3 = "delete from `ct_payments` where `order_id`='".$orderid."'";
            $result=mysqli_query($this->conn,$query3);
            /* order_client_info */
            $query4 = "delete from `ct_order_client_info` where `order_id`='".$orderid."'";
            $result=mysqli_query($this->conn,$query4);
        }
        public function delete_recurring_booking($recurring_id1)
        {
            
            /* order_client_info */
            $query5 = "delete `ct_bookings`.*,`ct_payments`.*,`ct_order_client_info`.* from `ct_bookings` INNER JOIN `ct_payments` on `ct_bookings`.`order_id`=`ct_payments`.`order_id` INNER JOIN `ct_order_client_info` on `ct_bookings`.`order_id`=`ct_order_client_info`.`order_id` where `recurring_id`='".$recurring_id1."'";
            // print_r($query5);
            // die;
            $result=mysqli_query($this->conn,$query5);
        }
        /* get total guest users */
        public function total_guest_users(){
            $query="select DISTINCT count(*) from `ct_bookings` where `client_id` = 0  ORDER BY `order_id`;";
            $result=mysqli_query($this->conn,$query);
            return count(mysqli_num_rows($result));
        }
		
		/* newly added */
    public function clientemailsender($orderid)
    {
 $query="select `s`.`title`,`oci`.`client_name`,`oci`.`client_email`,`b`.`booking_date_time`,`a`.`email`, `a`.`fullname`
from
`ct_order_client_info` as `oci`,`ct_bookings` as `b`,`ct_services` as `s` , `ct_admin_info` as `a`
where
`b`.`order_id` = '".$orderid."'
and `b`.`order_id`  = `oci`.`order_id`
and `b`.`service_id` = `s`.`id`";
            $result=mysqli_query($this->conn,$query);
        $value=mysqli_fetch_array($result);
        return $value;
    }
	
	
	 /*function to count total no of services */
        public function countallservice()
        {
            $query="select count(*) as `c` from `ct_services`";
            $result=mysqli_query($this->conn,$query);
            $value= @mysqli_fetch_row($result);
            return $value= isset($value[0])? $value[0] : '' ;
        }
    /*NEWLY ADDED FUNCTIONS */
    /*SMS TEMPLATE GET FOR CONFIRM*/
    public function gettemplate_sms($action,$user){
        $query="select * from `ct_sms_templates` where `sms_template_type` = '".$action."' and `user_type` = '".$user."'";
        $result=mysqli_query($this->conn,$query);
        $value= @mysqli_fetch_row($result);
        return $value;
    }
		/* get client order for popup api */
        public function getclientorder_api($orderid)
        {
           $query = "SELECT DISTINCT `b`.`booking_date_time`,
					`s`.`title`,
					`p`.`net_amount`,
					`b`.`client_id`,
					`b`.`order_id`,
					`p`.`payment_method`,
					`b`.`booking_status`
					FROM `ct_bookings` as `b`,`ct_services` as `s`,`ct_payments` as `p`
                    WHERE `b`.`service_id` = `s`.`id`
                    and `b`.`order_id` = `p`.`order_id`
                    and `b`.`order_id` = $orderid ";
            $result = mysqli_query($this->conn, $query);
            $value = mysqli_fetch_row($result);
			
            return $value;
        }
}
?>