<?php
  ini_set('display_errors', 'On');
  require __DIR__ . '/vendor/autoload.php';
  require_once('storage.php');
  include(dirname(dirname(dirname(__FILE__))))."/header.php";
  include(dirname(dirname(dirname(__FILE__))))."/objects/class_setting.php";
  include(dirname(dirname(dirname(__FILE__))))."/objects/class_connection.php";

  $database=new cleanto_db();
  $setting=new cleanto_setting();
  $conn=$database->connect();
  $database->conn=$conn;
  $setting->conn=$conn;
  // Storage Classe uses sessions for storing token > extend to your DB of choice
  $storage = new StorageClass();

  $provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => $setting->get_option('ct_xero_client_ID'),
    'clientSecret'            => $setting->get_option('ct_xero_client_secret'),
    'redirectUri'             => SITE_URL.'assets/xero/callback.php',
    'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken'          => 'https://identity.xero.com/connect/token',
    'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
  ]);

  // If we don't have an authorization code then get one
  if (!isset($_GET['code'])) {
    echo "Something went wrong, no authorization code found";
    exit("Something went wrong, no authorization code found");

  // Check given state against previously stored one to mitigate CSRF attack
  } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    echo "Invalid State";
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
  } else {

    try {
      // Try to get an access token using the authorization code grant.
      $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
      ]);

      $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$accessToken->getToken() );
      $identityInstance = new XeroAPI\XeroPHP\Api\IdentityApi(
        new GuzzleHttp\Client(),
        $config
      );

      $result = $identityInstance->getConnections();

      // Save my tokens, expiration tenant_id
      $storage->setToken(
          $accessToken->getToken(),
          $accessToken->getExpires(),
          $result[0]->getTenantId(),
          $accessToken->getRefreshToken(),
          $accessToken->getValues()["id_token"]
      );

      $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );
      $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
          new GuzzleHttp\Client(),
          $config
      );
      
      $apiResponse = $apiInstance->getOrganisations($result[0]->getTenantId());

      $setting->set_option('ct_xero_company_name', $apiResponse->getOrganisations()[0]->getName());
      $setting->set_option('ct_xero_oauth2state', $_SESSION['oauth2state']);
      $setting->set_option('ct_xero_oauth2_token', $_SESSION['oauth2']['token']);
      $setting->set_option('ct_xero_oauth2_expires', $_SESSION['oauth2']['expires']);
      $setting->set_option('ct_xero_oauth2_tenant_id', $result[0]->getTenantId());
      $setting->set_option('ct_xero_oauth2_refresh_token', $_SESSION['oauth2']['refresh_token']);
      $setting->set_option('ct_xero_oauth2_id_token', $_SESSION['oauth2']['id_token']);

      header('Location: '.SITE_URL.'/admin/settings.php');
      exit();

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
      echo "Callback failed";
      exit();
    }
  }
?>