<?php
include(dirname(dirname(dirname(__FILE__))).'/objects/class_connection.php');
include(dirname(dirname(dirname(__FILE__))).'/objects/class_setting.php');
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_CalendarService.php';
		$database=new cleanto_db();
		$setting=new cleanto_setting();
		$conn=$database->connect();
		$database->conn=$conn;
		$setting->conn=$conn;
		$GcclientID = $setting->get_staff_option('gc_client_id',$_REQUEST['pid']);
		$ct_gc_api_key = $setting->get_staff_option('ct_gc_api_key',$_REQUEST['pid']);
		$GcclientSecret = $setting->get_staff_option('gc_client_secret',$_REQUEST['pid']);
		$GcEDvalue = $setting->get_staff_option('gc_status',$_REQUEST['pid']);

		$client = new Google_Client();
		$client->setApplicationName("Google Calender");
		$client->setClientId($GcclientID);
		$client->setClientSecret($GcclientSecret);
		$ct_gc_admin_url = $setting->get_staff_option('gc_admin_url',$_REQUEST['pid']);
		
		$client->setRedirectUri($ct_gc_admin_url);
		$client->setDeveloperKey($ct_gc_api_key);
		$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/calendar'));
		$client->setAccessType('offline');
		$client->setApprovalPrompt( 'force' );
		
		$service = new Google_CalendarService($client);
		$provider_gc_data = $setting->get_staff_option('gc_token',$_REQUEST['pid']);

		if(sizeof((array)$provider_gc_data) > 0){
			$accesstoken = json_decode($provider_gc_data);
			$client->setAccessToken($provider_gc_data);
			if ($accesstoken) {
				if ($client->isAccessTokenExpired()) {
					$client->refreshToken($accesstoken->refresh_token);				
				}
			}

			/* $client->revokeToken($provider_gc_data[0]); */					
			if ($client->getAccessToken()){
				$calendarList = $service->calendarList->listCalendarList();
				$allCalenders = array();
				
				foreach($calendarList['items'] as $singleItem){
					if($singleItem['accessRole']=='owner'){
						$allCalenders[]=array($singleItem['id']=>$singleItem['summary'].'##==##'.$singleItem['id']);
					}
				}
			}
		}else{
			$allCalenders = array();
		}
echo json_encode($allCalenders);die();		
?>