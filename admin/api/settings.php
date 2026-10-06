<?php
/**
 * Admin Settings & Profile API
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'get';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_settings') {
    $input = getJsonInput();

    $settings = [
        'site_name' => trim($input['site_name'] ?? 'SMM Panel'),
        'site_tagline' => trim($input['site_tagline'] ?? 'Grow Your Social Media'),
        'site_currency' => trim($input['site_currency'] ?? '₹'),
        'currency_code' => trim($input['currency_code'] ?? 'INR'),
        'admin_email' => trim($input['admin_email'] ?? ''),
        'support_email' => trim($input['support_email'] ?? ''),
        'announcement' => trim($input['announcement'] ?? '')
    ];

    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
    foreach ($settings as $key => $val) {
        $stmt->execute([$key, $val, $val]);
    }

    jsonResponse(['success' => true, 'message' => 'Settings saved successfully.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
    $input = getJsonInput();
    $current = $input['current_password'] ?? '';
    $new = $input['new_password'] ?? '';

    if (empty($current) || empty($new)) {
        jsonResponse(['success' => false, 'message' => 'Please provide current and new password.'], 400);
    }

    $uStmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $uStmt->execute([$admin['id']]);
    $hash = $uStmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        jsonResponse(['success' => false, 'message' => 'Incorrect current password.'], 400);
    }

    $newHash = password_hash($new, PASSWORD_BCRYPT);
    $up = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $up->execute([$newHash, $admin['id']]);

    jsonResponse(['success' => true, 'message' => 'Admin password updated successfully.']);
}

// Get all settings as key-value map
$rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
$settingsMap = [];
foreach ($rows as $row) {
    $settingsMap[$row['setting_key']] = $row['setting_value'];
}

jsonResponse([
    'success' => true,
    'settings' => $settingsMap,
    'admin' => [
        'id' => $admin['id'],
        'name' => $admin['name'],
        'email' => $admin['email']
    ]
]);
