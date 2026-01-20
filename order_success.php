<?php
require('includes/db.php');
include('includes/header.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$deposit_id = $_GET['deposit_id'] ?? null;
?>

<div style="text-align: center; padding: 60px 20px;">
    <div id="status-icon" style="font-size: 5rem; color: #3b82f6; margin-bottom: 30px; transition: all 0.5s;">
        <i class="fas fa-spinner fa-spin" id="icon-inner"></i>
    </div>
    
    <div id="dynamic-status-container" style="max-width: 500px; margin: 0 auto;">
        <!-- Step 1: Processing/Waiting -->
        <div id="step-processing" style="display: block;">
            <h2 style="color: #1e293b; font-weight: 800; font-family: 'Outfit', sans-serif;">
                <?php echo ($lang == 'sw' ? 'Tunahakiki Malipo...' : 'Verifying Payment...'); ?>
            </h2>
            <div style="background: #f0f7ff; color: #0369a1; padding: 25px; border-radius: 16px; margin: 25px 0; border: 1px solid #bae6fd; line-height: 1.6;">
                <p style="margin-bottom: 15px;">
                    <i class="fas fa-mobile-alt"></i> <strong><?php echo ($lang == 'sw' ? 'Tafadhali weka PIN kwenye simu yako' : 'Please enter PIN on your phone'); ?></strong>
                </p>
                <p style="font-size: 0.95rem; opacity: 0.8;">
                    <?php echo ($lang == 'sw' ? 'Tunasubiri thibitisho kutoka kwa mtandao wako wa simu. Usifunge ukurasa huu.' : 'Waiting for confirmation from your mobile network. Please do not close this page.'); ?>
                </p>
            </div>
            <div class="loader-bar" style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 20px;">
                <div id="progress-bar" style="width: 30%; height: 100%; background: #3b82f6; border-radius: 10px; transition: width 0.5s;"></div>
            </div>
        </div>

        <!-- Step 2: Success -->
        <div id="step-success" style="display: none;">
            <h2 style="color: #16a34a; font-weight: 800; font-family: 'Outfit', sans-serif;">
                <?php echo ($lang == 'sw' ? 'Malipo Yamefanikiwa!' : 'Payment Successful!'); ?>
            </h2>
            <div style="background: #f0fdf4; color: #166534; padding: 25px; border-radius: 16px; margin: 25px 0; border: 1px solid #bbfcce;">
                <i class="fas fa-check-circle" style="font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                <strong><?php echo ($lang == 'sw' ? 'Ahsante Sana!' : 'Thank You!'); ?></strong><br>
                <?php echo ($lang == 'sw' ? 'Oda yako imapokelewa na risiti imetumwa kwenye email yako.' : 'Your order has been received and a receipt has been sent to your email.'); ?>
            </div>
            <a href="my_orders.php" class="btn btn-primary" style="width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">
                <?php echo ($lang == 'sw' ? 'Angalia Oda Zangu' : 'View My Orders'); ?>
            </a>
        </div>

        <!-- Step 3: Failed -->
        <div id="step-failed" style="display: none;">
            <h2 style="color: #dc2626; font-weight: 800; font-family: 'Outfit', sans-serif;">
                <?php echo ($lang == 'sw' ? 'Malipo Yamefeli' : 'Payment Failed'); ?>
            </h2>
            <div style="background: #fef2f2; color: #991b1b; padding: 25px; border-radius: 16px; margin: 25px 0; border: 1px solid #fecaca;">
                <i class="fas fa-times-circle" style="font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                <p id="fail-reason"><?php echo ($lang == 'sw' ? 'Imeshindikana kukamilisha malipo. Tafadhali jaribu tena.' : 'Could not complete payment. Please try again.'); ?></p>
            </div>
            <a href="checkout.php" class="btn btn-secondary" style="width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">
                <?php echo ($lang == 'sw' ? 'Jaribu Tena' : 'Try Again'); ?>
            </a>
        </div>
    </div>

    <p style="margin-top: 40px; color: #64748b;">
        <?php echo ($lang == 'sw' ? 'Namba ya oda:' : 'Order ID:'); ?> <strong>#<?php echo htmlspecialchars($order_id); ?></strong>
    </p>
</div>

<script>
    const depositId = "<?php echo $deposit_id; ?>";
    const orderId = "<?php echo $order_id; ?>";
    
    // Clear cart
    localStorage.removeItem('jj_cart');
    
    if (depositId) {
        let attempts = 0;
        const maxAttempts = 20; // Check for ~1 minute
        
        async function checkStatus() {
            attempts++;
            const progressBar = document.getElementById('progress-bar');
            if(progressBar) progressBar.style.width = Math.min(30 + (attempts * 3.5), 100) + '%';
            
            try {
                const response = await fetch(`verify_payment.php?deposit_id=${depositId}&order_id=${orderId}`);
                const data = await response.json();
                
                if (data.payment_status === 'paid') {
                    showSuccess();
                } else if (data.payment_status === 'failed') {
                    showFailed(data.reason || null);
                } else if (attempts < maxAttempts) {
                    setTimeout(checkStatus, 4000); // Check every 4 seconds
                } else {
                   // Timeout reached
                   window.location.href = 'my_orders.php';
                }
            } catch (error) {
                console.error('Status check failed', error);
                if (attempts < maxAttempts) setTimeout(checkStatus, 5000);
            }
        }

        function showSuccess() {
            document.getElementById('step-processing').style.display = 'none';
            document.getElementById('step-success').style.display = 'block';
            document.getElementById('status-icon').style.color = '#16a34a';
            document.getElementById('icon-inner').className = 'fas fa-check-circle';
        }

        function showFailed(reason) {
            document.getElementById('step-processing').style.display = 'none';
            document.getElementById('step-failed').style.display = 'block';
            document.getElementById('status-icon').style.color = '#dc2626';
            document.getElementById('icon-inner').className = 'fas fa-times-circle';
            if(reason) document.getElementById('fail-reason').innerText = reason;
        }

        // Start polling
        checkStatus();

    } else {
        // Fallback if no deposit ID (Manual payment)
        setTimeout(() => {
            document.getElementById('step-processing').style.display = 'none';
            document.getElementById('step-success').style.display = 'block';
            document.getElementById('status-icon').style.color = '#16a34a';
            document.getElementById('icon-inner').className = 'fas fa-check-circle';
        }, 2000);
    }
</script>

<?php include('includes/footer.php'); ?>
