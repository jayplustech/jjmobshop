<?php
require('includes/db.php');
include('includes/header.php');

$isLoggedIn = isset($_SESSION['user_id']);
?>

<h2>Checkout</h2>

<div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <div style="flex: 2; min-width: 300px;">
        <h3>Your Cart</h3>
        <div id="cart-display">Loading cart...</div>
        <div style="margin-top: 20px; text-align: right; font-size: 1.2rem; font-weight: bold;">
            Total: $<span id="cart-total">0.00</span>
        </div>
    </div>

    <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px;">
        <?php if ($isLoggedIn): ?>
            <h3>Delivery Details</h3>
            <form action="payment.php" method="POST">
                <!-- Hidden input to store cart data from JS -->
                <input type="hidden" name="cart_data" id="cart-data-input">
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly style="background:#f9f9f9;">
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="address" required rows="3" placeholder="Enter full address"></textarea>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="cod">Cash on Delivery</option>
                        <option value="card">Credit Card (Mock)</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Place Order</button>
            </form>
        <?php else: ?>
            <h3>Please Login</h3>
            <p>You need to be logged in to complete the purchase.</p>
            <a href="login.php" class="btn" style="display:block; text-align:center; margin-top:10px;">Login</a>
            <p style="text-align:center; margin-top:10px;">OR</p>
            <a href="register.php" class="btn btn-primary" style="display:block; text-align:center;">Register</a>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
