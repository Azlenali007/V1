<?php
/**
 * Profile API (View profile, update info, change password)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'view';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $input = getJsonInput();
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Full name cannot be empty.'], 400);
    }

    $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
    $stmt->execute([$name, $phone, $user['id']]);

    jsonResponse([
        'success' => true,
        'message' => 'Profile updated successfully!',
        'user' => [
            'id' => $user['id'],
            'name' => $name,
            'email' => $user['email'],
            'phone' => $phone
        ]
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'password') {
    $input = getJsonInput();
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        jsonResponse(['success' => false, 'message' => 'Please fill in all password fields.'], 400);
    }

    if ($newPassword !== $confirmPassword) {
        jsonResponse(['success' => false, 'message' => 'New password and confirmation do not match.'], 400);
    }

    if (strlen($newPassword) < 6) {
        jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters.'], 400);
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($currentPassword, $hash)) {
        jsonResponse(['success' => false, 'message' => 'Incorrect current password.'], 400);
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $upStmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $upStmt->execute([$newHash, $user['id']]);

    jsonResponse(['success' => true, 'message' => 'Password updated successfully!']);
}

// Default view
$stmt = $pdo->prepare('SELECT id, name, email, phone, balance, role, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$fresh = $stmt->fetch();

jsonResponse([
    'success' => true,
    'user' => $fresh
]);
