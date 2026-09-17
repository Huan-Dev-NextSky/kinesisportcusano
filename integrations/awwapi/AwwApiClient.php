<?php
/**
 * AwwApiClient - Client for Kinesis / Awwdev Appointment Booking REST API (v3.0)
 * 
 * Supports:
 * - JWT authentication with automatic 15-minute token refresh
 * - Retry with exponential backoff for 429 Rate Limiting
 * - RFC 7807 Problem Details error handling
 * - Services, Employees, Availability, Appointments, and Customer management
 */

class AwwApiClient {
    const ENV_SANDBOX = 'sandbox';
    const ENV_PRODUCTION = 'production';

    const URL_SANDBOX = 'https://dev-calendar.awwdev.com';
    const URL_PRODUCTION = 'https://calendar.awwdev.com';

    private $env;
    private $baseUrl;
    private $username;
    private $password;
    private $conn;
    private $setting;

    private $accessToken = null;
    private $refreshToken = null;
    private $tokenExpiresAt = 0;

    public function __construct($conn = null, $env = null, $username = null, $password = null) {
        $this->conn = $conn;
        
        if ($this->conn) {
            require_once dirname(dirname(dirname(__FILE__))) . '/objects/class_setting.php';
            $this->setting = new cleanto_setting();
            $this->setting->conn = $this->conn;
        }

        $this->env = $env ?: ($this->setting ? $this->setting->get_option('kinesis_api_env') : self::ENV_SANDBOX);
        if (!$this->env) {
            $this->env = self::ENV_SANDBOX;
        }

        $this->baseUrl = ($this->env === self::ENV_PRODUCTION) ? self::URL_PRODUCTION : self::URL_SANDBOX;
        $this->username = $username ?: ($this->setting ? $this->setting->get_option('kinesis_api_username') : '');
        $this->password = $password ?: ($this->setting ? $this->setting->get_option('kinesis_api_password') : '');

        $this->loadStoredTokens();
    }

    /**
     * Load cached tokens from settings table
     */
    private function loadStoredTokens() {
        if ($this->setting) {
            $this->accessToken = $this->setting->get_option('kinesis_api_access_token');
            $this->refreshToken = $this->setting->get_option('kinesis_api_refresh_token');
            $this->tokenExpiresAt = (int)$this->setting->get_option('kinesis_api_token_expires_at');
        }
    }

    /**
     * Persist tokens in database
     */
    private function saveTokens($accessToken, $refreshToken, $expiresInSeconds = 900, $persist = true) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenExpiresAt = time() + $expiresInSeconds;

