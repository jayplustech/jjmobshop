<?php
require('includes/db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $address = $_POST['address'] ?? '';
    $cart_json = $_POST['cart_data'] ?? '[]';
    
    $cart_items = json_decode($cart_json, true);
    
    if (empty($cart_items)) {
        die("Cart is empty.");
    }
    
    // Calculate total (Recalculate for security)
    $total_amount = 0;
    
    // We need to fetch current prices to be secure, or trust client for this simple demo. 
    // I'll trust client for simplicity but normally would query DB. 
    // Let's do a quick query for correctness.
    $valid_items = [];
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        if ($product) {
            $price = $product['price'];
            $qty = $item['quantity'];
            $total_amount += $price * $qty;
            $valid_items[] = [
                'id' => $item['id'],
                'price' => $price,
                'qty' => $qty
            ];
        }
    }
    
    if (count($valid_items) > 0) {
        // Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, address) VALUES (?, ?, 'pending', ?)");
        $stmt->execute([$user_id, $total_amount, $address]);
        $order_id = $pdo->lastInsertId();
        
        // Create Order Items
        $stmtObj = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($valid_items as $vi) {
            $stmtObj->execute([$order_id, $vi['id'], $vi['qty'], $vi['price']]);
        }
        
        // Redirect to success
        header("Location: order_success.php?id=$order_id");
        exit();
    } else {
        echo "Error: No valid items found.";
    }
} else {
    header("Location: index.php");
}
?>
