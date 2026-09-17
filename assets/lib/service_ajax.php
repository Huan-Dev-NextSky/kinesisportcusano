<?php  
require_once(dirname(dirname(__FILE__)).'/quickbooks/vendor/autoload.php');

include(dirname(dirname(dirname(__FILE__)))."/objects/class_connection.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_services.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_services_addon.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_services_addon_rates.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_design_settings.php");
include(dirname(dirname(dirname(__FILE__)))."/objects/class_setting.php");
include(dirname(dirname(dirname(__FILE__)))."/header.php");

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;

$con = new cleanto_db();
$conn = $con->connect();
$objservice = new cleanto_services();
$objservice->conn = $conn;
$objservice_addon = new cleanto_services_addon();
$objservice_addon->conn = $conn;
$objservice_addon_rate = new cleanto_services_addon_rates();
$objservice_addon_rate->conn = $conn;
$objdesignset = new cleanto_design_settings();
$objdesignset->conn = $conn;
$settings = new cleanto_setting();
$settings->conn = $conn;
$lang = $settings->get_option("ct_language");
$label_language_values = array();
$language_label_arr = $settings->get_all_labelsbyid($lang);
if ($language_label_arr[1] != "" || $language_label_arr[3] != "" || $language_label_arr[4] != "" || $language_label_arr[5] != "")
{
	$default_language_arr = $settings->get_all_labelsbyid("en");
	if($language_label_arr[1] != ''){
		$label_decode_front = base64_decode($language_label_arr[1]);
	}else{
		$label_decode_front = base64_decode($default_language_arr[1]);
	}
	if($language_label_arr[3] != ''){
		$label_decode_admin = base64_decode($language_label_arr[3]);
	}else{
		$label_decode_admin = base64_decode($default_language_arr[3]);
	}
	if($language_label_arr[4] != ''){
		$label_decode_error = base64_decode($language_label_arr[4]);
	}else{
		$label_decode_error = base64_decode($default_language_arr[4]);
	}
	if($language_label_arr[5] != ''){
		$label_decode_extra = base64_decode($language_label_arr[5]);
	}else{
		$label_decode_extra = base64_decode($default_language_arr[5]);
	}
	
	$label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);
	$label_decode_extra_unserial = unserialize($label_decode_extra);
    
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial);
	
	foreach($label_language_arr as $key => $value){
		$label_language_values[$key] = urldecode($value);
	}
}
else
{
  $default_language_arr = $settings->get_all_labelsbyid("en");
  
	$label_decode_front = base64_decode($default_language_arr[1]);
	$label_decode_admin = base64_decode($default_language_arr[3]);
	$label_decode_error = base64_decode($default_language_arr[4]);
	$label_decode_extra = base64_decode($default_language_arr[5]);
	
	$label_decode_front_unserial = unserialize($label_decode_front);
	$label_decode_admin_unserial = unserialize($label_decode_admin);
	$label_decode_error_unserial = unserialize($label_decode_error);
	$label_decode_extra_unserial = unserialize($label_decode_extra);
  
	$label_language_arr = array_merge($label_decode_front_unserial,$label_decode_admin_unserial,$label_decode_error_unserial,$label_decode_extra_unserial);
	
	foreach($label_language_arr as $key => $value){
		$label_language_values[$key] = urldecode($value);
	}
}
if(isset($_POST['pos']) && isset($_POST['ids']))
{
    echo "yes in ";
    echo count((array)$_POST['ids']);
    for($i=0;$i<count((array)$_POST['ids']);$i++)
    {
        $objservice->position=$_POST['pos'][$i];
        $objservice->id=$_POST['ids'][$i];
        $objservice->updateposition();
    }
}
elseif(isset($_POST['deleteid']))
{
    $objservice->id=$_POST['deleteid'];
    chmod(dirname(dirname(dirname(__FILE__)))."/assets/images/services", 0777);
	
    /* CODE TO DELETE ADDONS AND SERVICE IMAGE BEFORE DELETE SERVICE FORM TABLE */
    $addons = $objservice->get_exist_addons_by_serviceid($_POST['deleteid']);
	$methods = $objservice->get_exist_methods_by_serviceid($_POST['deleteid']);
	if($addons->num_rows > 0){
		while($r = mysqli_fetch_array($addons)){
			unlink(dirname(dirname(dirname(__FILE__)))."/assets/images/services/".$r['image']);
			$addons_rates = $objservice->get_exist_addons_rate_by_addonid($r['id']);
			while($t = mysqli_fetch_array($addons_rates))
			{
				/* DELETE ADDONS RATE */
				$objservice->delete_addons_rate($t['id']);
			}
			/* DELETE ADDONS */
			$objservice->delete_addons_of_service($r['id']);
		}
	}
	if($methods->num_rows > 0){
		while($r = mysqli_fetch_array($methods)){
			$methods_units = $objservice->get_exist_methods_units_by_methodid($r['id']);
			if($methods_units->num_rows > 0){
				while($t = mysqli_fetch_array($methods_units))
				{
					$methods_units_rate = $objservice->get_exist_methods_units_rate_by_unitid($t['id']);
					if($methods_units_rate->num_rows > 0){
						while($mur = mysqli_fetch_array($methods_units_rate))
						{
							/* Service method unit rate delete */
							$objservice->delete_service_method_unit_rate($mur['id']);
						}
					}
					/* Service method unit delete */
					$objservice->delete_method_unit($t['id']);
				}
			}	   
			/* Service method delete */
			$objservice->delete_method($r['id']);
		}
	}
    $objservice->delete_service();
}
elseif(isset($_POST['changestatus']))
{
    $objservice->id=$_POST['id'];
    $objservice->status = $_POST['changestatus'];
    $objservice->changestatus();
	if($objservice){
		if($_POST['changestatus']=='E'){
             echo $label_language_values['service_enable'];
		}else{
             echo $label_language_values['service_disable'];
		}
	}
}
elseif(isset($_POST['operationadd']))
{
    chmod(dirname(dirname(dirname(__FILE__)))."/assets/images/services", 0777);
    $objservice->title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $t = $objservice->check_same_title();
    $cnt = mysqli_num_rows($t);
    if($cnt == 0){
        $objservice->color = $_POST['color'];
        $objservice->title = filter_var(mysqli_real_escape_string($conn,ucwords($_POST['title'])), FILTER_SANITIZE_STRING);
        $objservice->description = mysqli_real_escape_string($conn,$_POST['description']);
        $objservice->price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $objservice->duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
        $objservice->status = $_POST['status'];
        $objservice->position = $_POST['position'];
        $objservice->ensure_price_duration_schema();
        $insertid = $objservice->add_service();
        $objservice->image = $_POST['image'];
        $objservice->update_recordfor_image($insertid);
        $objservice->sync_price_duration_to_units($insertid);
        /* REMOVE UNSED IMAGES FROM FOLDER */
        $used_images = $objservice->get_used_images();
        $imgarr = array();
		if($used_images->num_rows > 0){
			while($img  = mysqli_fetch_array($used_images)){
				$filtername = preg_replace('/\\.[^.\\s]{3,4}$/', '', $img[0]);
				array_push($imgarr,$filtername);
			}
		}
        array_push($imgarr,"default");
        array_push($imgarr,"default_service");
        array_push($imgarr,"default_service1");
        print_r($imgarr);
        $dir = dirname(dirname(dirname(__FILE__)))."/assets/images/services/";
        $cnt = 1;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if($cnt > 2){
                    $filtername = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
                    if (in_array($filtername, $imgarr)) {
                    }
                    else{
                      unlink(dirname(dirname(dirname(__FILE__)))."/assets/images/services/".$file);
                    }
                }
                $cnt++;
            }
            closedir($dh);
        }
    }
    else{
       echo "1";
    }
		
		/** Quickbooks Code Start **/
    if ($settings->get_option('ct_quickbooks_status') == 'Y') {
      $config = include(dirname(dirname(__FILE__)).'/quickbooks/config.php');

      $accessToken = unserialize($settings->get_option('ct_qb_session_access_token'));

      $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $settings->get_option('ct_quickbooks_client_ID'),
        'ClientSecret' =>  $settings->get_option('ct_quickbooks_client_secret'),
        'RedirectURI' => $config['oauth_redirect_uri'],
        'baseUrl' => $settings->get_option('ct_qb_account'),
        'refreshTokenKey' => $settings->get_option('ct_qb_refresh_token'),
        'QBORealmID' => $settings->get_option('ct_qb_company_id'),
      ));

      $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
      $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();

      $tokenArray = (array) $refreshedAccessTokenObj;
      end($tokenArray);
      $realID = prev($tokenArray);

      $settings->set_option('ct_qb_company_id',$realID);
      $settings->set_option('ct_qb_access_token',$refreshedAccessTokenObj->getAccessToken());
      $settings->set_option('ct_qb_refresh_token',$refreshedAccessTokenObj->getRefreshToken());
      $settings->set_option('ct_qb_x_refresh_token_expires_in',$refreshedAccessTokenObj->getRefreshTokenExpiresAt());
      $settings->set_option('ct_qb_expires_in',$refreshedAccessTokenObj->getAccessTokenExpiresAt());

      $_SESSION['sessionAccessToken'] = $refreshedAccessTokenObj;
      
      $dataService->updateOAuth2Token($refreshedAccessTokenObj);

      $getAccount = "SELECT * FROM ACCOUNT WHERE Name = 'Cleanto'";
      $account = $dataService->Query($getAccount);
      if ($account) {
        $accName = $account[0]->Name;
        $accValue = $account[0]->Id;
      }else{
        $accountObj = Account::create([
          "Name" => "Cleanto",
          "AccountType" => "Income"
        ]);
        $resultingAccountObj = $dataService->Add($accountObj);
        $accName = $resultingAccountObj->Name;
        $accValue = $resultingAccountObj->Id;
      }

      $itemObj = Item::create([
        "Name" => ucwords($_POST['title']),
        "Type" => "Service",
        "IncomeAccountRef" => [
          "name" => $accName,
          "value" => $accValue
        ],
      ]);

      $resultingItemObj = $dataService->Add($itemObj);
    }
    /** Quickbooks Code End **/
}
elseif(isset($_POST['operationedit']))
{
	chmod(dirname(dirname(dirname(__FILE__)))."/assets/images/services", 0777);
	$objservice->id = $_POST['id'];
	$objservice->color = $_POST['color'];
	$objservice->description = $_POST['description'];
	$objservice->title = filter_var(mysqli_real_escape_string($conn,ucwords($_POST['title'])), FILTER_SANITIZE_STRING);
	$objservice->image = $_POST['image'];
	$objservice->price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
	$objservice->duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
	$objservice->ensure_price_duration_schema();
	$objservice->update_service();
	$objservice->sync_price_duration_to_units((int)$_POST['id']);
	/* REMOVE UNSED IMAGES FROM FOLDER */
	$used_images = $objservice->get_used_images();
	$imgarr = array();
	if($used_images->num_rows > 0){
		while($img = mysqli_fetch_array($used_images)){
			$filtername = preg_replace('/\\.[^.\\s]{3,4}$/', '', $img[0]);
			array_push($imgarr,$filtername);
		}
	}
	array_push($imgarr,"default");
	array_push($imgarr,"default_service");
	array_push($imgarr,"default_service1");
	$dir = dirname(dirname(dirname(__FILE__)))."/assets/images/services/";
	$cnt = 1;
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if($cnt > 2){
				$filtername = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
				if (in_array($filtername, $imgarr)) {
				}
				else{
					unlink(dirname(dirname(dirname(__FILE__)))."/assets/images/services/".$file);
				}
			}
			$cnt++;
		}
		closedir($dh);
	}
}
elseif(isset($_POST['assigndesign']))
{
    $objdesignset->title=$_POST['divname'];
    $objdesignset->design=$_POST['designid'];
    $having = $objdesignset->readone();
    if(count((array)$having[0])>0)
    {
        $objdesignset->update_setting_design();
    }
    else
    {
        $objdesignset->add_setting_design();
    }
}
/*Delete Service Image*/
if(isset($_POST['action']) && $_POST['action']=='delete_image'){
	$objservice->id=$_POST['service_id'];
	$objservice->image="";
	$del_image=$objservice->update_image();
}

/*Create Embed Code*/
if(isset($_POST['action']) && $_POST['action']=='create_embed_accordingly'){
	$ids= implode(',', $_POST['service_ids']);
	$service_ids=base64_encode($ids);
	$appointment_detail = array(); 
	
	$front_design = $settings->get_option("ct_booking_page_design");
	if($front_design=='S'){
		$url = SITE_URL.'index_one_step.php';
	}else{
		$url = SITE_URL.'index.php';
	}
	
	$userembeddcode ='<div id="cleanto" class="direct-load" data-url="'.$url.'?service='.$service_ids.'"></div><script src="'.SITE_URL.'assets/js/jquery-2.1.4.min.js" type="text/javascript"></script><script src="'.SITE_URL.'assets/js/embed.js?time='.time().'" type="text/javascript" ></script>';

	
	$userembeddcode_embed = htmlspecialchars_decode($userembeddcode);
	
	
	$appointment_detail['embed'] = $userembeddcode_embed;
	echo json_encode($appointment_detail);
	die();
	
}
?>