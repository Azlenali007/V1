<?php
/**
 * Main PHP Entry Point & Frontend Dispatcher
 * SMM Panel - Social Media Marketing Hub
 */

declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

// Route API requests directly
if (str_starts_with($uri, '/api/')) {
    $endpoint = substr($uri, 5);
    $target = __DIR__ . '/api/' . $endpoint . '.php';
    if (file_exists($target)) {
        require $target;
        exit;
    }
}

if (str_starts_with($uri, '/admin/api/')) {
    $endpoint = substr($uri, 11);
    $target = __DIR__ . '/admin/api/' . $endpoint . '.php';
    if (file_exists($target)) {
        require $target;
        exit;
    }
}

// Check session for protected routes or direct views
$user = getAuthenticatedUser();

// For direct browser access (/login, /admin, /dashboard, etc.)
// Serve frontend single-page application with full client routing support
$filePath = file_exists(__DIR__ . '/dist/index.html') ? (__DIR__ . '/dist/index.html') : (__DIR__ . '/index.html');

if (file_exists($filePath)) {
    // Send standard HTML header
    header('Content-Type: text/html; charset=utf-8');
    readfile($filePath);
    exit;
}

http_response_code(404);
echo "404 - Not Found";
