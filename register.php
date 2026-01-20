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

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $accept_terms = isset($_POST['accept_terms']);

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!$accept_terms) {
        $error = "You must accept the Terms & Conditions to register.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<?php include('includes/header.php'); ?>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-tabs">
            <a href="login.php" class="auth-tab"><?php echo $txt['login']; ?></a>
            <a href="register.php" class="auth-tab active"><?php echo $txt['register']; ?></a>
        </div>

        <form method="POST" action="">
            <?php if(isset($error)): ?>
                <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:6px; font-size:14px; text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label><?php echo $txt['name_label']; ?></label>
                <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label><?php echo $txt['email_label']; ?></label>
                <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="example@gmail.com" required>
            </div>
            <div class="form-group">
                <label><?php echo $txt['password_label']; ?></label>
                <input type="password" name="password" placeholder="Create a password" required>
                <span class="form-error-text"><?php echo ($lang == 'sw' ? 'Isipungue herufi 6' : 'Must be at least 6 characters'); ?></span>
            </div>

            <div class="form-group checkbox-group" style="display: flex; align-items: flex-start; gap: 10px; margin-top: 20px;">
                <input type="checkbox" name="accept_terms" id="accept_terms" style="width: auto; margin-top: 5px;" required>
                <label for="accept_terms" style="font-size: 14px; color: #666; font-weight: 400; line-height: 1.4; cursor: pointer;">
                    <?php echo $txt['accept_terms']; ?> <a href="terms.php" target="_blank" style="color: var(--color-primary); font-weight: 600; text-decoration: underline;">Terms & Conditions</a>
                </label>
            </div>
            
            <button type="submit" name="register" class="btn btn-auth" style="margin-top: 10px;"><?php echo $txt['register_title']; ?></button>
        </form>

        <div class="auth-footer" style="margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
            <p style="font-size: 14px; color: #666;"><?php echo $txt['already_have_acc']; ?> <a href="login.php" style="color: var(--color-primary); font-weight: 600;"><?php echo $txt['login']; ?></a></p>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
