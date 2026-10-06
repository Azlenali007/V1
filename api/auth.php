<?php
/**
 * Authentication API (Register, Login, Logout, Session Check)
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$input = getJsonInput();

$pdo = Database::getConnection();

switch ($action) {
    case 'session':
        $user = getAuthenticatedUser();
        if ($user) {
            jsonResponse([
                'success' => true,
                'user' => [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'balance' => (float)$user['balance'],
                    'role' => $user['role'],
                    'created_at' => $user['created_at']
                ]
            ]);
        } else {
            jsonResponse(['success' => false, 'user' => null]);
        }
        break;

    case 'login':
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Please provide both email and password.'], 400);
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user['status'] !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Account is disabled. Please contact support.'], 403);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        jsonResponse([
            'success' => true,
            'message' => 'Logged in successfully.',
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
                'balance' => (float)$user['balance'],
                'role' => $user['role'],
                'created_at' => $user['created_at']
            ]
        ]);
        break;

    case 'register':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $phone = trim($input['phone'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Name, email, and password are required.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
        }

        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);
        }

        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email is already registered.'], 409);
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $insert = $pdo->prepare('INSERT INTO users (name, email, phone, password, balance, role, status) VALUES (?, ?, ?, ?, 0.00, "user", "active")');
        $insert->execute([$name, $email, $phone, $hashed]);
        $newId = (int)$pdo->lastInsertId();

        $_SESSION['user_id'] = $newId;
        $_SESSION['role'] = 'user';

        jsonResponse([
            'success' => true,
            'message' => 'Account created successfully.',
            'user' => [
                'id' => $newId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'balance' => 0.00,
                'role' => 'user'
            ]
        ], 201);
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid auth action.'], 400);
}
