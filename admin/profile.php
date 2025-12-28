<?php
require('../includes/db.php');
include('../includes/admin_header.php');

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        // Update logic
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $user_id]);
        }
        
        if ($result) {
            $message = "Profile updated successfully!";
            $_SESSION['user_name'] = $name; // Optional: Update session if used elsewhere
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<h2>Admin Profile</h2>

<?php if($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); max-width: 500px;">
    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" placeholder="********">
        </div>
        
        <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">Update Profile</button>
    </form>
</div>

<?php include('../includes/admin_footer.php'); ?>
