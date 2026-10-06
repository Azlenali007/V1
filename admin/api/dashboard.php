<?php
/**
 * Admin Dashboard API (Overview metrics)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$pdo = Database::getConnection();

// Total Users
$usersCount = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();

// Total Orders, Pending Orders, Completed Orders
$ordersData = $pdo->query('
    SELECT
        COUNT(*) as total_orders,
        SUM(CASE WHEN status IN ("Pending", "Processing") THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_orders
    FROM orders
')->fetch();

// Total Revenue from completed orders or deposits
$revenue = (float)$pdo->query('SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = "deposit" AND status = "Completed"')->fetchColumn();

// Recent orders
$recentOrders = $pdo->query('
    SELECT o.id, o.order_code, o.link, o.quantity, o.price, o.status, o.created_at,
           u.name as user_name, u.email as user_email, s.name as service_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN services s ON o.service_id = s.id
    ORDER BY o.id DESC
    LIMIT 10
')->fetchAll();

jsonResponse([
    'success' => true,
    'stats' => [
        'total_users' => $usersCount,
        'total_orders' => (int)($ordersData['total_orders'] ?? 0),
        'pending_orders' => (int)($ordersData['pending_orders'] ?? 0),
        'completed_orders' => (int)($ordersData['completed_orders'] ?? 0),
        'revenue' => $revenue
    ],
    'recent_orders' => $recentOrders
]);
