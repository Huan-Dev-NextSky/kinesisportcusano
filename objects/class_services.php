<?php  

class cleanto_services{
	public $id;
	public $title;
	public $description;
	public $color;
	public $image;
	public $status;
	public $position;
	public $price = 0;
	public $duration = 0;
	public $tablename="ct_services";
	public $table_name="ct_setting_design";
	public $table_name_sa="ct_services_addon";
	public $table_name_sm="ct_services_method";
	public $table_name_smu="ct_service_methods_units";
	public $table_name_smur="ct_services_methods_units_rate";
	public $table_name_smd="ct_service_methods_design";
	public $table_name_sar="ct_addon_service_rate";
	public $conn;

	/** Convert duration values (minutes int OR HH:MM:SS / HH:MM) to minutes. */
	public static function duration_to_minutes($value){
		if ($value === null || $value === '') {
			return 0;
		}
		if (is_numeric($value) && strpos((string)$value, ':') === false) {
			return max(0, (int)$value);
		}
		$str = trim((string)$value);
		if (preg_match('/^(\d+):(\d{1,2})(?::(\d{1,2}))?$/', $str, $m)) {
			$h = (int)$m[1];
			$min = (int)$m[2];
			$sec = isset($m[3]) ? (int)$m[3] : 0;
			return max(0, ($h * 60) + $min + (int)round($sec / 60));
		}
		return max(0, (int)$str);
	}

	/** Ensure price/duration columns exist; migrate VARCHAR HH:MM:SS duration to INT minutes. */
	public function ensure_price_duration_schema(){
		$cols = array();
		$res = mysqli_query($this->conn, "SHOW COLUMNS FROM `".$this->tablename."`");
		if ($res) {
			while ($row = mysqli_fetch_assoc($res)) {
				$cols[$row['Field']] = $row;
			}
		}
		if (!isset($cols['price'])) {
			mysqli_query($this->conn, "ALTER TABLE `".$this->tablename."` ADD `price` DOUBLE NOT NULL DEFAULT '0' AFTER `image`");
		}
		if (!isset($cols['duration'])) {
			mysqli_query($this->conn, "ALTER TABLE `".$this->tablename."` ADD `duration` INT(11) NOT NULL DEFAULT '0' AFTER `price`");
		} else {
			$type = strtolower((string)$cols['duration']['Type']);
			if (strpos($type, 'varchar') !== false || strpos($type, 'char') !== false || strpos($type, 'text') !== false) {
				/* Convert HH:MM:SS values to temporary int column then swap */
				@mysqli_query($this->conn, "ALTER TABLE `".$this->tablename."` ADD `duration_minutes` INT(11) NOT NULL DEFAULT '0'");
				$services = mysqli_query($this->conn, "SELECT `id`, `duration` FROM `".$this->tablename."`");
				if ($services) {
					while ($svc = mysqli_fetch_assoc($services)) {
						$mins = self::duration_to_minutes($svc['duration']);
						mysqli_query($this->conn, "UPDATE `".$this->tablename."` SET `duration_minutes`='".$mins."' WHERE `id`='".(int)$svc['id']."'");
					}
				}
				@mysqli_query($this->conn, "ALTER TABLE `".$this->tablename."` DROP COLUMN `duration`");
				@mysqli_query($this->conn, "ALTER TABLE `".$this->tablename."` CHANGE `duration_minutes` `duration` INT(11) NOT NULL DEFAULT '0'");
			}
		}
		/* Seed from first unit when service still has zeros */
		$services = mysqli_query($this->conn, "SELECT `id`,`price`,`duration` FROM `".$this->tablename."`");
		if ($services) {
			while ($svc = mysqli_fetch_assoc($services)) {
				$need_price = ((float)$svc['price'] == 0);
				$need_dur = (self::duration_to_minutes($svc['duration']) == 0);
				if (!$need_price && !$need_dur) {
					continue;
				}
				$unit = mysqli_query($this->conn, "SELECT `base_price`,`uduration` FROM `".$this->table_name_smu."` WHERE `services_id` = '".(int)$svc['id']."' ORDER BY `id` ASC LIMIT 1");
				$u = $unit ? mysqli_fetch_assoc($unit) : null;
				if (!$u) {
					continue;
				}
				$new_price = $need_price ? (float)$u['base_price'] : (float)$svc['price'];
				$new_dur = $need_dur ? (int)$u['uduration'] : self::duration_to_minutes($svc['duration']);
				mysqli_query($this->conn, "UPDATE `".$this->tablename."` SET `price`='".$new_price."', `duration`='".$new_dur."' WHERE `id`='".(int)$svc['id']."'");
			}
		}
	}

