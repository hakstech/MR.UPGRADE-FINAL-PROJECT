<?php
// Raw cURL test — no helper functions
$url     = 'https://api.sandbox.africastalking.com/version1/messaging';
$api_key = 'atsk_d3e1589e89a668ad40b8ec5775ab5dde0d32350c52b4935a9b069942de2326a70badd455';
$to      = '+256743741601';

$post = http_build_query(array(
    'username' => 'sandbox',
    'to'       => $to,
    'message'  => 'Test from Mr Upgrade ' . date('H:i:s'),
));

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'apiKey: ' . $api_key,
    'Content-Type: application/x-www-form-urlencoded',
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

rewind($verbose);
$verbose_log = stream_get_contents($verbose);

echo "<h3>HTTP Code: $http_code</h3>";
echo "<h3>cURL Error: " . ($curl_err ?: 'none') . "</h3>";
echo "<h3>Response: </h3><pre>" . htmlspecialchars($response ?: '(empty)') . "</pre>";
echo "<h3>cURL Verbose Log:</h3><pre>" . htmlspecialchars($verbose_log) . "</pre>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>cURL version: " . curl_version()['version'] . "</p>";
echo "<p>OpenSSL: " . curl_version()['ssl_version'] . "</p>";
