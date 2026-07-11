<?php
include(dirname(dirname(dirname(__FILE__)))."/objects/class_connection.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_setting.php");
include(dirname(dirname(dirname(__FILE__))).'/objects/class_front_first_step.php');
$con = new cleanto_db();
$conn = $con->connect();

$setting = new cleanto_setting();
$setting->conn = $conn;

$first_step=new cleanto_first_step();
$first_step->conn=$conn;
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_CalendarService.php';

$e_id = $_GET['e_id'];
$eventinfo = array();

$current_system_timezone = $setting->get_option("ct_timezone");
function get_providerevents($calendarId,$provider_access_token,$e_id,$GcclientID,$GcclientSecret,$GcEDvalue,$ct_gc_admin_url,$ct_gc_api_key){
	
	$clientP = new Google_Client();
	$clientP->setApplicationName("Cleanto Google Calender");
	$clientP->setClientId($GcclientID);
	$clientP->setClientSecret($GcclientSecret);
	$clientP->setRedirectUri($ct_gc_admin_url);
	$clientP->setDeveloperKey($ct_gc_api_key);
	$clientP->setScopes('https://www.googleapis.com/auth/calendar');
	$clientP->setAccessType('offline');
	$calP = new Google_CalendarService($clientP);	
	
	$clientP->setAccessToken($provider_access_token);
	$accesstoken = json_decode($provider_access_token);
	
	if ($provider_access_token) {
		if ($clientP->isAccessTokenExpired()) {
			$clientP->refreshToken($accesstoken->refresh_token);
		}
	}
	if ($clientP->getAccessToken()){
		$allevents_provider = $calP->events->get($calendarId,$e_id);
	}
	
	return $allevents_provider;
}

$GcEDvalue = $setting->get_option('ct_gc_status_configure');
$provider_gc_data = $setting->get_option('ct_gc_token');
if($GcEDvalue == 'Y' && $provider_gc_data != ''){
	$GcclientID = $setting->get_option('ct_gc_client_id');
	$GcclientSecret = $setting->get_option('ct_gc_client_secret');
	$GcEDvalue = $setting->get_option('ct_gc_status_configure');
	$provider_gc_id = $setting->get_option('ct_gc_id');
	$provider_gc_data = $setting->get_option('ct_gc_token');
	$ct_gc_admin_url = $setting->get_option('ct_gc_admin_url');
	$provider_events  =	get_providerevents($provider_gc_id,$provider_gc_data,$e_id,$GcclientID,$GcclientSecret,$GcEDvalue,$ct_gc_admin_url,$ct_gc_api_key);
	
	if(sizeof((array)$provider_events)>0){
		$update_date = $provider_events['updated'];
		$created_date = $provider_events['created'];
		$id = $provider_events['id'];
		
		$gcslotstart = "";
		$gcslotend = "";
		if(isset($provider_events['start']['dateTime']) && isset($provider_events['end']['dateTime'])){
			$gcslotstart_date_time = $provider_events['start']['dateTime'];
			$date = new DateTime($gcslotstart_date_time);
			$date->setTimezone(new DateTimeZone($current_system_timezone));
			$gcslotstart = $date->format('Y-m-d\TH:i:sP');
			
			$gcslotend_date_time = $provider_events['end']['dateTime'];
			$date = new DateTime($gcslotend_date_time);
			$date->setTimezone(new DateTimeZone($current_system_timezone));
			$gcslotend = $date->format('Y-m-d\TH:i:sP');
		}else{
			$gcslotstart = $provider_events['start']['date']."T00:00:00+00:00";
			$gcslotend = $provider_events['start']['date']."T23:59:59+00:00";
		}
		
		if(isset($provider_events['description'])){
			$description = $provider_events['description'];
		}else{
			$description = 'N/A';
		}
		
		if(isset($provider_events['summary'])){
			$eventtitle = $provider_events['summary'];
		}else{
			$eventtitle = 'N/A';
		}
		
		$eventinfo = array('id'=>$id,'color'=>"#4285f4",'title'=>$eventtitle,'description'=>$description,'start'=>$gcslotstart,'end'=>$gcslotend,'created_date'=>$created_date,'update_date'=>$update_date);
	}
}
$adminevents = json_encode($eventinfo);
echo $adminevents;
?>