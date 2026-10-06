<?php
/**
 * Application Constants and Helper Functions
 * SMM Panel
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

require_once __DIR__ . '/db.php';

define('APP_NAME', 'SMM Panel');
define('APP_CURRENCY', '₹');
define('APP_URL', getenv('APP_URL') ?: '');

function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function getJsonInput(): array {
    $input = file_get_contents('php://input');
    if (!$input) {
        return $_POST;
    }
    $decoded = json_decode($input, true);
    return is_array($decoded) ? array_merge($_POST, $decoded) : $_POST;
}

function getAuthenticatedUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, phone, balance, role, status, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user && $user['status'] === 'active') {
            return $user;
        }
    } catch (Exception $e) {
        return null;
    }

    return null;
}

function requireAuth(): array {
    $user = getAuthenticatedUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
    }
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Forbidden. Admin privileges required.'], 403);
    }
    return $user;
}

function generateOrderCode(PDO $pdo): string {
    do {
        $code = '#' . rand(10000, 99999);
        $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

function generateTxnCode(PDO $pdo): string {
    do {
        $code = 'TXN-' . rand(1000, 9999);
        $stmt = $pdo->prepare('SELECT id FROM transactions WHERE txn_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

function generateTicketCode(PDO $pdo): string {
    do {
        $code = '#T' . rand(1000, 9999);
        $stmt = $pdo->prepare('SELECT id FROM tickets WHERE ticket_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}
