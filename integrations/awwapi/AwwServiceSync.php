<?php
/**
 * AwwServiceSync - Synchronizes services from Kinesis API (v3.0) into local ct_services
 * 
 * Rules:
 * - Maps external Service.id to ct_services.external_service_id
 * - Inserts new services if not present locally
 * - Updates name, description, duration on existing services
 * - Preserves all local pricing, calculation methods, addons, and images created by admin
 */

class AwwServiceSync {
    private $conn;
    private $client;

    public function __construct($conn, $client = null) {
        $this->conn = $conn;
        if ($client) {
            $this->client = $client;
        } else {
            require_once dirname(__FILE__) . '/AwwApiClient.php';
            $this->client = new AwwApiClient($this->conn);
        }
    }

    public function syncAll() {
        require_once dirname(__FILE__) . '/AwwApiMigration.php';
        AwwApiMigration::run($this->conn);

        $res = $this->client->getServices();
        if (!$res['success']) {
            return array(
                'success' => false,
                'message' => 'Failed to retrieve services from API: ' . (isset($res['error']) ? $res['error'] : 'Unknown error'),
                'created' => 0,
                'updated' => 0,
                'total' => 0
            );
        }

        $services = is_array($res['data']) ? $res['data'] : array();
        $created = 0;
        $updated = 0;

        foreach ($services as $srv) {
            $extId = isset($srv['id']) ? (int)$srv['id'] : (isset($srv['Id']) ? (int)$srv['Id'] : 0);
            if (!$extId) continue;

            $name = isset($srv['name']) ? $srv['name'] : (isset($srv['Name']) ? $srv['Name'] : '');
            $desc = isset($srv['description']) ? $srv['description'] : (isset($srv['Description']) ? $srv['Description'] : '');
            $duration = isset($srv['duration']) ? $srv['duration'] : (isset($srv['Duration']) ? $srv['Duration'] : '01:00:00');

            $nameEsc = mysqli_real_escape_string($this->conn, $name);
            $descEsc = mysqli_real_escape_string($this->conn, $desc);
            $durEsc = mysqli_real_escape_string($this->conn, $duration);

            $check = mysqli_query($this->conn, "SELECT `id` FROM `ct_services` WHERE `external_service_id` = " . $extId);
            if ($check && mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                $localId = (int)$row['id'];
                $updateQ = "UPDATE `ct_services` SET `title` = '{$nameEsc}', `description` = '{$descEsc}', `duration` = '{$durEsc}' WHERE `id` = {$localId}";
                mysqli_query($this->conn, $updateQ);
                $updated++;
            } else {
                // Find next max position
                $posQ = mysqli_query($this->conn, "SELECT MAX(`position`) AS `max_pos` FROM `ct_services`");
                $posRow = mysqli_fetch_assoc($posQ);
                $pos = isset($posRow['max_pos']) ? ((int)$posRow['max_pos'] + 1) : 1;

                $insertQ = "INSERT INTO `ct_services` (`id`, `external_service_id`, `title`, `description`, `duration`, `color`, `image`, `status`, `position`) 
                            VALUES (NULL, {$extId}, '{$nameEsc}', '{$descEsc}', '{$durEsc}', '#1596e8', 'default.png', 'E', {$pos})";
                mysqli_query($this->conn, $insertQ);
                $created++;
            }
        }

        // Save last sync time
        $now = date('Y-m-d H:i:s');
        require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
        $setting = new cleanto_setting();
        $setting->conn = $this->conn;
        $setting->set_option('kinesis_last_sync_time', $now);
        $setting->set_option('kinesis_last_sync_status', 'SUCCESS (' . count($services) . ' services)');

        return array(
            'success' => true,
            'message' => 'Successfully synced ' . count($services) . ' service(s) (' . $created . ' added, ' . $updated . ' updated).',
            'created' => $created,
            'updated' => $updated,
            'total' => count($services),
            'lastSync' => $now
        );
    }
}
