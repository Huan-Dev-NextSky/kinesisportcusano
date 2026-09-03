<?php  
require_once(dirname(dirname(__FILE__)).'/assets/quickbooks/vendor/autoload.php');
require_once(dirname(dirname(__FILE__)).'/assets/xero/vendor/autoload.php');
require_once(dirname(dirname(__FILE__)).'/assets/xero/storage.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Data\IPPLinkedTxn;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPPayment;
use XeroAPI\XeroPHP\AccountingObjectSerializer;

if(isset($_POST['action']) && $_POST['action']=='complete_booking'){
	ob_start();
	session_start();
	include(dirname(dirname(__FILE__)).'/header.php');
	include(dirname(dirname(__FILE__)).'/objects/class_connection.php');
	include(dirname(dirname(__FILE__)).'/objects/class_setting.php');
	include(dirname(dirname(__FILE__)).'/objects/class_booking.php');
	include(dirname(dirname(__FILE__)).'/objects/class_services.php');
	include(dirname(dirname(__FILE__)).'/objects/class_front_first_step.php');
	include(dirname(dirname(__FILE__)).'/objects/class_users.php');
	include(dirname(dirname(__FILE__)).'/objects/class_order_client_info.php');
	include(dirname(dirname(__FILE__)).'/objects/class_coupon.php');
	include(dirname(dirname(__FILE__)).'/objects/class_frequently_discount.php');
	include(dirname(dirname(__FILE__)).'/objects/class_payments.php');
	include(dirname(dirname(__FILE__)).'/objects/class.phpmailer.php');
	include(dirname(dirname(__FILE__)).'/objects/class_general.php');
	include(dirname(dirname(__FILE__)).'/objects/class_email_template.php');
	include(dirname(dirname(__FILE__)).'/objects/class_adminprofile.php');
	include(dirname(dirname(__FILE__)).'/objects/plivo.php');
	include(dirname(dirname(__FILE__)).'/assets/twilio/Services/Twilio.php');
	include(dirname(dirname(__FILE__))."/objects/class_dashboard.php");
	include(dirname(dirname(__FILE__))."/objects/class_nexmo.php");
	
	$con = new cleanto_db();
	$conn = $con->connect();
	
	$setting = new cleanto_setting();
	$setting->conn = $conn;

	if ($setting->get_option('ct_allow_manual_booking') === 'N') {
		echo 'manual_booking_disabled';
		exit;
	}

	$booking=new cleanto_booking();
	$booking->conn=$conn;
	
	$objdashboard = new cleanto_dashboard();
	$objdashboard->conn = $conn;

	$objadminprofile = new cleanto_adminprofile();
	$objadminprofile->conn = $conn;

	$nexmo_admin = new cleanto_ct_nexmo();
	$nexmo_client = new cleanto_ct_nexmo();

	$first_step=new cleanto_first_step();
	$first_step->conn=$conn;

	$email_template = new cleanto_email_template();
	$email_template->conn=$conn;

	$general=new cleanto_general();
	$general->conn=$conn;

	$user=new cleanto_users();
	$order_client_info=new cleanto_order_client_info();
	$settings=new cleanto_setting();
	$coupon=new cleanto_coupon();
	$frequently_discount = new cleanto_frequently_discount();
	$payment = new cleanto_payments();
	$service = new cleanto_services();

	$frequently_discount->conn = $conn;
	$user->conn=$conn;
	$order_client_info->conn=$conn;
	$settings->conn=$conn;
	$coupon->conn=$conn;
	$payment->conn=$conn;
	$service->conn=$conn;

	$last_order_id=$booking->last_booking_id();

	$symbol_position=$settings->get_option('ct_currency_symbol_position');
	$decimal=$settings->get_option('ct_price_format_decimal_places');

	$company_email=$settings->get_option('ct_email_sender_address');
	$company_name=$settings->get_option('ct_email_sender_name');
	
	if(isset($_POST['recurrence_booking'])){
		$recurrence_booking_status = $_POST['recurrence_booking'];
	}else{
		$recurrence_booking_status = '';
	}
	
  if ($_POST['discount'] == 'undefined' || $_POST['discount'] == '' || $_POST['discount'] == 'undefined- undefined') {
    $_POST['discount'] = 0.00;
  }else{
    $_POST['discount'] = $_POST['discount'];
  }

	$total_discount =  @number_format($_POST['frequent_discount_amount'],2,".",',') + @number_format($_POST['discount'],2,".",',');

    $phone = "";
    if (substr($_POST['phone'], 0, 1) === '+')
    {
        $phone = $_POST['phone'];
    }
    else
    {
        $country_codes = explode(',',$setting->get_option("ct_company_country_code"));
        $phone = $country_codes[0].$_POST['phone'];
    }
	if($setting->get_option("ct_tax_vat_status") == 'N'){
		$tax = 0;
	}else{
		$tax = $_POST['taxes'];
	}
	
	$service->id = $_SESSION['ct_cart']['method'][0]['service_id'];
  $service_name = $service->get_service_name_for_mail();
	
	$email = addslashes($_POST['email']);
	$firstname = addslashes($_POST['firstname']);
	$lastname = addslashes($_POST['lastname']);
	$address = addslashes($_POST['address']);
	$zipcode = addslashes($_POST['zipcode']);
	$city = addslashes($_POST['city']);
	$state = addslashes($_POST['state']);
	$user_address = addslashes($_POST['user_address']);
	$user_zipcode = addslashes($_POST['user_zipcode']);
	$user_city = addslashes($_POST['user_city']);
	$user_state = addslashes($_POST['user_state']);
	$notes = addslashes($_POST['notes']);
	$staff_id = $_POST['staff_id'];
	$coupon_code = addslashes($_POST['coupon_code']);
	
	$array_value = array('existing_username' => $_POST['existing_username'], 'existing_password' => $_POST['existing_password'], 'password' => $_POST['password'], 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'phone' => $phone, 'user_address' => $user_address, 'user_zipcode' => $user_zipcode, 'user_city' => $user_city, 'user_state' => $user_state, 'address' => $address, 'zipcode' => $zipcode, 'city' => $city, 'state' => $state, 'notes' => $notes, 'vc_status' => $_POST['vc_status'],'staff_id' => $staff_id, 'p_status' => $_POST['p_status'], 'contact_status' => $_POST['contact_status'], 'payment_method' => $_POST['payment_method'],'tip_value' => $_POST['tip_value'], 'amount' => $_POST['amount'], 'discount' => number_format($total_discount, 2, ".", ','), 'taxes' => $tax, 'partial_amount' => '', 'net_amount' => $_POST['net_amount'], 'booking_date_time' => $_POST['booking_date_time'], 'frequently_discount' => $_POST['frequently_discount'], 'frequent_discount_amount' => $_POST['frequent_discount_amount'], 'action' => "complete_booking", 'coupon_discount' => $_POST['discount'], 'cc_card_num' => '','cc_exp_month' => '','cc_exp_year' => '','cc_card_code' => '','guest_user_status' => $_POST['guest_user_status'],'service_name' => $service_name,'coupon_code'=> $coupon_code,'recurrence_booking_status'=> $recurrence_booking_status);

	$_SESSION['ct_details']=$array_value;
	
	$transaction_id ='';
	include('manual_booking_complete.php');
	
	/** Quickbooks Code Start **/
  if ($settings->get_option('ct_quickbooks_status') == 'Y') {
    $config = include(dirname(dirname(__FILE__)).'/assets/quickbooks/config.php');

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

    $service->id = $_SESSION['ct_cart']['method'][0]['service_id'];
    $service_name = $service->get_service_name_for_mail();

    $DueDate = date("Y-m-d", strtotime($_SESSION["ct_details"]["booking_date_time"]));

    $getItems = "SELECT * FROM ITEM WHERE Name = '".$service_name."'";
    $items = $dataService->Query($getItems);

    $user_email_id = $_SESSION["ct_details"]["email"];

    $getCustomer = "SELECT * FROM CUSTOMER WHERE PrimaryEmailAddr = '".$user_email_id."'";
    $customer = $dataService->Query($getCustomer);

    if ($customer) {
      $CustomerRef = $customer[0]->Id;
    }else{
      $customerObj = Customer::create([
        "BillAddr" => [
          "Line1"=>  $_SESSION['ct_details']['address'],
          "City"=>  $_SESSION['ct_details']['city'],
          //"Country"=>  "USA",
          "PostalCode"=>  $_SESSION['ct_details']['zipcode']
        ],
        "FullyQualifiedName"=>  ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']),
        "DisplayName"=>  ucwords($_SESSION['ct_details']['firstname']).' '.ucwords($_SESSION['ct_details']['lastname']),
        "PrimaryPhone"=>  [
          "FreeFormNumber"=>  $_SESSION['ct_details']['phone']
        ],
        "PrimaryEmailAddr"=>  [
          "Address" =>  $user_email_id
        ]
      ]);
      $resultCustomerObj = $dataService->Add($customerObj);
      $CustomerRef = $resultCustomerObj->Id;
    }

    $invoice = Invoice::create([
      "Line" => [
        [
          "Description" => $items[0]->Name,
          "Amount" => $_SESSION['ct_details']['net_amount'],
          "DetailType" => "SalesItemLineDetail",
          "SalesItemLineDetail" => [
            "ItemRef" => [
              "value" => $items[0]->Id,
              "name" => $items[0]->Name
            ]
          ]
        ]
      ],
      "DueDate" => $DueDate,
      "CustomerRef" => [
        "value" => $CustomerRef
      ],
      "BillEmail" => [
        "Address" => $user_email_id
      ]
    ]);
    $resultInvoice  = $dataService->Add($invoice);

    if ($_SESSION['ct_details']['partial_amount'] !== '') {
      $qbLinkedInvoice = new IPPLinkedTxn();
      $qbLinkedInvoice->TxnId = $resultInvoice->Id;
      $qbLinkedInvoice->TxnType = 'Invoice';

      $qbLine = new IPPLine();
      $qbLine->Amount = $_SESSION['ct_details']['partial_amount'];
      $qbLine->LinkedTxn = $qbLinkedInvoice;

      $qbPayment = new IPPPayment();
      $qbPayment->CustomerRef = $CustomerRef;
      $qbPayment->TotalAmt = $_SESSION['ct_details']['partial_amount'];
      $qbPayment->Line = array($qbLine);

      $createdQbPayment = $dataService->Add($qbPayment);
    }
  }
  /** Quickbooks Code End **/

  /** Xero Code Start **/
  if ($settings->get_option('ct_xero_status') == 'Y' || $settings->get_option('ct_xero_company_name') !== '') {
    $storage = new StorageClass();
    $xeroTenantId = (string)$storage->getSession()['tenant_id'];

    if ($storage->getHasExpired()) {
      
      $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => $settings->get_option('ct_xero_client_ID'),
        'clientSecret'            => $settings->get_option('ct_xero_client_secret'),
        'redirectUri'             => SITE_URL.'assets/xero/callback.php',
        'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken'          => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
      ]);

      $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $storage->getRefreshToken()
      ]);

      // Save my token, expiration and refresh token
      $storage->setToken(
        $newAccessToken->getToken(),
        $newAccessToken->getExpires(),
        $xeroTenantId,
        $newAccessToken->getRefreshToken(),
        $newAccessToken->getValues()["id_token"]
      );
    }

    $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );
    $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
      new GuzzleHttp\Client(),
      $config
    );
    
    $apiResponse = $apiInstance->getOrganisations($xeroTenantId);

    $settings->set_option('ct_xero_company_name', $apiResponse->getOrganisations()[0]->getName());
    $settings->set_option('ct_xero_oauth2state', $_SESSION['oauth2state']);
    $settings->set_option('ct_xero_oauth2_token', $_SESSION['oauth2']['token']);
    $settings->set_option('ct_xero_oauth2_expires', $_SESSION['oauth2']['expires']);
    $settings->set_option('ct_xero_oauth2_tenant_id', $xeroTenantId);
    $settings->set_option('ct_xero_oauth2_refresh_token', $_SESSION['oauth2']['refresh_token']);
    $settings->set_option('ct_xero_oauth2_id_token', $_SESSION['oauth2']['id_token']);

    try{
      $getAccountsResponse = $apiInstance->getAccounts($xeroTenantId);
      $getAccount = $getAccountsResponse->getAccounts()[2];
      $AccountID = $getAccountsResponse->getAccounts()[2]->getAccountId();
    } catch (Exception $e) {

    }

    try {
      $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
      $contact->setName($_SESSION['ct_details']['firstname'].' '.$_SESSION['ct_details']['lastname'])
        ->setFirstName($_SESSION['ct_details']['firstname'])
        ->setLastName($_SESSION['ct_details']['lastname'])
        ->setEmailAddress($_SESSION["ct_details"]["email"]);

      $arr_contacts = [];
      array_push($arr_contacts, $contact);

      $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
      $contacts->setContacts($arr_contacts);

      $getContactRespoce = $apiInstance->updateOrCreateContacts($xeroTenantId, $contacts, false);
      $getContact = $getContactRespoce->getContacts()[0];
      $ContactID = $getContactRespoce->getContacts()[0]->getContactId();
    } catch (\XeroAPI\XeroPHP\ApiException $e) {

    }

    try {
      $lineitems[] = array(
        "Description"=>"Services",
        "Quantity"=>"",
        "UnitAmount"=>"",
        "AccountCode"=>"200",
        "TaxType"=>"NONE",
        "TaxAmount"=>$_SESSION['ct_details']['taxes'],
        "LineAmount"=>$_SESSION['ct_details']['net_amount']-$_SESSION['ct_details']['taxes']
      );

      $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
      $invoice->setReference('Cleanto')
        ->setDate(date('Y-m-d'))
        ->setDueDate($_SESSION['ct_details']['booking_date_time'])
        ->setContact($getContact)
        ->setLineItems($lineitems)
        ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
        ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
        ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);

      $arr_invoices = [];
      array_push($arr_invoices, $invoice);

      $invoices = new XeroAPI\XeroPHP\Models\Accounting\Invoices;
      $invoices->setInvoices($arr_invoices);

      $getInvoiceRespoce = $apiInstance->createInvoices($xeroTenantId, $invoices, false);
      $getInvoice = $getInvoiceRespoce->getInvoices()[0];
    } catch (Exception $e) {

    }

    if ($_SESSION['ct_details']['partial_amount'] !== '') {
      try{
        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setAccount($getAccount)
          ->setInvoice($getInvoice)
          ->setCurrencyRate(1)
          ->setAmount($_SESSION['ct_details']['partial_amount']);

        $arr_payments = [];
        array_push($arr_payments, $payment);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $payments->setPayments($arr_payments);

        $getPaymentRespoce = $apiInstance->createPayments($xeroTenantId, $payments, false);
        $getPayment = $getPaymentRespoce->getPayments()[0];
      } catch (Exception $e) {

      }
    }
  }
  /** Xero Code End **/
}
?>