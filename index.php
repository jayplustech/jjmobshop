<?php
require('includes/db.php');
include('includes/header.php');

// Logic for Category Filter, Search & Sorting
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['search'] ?? '';
$cat_id = $_GET['category'] ?? null;

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($cat_id) {
    $sql .= " AND category_id = ?";
    $params[] = $cat_id;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Sorting logic
if ($sort === 'price_asc') $sql .= " ORDER BY price ASC";
elseif ($sort === 'price_desc') $sql .= " ORDER BY price DESC";
else $sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
// Fetch all settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Hero Data
$hero_title = $settings['hero_title'] ?? 'Find Your Next <br><span>Smartphone</span>';
$hero_subtitle = $settings['hero_subtitle'] ?? 'Best Deals on Latest Models. Compare prices and get the best value for your money today.';
$hero_media_type = $settings['hero_media_type'] ?? 'image';

// Hero Media Logic: Prioritize latest product image
$latestProductStmt = $pdo->query("SELECT image FROM products WHERE image IS NOT NULL AND image != '' ORDER BY id DESC LIMIT 1");
$latestProductImage = $latestProductStmt->fetchColumn();

if ($latestProductImage) {
    $hero_media_url = $latestProductImage;
    $hero_media_type = 'image'; // Force image display for products
} else {
    $hero_media_type = $settings['hero_media_type'] ?? 'image';
    $hero_media_url = $settings['hero_media_url'] ?: 'assets/images/samsung s24 ultra.jpg';
}
?>

<!-- Hero Section (Show only on homepage/no category) -->
<?php if(!isset($_GET['category'])): ?>
<section class="hero">
    <?php if($hero_media_type === 'video'): ?>
        <video autoplay muted loop playsinline class="hero-video">
            <source src="<?php echo $hero_media_url; ?>" type="video/mp4">
        </video>
    <?php endif; ?>

    <div class="container hero-overlap">
        <div class="hero-content">
            <h1><?php echo $hero_title; ?></h1>
            <p><?php echo htmlspecialchars($hero_subtitle); ?></p>
            <a href="#shop" class="btn btn-primary"><?php echo $lang == 'sw' ? 'Nunua Sasa' : 'Shop Now'; ?></a>
        </div>
        
        <?php if($hero_media_type === 'image'): ?>
        <div class="hero-image">
            <img src="<?php echo $hero_media_url; ?>" alt="Hero Image">
        </div>
        <?php endif; ?>
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
        <h2 class="section-title">
            <?php 
                if ($search) echo lang_replace('search_results', ['query' => htmlspecialchars($search)]);
                elseif ($cat_id) {
                    $stmtCat = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                    $stmtCat->execute([$cat_id]);
                    $catName = $stmtCat->fetchColumn();
                    echo lang_replace('category_label', ['name' => htmlspecialchars($catName)]);
                }
                else echo $txt['latest_models']; 
            ?>
        </h2>
        
        <!-- Sorting -->
        <form method="GET" style="display:flex; align-items:center; gap:10px;">
            <?php if ($cat_id): ?><input type="hidden" name="category" value="<?php echo $cat_id; ?>"><?php endif; ?>
            <?php if ($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
            
            <span style="color:#666;"><?php echo $txt['sort_by']; ?></span>
            <select name="sort" onchange="this.form.submit()" style="padding: 8px; border-radius: 20px; border: 1px solid #ddd;">
                <option value="newest" <?php if($sort=='newest') echo 'selected'; ?>><?php echo $txt['newest']; ?></option>
                <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>><?php echo $txt['price_low_high']; ?></option>
                <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>><?php echo $txt['price_high_low']; ?></option>
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
                <span class="product-price">TZS <?php echo number_format($product['price'], 2); ?></span>
                <button class="btn btn-card" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>, '<?php echo $product['image']; ?>')">
                    <?php echo $txt['add_to_cart']; ?>
                </button>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; width:100%; padding: 50px 0;">
                <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                <p style="color: #777;"><?php echo $txt['no_products']; ?></p>
                <a href="index.php" class="btn btn-secondary" style="margin-top: 10px;"><?php echo $txt['show_all']; ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
