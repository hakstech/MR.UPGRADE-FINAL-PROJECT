<?php


define('PESAPAL_ENV',    'sandbox'); 

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'mrupgrade.mmuhub.com') {
    // ---- LIVE SERVER — still using SANDBOX until live Pesapal keys are obtained ----
    define('PESAPAL_KEY',      'TDpigBOOhs+zAl8cwH2Fl82jJGyD8xev');
    define('PESAPAL_SECRET',   '1KpqkfsMaihIcOlhnBo/gBZ5smw=');
    define('PESAPAL_BASE_URL', 'https://cybqa.pesapal.com/pesapalv3');
   
} else {
    // ---- LOCAL (sandbox) ----
    define('PESAPAL_KEY',      'TDpigBOOhs+zAl8cwH2Fl82jJGyD8xev');
    define('PESAPAL_SECRET',   '1KpqkfsMaihIcOlhnBo/gBZ5smw=');
    define('PESAPAL_BASE_URL', 'https://cybqa.pesapal.com/pesapalv3');
}

// ---- IPN & Callback URLs (auto-detect environment) ----
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'mrupgrade.mmuhub.com') {
    define('PESAPAL_IPN_URL',      'https://mrupgrade.mmuhub.com/payments/pesapal_ipn.php');
    define('PESAPAL_CALLBACK_URL', 'https://mrupgrade.mmuhub.com/payments/pesapal_callback.php');
} else {
    define('PESAPAL_IPN_URL',      'https://mrupgrade.mmuhub.com/payments/pesapal_ipn.php'); // IPN must be live URL even locally
    define('PESAPAL_CALLBACK_URL', 'http://localhost/mrupgrade/payments/pesapal_callback.php');
}
define('PESAPAL_CURRENCY',      'UGX');
define('PESAPAL_COUNTRY',       'UG');

class PesapalAPI {

    private $token = null;

    // --- Step 1: Get OAuth Bearer Token ---
    public function getToken() {
        if ($this->token) return $this->token;

        $url  = PESAPAL_BASE_URL . '/api/Auth/RequestToken';
        $body = json_encode(array(
            'consumer_key'    => PESAPAL_KEY,
            'consumer_secret' => PESAPAL_SECRET,
        ));

        $response = $this->post($url, $body);
        if (!$response || !isset($response['token'])) {
            throw new Exception('Pesapal: Failed to get auth token. Response: ' . json_encode($response));
        }
        $this->token = $response['token'];
        return $this->token;
    }

    // --- Step 2: Register IPN URL ---
    public function registerIPN() {
        $token = $this->getToken();
        $url   = PESAPAL_BASE_URL . '/api/URLSetup/RegisterIPN';
        $body  = json_encode(array(
            'url'          => PESAPAL_IPN_URL,
            'ipn_notification_type' => 'GET',
        ));
        $response = $this->post($url, $body, $token);
        return $response;
    }

    // --- Step 3: Get IPN ID (needed when submitting order) ---
    public function getIPNID() {
        $token    = $this->getToken();
        $url      = PESAPAL_BASE_URL . '/api/URLSetup/GetIpnList';
        $response = $this->get($url, $token);

        if (isset($response[0]['ipn_id'])) {
            return $response[0]['ipn_id'];
        }
        // If no IPN registered yet, register it and get ID
        $reg = $this->registerIPN();
        return $reg['ipn_id'] ?? null;
    }

    // Strip special chars from address for Pesapal (only allows alphanumeric, dashes, underscores, dots, colons, @)
    public function cleanAddress($addr) {
        return preg_replace('/[^a-zA-Z0-9\-_\.:\@ ]/', ' ', $addr);
    }

    // --- Step 4: Submit Order to Pesapal ---
    public function submitOrder($order_data) {
        $token  = $this->getToken();
        $ipn_id = $this->getIPNID();
        $url    = PESAPAL_BASE_URL . '/api/Transactions/SubmitOrderRequest';

        $body = json_encode(array(
            'id'                        => $order_data['order_number'],
            'currency'                  => PESAPAL_CURRENCY,
            'amount'                    => (float)$order_data['amount'],
            'description'               => $order_data['description'],
            'callback_url'              => PESAPAL_CALLBACK_URL,
            'notification_id'           => $ipn_id,
            'billing_address'           => array(
                'email_address'   => $order_data['email'],
                'phone_number'    => $order_data['phone'],
                'country_code'    => PESAPAL_COUNTRY,
                'first_name'      => $order_data['first_name'],
                'last_name'       => $order_data['last_name'],
                'line_1'          => $this->cleanAddress($order_data['address'] ?? ''),
                'city'            => $this->cleanAddress($order_data['city']    ?? 'Kampala'),
                'state'           => '',
                'postal_code'     => '',
                'zip_code'        => '',
            ),
        ));

        $response = $this->post($url, $body, $token);
        return $response;
    }

    // --- Step 5: Get Transaction Status ---
    public function getTransactionStatus($order_tracking_id) {
        $token    = $this->getToken();
        $url      = PESAPAL_BASE_URL . '/api/Transactions/GetTransactionStatus?orderTrackingId=' . urlencode($order_tracking_id);
        $response = $this->get($url, $token);
        return $response;
    }

    // --- HTTP POST helper ---
    private function post($url, $body, $token = null) {
        $headers = array('Content-Type: application/json', 'Accept: application/json');
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) throw new Exception('cURL error: ' . $err);
        return json_decode($result, true);
    }

    // --- HTTP GET helper ---
    private function get($url, $token = null) {
        $headers = array('Accept: application/json');
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) throw new Exception('cURL error: ' . $err);
        return json_decode($result, true);
    }
}
