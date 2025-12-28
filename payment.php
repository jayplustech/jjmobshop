<?php
require('includes/db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if we are receiving data (either from Checkout or from Self-Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture Data
    $address = $_POST['address'] ?? '';
    // If coming from self-submission, we might need to be careful not to double escape or lose data
    // Usually json encoding is robust enough if passed as hidden value content
    $cart_json = $_POST['cart_data'] ?? '[]';
    
    // Calculate Total for Display/Processing
    $cart_items = json_decode($cart_json, true);
    $total_amount = 0;
    $valid_items = [];
    
    if (empty($cart_items)) {
        header("Location: index.php"); // Back to home if empty
        exit();
    }
    
    // Calculate Total
    foreach ($cart_items as $item) {
        // Fetch real price
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
    
    // FINAL PROCESSING STEP
    if (isset($_POST['final_pay'])) {
        // This button name 'final_pay' means user confirmed the Visual Payment Page
        
        if (count($valid_items) > 0) {
            // Create Order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, address) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $total_amount, $address]);
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
            $error = "Payment failed. Invalid items.";
        }
    }
} else {
    // If accessed via GET, redirect to home (must come from checkout)
    header("Location: index.php");
    exit();
}

// If we are here, we are in the "Visual Step"
// We display the header/footer and the Payment UI
include('includes/header.php');
?>

<div class="container">
    <div class="payment-wrapper">
        <div class="payment-total">
            <h2>Total Amount to Pay</h2>
            <div class="amount">$<?php echo number_format($total_amount, 2); ?></div>
        </div>
        
        <form method="POST" action="payment.php" id="payment-form">
            <!-- Carry over data -->
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
            <input type="hidden" name="cart_data" value="<?php echo htmlspecialchars($cart_json); ?>">
            <input type="hidden" name="selected_method" id="selected-method" value="mpesa">
            
            <h3 style="margin-bottom:20px; text-align:center; color:#555;">Select Payment Method</h3>
            
            <div class="payment-methods">
                <div class="payment-card active" onclick="selectMethod('mpesa', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>M-Pesa</span>
                </div>
                <div class="payment-card" onclick="selectMethod('tigo', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Tigo Pesa</span>
                </div>
                <div class="payment-card" onclick="selectMethod('airtel', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Airtel Money</span>
                </div>
                <div class="payment-card" onclick="selectMethod('card', this)">
                    <i class="far fa-credit-card"></i>
                    <span>Credit Card</span>
                </div>
            </div>
            
            <button type="submit" name="final_pay" class="btn btn-pay">
                Pay $<?php echo number_format($total_amount, 2); ?> Now
            </button>
        </form>
    </div>
</div>

<script>
function selectMethod(method, element) {
    // Update hidden input
    document.getElementById('selected-method').value = method;
    
    // Update UI
    document.querySelectorAll('.payment-card').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
}
</script>

<?php include('includes/footer.php'); ?>
