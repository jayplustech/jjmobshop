<?php
require('includes/db.php');
session_start();

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
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
            <a href="login.php" class="auth-tab">Login</a>
            <a href="register.php" class="auth-tab active">Register</a>
        </div>

        <form method="POST" action="">
            <?php if(isset($error)): ?>
                <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:6px; font-size:14px; text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
                <span class="form-error-text">Must be at least 6 characters</span>
            </div>
            
            <button type="submit" name="register" class="btn btn-auth">Create Account</button>
        </form>

        <div class="auth-footer">
            <p>By registering, you agree to our <a href="#">Terms & Conditions</a></p>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
