<?php
/**
 * Admin Payments API (Gateway settings & transactions list)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

// Update gateway settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_settings') {
    $input = getJsonInput();
    $keyId = trim($input['key_id'] ?? '');
    $keySecret = trim($input['key_secret'] ?? '');
    $minDeposit = (float)($input['min_deposit'] ?? 100.00);
    $maxDeposit = (float)($input['max_deposit'] ?? 50000.00);
    $isActive = !empty($input['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare('
        UPDATE payment_gateways
        SET key_id = ?, key_secret = ?, min_deposit = ?, max_deposit = ?, is_active = ?
        WHERE slug = "razorpay"
    ');
    $stmt->execute([$keyId, $keySecret, $minDeposit, $maxDeposit, $isActive]);

    jsonResponse(['success' => true, 'message' => 'Payment gateway settings saved successfully.']);
}

// Fetch gateway settings & all transactions
$gateway = $pdo->query('SELECT * FROM payment_gateways WHERE slug = "razorpay" LIMIT 1')->fetch();

$txns = $pdo->query('
    SELECT t.id, t.txn_code, t.type, t.amount, t.method, t.status, t.description, t.created_at,
           u.name as user_name, u.email as user_email
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
')->fetchAll();

jsonResponse([
    'success' => true,
    'gateway' => $gateway,
    'transactions' => $txns
]);
