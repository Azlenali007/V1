<?php
/**
 * Orders API (Place Order, List Orders, User Stats)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $input = getJsonInput();
    $serviceId = (int)($input['service_id'] ?? 0);
    $link = trim($input['link'] ?? '');
    $quantity = (int)($input['quantity'] ?? 0);

    if ($serviceId <= 0 || empty($link) || $quantity <= 0) {
        jsonResponse(['success' => false, 'message' => 'Please fill in all order fields correctly.'], 400);
    }

    // Verify service existence & rules
    $stmt = $pdo->prepare('SELECT id, name, price_per_k, min_quantity, max_quantity FROM services WHERE id = ? AND status = "active" LIMIT 1');
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        jsonResponse(['success' => false, 'message' => 'Selected service is currently unavailable.'], 404);
    }

    if ($quantity < (int)$service['min_quantity']) {
        jsonResponse(['success' => false, 'message' => "Minimum quantity required is {$service['min_quantity']}."], 400);
    }

    if ($quantity > (int)$service['max_quantity']) {
        jsonResponse(['success' => false, 'message' => "Maximum quantity allowed is {$service['max_quantity']}."], 400);
    }

    $totalPrice = round(((float)$service['price_per_k'] / 1000) * $quantity, 2);

    // Run order placement in atomic transaction
    $pdo->beginTransaction();
    try {
        // Lock user record for update
        $userStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
        $userStmt->execute([$user['id']]);
        $currentBalance = (float)$userStmt->fetchColumn();

        if ($currentBalance < $totalPrice) {
            $pdo->rollBack();
            jsonResponse([
                'success' => false,
                'message' => 'Insufficient wallet balance. Please add funds to place this order.',
                'required' => $totalPrice,
                'balance' => $currentBalance
            ], 400);
        }

        $newBalance = round($currentBalance - $totalPrice, 2);
        $updateBal = $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
        $updateBal->execute([$newBalance, $user['id']]);

        $orderCode = generateOrderCode($pdo);
        $orderStmt = $pdo->prepare('
            INSERT INTO orders (order_code, user_id, service_id, link, quantity, price, status)
            VALUES (?, ?, ?, ?, ?, ?, "Processing")
        ');
        $orderStmt->execute([$orderCode, $user['id'], $serviceId, $link, $quantity, $totalPrice]);
        $newOrderId = (int)$pdo->lastInsertId();

        // Record transaction
        $txnCode = generateTxnCode($pdo);
        $txnStmt = $pdo->prepare('
            INSERT INTO transactions (txn_code, user_id, type, amount, method, status, description)
            VALUES (?, ?, "order", ?, "Razorpay", "Completed", ?)
        ');
        $txnDesc = "Order Payment - " . $service['name'];
        $txnStmt->execute([$txnCode, $user['id'], $totalPrice, $txnDesc]);

        $pdo->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order' => [
                'id' => $newOrderId,
                'order_code' => $orderCode,
                'service_name' => $service['name'],
                'quantity' => $quantity,
                'price' => $totalPrice,
                'link' => $link,
                'status' => 'Processing',
                'created_at' => date('Y-m-d H:i:s')
            ],
            'new_balance' => $newBalance
        ], 201);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to process order: ' . $e->getMessage()], 500);
    }
}

if ($action === 'stats') {
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) as total_orders,
            SUM(CASE WHEN status IN ("Pending", "Processing") THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_orders
        FROM orders
        WHERE user_id = ?
    ');
    $stmt->execute([$user['id']]);
    $stats = $stmt->fetch();

    $balStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
    $balStmt->execute([$user['id']]);
    $currentBal = (float)$balStmt->fetchColumn();

    jsonResponse([
        'success' => true,
        'balance' => $currentBal,
        'total_orders' => (int)($stats['total_orders'] ?? 0),
        'pending_orders' => (int)($stats['pending_orders'] ?? 0),
        'completed_orders' => (int)($stats['completed_orders'] ?? 0)
    ]);
}

// List user orders
$statusFilter = $_GET['status'] ?? 'All';
$sql = '
    SELECT o.id, o.order_code, o.service_id, o.link, o.quantity, o.price, o.status, o.created_at,
           s.name as service_name, c.name as category_name, c.icon as category_icon
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE o.user_id = ?
';
$params = [$user['id']];

if (!empty($statusFilter) && $statusFilter !== 'All') {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY o.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'orders' => $orders
]);