        if ($persist && $this->setting) {
            $this->setting->set_option('kinesis_api_access_token', $this->accessToken);
            $this->setting->set_option('kinesis_api_refresh_token', $this->refreshToken);
            $this->setting->set_option('kinesis_api_token_expires_at', (string)$this->tokenExpiresAt);
        }
    }

    /**
     * Clear cached tokens
     */
    public function clearTokens() {
        $this->accessToken = null;
        $this->refreshToken = null;
        $this->tokenExpiresAt = 0;

        if ($this->setting) {
            $this->setting->set_option('kinesis_api_access_token', '');
            $this->setting->set_option('kinesis_api_refresh_token', '');
            $this->setting->set_option('kinesis_api_token_expires_at', '0');
        }
    }

    /**
     * Perform login (POST /api/account/login)
     */
    public function login($username = null, $password = null, $persistTokens = true) {
        $user = $username ?: $this->username;
        $pass = $password ?: $this->password;

        if (empty($user) || empty($pass)) {
            return array(
                'success' => false,
                'status' => 400,
                'error' => 'API username or password is not configured.'
            );
        }

        $url = $this->baseUrl . '/api/account/login';
        $payload = array(
            'username' => $user,
            'password' => $pass
        );

        $response = $this->executeCurl('POST', $url, $payload, null, false);

        if ($response['status'] === 200 && !empty($response['data']['accessToken'])) {
            $this->saveTokens(
                $response['data']['accessToken'],
                $response['data']['refreshToken'],
                900, // 15 minutes as per v3.0 specification
                $persistTokens
            );
            return array(
                'success' => true,
                'status' => 200,
                'data' => $response['data']
            );
        }

        return array(
            'success' => false,
            'status' => $response['status'],
            'error' => isset($response['data']['detail']) ? $response['data']['detail'] : (isset($response['raw']) ? $response['raw'] : 'Login failed')
        );
    }

    /**
     * Refresh access token (POST /api/account/refresh-token)
     */
    public function refreshToken() {
        if (empty($this->refreshToken)) {
            return $this->login();
        }

        $url = $this->baseUrl . '/api/account/refresh-token';
        $payload = array(
            'refreshToken' => $this->refreshToken
        );

        $response = $this->executeCurl('POST', $url, $payload, null, false);

        if ($response['status'] === 200 && !empty($response['data']['accessToken'])) {
            $this->saveTokens(
                $response['data']['accessToken'],
                $response['data']['refreshToken'],
                900
            );
            return array(
                'success' => true,
                'status' => 200,
                'data' => $response['data']
            );
        }

        // If refresh fails (token revoked/expired), perform a fresh login
        $this->clearTokens();
        return $this->login();
    }

    /**
     * Logout (POST /api/account/logout)
     */
    public function logout() {
        if (!empty($this->accessToken)) {
            $url = $this->baseUrl . '/api/account/logout';
            $this->executeCurl('POST', $url, null, $this->accessToken, false);
        }
        $this->clearTokens();
        return array('success' => true);
    }

    /**
     * Get valid access token, auto-refreshing if needed
     */
    public function getValidToken() {
        // Refresh token if within 60 seconds of expiration or empty
        if (empty($this->accessToken) || time() >= ($this->tokenExpiresAt - 60)) {
            $auth = !empty($this->refreshToken) ? $this->refreshToken() : $this->login();
            if (!$auth['success']) {
                return false;
            }
        }
        return $this->accessToken;
    }

    /**
     * Generic API Request dispatcher
     */
    public function request($method, $endpoint, $data = null, $authRequired = true, $retryCount = 0) {
        $token = null;
        if ($authRequired) {
            $token = $this->getValidToken();
            if (!$token) {
                return array(
                    'success' => false,
                    'status' => 401,
                    'error' => 'Authentication failed. Please check your API credentials.'
                );
            }
        }

        $url = $this->baseUrl . $endpoint;
        $response = $this->executeCurl($method, $url, $data, $token);

        // Handle 401 Unauthorized -> Refresh token and retry once
        if ($response['status'] === 401 && $authRequired && $retryCount === 0) {
            $refresh = $this->refreshToken();
            if ($refresh['success']) {
                return $this->request($method, $endpoint, $data, true, $retryCount + 1);
            }
        }

        // Handle 429 Too Many Requests -> Backoff and retry
        if ($response['status'] === 429 && $retryCount < 2) {
            $retryAfter = isset($response['headers']['retry-after']) ? (int)$response['headers']['retry-after'] : 2;
            if ($retryAfter > 0 && $retryAfter <= 5) {
                sleep($retryAfter);
                return $this->request($method, $endpoint, $data, $authRequired, $retryCount + 1);
            }
        }

        $isSuccess = ($response['status'] >= 200 && $response['status'] < 300);
        return array(
            'success' => $isSuccess,
            'status' => $response['status'],
            'data' => $response['data'],
            'error' => !$isSuccess ? (isset($response['data']['detail']) ? $response['data']['detail'] : (isset($response['raw']) ? $response['raw'] : 'HTTP ' . $response['status'])) : null,
            'problemDetails' => !$isSuccess && isset($response['data']['title']) ? $response['data'] : null
        );
    }

    /**
     * Low-level cURL execution
     */
    private function executeCurl($method, $url, $data = null, $token = null) {
        $ch = curl_init();
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json'
        );

        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $method = strtoupper($method);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($data !== null && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $jsonPayload = is_string($data) ? $data : json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return array(
                'status' => 0,
                'data' => null,
                'raw' => $error,
                'headers' => array()
            );
        }

        $rawHeader = substr($response, 0, $headerSize);
        $rawBody = substr($response, $headerSize);

        $parsedHeaders = array();
        foreach (explode("\r\n", $rawHeader) as $i => $line) {
            if ($i === 0) continue;
            if (strpos($line, ': ') !== false) {
                list($key, $val) = explode(': ', $line, 2);
                $parsedHeaders[strtolower(trim($key))] = trim($val);
            }
        }

        $decodedData = json_decode($rawBody, true);

        return array(
            'status' => $httpCode,
            'data' => $decodedData !== null ? $decodedData : $rawBody,
            'raw' => $rawBody,
            'headers' => $parsedHeaders
        );
    }

    /* =========================================================================
     * PUBLIC API METHODS (V3.0 Specification)
     * ========================================================================= */

    /**
     * Test connection with current settings
     */
    public function testConnection() {
        /* Do not persist JWT — testing must not overwrite saved tokens */
        $loginRes = $this->login(null, null, false);
        if (!$loginRes['success']) {
            return array(
                'success' => false,
                'message' => 'Login failed: ' . (isset($loginRes['error']) ? $loginRes['error'] : 'Invalid credentials')
            );
        }

        $servicesRes = $this->getServices();
        if (!$servicesRes['success']) {
            return array(
                'success' => false,
                'message' => 'Login succeeded, but failed to fetch services: ' . (isset($servicesRes['error']) ? $servicesRes['error'] : 'Unknown error')
            );
        }

        $count = is_array($servicesRes['data']) ? count($servicesRes['data']) : 0;
        return array(
            'success' => true,
            'message' => 'Connection successful! Retrieved ' . $count . ' active service(s) from ' . ($this->env === self::ENV_PRODUCTION ? 'Production' : 'Sandbox') . '.',
            'servicesCount' => $count,
            'environment' => $this->env
        );
    }

    /**
     * GET /api/v1/public/services
     */
    public function getServices() {
        return $this->request('GET', '/api/v1/public/services');
    }

    /**
     * GET /api/v1/public/employees
     * 
     * @param int $serviceId
     * @param string $startDate ISO 8601 (e.g. 2026-01-01T00:00:00+01:00)
     * @param string $endDate ISO 8601 (e.g. 2026-01-31T23:59:59+01:00)
     */
    public function getEmployees($serviceId, $startDate, $endDate) {
        $params = http_build_query(array(
            'serviceId' => $serviceId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ));
        return $this->request('GET', '/api/v1/public/employees?' . $params);
    }

    /**
     * Get all employees across services from Kinesis API
     */
    public function getAllEmployees() {
        // First try fetching directly if supported
        $res = $this->request('GET', '/api/v1/public/employees');
        if ($res['success'] && !empty($res['data']) && is_array($res['data'])) {
            return $res;
        }

        // If direct list requires parameters, iterate across all services
        $startDate = date('c', strtotime('today'));
        $endDate = date('c', strtotime('+60 days'));

        $servicesRes = $this->getServices();
        if (!$servicesRes['success'] || empty($servicesRes['data']) || !is_array($servicesRes['data'])) {
            return array('success' => false, 'error' => 'Unable to fetch services from Kinesis API', 'data' => array());
        }

        $allEmployees = array();
        $seenIds = array();

        foreach ($servicesRes['data'] as $srv) {
            $srvId = isset($srv['id']) ? $srv['id'] : (isset($srv['Id']) ? $srv['Id'] : null);
            if (!$srvId) continue;

            $empRes = $this->getEmployees($srvId, $startDate, $endDate);
            if ($empRes['success'] && !empty($empRes['data']) && is_array($empRes['data'])) {
                foreach ($empRes['data'] as $emp) {
                    $empId = isset($emp['id']) ? $emp['id'] : (isset($emp['Id']) ? $emp['Id'] : null);
                    if ($empId && !isset($seenIds[$empId])) {
                        $seenIds[$empId] = true;
                        $allEmployees[] = $emp;
                    }
                }
            }
        }

        return array(
            'success' => true,
            'data' => $allEmployees
        );
    }

    /**
     * GET /api/v1/public/appointments/availability
     * 
     * @param int $serviceId
     * @param string $startDate ISO 8601
     * @param string $endDate ISO 8601
     * @param int|null $employeeId
     */
    public function getAvailability($serviceId, $startDate, $endDate, $employeeId = null) {
        $query = array(
            'serviceId' => $serviceId,
            'startDate' => $startDate,
            'endDate' => $endDate
        );
        if ($employeeId !== null && $employeeId !== '') {
            $query['employeeId'] = $employeeId;
        }
        return $this->request('GET', '/api/v1/public/appointments/availability?' . http_build_query($query));
    }

    /**
     * POST /api/v1/public/appointments (Create appointment with new customer)
     * 
     * @param string $dateTime ISO 8601 (e.g. 2026-01-01T09:00:00+01:00)
     * @param int $serviceId
     * @param int $employeeId
     * @param array $customer [firstName, lastName, dateOfBirth (YYYY-MM-DD), email, phoneNumber]
     */
    public function createAppointmentWithNewCustomer($dateTime, $serviceId, $employeeId, $customer) {
        $payload = array(
            'dateTime' => $dateTime,
            'serviceId' => (int)$serviceId,
            'employeeId' => (int)$employeeId,
            'customer' => array(
                'firstName' => $customer['firstName'],
                'lastName' => $customer['lastName'],
                'dateOfBirth' => isset($customer['dateOfBirth']) ? $customer['dateOfBirth'] : '1990-01-01',
                'email' => $customer['email'],
                'phoneNumber' => $customer['phoneNumber']
            )
        );
        return $this->request('POST', '/api/v1/public/appointments', $payload);
    }

    /**
     * POST /api/v1/public/customers/{customerId}/appointments (Create appointment for existing customer)
     */
    public function createAppointmentForExistingCustomer($customerId, $dateTime, $serviceId, $employeeId) {
        $payload = array(
            'dateTime' => $dateTime,
            'serviceId' => (int)$serviceId,
            'employeeId' => (int)$employeeId
        );
        return $this->request('POST', '/api/v1/public/customers/' . (int)$customerId . '/appointments', $payload);
    }

    /**
     * GET /api/v1/public/customers/{customerId}/appointments/{appointmentId}
     */
    public function getAppointmentDetail($customerId, $appointmentId) {
        return $this->request('GET', '/api/v1/public/customers/' . (int)$customerId . '/appointments/' . (int)$appointmentId);
    }

    /**
     * PATCH /api/v1/public/customers/{customerId}/appointments/{appointmentId}
     */
    public function updateAppointment($customerId, $appointmentId, $dateTime = null, $employeeId = null) {
        $payload = array();
        if ($dateTime !== null) {
            $payload['dateTime'] = $dateTime;
        }
        if ($employeeId !== null) {
            $payload['employeeId'] = (int)$employeeId;
        }
        return $this->request('PATCH', '/api/v1/public/customers/' . (int)$customerId . '/appointments/' . (int)$appointmentId, $payload);
    }

    /**
     * DELETE /api/v1/public/customers/{customerId}/appointments/{appointmentId}
     */
    public function cancelAppointment($customerId, $appointmentId) {
        return $this->request('DELETE', '/api/v1/public/customers/' . (int)$customerId . '/appointments/' . (int)$appointmentId);
    }

    /**
     * GET /api/v1/public/customers?email={email}
     */
    public function searchCustomerByEmail($email) {
        $query = http_build_query(array('email' => trim($email)));
        return $this->request('GET', '/api/v1/public/customers?' . $query);
    }

    /**
     * GET /api/v1/public/customers/{id}
     */
    public function getCustomer($id) {
        return $this->request('GET', '/api/v1/public/customers/' . (int)$id);
    }

    /**
     * PUT /api/v1/public/customers/{id}
     */
    public function updateCustomer($id, $customerData) {
        return $this->request('PUT', '/api/v1/public/customers/' . (int)$id, $customerData);
    }

    /**
     * POST /api/v1/public/customers — create customer when not yet on Kinesis
     *
     * @param array $customerData [firstName, lastName, dateOfBirth, email, phoneNumber]
     */
    public function createCustomer($customerData) {
        $payload = array(
            'firstName' => isset($customerData['firstName']) ? $customerData['firstName'] : '',
            'lastName' => isset($customerData['lastName']) ? $customerData['lastName'] : '',
            'dateOfBirth' => isset($customerData['dateOfBirth']) ? $customerData['dateOfBirth'] : '1990-01-01',
            'email' => isset($customerData['email']) ? $customerData['email'] : '',
            'phoneNumber' => isset($customerData['phoneNumber']) ? $customerData['phoneNumber'] : ''
        );
        return $this->request('POST', '/api/v1/public/customers', $payload);
    }

    /**
     * GET /api/v1/public/customers/{customerId}/appointments
     */
    public function getCustomerAppointments($customerId, $startDate, $endDate, $status = null) {
        $query = array(
            'startDate' => $startDate,
            'endDate' => $endDate
        );
        if ($status !== null) {
            $query['status'] = $status;
        }
        return $this->request('GET', '/api/v1/public/customers/' . (int)$customerId . '/appointments?' . http_build_query($query));
    }
}
