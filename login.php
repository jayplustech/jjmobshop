<?php
require('includes/db.php');
session_start();

// Auto redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Secure Session: Prevent Session Fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-tabs">
            <a href="login.php" class="auth-tab active"><?php echo $txt['login']; ?></a>
            <a href="register.php" class="auth-tab"><?php echo $txt['register']; ?></a>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
            <p class="success" style="font-size:14px; text-align:center; padding:10px; background:#d4edda; color:#155724; border-radius:6px; margin-bottom:20px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php if(isset($error)): ?>
                <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:6px; font-size:14px; text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label><?php echo $txt['email_label']; ?></label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>
            <div class="form-group">
                <label><?php echo $txt['password_label']; ?></label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-auth"><?php echo $txt['login_title']; ?></button>
        </form>
        
        <div class="auth-footer">
            <p><?php echo $txt['forgot_password']; ?> <a href="#">Reset here</a></p>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
