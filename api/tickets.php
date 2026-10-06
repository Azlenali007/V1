<?php
/**
 * Support Tickets API
 * SMM Panel
 */

declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $input = getJsonInput();
    $subject = trim($input['subject'] ?? '');
    $message = trim($input['message'] ?? '');
    $priority = in_array($input['priority'] ?? '', ['Low', 'Medium', 'High']) ? $input['priority'] : 'Medium';

    if (empty($subject) || empty($message)) {
        jsonResponse(['success' => false, 'message' => 'Please provide both subject and message.'], 400);
    }

    $pdo->beginTransaction();
    try {
        $ticketCode = generateTicketCode($pdo);
        $stmt = $pdo->prepare('
            INSERT INTO tickets (ticket_code, user_id, subject, status, priority)
            VALUES (?, ?, ?, "Open", ?)
        ');
        $stmt->execute([$ticketCode, $user['id'], $subject, $priority]);
        $ticketId = (int)$pdo->lastInsertId();

        $msgStmt = $pdo->prepare('
            INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message)
            VALUES (?, "user", ?, ?)
        ');
        $msgStmt->execute([$ticketId, $user['id'], $message]);

        $pdo->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Ticket created successfully!',
            'ticket' => [
                'id' => $ticketId,
                'ticket_code' => $ticketCode,
                'subject' => $subject,
                'status' => 'Open',
                'priority' => $priority,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ], 201);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to create ticket: ' . $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reply') {
    $input = getJsonInput();
    $ticketId = (int)($input['ticket_id'] ?? 0);
    $message = trim($input['message'] ?? '');

    if ($ticketId <= 0 || empty($message)) {
        jsonResponse(['success' => false, 'message' => 'Message cannot be empty.'], 400);
    }

    // Verify ownership
    $stmt = $pdo->prepare('SELECT id, status FROM tickets WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$ticketId, $user['id']]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        jsonResponse(['success' => false, 'message' => 'Ticket not found.'], 404);
    }

    $msgStmt = $pdo->prepare('
        INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message)
        VALUES (?, "user", ?, ?)
    ');
    $msgStmt->execute([$ticketId, $user['id'], $message]);

    // If ticket was closed, re-open it
    if ($ticket['status'] === 'Closed') {
        $pdo->prepare('UPDATE tickets SET status = "Open" WHERE id = ?')->execute([$ticketId]);
    }

    jsonResponse(['success' => true, 'message' => 'Reply sent successfully.']);
}

if ($action === 'messages') {
    $ticketId = (int)($_GET['ticket_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, ticket_code, subject, status, priority, created_at FROM tickets WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$ticketId, $user['id']]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        jsonResponse(['success' => false, 'message' => 'Ticket not found.'], 404);
    }

    $msgStmt = $pdo->prepare('
        SELECT tm.id, tm.sender_type, tm.message, tm.created_at,
               CASE WHEN tm.sender_type = "user" THEN u.name ELSE "Support Agent" END as sender_name
        FROM ticket_messages tm
        LEFT JOIN users u ON tm.sender_id = u.id AND tm.sender_type = "user"
        WHERE tm.ticket_id = ?
        ORDER BY tm.id ASC
    ');
    $msgStmt->execute([$ticketId]);
    $messages = $msgStmt->fetchAll();

    jsonResponse([
        'success' => true,
        'ticket' => $ticket,
        'messages' => $messages
    ]);
}

// List tickets with status filter
$statusFilter = $_GET['status'] ?? 'All';
$sql = 'SELECT id, ticket_code, subject, status, priority, created_at, updated_at FROM tickets WHERE user_id = ?';
$params = [$user['id']];

if (!empty($statusFilter) && $statusFilter !== 'All') {
    $sql .= ' AND status = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'tickets' => $tickets
]);
