<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get categories and language strings
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/languages.php';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJ.MOBISHOP | Premium Mobile Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <!-- Sidebar Navigation -->
        <div id="sidebar-overlay" class="sidebar-overlay"></div>
        <div id="sidebar-nav" class="sidebar-nav">
            <button id="menu-close" class="close-btn">&times;</button>
            <div class="sidebar-header">
                <h3><?php echo ($lang == 'sw' ? 'Orodha' : 'Menu'); ?></h3>
            </div>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $txt['home']; ?></a></li>
                <li><a href="index.php?#shop"><i class="fas fa-store"></i> <?php echo $txt['shop']; ?></a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="my_orders.php"><i class="fas fa-box"></i> <?php echo $txt['my_orders']; ?></a></li>
                <?php endif; ?>
                <li><a href="checkout.php"><i class="fas fa-shopping-bag"></i> <?php echo $txt['cart']; ?></a></li>
                <li><a href="#footer"><i class="fas fa-envelope"></i> <?php echo $txt['contact']; ?></a></li>
                
                <!-- Mobile Login/Logout Links -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="mobile-only"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo $txt['logout']; ?></a></li>
                <?php else: ?>
                    <li class="mobile-only"><a href="login.php"><i class="fas fa-sign-in-alt"></i> <?php echo $txt['login']; ?></a></li>
                <?php endif; ?>
                <li class="mobile-only">
                    <a href="?lang=<?php echo $lang == 'en' ? 'sw' : 'en'; ?>">
                        <img src="https://flagcdn.com/w40/<?php echo $lang == 'en' ? 'tz' : 'gb'; ?>.png" width="20" alt="Flag" style="border-radius:2px; margin-right:10px;">
                        <?php echo $txt['switch_to']; ?>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Toast Notification Container -->
        <div id="toast-container"></div>

        <div class="container">
            <a href="index.php" class="brand">JJ.<span>MOBISHOP</span></a>
            
            <div class="header-actions">
                <!-- Language Switcher with Flag -->
                <a href="?lang=<?php echo $lang == 'en' ? 'sw' : 'en'; ?>" class="lang-switch" title="<?php echo $txt['switch_to']; ?>">
                    <img src="https://flagcdn.com/w40/<?php echo $lang == 'en' ? 'tz' : 'gb'; ?>.png" width="24" alt="Flag" style="border-radius:3px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                </a>

                <form action="index.php" method="GET" class="search-form">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="<?php echo $txt['search_placeholder']; ?>" class="search-bar" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>

                <a href="checkout.php" class="header-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge" id="cart-count">0</span>
                </a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" title="<?php echo $txt['logout']; ?>"><i class="fas fa-user-circle"></i></a>
                <?php else: ?>
                    <a href="login.php" title="<?php echo $txt['login']; ?>"><i class="far fa-user"></i></a>
                <?php endif; ?>

                <!-- Hamburger Menu -->
                <button id="menu-toggle" class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
});
</script>
