<?php
require('../includes/db.php');
include('../includes/admin_header.php');


// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header("Location: orders.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: orders.php");
    exit();
}

// Fetch Orders
$orders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();

// Handle View Items
$view_order = null;
$order_items = [];
if (isset($_GET['view'])) {
    $view_id = $_GET['view'];
    $stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$view_id]);
    $view_order = $stmt->fetch();
    
    if($view_order) {
        $stmtItems = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmtItems->execute([$view_id]);
        $order_items = $stmtItems->fetchAll();
    }
}
?>

<h2>Manage Orders</h2>

<!-- Orders Table -->
<div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                <!-- Truncate address to avoid breaking layout -->
                <td style="max-width:200px;"><?php echo htmlspecialchars($order['address']); ?></td>
                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                <td>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="update_status" value="1"> <!-- FIX: Added hidden trigger -->
                        <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius:4px; border:1px solid #ddd; background:<?php echo $order['status']=='completed' ? '#d4edda' : ($order['status']=='pending'?'#fff3cd':'#f8d7da'); ?>">
                            <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>Pending</option>
                            <option value="processing" <?php if($order['status']=='processing') echo 'selected'; ?>>Processing</option>
                            <option value="completed" <?php if($order['status']=='completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if($order['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </form>
                </td>
                <td>
                    <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn" style="padding: 5px 10px; font-size:0.85rem; background:#007BFF; color:#fff;">View</a>
                    <a href="orders.php?delete=<?php echo $order['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size:0.85rem;" onclick="return confirm('Delete order?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Order Details Modal -->
<?php if ($view_order): ?>
<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000;">
    <div style="background:#fff; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; padding:20px; border-radius:8px; position:relative;">
        <a href="orders.php" style="position:absolute; top:15px; right:20px; font-size:24px; text-decoration:none; color:#333;">&times;</a>
        
        <h3 style="border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:20px;">Order #<?php echo $view_order['id']; ?></h3>
        
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($view_order['user_name']); ?> (<?php echo htmlspecialchars($view_order['email']); ?>)</p>
        <p><strong>Date:</strong> <?php echo $view_order['created_at']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($view_order['status']); ?></p>

        <h4 style="margin-top:20px;">Items</h4>
        <table style="width:100%; border-collapse: collapse; margin-top:10px;">
            <tr style="background:#eee;">
                <th style="padding:8px; text-align:left;">Product</th>
                <th style="padding:8px; text-align:left;">Qty</th>
                <th style="padding:8px; text-align:left;">Price</th>
            </tr>
            <?php foreach($order_items as $item): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:8px;">
                    <?php if($item['image']): ?><img src="../<?php echo $item['image']; ?>" style="width:30px; vertical-align:middle; margin-right:5px;"><?php endif; ?>
                    <?php echo htmlspecialchars($item['name']); ?>
                </td>
                <td style="padding:8px;">x<?php echo $item['quantity']; ?></td>
                <td style="padding:8px;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h3 style="text-align:right; margin-top:20px;">Total: $<?php echo number_format($view_order['total_amount'], 2); ?></h3>
    </div>
</div>
<?php endif; ?>

<?php include('../includes/admin_footer.php'); ?>
