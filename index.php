<?php
/**
 * Main PHP Entry Point & Frontend Dispatcher
 * SMM Panel - Social Media Marketing Hub
 */

declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

// If request is for an API endpoint without .php extension
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

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

// Serve the main application UI
if (file_exists(__DIR__ . '/dist/index.html')) {
    readfile(__DIR__ . '/dist/index.html');
} else {
    readfile(__DIR__ . '/index.html');
}
