<?php
/**
 * Get Cart Count API
 * Returns the number of items in the user's cart (from localStorage via session or direct count)
 */
require_once __DIR__ . '/includes/db.php';
session_start();

header('Content-Type: application/json');

// For now, we'll return 0 and let the JavaScript handle the localStorage cart
// This endpoint can be extended later for server-side cart storage

$count = 0;

// If you want to count pending orders instead:
if (isset($_SESSION['user_id'])) {
    // Count items in pending orders (not yet paid)
    // 'processing' implies paid/success usually, so we exclude it to satisfy "reset to zero once paid"
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.quantity), 0) as total_items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND o.status IN ('pending', 'failed')
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $count = $row ? (int)$row['total_items'] : 0;
}

echo json_encode(['count' => $count, 'success' => true]);
?>
