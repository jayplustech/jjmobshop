<?php
require('includes/db.php');
include('includes/header.php');

$categoryId = $_GET['category'] ?? null;

if ($categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $products = $stmt->fetchAll();
    
    // Get category name for title
    $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->execute([$categoryId]);
    $catName = $catStmt->fetchColumn();
    $title = $catName . " Products";
} else {
    $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
    $title = "All Products";
}
?>

<h2><?php echo htmlspecialchars($title); ?></h2>

<div class="product-grid">
    <?php if(count($products) > 0): ?>
        <?php foreach($products as $p): ?>
        <div class="product-card">
            <?php if($p['image']): ?>
                <img src="<?php echo $p['image']; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
            <?php else: ?>
                <div style="height:200px; background:#eee; display:flex; align-items:center; justify-content:center;">No Image</div>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            <p class="price">$<?php echo $p['price']; ?></p>
            <p class="desc" style="font-size:0.9rem; color:#666; margin-bottom:10px; height: 3.2em; overflow: hidden;"><?php echo htmlspecialchars($p['description']); ?></p>
            <button class="btn" onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', <?php echo $p['price']; ?>, '<?php echo $p['image']; ?>')">Add to Cart</button>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>
