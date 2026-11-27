<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/africastalking.php';

$result = sendSMS('0743741601', 'Test SMS from Mr. Upgrade - ' . date('H:i:s'));

echo "<h3>Result:</h3><pre>";
print_r($result);
echo "</pre>";
echo "<p>Normalized number: " . normalizePhone('0743741601') . "</p>";
