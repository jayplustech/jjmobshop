<?php
require('includes/db.php');
include('includes/header.php');

// Logic for Category Filter & Sorting
$sort = $_GET['sort'] ?? 'newest';
$sql = "SELECT * FROM products";
$params = [];

if (isset($_GET['category'])) {
    $cat_id = $_GET['category'];
    $sql .= " WHERE category_id = ?";
    $params[] = $cat_id;
}

if ($sort === 'price_asc') $sql .= " ORDER BY price ASC";
elseif ($sort === 'price_desc') $sql .= " ORDER BY price DESC";
else $sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Hero Section (Show only on homepage/no category) -->
<?php if(!isset($_GET['category'])): ?>
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your Next <br><span>Smartphone</span></h1>
            <p>Best Deals on Latest Models. Compare prices and get the best value for your money today.</p>
            <a href="#shop" class="btn btn-primary">Shop Now</a>
        </div>
        <div class="hero-image">
            <img src="assets/images/samsung s24 ultra.jpg" alt="Latest Smartphones">
        </div>
    </div>
</section>

<!-- Categories Section -->
<div class="container" style="margin-top: -40px; position: relative; z-index: 10;">
    <div class="category-grid">
        <?php foreach($cats as $cat): ?>
        <a href="index.php?category=<?php echo $cat['id']; ?>" class="category-card">
            <?php if ($cat['image']): ?>
                <img src="<?php echo $cat['image']; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" style="width: 30px; height: 30px; object-fit: contain; margin-bottom: 8px; display:block; margin-left:auto; margin-right:auto;">
            <?php else: ?>
                <i class="fas fa-mobile-alt" style="font-size: 2rem; margin-bottom: 10px; display:block;"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($cat['name']); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="container" id="shop" style="padding-top: 60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
        <h2 class="section-title">Latest <span>Models</span></h2>
        
        <!-- Sorting -->
        <form method="GET" style="display:flex; align-items:center; gap:10px;">
            <?php if(isset($_GET['category'])): ?>
                <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
            <?php endif; ?>
            <span style="color:#666;">Sort by:</span>
            <select name="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 20px; border: 1px solid #ddd;">
                <option value="newest" <?php if($sort=='newest') echo 'selected'; ?>>Newest</option>
                <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>>Price: Low to High</option>
                <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>>Price: High to Low</option>
            </select>
        </form>
    </div>

    <!-- Product Grid -->
    <div class="product-grid">
        <?php if(count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                <button class="btn btn-card" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>, '<?php echo $product['image']; ?>')">
                    Add to Cart
                </button>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; col-span:full;">No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
