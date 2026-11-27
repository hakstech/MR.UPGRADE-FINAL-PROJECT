<?php
// ============================================================
// PESAPAL IPN (Instant Payment Notification) Handler
// URL: https://mrupgrade.com/payments/pesapal_ipn.php
// Pesapal calls this URL when a payment completes/fails
// ============================================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/pesapal.php';

// Log all IPN calls for debugging
$log_file = __DIR__ . '/../logs/pesapal_ipn.log';
if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0755, true);
file_put_contents($log_file, date('Y-m-d H:i:s') . ' IPN: ' . json_encode($_GET) . "\n", FILE_APPEND);

// Pesapal sends: orderTrackingId, orderMerchantReference, orderNotificationType
$tracking_id  = trim($_GET['orderTrackingId']         ?? '');
$order_number = trim($_GET['orderMerchantReference']  ?? '');

if (!$tracking_id || !$order_number) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

try {
    $pesapal  = new PesapalAPI();
    $status   = $pesapal->getTransactionStatus($tracking_id);

    file_put_contents($log_file, date('Y-m-d H:i:s') . ' Status: ' . json_encode($status) . "\n", FILE_APPEND);

    $payment_status_code = $status['payment_status_description'] ?? $status['status_code'] ?? '';
    $payment_method      = $status['payment_method'] ?? '';

    // Map Pesapal status to our status
    $our_payment_status = 'unpaid';
    $our_order_status   = 'pending';

    if ($payment_status_code === 'Completed' || $payment_status_code === 'COMPLETED') {
        $our_payment_status = 'paid';
        $our_order_status   = 'confirmed';
    } elseif ($payment_status_code === 'Failed' || $payment_status_code === 'FAILED') {
        $our_payment_status = 'unpaid';
        $our_order_status   = 'cancelled';
    }

    // Update order
    $upd = $conn->prepare("UPDATE orders SET payment_status=?, status=?, pesapal_tracking_id=?, payment_reference=? WHERE order_number=?");
    $upd->bind_param('sssss', $our_payment_status, $our_order_status, $tracking_id, $payment_method, $order_number);
    $upd->execute();

    // Update pesapal_transactions
    $tx = $conn->prepare("UPDATE pesapal_transactions SET status=?, payment_method=? WHERE pesapal_tracking_id=? OR order_number=?");
    $tx->bind_param('ssss', $payment_status_code, $payment_method, $tracking_id, $order_number);
    $tx->execute();

    // Also handle installment: if paid, mark deposit_paid
    if ($our_payment_status === 'paid') {
        $ord = $conn->prepare("SELECT id, payment_type, deposit_paid, total_amount FROM orders WHERE order_number=?");
        $ord->bind_param('s', $order_number);
        $ord->execute();
        $order = $ord->get_result()->fetch_assoc();

        if ($order && $order['payment_type'] === 'installment') {
            // Mark deposit as paid, keep order as 'active' installment
            $conn->prepare("UPDATE orders SET payment_status='deposit_paid' WHERE order_number=?")->execute() || null;
            $upd2 = $conn->prepare("UPDATE orders SET payment_status='deposit_paid' WHERE order_number=?");
            $upd2->bind_param('s', $order_number);
            $upd2->execute();
        }
    }

    // Respond to Pesapal (required)
    echo 'OK';

} catch (Exception $e) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' ERROR: ' . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo 'Error';
}
