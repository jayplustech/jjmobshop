<?php
require('../includes/db.php');
include('../includes/admin_header.php');

// Fetch counts
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<h2>Dashboard Overview</h2>

<div class="product-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 30px;">
    <div class="product-card">
        <h3>Users</h3>
        <p class="price"><?php echo $userCount; ?></p>
    </div>
    <div class="product-card">
        <h3>Products</h3>
        <p class="price"><?php echo $productCount; ?></p>
    </div>
    <div class="product-card">
        <h3>Total Orders</h3>
        <p class="price"><?php echo $orderCount; ?></p>
    </div>
</div>

<h3>Recent Orders</h3>
<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            <td><?php echo ucfirst($order['status']); ?></td>
            <td><?php echo $order['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('../includes/admin_footer.php'); ?>
