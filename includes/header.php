<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get categories for navigation
require_once __DIR__ . '/db.php';
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJ.MOBISHOP</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color:#fff;">JJ.MOBISHOP</a></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php foreach($cats as $c): ?>
                        <li><a href="index.php?category=<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></a></li>
                    <?php endforeach; ?>
                    
                    <li><a href="checkout.php">Cart (<span id="cart-count">0</span>)</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container main-content">
