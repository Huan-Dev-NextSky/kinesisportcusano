<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(dirname(dirname(dirname(__FILE__))).'/objects/class_connection.php');
require_once(dirname(dirname(dirname(__FILE__))).'/objects/class_setting.php');

use QuickBooksOnline\API\DataService\DataService;

session_start();

function processCode()
{
    $database=new cleanto_db();
    $setting=new cleanto_setting();
    $conn=$database->connect();
    $database->conn=$conn;
    $setting->conn=$conn;
    
    // Create SDK instance
    $config = include('config.php');
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $setting->get_option('ct_quickbooks_client_ID'),
        'ClientSecret' =>  $setting->get_option('ct_quickbooks_client_secret'),
        'RedirectURI' => $config['oauth_redirect_uri'],
        'scope' => $config['oauth_scope'],
        'baseUrl' => $setting->get_option('ct_qb_account')
    ));
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $parseUrl = parseAuthRedirectUrl($_SERVER['QUERY_STRING']);

    /*
     * Update the OAuth2Token
     */
    $accessToken =
    $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'],
    $parseUrl['realmId']);
    $dataService->updateOAuth2Token($accessToken);

    /*
     * Setting the accessToken for session variable
     */
    $_SESSION['sessionAccessToken'] = $accessToken;
}
function parseAuthRedirectUrl($url)
{
    parse_str($url,$qsArray);
    return array(
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    );
}

$result = processCode();

?>