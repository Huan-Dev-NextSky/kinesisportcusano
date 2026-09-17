<?php
/**
 * AwwServiceSync - Synchronizes services from Kinesis API (v3.0) into local ct_services
 *
 * Rules:
 * - Maps external Service.id to ct_services.external_service_id
 * - Inserts new services if not present locally (sets duration + price once)
 * - Updates name, description on existing services
 * - Does NOT overwrite Durata / Prezzo Base on update (admin-owned fields)
 * - Syncs duration/price into method units only when creating a new service
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

        require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_services.php';
        $objservice = new cleanto_services();
        $objservice->conn = $this->conn;
        $objservice->ensure_price_duration_schema();

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
            $durationRaw = isset($srv['duration']) ? $srv['duration'] : (isset($srv['Duration']) ? $srv['Duration'] : '01:00:00');
            $durationMins = cleanto_services::duration_to_minutes($durationRaw);
            $apiPrice = null;
            foreach (array('price', 'Price', 'basePrice', 'base_price', 'cost', 'Cost', 'amount', 'Amount') as $priceKey) {
                if (isset($srv[$priceKey]) && $srv[$priceKey] !== '' && is_numeric($srv[$priceKey])) {
                    $apiPrice = (float)$srv[$priceKey];
                    break;
                }
            }

            $nameEsc = mysqli_real_escape_string($this->conn, $name);
            $descEsc = mysqli_real_escape_string($this->conn, $desc);

            $check = mysqli_query($this->conn, "SELECT `id`, `price`, `duration` FROM `ct_services` WHERE `external_service_id` = " . $extId);
            if ($check && mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                $localId = (int)$row['id'];
                /* Update: never overwrite local Durata / Prezzo Base */
                $updateQ = "UPDATE `ct_services` SET `title` = '{$nameEsc}', `description` = '{$descEsc}' WHERE `id` = {$localId}";
                mysqli_query($this->conn, $updateQ);
                $updated++;
            } else {
                $posQ = mysqli_query($this->conn, "SELECT MAX(`position`) AS `max_pos` FROM `ct_services`");
                $posRow = mysqli_fetch_assoc($posQ);
                $pos = isset($posRow['max_pos']) ? ((int)$posRow['max_pos'] + 1) : 1;
                $price = ($apiPrice !== null) ? $apiPrice : 0;

                $insertQ = "INSERT INTO `ct_services` (`id`, `external_service_id`, `title`, `description`, `duration`, `color`, `image`, `price`, `status`, `position`)
                            VALUES (NULL, {$extId}, '{$nameEsc}', '{$descEsc}', '{$durationMins}', '#1596e8', 'default.png', '{$price}', 'E', {$pos})";
                mysqli_query($this->conn, $insertQ);
                $localId = (int)mysqli_insert_id($this->conn);
                if ($localId > 0) {
                    $objservice->price = $price;
                    $objservice->duration = $durationMins;
                    $objservice->sync_price_duration_to_units($localId);
                }
                $created++;
            }
        }

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