	/** Keep method units in sync so booking cart still uses unit price/duration. */
	public function sync_price_duration_to_units($service_id){
		$service_id = (int)$service_id;
		$price = (float)$this->price;
		$duration = (int)$this->duration;
		$units = mysqli_query($this->conn, "SELECT `id` FROM `".$this->table_name_smu."` WHERE `services_id` = '".$service_id."'");
		if ($units && mysqli_num_rows($units) > 0) {
			mysqli_query($this->conn, "UPDATE `".$this->table_name_smu."` SET `base_price`='".$price."', `uduration`='".$duration."' WHERE `services_id`='".$service_id."'");
			return true;
		}
		/* Create a default method + unit so the service is bookable */
		$method_title = mysqli_real_escape_string($this->conn, 'Standard');
		mysqli_query($this->conn, "INSERT INTO `".$this->table_name_sm."` (`id`,`service_id`,`method_title`,`status`,`position`) VALUES (NULL,'".$service_id."','".$method_title."','E','0')");
		$method_id = (int)mysqli_insert_id($this->conn);
		if ($method_id <= 0) {
			return false;
		}
		$unit_title = mysqli_real_escape_string($this->conn, 'Session');
		mysqli_query($this->conn, "INSERT INTO `".$this->table_name_smu."` (`id`,`services_id`,`methods_id`,`units_title`,`base_price`,`minlimit`,`maxlimit`,`status`,`position`,`limit_title`,`unit_symbol`,`half_section`,`uduration`) VALUES (NULL,'".$service_id."','".$method_id."','".$unit_title."','".$price."','1','1','E','0','','','D','".$duration."')");
		return true;
	}
	
