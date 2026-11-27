<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/pesapal.php';

echo "<h2>Pesapal Integration Test</h2>";

try {
    $pesapal = new PesapalAPI();

    // Step 1: Test auth token
    echo "<h3>Step 1: Get Auth Token</h3>";
    $token = $pesapal->getToken();
    echo "<p style='color:green'>✓ Token obtained: " . substr($token, 0, 30) . "...</p>";

    // Step 2: Test IPN registration
    echo "<h3>Step 2: Register / Get IPN ID</h3>";
    $ipn_id = $pesapal->getIPNID();
    echo "<p style='color:" . ($ipn_id ? 'green' : 'red') . "'>IPN ID: " . ($ipn_id ?? 'FAILED - no IPN ID returned') . "</p>";

    // Step 3: Submit a test order
    echo "<h3>Step 3: Submit Test Order</h3>";
    $result = $pesapal->submitOrder(array(
        'order_number' => 'TEST-' . time(),
        'amount'       => 100,
        'description'  => 'Mr. Upgrade Test Payment',
        'email'        => 'test@mrupgrade.ug',
        'phone'        => '+256743741601',
        'first_name'   => 'Test',
        'last_name'    => 'Customer',
        'address'      => 'Pioneer Mall Kampala',
        'city'         => 'Kampala',
    ));

    echo "<pre>";
    print_r($result);
    echo "</pre>";

    if (isset($result['redirect_url'])) {
        echo "<p style='color:green'>✓ Redirect URL obtained!</p>";
        echo "<p><strong>Redirect URL:</strong> <a href='" . htmlspecialchars($result['redirect_url']) . "' target='_blank'>" . htmlspecialchars($result['redirect_url']) . "</a></p>";
        echo "<p style='color:blue'>Click the link above to test the payment page</p>";
    } elseif (isset($result['error'])) {
        echo "<p style='color:red'>✗ Error: " . htmlspecialchars($result['error']['message'] ?? json_encode($result['error'])) . "</p>";
    } else {
        echo "<p style='color:orange'>⚠ No redirect_url in response. Full response above.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
