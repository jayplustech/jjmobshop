<?php
require('../includes/db.php');
include('../includes/admin_header.php');

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $imagePath = '';

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $imagePath = "uploads/" . $fileName;
        }
    }

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
        $stmt->execute([$name, $imagePath]);
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit();
}

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<h2>Manage Categories</h2>

<div style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px;">
    <form method="POST" action="" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
        <input type="text" name="name" placeholder="New Category Name" required style="flex: 1;">
        <input type="file" name="image" style="flex: 1;">
        <button type="submit" name="add_category" class="btn">Add Category</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?php echo $cat['id']; ?></td>
            <td><?php if ($cat['image']): ?>
                    <img src="../<?php echo $cat['image']; ?>" width="40" style="border-radius: 4px;">
                <?php else: ?>
                    <i class="fas fa-folder" style="font-size: 1.5rem; color: #ccc;"></i>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($cat['name']); ?></td>
            <td><?php echo $cat['created_at']; ?></td>
            <td>
                <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('../includes/admin_footer.php'); ?>
