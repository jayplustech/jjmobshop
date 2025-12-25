<?php
require('includes/db.php');
include('includes/header.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
?>

<div style="text-align: center; padding: 50px;">
    <h2 style="color: #28a745;">Order Placed Successfully!</h2>
    <p>Thank you for your purchase. Your order ID is <strong>#<?php echo htmlspecialchars($order_id); ?></strong>.</p>
    <p>We will process your order shortly.</p>
    <a href="index.php" class="btn">Continue Shopping</a>
</div>

<script>
    // Clear the cart
    localStorage.removeItem('jj_cart');
    // Update count in header
    if (document.getElementById('cart-count')) {
        document.getElementById('cart-count').innerText = '0';
    }
</script>

<?php include('includes/footer.php'); ?>
