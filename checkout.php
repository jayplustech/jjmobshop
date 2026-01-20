<?php
require('includes/db.php');
include('includes/header.php');

$isLoggedIn = isset($_SESSION['user_id']);
?>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <h2><?php echo ($lang == 'sw' ? 'Malipo' : 'Checkout'); ?></h2>

    <div class="checkout-grid">
        <div class="checkout-main">
            <h3><?php echo $txt['checkout_summary']; ?></h3>
            <div id="cart-display"><?php echo ($lang == 'sw' ? 'Inapakia kikapu...' : 'Loading cart...'); ?></div>
            <div style="margin-top: 25px; text-align: right; font-size: 1.4rem; font-weight: 800; border-top: 1px solid #eee; padding-top: 15px;">
                <span style="font-size: 0.9rem; color: #666; font-weight: 400;"><?php echo $txt['total_amount']; ?>:</span> 
                TZS <span id="cart-total">0.00</span>
            </div>
        </div>

        <div class="checkout-sidebar">
            <?php if ($isLoggedIn): ?>
                <h3 style="margin-bottom: 20px;"><?php echo $txt['delivery_address']; ?></h3>
                <form action="API.php" method="POST">
                    <!-- Hidden input to store cart data from JS -->
                    <input type="hidden" name="cart_data" id="cart-data-input">
                    
                    <div class="form-group">
                        <label><?php echo $txt['name_label']; ?></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly style="background:#f8f9fa; border: 1px solid #eee;">
                    </div>
                    <div class="form-group">
                        <label><?php echo ($lang == 'sw' ? 'Barua Pepe (Email)' : 'Email Address'); ?></label>
                        <input type="email" name="order_email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required placeholder="<?php echo ($lang == 'sw' ? 'Weka email ya kupokea risiti...' : 'Enter email for order receipt...'); ?>" style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 12px; font-family: inherit;">
                    </div>
                    <div class="form-group">
                        <label><?php echo $txt['delivery_address']; ?></label>
                        <textarea name="address" required rows="4" placeholder="<?php echo $txt['enter_address']; ?>" style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 12px; font-family: inherit;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-size: 1.1rem;"><?php echo $txt['place_order']; ?></button>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 20px 0;">
                    <i class="fas fa-lock" style="font-size: 2.5rem; color: #eee; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px;"><?php echo $txt['login']; ?></h3>
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;"><?php echo ($lang == 'sw' ? 'Unatakiwa uingie kwenye akaunti ili ukamilishe oda.' : 'You need to be logged in to complete the purchase.'); ?></p>
                    <a href="login.php" class="btn btn-primary" style="display:block; text-align:center; margin-bottom: 15px;"><?php echo $txt['login']; ?></a>
                    <div style="display: flex; align-items: center; gap: 10px; margin: 15px 0;">
                        <hr style="flex:1; opacity:0.1;"> <span style="font-size: 0.8rem; color:#999;"><?php echo ($lang == 'sw' ? 'AU' : 'OR'); ?></span> <hr style="flex:1; opacity:0.1;">
                    </div>
                    <a href="register.php" class="btn btn-secondary" style="display:block; text-align:center;"><?php echo $txt['register']; ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
