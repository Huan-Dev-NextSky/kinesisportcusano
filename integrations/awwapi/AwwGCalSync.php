<?php
/**
 * AwwGCalSync - STEP 1: Syncs Reservations from Google Calendar into Local System (Cleanto DB)
 * 
 * Flow:
 * Google Calendar Event -> Cleanto Database (ct_bookings, ct_order_client_info, ct_users, ct_payments, ct_gcal_kinesis_sync)
 * 
 * Once data resides safely in the Local System, Step 2 (AwwAppointmentSync) pushes it from the System to Kinesis API.
 */

require_once dirname(__FILE__) . '/AwwApiMigration.php';

class AwwGCalSync {
    private $conn;
    private $setting;

    public function __construct($conn) {
        $this->conn = $conn;
        require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
        $this->setting = new cleanto_setting();
        $this->setting->conn = $this->conn;
    }

    /**
     * Main sync function: Google Calendar -> Local System DB
     * 
     * @param int $daysPast How many days back to inspect
     * @param int $daysFuture How many days ahead to inspect
     * @return array Summary of local bookings created/updated/cancelled
     */
    public function syncAll($daysPast = 7, $daysFuture = 60) {
        AwwApiMigration::run($this->conn);

        $gcStatus = $this->setting->get_option('ct_gc_status');
        $gcConfigured = $this->setting->get_option('ct_gc_status_configure');
        $gcToken = $this->setting->get_option('ct_gc_token');
        $syncDirection = $this->setting->get_option('ct_gc_sync_direction') ?: 'two_way';

        if ($syncDirection === 'system_to_gcal') {
            $msg = 'Google Calendar -> System import is skipped because Sync Direction is configured as "System -> Google Calendar (Export Only)".';
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'skipped' => 0,
                'total' => 0
            );
        }

