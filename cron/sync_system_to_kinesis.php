<?php
/**
 * Cronjob: STEP 2 - Sync Reservations from Local System (Cleanto DB) to Kinesis REST API
 * 
 * Usage from CLI / Crontab:
 * php /path/to/cron/sync_system_to_kinesis.php
 * Or via web request:
 * https://your-domain.com/cron/sync_system_to_kinesis.php
 */

$isCli = (php_sapi_name() === 'cli');

require_once dirname(dirname(__FILE__)) . '/objects/class_connection.php';
require_once dirname(dirname(__FILE__)) . '/objects/class_setting.php';
require_once dirname(__FILE__) . '/cron_auth.php';
require_once dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwApiClient.php';
require_once dirname(dirname(__FILE__)) . '/integrations/awwapi/AwwAppointmentSync.php';

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

$kinesisStatus = $setting->get_option('kinesis_api_status');
if ($kinesisStatus !== 'Y') {
    $msg = 'Kinesis API sync is currently disabled in settings.';
    if ($isCli) {
        fwrite(STDERR, $msg . "\n");
        exit(1);
    }
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => $msg));
    exit;
}

$sync = new AwwAppointmentSync($conn);
$result = $sync->syncAllPending();

if ($isCli) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $result['message'] . "\n";
    exit($result['success'] ? 0 : 1);
} else {
    header('Content-Type: application/json');
    echo json_encode($result);
}