	/* Function for Add service*/
	public function add_service(){
		$query="insert into `".$this->tablename."` (`id`,`title`,`description`,`color`,`image`,`price`,`duration`,`status`,`position`) values(NULL,'".$this->title."','".$this->description."','".$this->color."','".$this->image."','".(float)$this->price."','".(int)$this->duration."','".$this->status."','".$this->position."')";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_insert_id($this->conn);
		return $value;
	}
	/* Function for Update service-Not Used in this*/
	public function update_service(){
		$query="update `".$this->tablename."` set `title`='".$this->title."',`description`='".$this->description."',`image`='".$this->image."',`color`='".$this->color."',`price`='".(float)$this->price."',`duration`='".(int)$this->duration."' where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* Function for Delete service*/
	public function delete_service(){
		$query="delete from `".$this->tablename."` where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* function to get all addons if exist with the service id*/
	public function get_exist_addons_by_serviceid($id){
		$query="select `ct_services_addon`.* from `ct_services`,`ct_services_addon` where `ct_services`.`id` = `ct_services_addon`.`service_id` and `ct_services`.`id` = $id";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* function to get all addons_rate if exist with the addon id*/
	public function get_exist_addons_rate_by_addonid($id){
		$query = "SELECT * FROM `ct_addon_service_rate` where `addon_service_id` = $id";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* function to get all methods if exist with the service id*/
	public function get_exist_methods_by_serviceid($id){
		$query="select `ct_services_method`.* from `ct_services`,`ct_services_method` where `ct_services`.`id` = `ct_services_method`.`service_id` and `ct_services`.`id` = $id";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* function to get all methods_unit if exist with the service id*/
	public function get_exist_methods_units_by_methodid($id){
		$query = "SELECT * FROM `ct_service_methods_units` where `methods_id` = $id";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}	
	/* function to get all methods_unit if exist with the service id*/
	public function get_exist_methods_units_rate_by_unitid($id){
		$query = "SELECT * FROM `ct_services_methods_units_rate` WHERE `units_id`=$id";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/*  delete all addons with the particular service */	 
	public function delete_addons_of_service($addonid){
		$query="delete from `".$this->table_name_sa."` where `id`=$addonid";
		mysqli_query($this->conn,$query);
	}
	/*  delete the rate of the addons  */
	public function delete_addons_rate($addon_rate_id){
		$query="delete from `".$this->table_name_sar."` where `id`=$addon_rate_id";
		mysqli_query($this->conn,$query);
	}
	/*  delete all method unit rate */
	public function delete_service_method_unit_rate($method_unit_rate_id){
		$query="delete from `".$this->table_name_smur."` where `id`=$method_unit_rate_id";
		mysqli_query($this->conn,$query);
	}
	/*  delete all method unit */
	public function delete_method_unit($method_unit){
		$query="delete from `".$this->table_name_smu."` where `id`=$method_unit";
		mysqli_query($this->conn,$query);
	}
	/*  delete all method */
	public function delete_method($methodid){
		$query="delete from `".$this->table_name_sm."` where `id`=$methodid";
		mysqli_query($this->conn,$query);
		$query="delete from `".$this->table_name_smd."` where `service_methods_id`=$methodid";
		mysqli_query($this->conn,$query);
	}
	/*  NEWLY ADDED */
	/* Function for Read All data from table */
	public function readall(){
		$query="select DISTINCT `ct_services`.* from `ct_services`, `ct_services_method`, `ct_service_methods_units` where `ct_services`.`status` = 'E' and `ct_services_method`.`status` = 'E' and `ct_service_methods_units`.`status` = 'E' and `ct_services_method`.`service_id` = `ct_services`.`id` and `ct_service_methods_units`.`services_id` = `ct_services`.`id` ORDER BY `ct_services`.`position`";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* Function for Read Only one data matched with Id*/
	public function readone(){
		/* Legacy [1]=title — external_service_id/price/duration appended */
		$query="select `id`, `title`, `description`, `color`, `image`, `status`, `position`, `external_service_id`, `price`, `duration` from `".$this->tablename."` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value;
	}
	/* Function to fetch all data in admin panel*/
	public function getalldata(){
		$query="select `id`, `title`, `description`, `color`, `image`, `status`, `position`, `external_service_id`, `price`, `duration` from `".$this->tablename."` order by `position`";
		$result=mysqli_query($this->conn,$query);
		
		return $result;
	}
	/*  function to update the position of the services*/
	public function updateposition(){
		$query="update `".$this->tablename."` set `position`='".$this->position."' where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* Function to update the status of  services */
	public function  changestatus(){
		$query="update `".$this->tablename."` set `status`='".$this->status."' where `id`='".$this->id."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* function to count total no of services */
	public function countallservice(){
		$query="select count(*) as `c` from `".$this->tablename."`";
		$result=mysqli_query($this->conn,$query);
		if($result){
			$value=mysqli_fetch_row($result);
			return $value= isset($value[0])? $value[0] : '' ;
		} else { return false; }
	}
	/*  to get design type to show in front end */
	// public function get_setting_design($title){
	// 	$this->title=$title;
	// 	$query="select `design` from `".$this->table_name."` where `title`='".$this->title."'";
	// 	$result=mysqli_query($this->conn,$query);
	// 	if($result){
	// 		$value=mysqli_fetch_row($result);
	// 		return $value= isset($value[0])? $value[0] : '' ;
	// 	}  else  { return false; }
	// }
	/*  get last inserted record */
	public function getlast_record_insert(){
		$query = "select MAX(`id`) from `ct_services`";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value= isset($value[0])? $value[0] : '' ;
	}
	/*  update record to insert image name in the inserted record */
	public function update_recordfor_image($insertedid){
		$query="update `".$this->tablename."` set `image`='".$this->image."' where `id`='".$insertedid."' ";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/*  Check for the bookings of the services */
	public function service_isin_use($id){
		 $query = "select * from `ct_bookings` where `ct_bookings`.`service_id` = $id  LIMIT 1";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value= isset($value[0])? $value[0] : '' ;
	}
	/* Update Image in services*/
	public function update_image(){
		$query="update `".$this->tablename."` set `image`='".$this->image."' where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/* Function for get service name for confirm booking mail in frontend*/
	public function get_service_name_for_mail(){
		$query="select `title` from `ct_services` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value= isset($value[0])? $value[0] : '' ;
	}
	/*  check for the entry of the same title */
	public function check_same_title(){
		$query = "select * from `ct_services` where `title`='".ucwords($this->title)."'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/*  TO GET ALL IMAGES NAMES FROM SERVICE,ADDONS TABLE FOR DELETING NOT USED IN DIRECTORY */
	public function get_used_images(){
		$query = "SELECT `s`.`image` as `image` FROM `ct_services` as `s` UNION SELECT `sa`.`image` as `image` FROM `ct_services_addon` as `sa` UNION SELECT `setim`.`option_value` as `setimage` FROM `ct_settings` as `setim` WHERE `option_name` = 'ct_company_logo'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	/*  TO GET ALL IMAGES NAMES FROM SERVICE,ADDONS TABLE FOR DELETING NOT USED IN DIRECTORY */
	public function get_used_staff_images(){
		$query = "select `image` as `image` from `ct_admin_info` where `role`='doctor'";
		$result=mysqli_query($this->conn,$query);
		return $result;
	}
	public function readall_for_frontend_services(){
		$query="(select `ct_services`.* from `ct_services`, `ct_services_method`, `ct_service_methods_units` where `ct_services`.`status` = 'E' and ((`ct_services_method`.`status` = 'E' and `ct_service_methods_units`.`status` = 'E' and `ct_services_method`.`service_id` = `ct_services`.`id` and `ct_service_methods_units`.`services_id` = `ct_services`.`id`)) group by `ct_services`.`id`, `ct_services`.`title`, `ct_services`.`description`, `ct_services`.`color`, `ct_services`.`image`, `ct_services`.`status`, `ct_services`.`position` ORDER BY `position`) UNION (select `ct_services`.* from `ct_services`, `ct_services_addon` where `ct_services`.`status` = 'E' and `ct_services_addon`.`status` = 'E' and `ct_services_addon`.`service_id` = `ct_services`.`id` group by `ct_services`.`id`, `ct_services`.`title`, `ct_services`.`description`, `ct_services`.`color`, `ct_services`.`image`, `ct_services`.`status`, `ct_services`.`position`) ORDER BY `position`";
		$result=mysqli_query($this->conn,$query);
		return $result;
 }
 	public function readall_for_frontend_services_byservice_id($service_id){
	 	$query="(select `ct_services`.* from `ct_services`, `ct_services_method`, `ct_service_methods_units` where `ct_services`.`id` in(".$service_id.")  and `ct_services`.`status` = 'E' and  ((`ct_services_method`.`status` = 'E' and `ct_service_methods_units`.`status` = 'E' and `ct_services_method`.`service_id` = `ct_services`.`id` and `ct_service_methods_units`.`services_id` = `ct_services`.`id` )) group by `ct_services`.`id`, `ct_services`.`title`, `ct_services`.`description`, `ct_services`.`color`, `ct_services`.`image`, `ct_services`.`status`, `ct_services`.`position` ORDER BY `position`) UNION (select `ct_services`.* from `ct_services`, `ct_services_addon` where `ct_services`.`id` in(".$service_id.")  and `ct_services`.`status` = 'E' and `ct_services_addon`.`status` = 'E' and `ct_services_addon`.`service_id` = `ct_services`.`id`  group by `ct_services`.`id`, `ct_services`.`title`, `ct_services`.`description`, `ct_services`.`color`, `ct_services`.`image`, `ct_services`.`status`, `ct_services`.`position`) ORDER BY `position`";
		$result=mysqli_query($this->conn,$query);
		return $result;
 }
 /* Function for get service name for Google calendar */
	public function get_service_name_gc(){
		$query="select `title` from `ct_services` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value= isset($value[0])? $value[0] : '' ;
	}
	
  /* Function for get method name for Google calendar */
	public function get_method_name_gc(){
		$query="select `method_title` from `ct_services_method` where `id`='".$this->id."'";
		$result=mysqli_query($this->conn,$query);
		$value=mysqli_fetch_row($result);
		return $value= isset($value[0])? $value[0] : '' ;
	}
}
?>