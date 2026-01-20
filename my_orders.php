<?php
require('includes/db.php');
include('includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User's Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Handle View Items (Modal Logic)
$view_order = null;
$order_items = [];
if (isset($_GET['view'])) {
    $view_id = $_GET['view'];
    // Ensure the order belongs to the user for security
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$view_id, $user_id]);
    $view_order = $stmt->fetch();
    
    if($view_order) {
        $stmtItems = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmtItems->execute([$view_id]);
        $order_items = $stmtItems->fetchAll();
    }
}
?>

<div class="container" style="padding-top: 40px; min-height: 60vh;">
    <h2 class="section-title"><?php echo $lang == 'sw' ? 'Oda' : 'My'; ?> <span><?php echo ($lang == 'sw' ? 'Zangu' : 'Orders'); ?></span></h2>

    <?php if (count($orders) > 0): ?>
        <div style="overflow-x:auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0A1A2F; color: #fff; text-align: left;">
                        <th style="padding: 15px;"><?php echo $txt['order_id']; ?></th>
                        <th style="padding: 15px;"><?php echo $txt['order_date']; ?></th>
                        <th style="padding: 15px;"><?php echo ($lang == 'sw' ? 'Jumla' : 'Total'); ?></th>
                        <th style="padding: 15px;"><?php echo ($lang == 'sw' ? 'Malipo' : 'Payment'); ?></th>
                        <th style="padding: 15px;"><?php echo $txt['order_status']; ?></th>
                        <th style="padding: 15px;"><?php echo $txt['order_action']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;">#<?php echo $order['id']; ?></td>
                        <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td style="padding: 15px; font-weight: bold;">TZS <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td style="padding: 15px; font-size: 0.85rem;">
                            <?php echo strtoupper($order['payment_method'] ?? 'COD'); ?>
                        </td>
                        <td style="padding: 15px;">
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; background: <?php echo $order['status']=='completed'||$order['status']=='paid' ? '#d4edda' : ($order['status']=='pending'?'#fff3cd':'#f8d7da'); ?>; color: <?php echo $order['status']=='completed'||$order['status']=='paid' ? '#155724' : ($order['status']=='pending'?'#856404':'#721c24'); ?>;">
                                <?php echo $txt['status_' . $order['status']] ?? ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <a href="my_orders.php?view=<?php echo $order['id']; ?>" class="btn" style="padding: 8px 15px; font-size: 0.85rem;"><?php echo $txt['view']; ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; background: #fff; border-radius: 12px;">
            <i class="fas fa-box-open" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
            <p><?php echo $txt['no_orders']; ?></p>
            <a href="index.php" class="btn" style="margin-top: 10px;"><?php echo ($lang == 'sw' ? 'Anza Kununua' : 'Start Shopping'); ?></a>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<?php if ($view_order): ?>
<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:10000;">
    <div style="background:#fff; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; padding:25px; border-radius:12px; position:relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <a href="my_orders.php" style="position:absolute; top:15px; right:20px; font-size:24px; text-decoration:none; color:#999; transition: 0.3s;">&times;</a>
        
        <h3 style="border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px; color: #0A1A2F;"><?php echo $txt['order_details']; ?> #<?php echo $view_order['id']; ?></h3>
        
        <div style="margin-bottom: 20px; font-size: 0.95rem;">
            <p><strong><?php echo $txt['order_date']; ?>:</strong> <?php echo date('F j, Y, g:i a', strtotime($view_order['created_at'])); ?></p>
            <p><strong><?php echo $txt['order_status']; ?>:</strong> <span style="font-weight: bold; color: <?php echo $view_order['status']=='completed'||$view_order['status']=='paid' ? '#28a745' : 'inherit'; ?>"><?php echo $txt['status_' . $view_order['status']] ?? ucfirst($view_order['status']); ?></span></p>
            <p><strong><?php echo ($lang == 'sw' ? 'Njia ya Malipo' : 'Payment Method'); ?>:</strong> <?php echo strtoupper($view_order['payment_method'] ?? 'COD'); ?></p>
            <?php if(!empty($view_order['transaction_id'])): ?>
                <p><strong>Transaction ID:</strong> <code style="background:#f4f4f4; padding:2px 6px; border-radius:3px; font-size:0.85rem;"><?php echo htmlspecialchars($view_order['transaction_id']); ?></code></p>
            <?php endif; ?>
            <p><strong><?php echo $txt['delivery_address']; ?>:</strong> <?php echo nl2br(htmlspecialchars($view_order['address'])); ?></p>
        </div>

        <h4 style="margin-bottom: 10px; color: #555;"><?php echo ($lang == 'sw' ? 'Bidhaa' : 'Items'); ?></h4>
        <div style="background: #f9f9f9; border-radius: 8px; padding: 15px;">
            <?php foreach($order_items as $item): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if($item['image']): ?>
                        <img src="<?php echo $item['image']; ?>" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; background: #fff;">
                    <?php endif; ?>
                    <div>
                        <div style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div style="font-size: 0.8rem; color: #777;">x<?php echo $item['quantity']; ?></div>
                    </div>
                </div>
                <div style="font-weight: bold; font-size: 0.9rem;">
                    TZS <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ccc; font-size: 1.1rem; font-weight: bold;">
                <span><?php echo ($lang == 'sw' ? 'Jumla' : 'Total'); ?></span>
                <span style="color: var(--color-primary);">TZS <?php echo number_format($view_order['total_amount'], 2); ?></span>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 20px;">
            <a href="my_orders.php" class="btn btn-secondary" style="background: #eee; color: #333; padding: 10px 20px;"><?php echo ($lang == 'sw' ? 'Funga' : 'Close'); ?></a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include('includes/footer.php'); ?>
