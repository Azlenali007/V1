<?php
/**
 * Admin Services API (Categories & Services CRUD)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_category') {
    $input = getJsonInput();
    $name = trim($input['name'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $icon = trim($input['icon'] ?? 'instagram');

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Category name is required.'], 400);
    }

    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, icon, sort_order, status) VALUES (?, ?, ?, 99, "active")');
    $stmt->execute([$name, $slug, $icon]);

    jsonResponse(['success' => true, 'message' => 'Category added successfully.']);
}

// Add Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_service') {
    $input = getJsonInput();
    $categoryId = (int)($input['category_id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $price = (float)($input['price_per_k'] ?? 0);
    $minQty = (int)($input['min_quantity'] ?? 100);
    $maxQty = (int)($input['max_quantity'] ?? 1000000);
    $badge = trim($input['badge'] ?? 'High Quality');
    $speed = trim($input['speed'] ?? 'Fast Delivery');
    $desc = trim($input['description'] ?? '');

    if ($categoryId <= 0 || empty($name) || $price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Please provide valid category, name, and price.'], 400);
    }

    $stmt = $pdo->prepare('
        INSERT INTO services (category_id, name, price_per_k, min_quantity, max_quantity, badge, speed, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active")
    ');
    $stmt->execute([$categoryId, $name, $price, $minQty, $maxQty, $badge, $speed, $desc]);

    jsonResponse(['success' => true, 'message' => 'Service added successfully.']);
}

// Edit Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_service') {
    $input = getJsonInput();
    $id = (int)($input['id'] ?? 0);
    $categoryId = (int)($input['category_id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $price = (float)($input['price_per_k'] ?? 0);
    $minQty = (int)($input['min_quantity'] ?? 100);
    $maxQty = (int)($input['max_quantity'] ?? 1000000);
    $badge = trim($input['badge'] ?? 'High Quality');
    $speed = trim($input['speed'] ?? 'Fast Delivery');
    $desc = trim($input['description'] ?? '');

    if ($id <= 0 || $categoryId <= 0 || empty($name) || $price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid parameters for editing service.'], 400);
    }

    $stmt = $pdo->prepare('
        UPDATE services
        SET category_id = ?, name = ?, price_per_k = ?, min_quantity = ?, max_quantity = ?, badge = ?, speed = ?, description = ?
        WHERE id = ?
    ');
    $stmt->execute([$categoryId, $name, $price, $minQty, $maxQty, $badge, $speed, $desc, $id]);

    jsonResponse(['success' => true, 'message' => 'Service updated successfully.']);
}

// Toggle Service Active/Disabled
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status') {
    $input = getJsonInput();
    $id = (int)($input['id'] ?? 0);
    $status = $input['status'] === 'disabled' ? 'disabled' : 'active';

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid service ID.'], 400);
    }

    $stmt = $pdo->prepare('UPDATE services SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);

    jsonResponse(['success' => true, 'message' => "Service status set to {$status}."]);
}

// Delete Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_service') {
    $input = getJsonInput();
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid service ID.'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);

    jsonResponse(['success' => true, 'message' => 'Service deleted successfully.']);
}

// Default list all services with category info
$categories = $pdo->query('SELECT id, name, slug, icon FROM categories ORDER BY sort_order ASC')->fetchAll();

$services = $pdo->query('
    SELECT s.*, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    ORDER BY c.sort_order ASC, s.id ASC
')->fetchAll();

jsonResponse([
    'success' => true,
    'categories' => $categories,
    'services' => $services
]);
