<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();
header('Content-Type: application/json');

$action     = $_POST['action']     ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];

function getCartTotals($conn, $user_id) {
    $res = $conn->prepare("SELECT SUM(c.quantity * p.price) as total, SUM(c.quantity) as count FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
    $res->bind_param('i', $user_id);
    $res->execute();
    $row = $res->get_result()->fetch_assoc();
    return [
        'total'    => 'UGX ' . number_format((float)($row['total'] ?? 0)),
        'subtotal' => 'UGX ' . number_format((float)($row['total'] ?? 0)),
        'count'    => (int)($row['count'] ?? 0),
    ];
}

if ($action === 'update' && $product_id) {
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?");
    $stmt->bind_param('iii', $qty, $user_id, $product_id);
    $stmt->execute();
    // Get line total
    $lt = $conn->prepare("SELECT p.price FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=? AND c.product_id=?");
    $lt->bind_param('ii', $user_id, $product_id);
    $lt->execute();
    $price = (float)($lt->get_result()->fetch_assoc()['price'] ?? 0);
    $totals = getCartTotals($conn, $user_id);
    $totals['line_total'] = 'UGX ' . number_format($price * $qty);
    echo json_encode(array_merge(['ok' => true], $totals));

} elseif ($action === 'remove' && $product_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    echo json_encode(array_merge(['ok' => true], getCartTotals($conn, $user_id)));

} else {
    echo json_encode(['ok' => false]);
}
