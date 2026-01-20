<?php
require('includes/db.php');
session_start();

if (!isset($_SESSION['user_id']) && !defined('BYPASS_AUTH')) {
    // If not through session and not bypassed (like callback), error
    if (isset($_GET['deposit_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit();
    }
}

// Logic only runs if explicitly called via GET
if (isset($_GET['deposit_id'])) {
    $deposit_id = $_GET['deposit_id'];
    $order_id = $_GET['order_id'] ?? null;
    
    $pawapay_config = include('payments/config.php');
    require_once('payments/PawaPayHandler.php');
    $pawapay = new PawaPayHandler($pawapay_config);
    
    $response = $pawapay->checkStatus($deposit_id);
    
    if ($response['status'] == 200) {
        $pawa_data = $response['response'];
        if (isset($pawa_data['data']) && is_array($pawa_data['data'])) {
            $status = $pawa_data['data']['status'] ?? 'PENDING';
        } else {
            $status = $pawa_data['status'] ?? 'PENDING';
        }

        if ($status === 'COMPLETED') {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE transaction_id = ? AND status != 'paid'");
            $stmt->execute([$deposit_id]);
            
            $stmtDetails = $pdo->prepare("
                SELECT o.id as order_id, o.total_amount, o.order_email as email, u.name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.transaction_id = ?
            ");
            $stmtDetails->execute([$deposit_id]);
            $order = $stmtDetails->fetch();

            if ($order && $stmt->rowCount() > 0) {
                // Fetch Order Items for the Summary
                $stmtItems = $pdo->prepare("
                    SELECT p.name, oi.quantity, oi.price 
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['order_id']]);
                $items = $stmtItems->fetchAll();
                
                sendNotification($order['email'], $order['name'], $order['order_id'], $order['total_amount'], $items);
            }

            log_msg("Payment Confirmed - ID: $deposit_id");
            echo json_encode(['status' => 'success', 'payment_status' => 'paid']);
            exit();
        } elseif ($status === 'FAILED') {
            $reason = 'Unknown';
            if (isset($pawa_data['data']['failureReason']['failureMessage'])) {
                $reason = $pawa_data['data']['failureReason']['failureMessage'];
            }
            $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE transaction_id = ? AND status = 'pending'");
            $stmt->execute([$deposit_id]);
            echo json_encode(['status' => 'success', 'payment_status' => 'failed', 'reason' => $reason]);
            exit();
        } else {
            echo json_encode(['status' => 'success', 'payment_status' => 'pending']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'API Error']);
        exit();
    }
}

/**
 * Function to send Professional HTML Email notifications with Order Summary
 */
function sendNotification($email, $name, $orderId, $amount, $items = []) {
    $subject = "Thibitisho la Malipo - Order #$orderId";
    $formattedTotal = number_format($amount, 2);
    
    // Generate Order Summary Table Rows
    $itemRows = "";
    foreach ($items as $item) {
        $subtotal = number_format($item['price'] * $item['quantity'], 2);
        $price = number_format($item['price'], 2);
        $itemRows .= "
        <tr>
            <td style='padding: 12px; border-bottom: 1px solid #eee;'>{$item['name']}</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>TZS $price</td>
            <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>TZS $subtotal</td>
        </tr>";
    }

    $message = "
    <div style='font-family: \"Outfit\", Arial, sans-serif; max-width: 650px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
        <div style='background: #0f172a; padding: 40px 30px; text-align: center; color: #ffffff;'>
            <h1 style='margin: 0; font-size: 28px; letter-spacing: 3px; font-weight: 800;'>JJ <span style='color: #3b82f6;'>MOBISHOP</span></h1>
            <p style='margin: 10px 0 0; opacity: 0.8; font-size: 16px;'>Malipo Yamefanikiwa!</p>
        </div>
        <div style='padding: 40px 30px;'>
            <h2 style='color: #1e293b; margin-top: 0;'>Habari $name,</h2>
            <p style='color: #64748b; line-height: 1.6; font-size: 16px;'>
                Ahsante kwa kufanya manunuzi. Tumepokea malipo yako ya <strong>TZS $formattedTotal</strong>. Hapa kuna muhtasari wa oda yako <strong>#$orderId</strong>:
            </p>
            
            <div style='margin: 30px 0;'>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px; color: #334155;'>
                    <thead>
                        <tr style='background: #f8fafc; color: #1e293b;'>
                            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0;'>Bidhaa</th>
                            <th style='padding: 12px; text-align: center; border-bottom: 2px solid #e2e8f0;'>Idadi</th>
                            <th style='padding: 12px; text-align: right; border-bottom: 2px solid #e2e8f0;'>Bei</th>
                            <th style='padding: 12px; text-align: right; border-bottom: 2px solid #e2e8f0;'>Jumla</th>
                        </tr>
                    </thead>
                    <tbody>
                        $itemRows
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='padding: 20px 12px 10px; text-align: right; font-weight: bold; font-size: 16px; color: #1e293b;'>Jumla Kuu:</td>
                            <td style='padding: 20px 12px 10px; text-align: right; font-weight: 800; font-size: 18px; color: #3b82f6;'>TZS $formattedTotal</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style='background: #f0fdf4; padding: 20px; border-radius: 12px; border: 1px solid #bbfcce; color: #166534; margin-bottom: 30px;'>
                <p style='margin: 0; font-weight: bold;'>Hatua Inayofuata:</p>
                <p style='margin: 5px 0 0; font-size: 14px;'>Tunaanza kuandaa vitu vyako sasa. Utajulishwa pindi vitakapokuwa tayari kufikishwa kwako.</p>
            </div>

            <div style='text-align: center;'>
                <a href='http://localhost/JJMOBISHOP/my_orders.php' style='display: inline-block; background: #3b82f6; color: #ffffff; padding: 16px 35px; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 16px; box-shadow: 0 10px 15px rgba(59, 130, 246, 0.2);'>Fuatilia Oda Yako</a>
            </div>
        </div>
        <div style='background: #f8fafc; padding: 30px; text-align: center; color: #94a3b8; font-size: 13px; border-top: 1px solid #f1f5f9;'>
            <p style='margin: 0;'>&copy; " . date('Y') . " JJ MOBISHOP. Haki zote zimehifadhiwa.</p>
        </div>
    </div>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: JJ MOBISHOP <noreply@jjmobishop.com>" . "\r\n";

    // Send Email
    $mailSent = mail($email, $subject, $message, $headers);
    
    if ($mailSent) {
        log_msg("EMAIL SENT SUCCESSFULLY: To $email for Order #$orderId");
    } else {
        log_msg("EMAIL FAILED TO SEND: To $email for Order #$orderId. Check PHP mail configuration.");
    }
}
