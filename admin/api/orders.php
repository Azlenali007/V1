<?php
/**
 * Admin Orders API (View all orders, filter status, update order status)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    $input = getJsonInput();
    $orderId = (int)($input['order_id'] ?? 0);
    $status = trim($input['status'] ?? '');
    $refund = !empty($input['refund_if_cancelled']);

    $allowed = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    if ($orderId <= 0 || !in_array($status, $allowed)) {
        jsonResponse(['success' => false, 'message' => 'Invalid order ID or status value.'], 400);
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, user_id, price, status FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $prevStatus = $order['status'];
        $up = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $up->execute([$status, $orderId]);

        // If newly cancelled and refund requested
        if ($status === 'Cancelled' && $prevStatus !== 'Cancelled' && $refund) {
            $uUp = $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
            $uUp->execute([$order['price'], $order['user_id']]);

            $txnCode = generateTxnCode($pdo);
            $tStmt = $pdo->prepare('
                INSERT INTO transactions (txn_code, user_id, type, amount, method, status, description)
                VALUES (?, ?, "refund", ?, "System Refund", "Completed", ?)
            ');
            $tStmt->execute([$txnCode, $order['user_id'], $order['price'], "Refund for Cancelled Order #{$orderId}"]);
        }

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => "Order #{$orderId} updated to {$status}."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
    }
}

// List all orders with filters
$statusFilter = $_GET['status'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$sql = '
    SELECT o.id, o.order_code, o.service_id, o.user_id, o.link, o.quantity, o.price, o.status, o.created_at,
           u.name as user_name, u.email as user_email,
           s.name as service_name, c.name as category_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN services s ON o.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE 1=1
';
$params = [];

if (!empty($statusFilter) && $statusFilter !== 'All') {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= ' AND (o.order_code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.link LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= ' ORDER BY o.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

jsonResponse(['success' => true, 'orders' => $orders]);
