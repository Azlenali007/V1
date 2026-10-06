<?php
/**
 * Direct Login Page Route
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

// Redirect to /login path for clean URL or serve application
header('Location: /login');
exit;
