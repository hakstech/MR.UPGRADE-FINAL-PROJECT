<?php
// ============================================================
// PESAPAL CALLBACK — Redirect page after customer pays
// Pesapal redirects customer here after payment attempt
// ============================================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/pesapal.php';

$tracking_id  = trim($_GET['OrderTrackingId']        ?? $_GET['orderTrackingId'] ?? '');
$order_number = trim($_GET['OrderMerchantReference'] ?? $_GET['orderMerchantReference'] ?? '');

if (!$tracking_id || !$order_number) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

try {
    $pesapal = new PesapalAPI();
    $status  = $pesapal->getTransactionStatus($tracking_id);

    $payment_status_code = $status['payment_status_description'] ?? $status['status_code'] ?? 'PENDING';
    $payment_method      = $status['payment_method'] ?? '';

    if ($payment_status_code === 'Completed' || $payment_status_code === 'COMPLETED') {
        $our_payment_status = 'paid';
        $our_order_status   = 'confirmed';
    } elseif ($payment_status_code === 'Failed' || $payment_status_code === 'FAILED') {
        $our_payment_status = 'unpaid';
        $our_order_status   = 'cancelled';
    } else {
        $our_payment_status = 'unpaid';
        $our_order_status   = 'pending';
    }

    // Update order status
    $upd = $conn->prepare("UPDATE orders SET payment_status=?, status=?, pesapal_tracking_id=? WHERE order_number=?");
    $upd->bind_param('ssss', $our_payment_status, $our_order_status, $tracking_id, $order_number);
    $upd->execute();

    // Update installment if deposit payment
    $ord_stmt = $conn->prepare("SELECT id, payment_type FROM orders WHERE order_number=?");
    $ord_stmt->bind_param('s', $order_number);
    $ord_stmt->execute();
    $order = $ord_stmt->get_result()->fetch_assoc();

    if ($order && $order['payment_type'] === 'installment' && $our_payment_status === 'paid') {
        $upd2 = $conn->prepare("UPDATE orders SET payment_status='deposit_paid' WHERE order_number=?");
        $upd2->bind_param('s', $order_number);
        $upd2->execute();
    }

} catch (Exception $e) {
    // If status check fails, still redirect to confirmation
}

// Redirect to order confirmation page
header('Location: ' . SITE_URL . '/order-confirm?order=' . urlencode($order_number));
exit;
