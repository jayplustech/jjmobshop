<?php
require('../includes/db.php');
include('../includes/admin_header.php');

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
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
    <form method="POST" action="" style="display: flex; gap: 10px;">
        <input type="text" name="name" placeholder="New Category Name" required style="flex: 1;">
        <button type="submit" name="add_category" class="btn">Add Category</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?php echo $cat['id']; ?></td>
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
