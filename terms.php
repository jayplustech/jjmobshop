<?php
require('includes/db.php');
session_start();
include('includes/header.php');

// Fetch terms from database
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'terms_and_conditions'");
$stmt->execute();
$terms_content = $stmt->fetchColumn();

// Fallback if not found
if (!$terms_content) {
    $terms_content = "<h2>Terms and Conditions</h2><p>Our terms and conditions are being updated. Please check back later.</p>";
}
?>

<div class="terms-container" style="max-width: 800px; margin: 50px auto; padding: 40px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px;">Terms and Conditions</h1>
    
    <div style="line-height: 1.8; color: #444; font-size: 1.1rem;">
        <?php echo $terms_content; ?>

        <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
            <p style="font-style: italic; color: #888;">Last updated: <?php echo date('F d, Y'); ?></p>
            <a href="register.php" class="btn btn-primary" style="background: var(--primary-color); color: #fff; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Back to Registration</a>
        </div>
    </div>
</div>

<style>
    .terms-container {
        animation: fadeIn 0.8s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        filter: brightness(1.1);
    }
</style>

<?php include('includes/footer.php'); ?>
