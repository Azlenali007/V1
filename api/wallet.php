<?php
/**
 * Wallet API (Add Funds, Balance, Transactions History)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'transactions';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_funds') {
    $input = getJsonInput();
    $amount = (float)($input['amount'] ?? 0);
    $method = trim($input['method'] ?? 'Razorpay');

    // Check payment gateway settings
    $gwStmt = $pdo->prepare('SELECT * FROM payment_gateways WHERE slug = "razorpay" LIMIT 1');
    $gwStmt->execute();
    $gateway = $gwStmt->fetch();

    $minDeposit = $gateway ? (float)$gateway['min_deposit'] : 100.00;
    $maxDeposit = $gateway ? (float)$gateway['max_deposit'] : 50000.00;

    if ($amount < $minDeposit) {
        jsonResponse(['success' => false, 'message' => "Minimum deposit amount is ₹{$minDeposit}."], 400);
    }

    if ($amount > $maxDeposit) {
        jsonResponse(['success' => false, 'message' => "Maximum deposit amount is ₹{$maxDeposit}."], 400);
    }

    // Process deposit in atomic transaction
    $pdo->beginTransaction();
    try {
        $userStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
        $userStmt->execute([$user['id']]);
        $currentBalance = (float)$userStmt->fetchColumn();

        $newBalance = round($currentBalance + $amount, 2);
        $updateBal = $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
        $updateBal->execute([$newBalance, $user['id']]);

        $txnCode = generateTxnCode($pdo);
        $txnStmt = $pdo->prepare('
            INSERT INTO transactions (txn_code, user_id, type, amount, method, status, description)
            VALUES (?, ?, "deposit", ?, ?, "Completed", ?)
        ');
        $desc = "Add Funds via " . $method;
        $txnStmt->execute([$txnCode, $user['id'], $amount, $method, $desc]);

        $pdo->commit();

        jsonResponse([
            'success' => true,
            'message' => "₹{$amount} added to your wallet successfully!",
            'new_balance' => $newBalance,
            'transaction' => [
                'txn_code' => $txnCode,
                'amount' => $amount,
                'method' => $method,
                'status' => 'Completed',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Deposit failed: ' . $e->getMessage()], 500);
    }
}

// Fetch user transactions
$typeFilter = $_GET['type'] ?? 'All';
$sql = 'SELECT id, txn_code, type, amount, method, status, description, created_at FROM transactions WHERE user_id = ?';
$params = [$user['id']];

if ($typeFilter === 'Add Funds') {
    $sql .= ' AND type = "deposit"';
} elseif ($typeFilter === 'Orders') {
    $sql .= ' AND type = "order"';
} elseif ($typeFilter === 'Refunds') {
    $sql .= ' AND type = "refund"';
}

$sql .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get fresh balance
$balStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
$balStmt->execute([$user['id']]);
$freshBalance = (float)$balStmt->fetchColumn();

jsonResponse([
    'success' => true,
    'balance' => $freshBalance,
    'transactions' => $transactions
]);
