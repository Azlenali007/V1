<?php
/**
 * Admin Users API (Manage users, balance adjustments, toggle status)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'adjust_balance') {
    $input = getJsonInput();
    $userId = (int)($input['user_id'] ?? 0);
    $amount = (float)($input['amount'] ?? 0);
    $operation = $input['operation'] ?? 'add'; // 'add' or 'subtract'
    $reason = trim($input['reason'] ?? 'Admin balance adjustment');

    if ($userId <= 0 || $amount <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid user ID or amount.'], 400);
    }

    $pdo->beginTransaction();
    try {
        $uStmt = $pdo->prepare('SELECT id, balance FROM users WHERE id = ? FOR UPDATE');
        $uStmt->execute([$userId]);
        $user = $uStmt->fetch();

        if (!$user) {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
        }

        $currentBal = (float)$user['balance'];
        $newBal = $operation === 'add' ? ($currentBal + $amount) : ($currentBal - $amount);
        if ($newBal < 0) $newBal = 0.00;

        $up = $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
        $up->execute([$newBal, $userId]);

        $txnCode = generateTxnCode($pdo);
        $txnType = $operation === 'add' ? 'deposit' : 'refund';
        $tStmt = $pdo->prepare('
            INSERT INTO transactions (txn_code, user_id, type, amount, method, status, description)
            VALUES (?, ?, ?, ?, "Manual Admin", "Completed", ?)
        ');
        $tStmt->execute([$txnCode, $userId, $txnType, $amount, $reason]);

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => 'User balance updated successfully.', 'new_balance' => $newBal]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to adjust balance: ' . $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status') {
    $input = getJsonInput();
    $userId = (int)($input['user_id'] ?? 0);
    $status = $input['status'] === 'disabled' ? 'disabled' : 'active';

    if ($userId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid user ID.'], 400);
    }

    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role != "admin"');
    $stmt->execute([$status, $userId]);

    jsonResponse(['success' => true, 'message' => "User account is now {$status}."]);
}

// User List with search
$search = trim($_GET['search'] ?? '');
$sql = '
    SELECT u.id, u.name, u.email, u.phone, u.balance, u.role, u.status, u.created_at,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders
    FROM users u
    WHERE u.role = "user"
';
$params = [];

if (!empty($search)) {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.id = ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = (int)$search;
}

$sql .= ' ORDER BY u.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

jsonResponse(['success' => true, 'users' => $users]);
