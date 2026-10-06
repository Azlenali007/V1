<?php
/**
 * Services API (Categories and Services list)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'all';
$categorySlug = $_GET['category'] ?? null;
$search = trim($_GET['search'] ?? '');

if ($action === 'categories') {
    $stmt = $pdo->query('SELECT id, name, slug, icon, sort_order FROM categories WHERE status = "active" ORDER BY sort_order ASC');
    jsonResponse(['success' => true, 'categories' => $stmt->fetchAll()]);
}

// Fetch categories with nested or related services
$catQuery = 'SELECT id, name, slug, icon, sort_order FROM categories WHERE status = "active" ORDER BY sort_order ASC';
$categories = $pdo->query($catQuery)->fetchAll();

$serviceSql = '
    SELECT s.id, s.category_id, s.name, s.price_per_k, s.min_quantity, s.max_quantity, s.badge, s.speed, s.description, c.name as category_name, c.slug as category_slug, c.icon as category_icon
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.status = "active" AND c.status = "active"
';
$params = [];

if (!empty($categorySlug)) {
    $serviceSql .= ' AND c.slug = ?';
    $params[] = $categorySlug;
}

if (!empty($search)) {
    $serviceSql .= ' AND (s.name LIKE ? OR c.name LIKE ? OR s.badge LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$serviceSql .= ' ORDER BY c.sort_order ASC, s.id ASC';
$stmt = $pdo->prepare($serviceSql);
$stmt->execute($params);
$services = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'categories' => $categories,
    'services' => $services
]);
