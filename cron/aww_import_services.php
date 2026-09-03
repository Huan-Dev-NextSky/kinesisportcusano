<?php
/**
 * Cronjob: Sync services from Kinesis API into local database
 * 
 * Usage from CLI / Crontab:
 * php /path/to/cron/aww_import_services.php
 * Or via web request (secured by optional secret key):
 * https://your-domain.com/cron/aww_import_services.php?key=YOUR_CRON_KEY
 */

// Allow execution from CLI or web
$isCli = (php_sapi_name() === 'cli');

require_once dirname(dirname(__FILE__)) . '/objects/class_connection.php';
require_once dirname(dirname(__FILE__)) . '/objects/class_setting.php';
require_once dirname(__FILE__) . '/cron_auth.php';
require_once dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwApiClient.php';
require_once dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwServiceSync.php';

$db = new cleanto_db();
$conn = $db->connect();

if (!$conn) {
    $msg = "Database connection failed.";
    if ($isCli) {
        fwrite(STDERR, $msg . "\n");
        exit(1);
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'error' => $msg));
        exit;
    }
}

$setting = new cleanto_setting();
$setting->conn = $conn;

if (!ct_cron_web_authorized($setting, $isCli)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Forbidden. Pass ?key= matching ct_cron_secret or ct_api_key.'));
    exit;
}

$status = $setting->get_option('kinesis_api_status');
if ($status !== 'Y') {
    $msg = 'Kinesis API sync is currently disabled in settings.';
    if ($isCli) {
        fwrite(STDERR, $msg . "\n");
        exit(1);
    }
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => $msg));
    exit;
}

$sync = new AwwServiceSync($conn);
$result = $sync->syncAll();

if ($isCli) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $result['message'] . "\n";
    exit($result['success'] ? 0 : 1);
} else {
    header('Content-Type: application/json');
    echo json_encode($result);
}
