<?php

define('AT_USERNAME', 'sandbox'); 

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'mrupgrade.mmuhub.com') {
    // ---- LIVE ----
    define('AT_API_KEY',  'YOUR_LIVE_AT_API_KEY');
    define('AT_SMS_URL',  'https://api.africastalking.com/version1/messaging');
    // define('AT_SHORTCODE', '10954'); 
} else {
    // ---- SANDBOX (local) ----
    define('AT_API_KEY',  'atsk_6f6303d96580a93af242ddc16502b68257e699dac36aaa69a936b7909f843547e24f195f');
    define('AT_SMS_URL',  'https://api.sandbox.africastalking.com/version1/messaging');
}

function normalizePhone($phone) {
    $digits = preg_replace('/[^0-9]/', '', $phone);

    if (strlen($digits) === 10 && substr($digits, 0, 1) === '0') {
        return '+256' . substr($digits, 1);
    }
    if (strlen($digits) === 12 && substr($digits, 0, 3) === '256') {
        return '+' . $digits;
    }
    if (strlen($digits) === 12) {
        return '+' . $digits;
    }
    if (substr($phone, 0, 1) !== '+') {
        return '+' . $digits;
    }
    return '+' . $digits;
}

function sendSMS($to, $message) {
    $to = normalizePhone($to);

    $post_data = http_build_query(array(
        'username' => AT_USERNAME,
        'to'       => $to,
        'message'  => $message,
    ));

    $ch = curl_init(AT_SMS_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'apiKey: ' . AT_API_KEY,
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($post_data),
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response  = curl_exec($ch);
    $curl_err  = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_err) {
        return array('success' => false, 'message' => 'Connection error: ' . $curl_err, 'data' => array());
    }

    if (empty($response)) {
        return array('success' => false, 'message' => 'Empty response from API (HTTP ' . $http_code . '). Check your API key.', 'data' => array());
    }

    $data = json_decode($response, true);

    if ($http_code === 201 || $http_code === 200) {
        if (isset($data['SMSMessageData']['Recipients'][0])) {
            $recipient = $data['SMSMessageData']['Recipients'][0];
            $status    = $recipient['status'] ?? '';
            if ($status === 'Success') {
                return array('success' => true, 'message' => 'SMS sent successfully to ' . $to, 'data' => $data);
            } else {
                return array('success' => false, 'message' => 'AT API error: ' . $status . ' — ' . ($recipient['number'] ?? ''), 'data' => $data);
            }
        }
        return array('success' => true, 'message' => 'SMS sent', 'data' => $data);
    }

    if ($http_code === 403) {
        return array(
            'success' => false,
            'message' => 'Access denied (403). Make sure: (1) the recipient number is added in AT Sandbox Simulator, (2) the API key is correct.',
            'data'    => $data ?? array()
        );
    }

    return array('success' => false, 'message' => 'API error (HTTP ' . $http_code . '): ' . $response, 'data' => $data ?? array());
}
