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

$orders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
?>

<h2>Manage Orders</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Address</th>
            <th>Total</th>
            <th>Status</th>
            <th>Items</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($orders as $order): ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
            <td style="max-width: 200px;"><?php echo htmlspecialchars($order['address']); ?></td>
            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            <td>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                        <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>Pending</option>
                        <option value="processing" <?php if($order['status']=='processing') echo 'selected'; ?>>Processing</option>
                        <option value="completed" <?php if($order['status']=='completed') echo 'selected'; ?>>Completed</option>
                        <option value="cancelled" <?php if($order['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                </form>
            </td>
            <td>
                <!-- Could add a modal or expand row to view items, for now just a link or simplified -->
                <button class="btn" style="font-size:0.8rem; padding: 5px;">View</button>
            </td>
            <td><?php echo $order['created_at']; ?></td>
            <td>
                 <a href="orders.php?delete=<?php echo $order['id']; ?>" class="btn btn-danger" style="padding: 5px;" onclick="return confirm('Delete order?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('../includes/admin_footer.php'); ?>
