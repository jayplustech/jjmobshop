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
    <title>JJ.MOBISHOP | Premium Mobile Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="brand">JJ.<span>MOBISHOP</span></a>
            
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#shop"><i class="fas fa-store"></i> Shop</a></li>
                    <li><a href="checkout.php"><i class="fas fa-shopping-bag"></i> Cart <span class="badge" id="cart-count">0</span></a></li>
                    <li><a href="#footer"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <div style="position:relative;">
                    <i class="fas fa-search" style="position:absolute; left:10px; top:10px; font-size:12px; color:rgba(255,255,255,0.5);"></i>
                    <input type="text" placeholder="Search" class="search-bar" style="padding-left:30px;">
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php"><i class="fas fa-user-circle"></i></a>
                <?php else: ?>
                    <a href="login.php"><i class="far fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>
