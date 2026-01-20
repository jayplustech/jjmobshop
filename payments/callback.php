<?php
require_once('../includes/db.php');
define('BYPASS_AUTH', true); // Inaruhusu kuinclude verify_payment.php bila session check kurusha error

// PawaPay hutuma data kwa muundo wa JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the callback for debugging
$logFile = '../logs/pawapay_callback.log';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] CALLBACK RECEIVED: " . $input . PHP_EOL, FILE_APPEND);

if (isset($data['depositId']) && isset($data['status'])) {
    $depositId = $data['depositId'];
    $status = $data['status'];

    if ($status === 'COMPLETED') {
        // 1. Update Order Status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE transaction_id = ? AND status != 'paid'");
        $stmt->execute([$depositId]);

        if ($stmt->rowCount() > 0) {
            // 2. Fetch Details for Email Summary
            $stmtDetails = $pdo->prepare("
                SELECT o.id as order_id, o.total_amount, o.order_email as email, u.name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.transaction_id = ?
            ");
            $stmtDetails->execute([$depositId]);
            $order = $stmtDetails->fetch();

            if ($order) {
                // Fetch Items for Summary
                $stmtItems = $pdo->prepare("
                    SELECT p.name, oi.quantity, oi.price 
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['order_id']]);
                $items = $stmtItems->fetchAll();

                // Tunatumia logic ya email tuliyoweka
                require_once('../verify_payment.php'); 
                if (function_exists('sendNotification')) {
                    sendNotification($order['email'], $order['name'], $order['order_id'], $order['total_amount'], $items);
                }
            }
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SUCCESS: Order updated and Email sent for ID: $depositId" . PHP_EOL, FILE_APPEND);
        }
    } elseif ($status === 'FAILED') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE transaction_id = ? AND status = 'pending'");
        $stmt->execute([$depositId]);
    }

    // PawaPay inahitaji 200 OK kuitikia kuwa tumepokea callback
    http_response_code(200);
    echo json_encode(["status" => "accepted"]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
