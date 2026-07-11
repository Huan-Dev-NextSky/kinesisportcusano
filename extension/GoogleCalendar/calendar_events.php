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

$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$eventinfo = array();

$current_system_timezone = $setting->get_option("ct_timezone");
function get_providerevents($calendarId,$provider_access_token,$sdate,$edate,$GcclientID,$GcclientSecret,$GcEDvalue,$ct_gc_admin_url,$ct_gc_api_key){
	
	$timeMin = $sdate.'T00:00:00Z';
	$timeMax = $edate.'T23:59:59Z';
	
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
		$allevents_provider = $calP->events->listEvents($calendarId,array(
		'singleEvents' => true,
		'orderBy'      => 'startTime',
		'timeMin'      => $timeMin,
		'timeMax' 	   => $timeMax,
		'maxResults'   => 500,
		));
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
	$ct_gc_api_key = $setting->get_option('ct_gc_api_key');
	$provider_events  =	get_providerevents($provider_gc_id,$provider_gc_data,$sdate,$edate,$GcclientID,$GcclientSecret,$GcEDvalue,$ct_gc_admin_url,$ct_gc_api_key);
	
	if(sizeof((array)$provider_events)>0){
		foreach($provider_events['items'] as $provider_single_event){
			if(isset($provider_single_event['start']['timezone'])){
				$eventtimezone = $provider_single_event['start']['timezone'];	
			}elseif(isset($provider_single_event['timezone'])){
				$eventtimezone = $provider_single_event['timezone'];	
			}else{
				$eventtimezone = $provider_events['timeZone'];	
			}
			$gcslotstart = "";
			$gcslotend = "";
			if(isset($provider_single_event['start']['dateTime']) && isset($provider_single_event['end']['dateTime'])){
				$gcslotstart_date_time = $provider_single_event['start']['dateTime'];
				$date = new DateTime($gcslotstart_date_time);
				$date->setTimezone(new DateTimeZone($current_system_timezone));
				$gcslotstart = $date->format('Y-m-d\TH:i:sP');
				
				$gcslotend_date_time = $provider_single_event['end']['dateTime'];
				$date = new DateTime($gcslotend_date_time);
				$date->setTimezone(new DateTimeZone($current_system_timezone));
				$gcslotend = $date->format('Y-m-d\TH:i:sP');
			}else{
				$gcslotstart = $provider_single_event['start']['date']."T00:00:00+00:00";
				$gcslotend = $provider_single_event['start']['date']."T23:59:59+00:00";
			}
			
			if(isset($provider_single_event['summary'])){
				$eventtitle = $provider_single_event['summary'];
			}else{
				$eventtitle = '';
			}
			$update_date = $provider_single_event['updated'];
			$id = $provider_single_event['id'];
			
			/* if(!isset($provider_single_event['transparency'])){ */
				$eventinfo[] = array('id'=>$id,'color'=>"#4285f4",'title'=>$eventtitle,'start'=>$gcslotstart,'end'=>$gcslotend,'timezone'=>$eventtimezone,'update_date'=>$update_date);
			/* } */
		}
	}
}
$adminevents = json_encode($eventinfo);
echo $adminevents;
?>