        if ($gcStatus !== 'Y' || $gcConfigured !== 'Y' || empty($gcToken)) {
            $msg = 'Google Calendar integration is not enabled or not authorized in Settings.';
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'skipped' => 0,
                'total' => 0
            );
        }

        // 1. Initialize Google Calendar Service
        $googleService = $this->initGoogleCalendarService();
        if (!$googleService['success']) {
            $this->updateSyncStatus(false, $googleService['message']);
            return array(
                'success' => false,
                'message' => $googleService['message'],
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'skipped' => 0,
                'total' => 0
            );
        }

        $calService = $googleService['calService'];
        $calendarId = $this->setting->get_option('ct_gc_id');
        $systemTimezone = $this->setting->get_option('ct_timezone') ?: 'Europe/Rome';

        // 2. Compute date window
        $startDate = date('Y-m-d', strtotime("-{$daysPast} days"));
        $endDate = date('Y-m-d', strtotime("+{$daysFuture} days"));
        $timeMin = $startDate . 'T00:00:00Z';
        $timeMax = $endDate . 'T23:59:59Z';

        try {
            $events = $calService->events->listEvents($calendarId, array(
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'maxResults' => 500,
                'showDeleted' => true
            ));
        } catch (Exception $e) {
            $msg = 'Failed to fetch Google Calendar events: ' . $e->getMessage();
            $this->updateSyncStatus(false, $msg);
            return array(
                'success' => false,
                'message' => $msg,
                'created' => 0,
                'updated' => 0,
                'cancelled' => 0,
                'skipped' => 0,
                'total' => 0
            );
        }

        $eventItems = isset($events['items']) ? $events['items'] : array();
        $totalEvents = count($eventItems);

        $createdCount = 0;
        $updatedCount = 0;
        $cancelledCount = 0;
        $skippedCount = 0;
        $errors = array();
        $activeGcalIds = array();

        // 3. Process each event into Local System Database
        foreach ($eventItems as $event) {
            $gEventId = isset($event['id']) ? trim($event['id']) : '';
            if (empty($gEventId)) {
                $skippedCount++;
                continue;
            }

            $summary = isset($event['summary']) ? trim($event['summary']) : '';
            $description = isset($event['description']) ? trim($event['description']) : '';
            $status = isset($event['status']) ? trim($event['status']) : 'confirmed';

            // Check if cancelled in GCal payload
            if ($status === 'cancelled') {
                $cancelRes = $this->handleCancelledEvent($gEventId);
                if ($cancelRes) {
                    $cancelledCount++;
                } else {
                    $skippedCount++;
                }
                continue;
            }

            $activeGcalIds[] = $gEventId;

            // Extract event timestamps
            $startDateTime = null;
            $endDateTime = null;
            if (isset($event['start']['dateTime']) && isset($event['end']['dateTime'])) {
                $dtStart = new DateTime($event['start']['dateTime']);
                $dtStart->setTimezone(new DateTimeZone($systemTimezone));
                $startDateTime = $dtStart->format('Y-m-d H:i:s');

                $dtEnd = new DateTime($event['end']['dateTime']);
                $dtEnd->setTimezone(new DateTimeZone($systemTimezone));
                $endDateTime = $dtEnd->format('Y-m-d H:i:s');
            } elseif (isset($event['start']['date'])) {
                $startDateTime = $event['start']['date'] . ' 09:00:00';
                $endDateTime = $event['start']['date'] . ' 10:00:00';
            }

            if (!$startDateTime) {
                $skippedCount++;
                continue;
            }

            $existingBooking = $this->findLocalBookingByGCalId($gEventId);
            $existingSync = $this->findSyncRecordByGCalId($gEventId);

            $attendeeInfo = $this->extractAttendeeInfo($event);
            $parsedData = $this->parseEventContent($summary, $description, $attendeeInfo, $startDateTime, $endDateTime);

            if ($existingBooking || $existingSync) {
                // Event exists in System -> Check if time/content changed
                $updateRes = $this->updateExistingBooking($existingBooking, $existingSync, $parsedData, $gEventId);
                if ($updateRes['updated']) {
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                // New Event -> Create booking record in Local System DB
                $createRes = $this->createNewBookingInSystem($parsedData, $gEventId);
                if ($createRes['success']) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                    if (!empty($createRes['error'])) {
                        $errors[] = "Event {$gEventId}: " . $createRes['error'];
                    }
                }
            }
        }

        // Do not treat "missing from list" as cancelled — events can fall outside the
        // fetch window, hit maxResults, or fail timezone matching (false cancels).
        // Cancellations come from showDeleted + status=cancelled above.

        $summaryMsg = sprintf(
            'Google Calendar -> System sync completed: %d event(s) processed. Created in System: %d, Updated: %d, Cancelled: %d, Skipped: %d.',
            $totalEvents,
            $createdCount,
            $updatedCount,
            $cancelledCount,
            $skippedCount
        );

        $this->updateSyncStatus(true, $summaryMsg);

        return array(
            'success' => true,
            'message' => $summaryMsg,
            'total' => $totalEvents,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'cancelled' => $cancelledCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        );
    }

    /**
     * Initialize Google Calendar Client
     */
    private function initGoogleCalendarService() {
        $gcClientId = $this->setting->get_option('ct_gc_client_id');
        $gcClientSecret = $this->setting->get_option('ct_gc_client_secret');
        $gcAdminUrl = $this->setting->get_option('ct_gc_admin_url');
        $gcApiKey = $this->setting->get_option('ct_gc_api_key');
        $gcToken = $this->setting->get_option('ct_gc_token');

        $googleClientFile = dirname(dirname(dirname(__FILE__))) . '/extension/GoogleCalendar/google-api-php-client/src/Google_Client.php';
        $googleCalServiceFile = dirname(dirname(dirname(__FILE__))) . '/extension/GoogleCalendar/google-api-php-client/src/contrib/Google_CalendarService.php';

        if (!file_exists($googleClientFile) || !file_exists($googleCalServiceFile)) {
            return array('success' => false, 'message' => 'Google Calendar client library files missing.');
        }

        require_once $googleClientFile;
        require_once $googleCalServiceFile;

        try {
            $client = new Google_Client();
            $client->setApplicationName("Kinesis Cleanto Google Calendar Sync");
            $client->setClientId($gcClientId);
            $client->setClientSecret($gcClientSecret);
            $client->setRedirectUri($gcAdminUrl);
            $client->setDeveloperKey($gcApiKey);
            $client->setScopes('https://www.googleapis.com/auth/calendar');
            $client->setAccessType('offline');

            $client->setAccessToken($gcToken);
            $tokenObj = json_decode($gcToken);

            if ($client->isAccessTokenExpired()) {
                if (isset($tokenObj->refresh_token) && !empty($tokenObj->refresh_token)) {
                    $client->refreshToken($tokenObj->refresh_token);
                    $newAccessToken = $client->getAccessToken();
                    $this->setting->set_option('ct_gc_token', $newAccessToken);
                }
            }

            $calService = new Google_CalendarService($client);
            return array('success' => true, 'calService' => $calService);
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Google Client Init Error: ' . $e->getMessage());
        }
    }

    /**
     * Extract attendee info if present
     */
    private function extractAttendeeInfo($event) {
        $info = array('email' => '', 'name' => '');
        if (!empty($event['attendees']) && is_array($event['attendees'])) {
            foreach ($event['attendees'] as $att) {
                if (!empty($att['email']) && strpos($att['email'], 'calendar.google.com') === false) {
                    $info['email'] = trim($att['email']);
                    if (!empty($att['displayName'])) {
                        $info['name'] = trim($att['displayName']);
                    }
                    break;
                }
            }
        }
        return $info;
    }

    /**
     * Parse Event Summary, Description and Attendees
     */
    private function parseEventContent($summary, $description, $attendeeInfo, $startDateTime, $endDateTime) {
        $firstName = 'Guest';
        $lastName = 'User';
        $email = !empty($attendeeInfo['email']) ? $attendeeInfo['email'] : '';
        $phone = '';
        $dob = '1990-01-01';
        $serviceName = '';
        $staffName = '';
        $notes = '';

        // Check if description is HTML table from Cleanto
        if (strpos($description, '<table') !== false && strpos($description, 'With') !== false) {
            if (preg_match('/<td>With<\/td>\s*<td>&nbsp;&nbsp;([^<]+)<\/td>/i', $description, $m)) {
                $fullName = trim($m[1]);
                $parts = explode(' ', $fullName, 2);
                $firstName = isset($parts[0]) ? $parts[0] : 'Guest';
                $lastName = isset($parts[1]) ? $parts[1] : 'User';
            }
            if (preg_match('/<td>Email<\/td>\s*<td>&nbsp;&nbsp;([^<]+)<\/td>/i', $description, $m)) {
                $email = trim($m[1]);
            }
            if (preg_match('/<td>Phone<\/td>\s*<td>&nbsp;&nbsp;([^<]+)<\/td>/i', $description, $m)) {
                $phone = trim($m[1]);
            }
            if (preg_match('/<td>For<\/td>\s*<td>&nbsp;&nbsp;([^<]+)<\/td>/i', $description, $m)) {
                $serviceName = trim($m[1]);
            }
        } else {
            // Parse plain text / key-value lines
            $lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $description));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match('/^(email|e-mail|mail):\s*(.+)$/i', $line, $m)) {
                    $email = trim($m[2]);
                } elseif (preg_match('/^(phone|tel|telefono|cell|cellulare):\s*(.+)$/i', $line, $m)) {
                    $phone = trim($m[2]);
                } elseif (preg_match('/^(dob|data di nascita|birth|nascita):\s*(.+)$/i', $line, $m)) {
                    $dobRaw = trim($m[2]);
                    $parsedDob = date('Y-m-d', strtotime($dobRaw));
                    if ($parsedDob && $parsedDob !== '1970-01-01') {
                        $dob = $parsedDob;
                    }
                } elseif (preg_match('/^(service|servizio|prestazione|trattamento):\s*(.+)$/i', $line, $m)) {
                    $serviceName = trim($m[2]);
                } elseif (preg_match('/^(doctor|dr|dottore|medico|staff|operatore):\s*(.+)$/i', $line, $m)) {
                    $staffName = trim($m[2]);
                } elseif (preg_match('/^(name|nome|paziente|cliente):\s*(.+)$/i', $line, $m)) {
                    $fullName = trim($m[2]);
                    $parts = explode(' ', $fullName, 2);
                    $firstName = isset($parts[0]) ? $parts[0] : 'Guest';
                    $lastName = isset($parts[1]) ? $parts[1] : 'User';
                } else {
                    $notes .= ($notes ? "\n" : '') . $line;
                }
            }

            // Parse Summary if name or service is still generic
            if (!empty($summary)) {
                if (strpos($summary, '-') !== false) {
                    $sumParts = explode('-', $summary, 2);
                    $part1 = trim($sumParts[0]);
                    $part2 = trim($sumParts[1]);

                    $matchedService = $this->matchService($part2);
                    if ($matchedService) {
                        $serviceName = $matchedService['title'];
                        $nameParts = explode(' ', $part1, 2);
                        $firstName = isset($nameParts[0]) ? $nameParts[0] : 'Guest';
                        $lastName = isset($nameParts[1]) ? $nameParts[1] : 'User';
                    } else {
                        $matchedService1 = $this->matchService($part1);
                        if ($matchedService1) {
                            $serviceName = $matchedService1['title'];
                            $nameParts = explode(' ', $part2, 2);
                            $firstName = isset($nameParts[0]) ? $nameParts[0] : 'Guest';
                            $lastName = isset($nameParts[1]) ? $nameParts[1] : 'User';
                        } else {
                            $nameParts = explode(' ', $part1, 2);
                            $firstName = isset($nameParts[0]) ? $nameParts[0] : 'Guest';
                            $lastName = isset($nameParts[1]) ? $nameParts[1] : 'User';
                        }
                    }
                } else {
                    if ($firstName === 'Guest' && $lastName === 'User') {
                        $nameParts = explode(' ', $summary, 2);
                        $firstName = isset($nameParts[0]) ? $nameParts[0] : 'Guest';
                        $lastName = isset($nameParts[1]) ? $nameParts[1] : 'User';
                    }
                }
            }
        }

        $serviceRecord = $this->matchService($serviceName);
        if (!$serviceRecord) {
            $serviceRecord = $this->getDefaultService();
        }

        $staffRecord = $this->matchStaff($staffName);

        if (empty($email)) {
            $cleanFirst = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($firstName));
            $cleanLast = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($lastName));
            if (!empty($cleanFirst) && !empty($cleanLast)) {
                $email = $cleanFirst . '.' . $cleanLast . '@kinesisport.it';
            } else {
                $email = 'client_' . substr(md5($summary . $startDateTime), 0, 8) . '@kinesisport.it';
            }
        }

        if (empty($phone)) {
            $phone = '0000000000';
        }

        return array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => strtolower($email),
            'phone' => $phone,
            'dob' => $dob,
            'service' => $serviceRecord,
            'staff' => $staffRecord,
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'notes' => $notes,
            'summary' => $summary
        );
    }

    /**
     * Match Service in database by name
     */
    private function matchService($serviceName) {
        if (empty($serviceName)) return null;

        $esc = mysqli_real_escape_string($this->conn, trim($serviceName));
        $res = mysqli_query($this->conn, "SELECT `id`, `external_service_id`, `title`, `duration` FROM `ct_services` WHERE `title` LIKE '%{$esc}%' AND `status` = 'E' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return null;
    }

    /**
     * Get default active service
     */
    private function getDefaultService() {
        $res = mysqli_query($this->conn, "SELECT `id`, `external_service_id`, `title`, `duration` FROM `ct_services` WHERE `status` = 'E' ORDER BY `position` ASC, `id` ASC LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return array(
            'id' => 1,
            'external_service_id' => 1,
            'title' => 'General Appointment',
            'duration' => '01:00:00'
        );
    }

    /**
     * Match Staff/Doctor in database
     */
    private function matchStaff($staffName) {
        if (!empty($staffName)) {
            $esc = mysqli_real_escape_string($this->conn, trim($staffName));
            $res = mysqli_query($this->conn, "SELECT `id`, `fullname`, `email` FROM `ct_admin_info` WHERE `fullname` LIKE '%{$esc}%' LIMIT 1");
            if ($res && mysqli_num_rows($res) > 0) {
                return mysqli_fetch_assoc($res);
            }
        }
        $res = mysqli_query($this->conn, "SELECT `id`, `fullname`, `email` FROM `ct_admin_info` WHERE `enable_booking` = 'Y' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return array('id' => 1, 'fullname' => 'Admin', 'email' => '');
    }

    /**
     * Create New Booking inside Local System Database
     */
    private function createNewBookingInSystem($parsed, $gEventId) {
        // 1. Find or create ct_users record
        $userId = $this->getOrCreateUser($parsed);
        if (!$userId) {
            return array('success' => false, 'error' => 'Could not create or find local user.');
        }

        // 2. Generate next order_id
        $orderRes = mysqli_query($this->conn, "SELECT MAX(`order_id`) as max_oid FROM `ct_bookings`");
        $orderRow = mysqli_fetch_assoc($orderRes);
        $nextOrderId = (!empty($orderRow['max_oid']) && (int)$orderRow['max_oid'] >= 1000) ? ((int)$orderRow['max_oid'] + 1) : 1000;

        $currentTime = date('Y-m-d H:i:s');
        $bookingDateTime = $parsed['startDateTime'];
        $serviceId = (int)$parsed['service']['id'];
        $staffId = (int)$parsed['staff']['id'];
        $escGEventId = mysqli_real_escape_string($this->conn, $gEventId);

        // 3. Insert into ct_bookings (kinesis_sync_status is set to PENDING for Step 2)
        $insertBooking = "INSERT INTO `ct_bookings` (
            `id`, `order_id`, `client_id`, `order_date`, `booking_date_time`,
            `service_id`, `method_id`, `method_unit_id`, `method_unit_qty`, `method_unit_qty_rate`,
            `booking_status`, `reject_reason`, `reminder_status`, `lastmodify`, `read_status`,
            `staff_ids`, `gc_event_id`, `gc_staff_event_id`,
            `kinesis_appointment_id`, `kinesis_customer_id`, `kinesis_sync_status`, `kinesis_sync_time`
        ) VALUES (
            NULL, '{$nextOrderId}', '{$userId}', '{$currentTime}', '{$bookingDateTime}',
            '{$serviceId}', '0', '0', '0', '0',
            'A', '', '0', '{$currentTime}', 'U',
            '{$staffId}', '{$escGEventId}', '',
            NULL, NULL, 'PENDING', NULL
        )";
        $bookingRes = mysqli_query($this->conn, $insertBooking);
        if (!$bookingRes) {
            return array('success' => false, 'error' => 'Failed to insert booking into system: ' . mysqli_error($this->conn));
        }
        $localBookingId = mysqli_insert_id($this->conn);

        // 4. Insert into ct_order_client_info
        $clientName = mysqli_real_escape_string($this->conn, $parsed['firstName'] . ' ' . $parsed['lastName']);
        $clientEmail = mysqli_real_escape_string($this->conn, $parsed['email']);
        $clientPhone = mysqli_real_escape_string($this->conn, $parsed['phone']);
        $personalInfo = base64_encode(serialize(array(
            'zip' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'notes' => $parsed['notes'],
            'vc_status' => 'N',
            'p_status' => 'N',
            'contact_status' => ''
        )));

        $insertClient = "INSERT INTO `ct_order_client_info` (
            `id`, `order_id`, `client_name`, `client_email`, `client_phone`, `client_personal_info`, `order_duration`, `recurring_id`
        ) VALUES (
            NULL, '{$nextOrderId}', '{$clientName}', '{$clientEmail}', '{$clientPhone}', '{$personalInfo}', '60', '0'
        )";
        mysqli_query($this->conn, $insertClient);

        // 5. Insert into ct_payments
        $insertPayment = "INSERT INTO `ct_payments` (
            `id`, `order_id`, `payment_method`, `transaction_id`, `amount`, `discount`, `taxes`,
            `partial_amount`, `payment_date`, `net_amount`, `lastmodify`, `frequently_discount`,
            `frequently_discount_amount`, `recurrence_status`, `tip`, `payment_status`
        ) VALUES (
            NULL, '{$nextOrderId}', 'google-calendar', '{$escGEventId}', '0', '0', '0',
            '0', '{$bookingDateTime}', '0', '{$currentTime}', '',
            '0', 'N', '0', 'Pending'
        )";
        mysqli_query($this->conn, $insertPayment);

        // 6. Insert mapping record into ct_gcal_kinesis_sync
        $escSummary = mysqli_real_escape_string($this->conn, $parsed['summary']);
        $escStart = mysqli_real_escape_string($this->conn, $parsed['startDateTime']);
        $escEnd = mysqli_real_escape_string($this->conn, $parsed['endDateTime']);
        $escDob = mysqli_real_escape_string($this->conn, $parsed['dob']);
        $extServiceId = !empty($parsed['service']['external_service_id']) ? (int)$parsed['service']['external_service_id'] : (int)$serviceId;

        $insertSync = "INSERT INTO `ct_gcal_kinesis_sync` (
            `google_event_id`, `local_order_id`, `local_booking_id`, `kinesis_customer_id`, `kinesis_appointment_id`,
            `event_summary`, `event_start`, `event_end`, `customer_email`, `customer_phone`, `customer_name`, `customer_dob`,
            `service_id`, `employee_id`, `sync_status`, `sync_action`, `last_sync_message`, `created_at`, `updated_at`
        ) VALUES (
            '{$escGEventId}', '{$nextOrderId}', '{$localBookingId}', NULL, NULL,
            '{$escSummary}', '{$escStart}', '{$escEnd}', '{$clientEmail}', '{$clientPhone}', '{$clientName}', '{$escDob}',
            '{$extServiceId}', '{$staffId}', 'PENDING', 'CREATE', 'Imported to local system from Google Calendar', '{$currentTime}', '{$currentTime}'
        )";
        mysqli_query($this->conn, $insertSync);

        return array(
            'success' => true,
            'order_id' => $nextOrderId,
            'booking_id' => $localBookingId
        );
    }

    /**
     * Update existing booking in Local System Database
     */
    private function updateExistingBooking($existingBooking, $existingSync, $parsed, $gEventId) {
        $updated = false;
        $newStart = $parsed['startDateTime'];
        $newEnd = $parsed['endDateTime'];

        $currentBookingTime = $existingBooking ? $existingBooking['booking_date_time'] : ($existingSync ? $existingSync['event_start'] : '');

        if ($currentBookingTime !== $newStart) {
            $currentTime = date('Y-m-d H:i:s');
            $escStart = mysqli_real_escape_string($this->conn, $newStart);
            $escEnd = mysqli_real_escape_string($this->conn, $newEnd);
            $escGEventId = mysqli_real_escape_string($this->conn, $gEventId);

            // Update ct_bookings with UPDATE_PENDING status
            if ($existingBooking) {
                mysqli_query($this->conn, "UPDATE `ct_bookings` SET `booking_date_time` = '{$escStart}', `lastmodify` = '{$currentTime}', `kinesis_sync_status` = 'UPDATE_PENDING' WHERE `id` = " . (int)$existingBooking['id']);
            }

            // Update ct_gcal_kinesis_sync
            mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET `event_start` = '{$escStart}', `event_end` = '{$escEnd}', `sync_status` = 'PENDING', `sync_action` = 'UPDATE', `updated_at` = '{$currentTime}' WHERE `google_event_id` = '{$escGEventId}'");

            $updated = true;
        }

        return array('updated' => $updated);
    }

    /**
     * Handle cancelled/deleted Google Calendar event in Local System Database
     */
    private function handleCancelledEvent($gEventId) {
        $escGEventId = mysqli_real_escape_string($this->conn, $gEventId);
        $syncRes = mysqli_query($this->conn, "SELECT * FROM `ct_gcal_kinesis_sync` WHERE `google_event_id` = '{$escGEventId}' LIMIT 1");
        if ($syncRes && mysqli_num_rows($syncRes) > 0) {
            $syncRow = mysqli_fetch_assoc($syncRes);
            $currentTime = date('Y-m-d H:i:s');

            // Update ct_bookings status to CC (Cancelled) & CANCEL_PENDING
            if (!empty($syncRow['local_booking_id'])) {
                mysqli_query($this->conn, "UPDATE `ct_bookings` SET `booking_status` = 'CC', `lastmodify` = '{$currentTime}', `kinesis_sync_status` = 'CANCEL_PENDING' WHERE `id` = " . (int)$syncRow['local_booking_id']);
            } elseif (!empty($syncRow['local_order_id'])) {
                mysqli_query($this->conn, "UPDATE `ct_bookings` SET `booking_status` = 'CC', `lastmodify` = '{$currentTime}', `kinesis_sync_status` = 'CANCEL_PENDING' WHERE `order_id` = " . (int)$syncRow['local_order_id']);
            }

            // Update sync record
            mysqli_query($this->conn, "UPDATE `ct_gcal_kinesis_sync` SET `sync_status` = 'CANCEL_PENDING', `sync_action` = 'CANCEL', `updated_at` = '{$currentTime}' WHERE `id` = " . (int)$syncRow['id']);
            return true;
        }
        return false;
    }

    /**
     * Find or create local ct_users record
     */
    private function getOrCreateUser($parsed) {
        $email = mysqli_real_escape_string($this->conn, $parsed['email']);
        $chk = mysqli_query($this->conn, "SELECT `id` FROM `ct_users` WHERE `user_email` = '{$email}' LIMIT 1");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $row = mysqli_fetch_assoc($chk);
            return (int)$row['id'];
        }

        $currentTime = date('Y-m-d H:i:s');
        $randomPwd = substr(md5(uniqid(rand(), true)), 0, 8);
        $hashedPwd = md5($randomPwd);
        $firstName = mysqli_real_escape_string($this->conn, $parsed['firstName']);
        $lastName = mysqli_real_escape_string($this->conn, $parsed['lastName']);
        $phone = mysqli_real_escape_string($this->conn, $parsed['phone']);
        $referralCode = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);

        $insert = "INSERT INTO `ct_users` (
            `id`, `user_email`, `user_pwd`, `first_name`, `last_name`, `phone`,
            `zip`, `address`, `city`, `state`, `notes`, `vc_status`, `p_status`,
            `contact_status`, `status`, `usertype`, `cus_dt`, `stripe_id`, `referal_code`, `wallet_amount`
        ) VALUES (
            NULL, '{$email}', '{$hashedPwd}', '{$firstName}', '{$lastName}', '{$phone}',
            '', '', '', '', '', 'N', 'N',
            '', 'E', 'client', '{$currentTime}', '', '{$referralCode}', '0'
        )";
        $res = mysqli_query($this->conn, $insert);
        if ($res) {
            return mysqli_insert_id($this->conn);
        }
        return null;
    }

    /**
     * Find local booking by Google Event ID
     */
    private function findLocalBookingByGCalId($gEventId) {
        $esc = mysqli_real_escape_string($this->conn, $gEventId);
        $res = mysqli_query($this->conn, "SELECT * FROM `ct_bookings` WHERE `gc_event_id` = '{$esc}' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return null;
    }

    /**
     * Find sync record by Google Event ID
     */
    private function findSyncRecordByGCalId($gEventId) {
        $esc = mysqli_real_escape_string($this->conn, $gEventId);
        $res = mysqli_query($this->conn, "SELECT * FROM `ct_gcal_kinesis_sync` WHERE `google_event_id` = '{$esc}' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return null;
    }

    /**
     * Update sync status in settings table
     */
    private function updateSyncStatus($success, $message) {
        $now = date('Y-m-d H:i:s');
        $statusStr = $success ? 'SUCCESS: ' . $message : 'FAILED: ' . $message;
        $this->setting->set_option('gcal_last_sync_time', $now);
        $this->setting->set_option('gcal_last_sync_status', $statusStr);
    }
}
