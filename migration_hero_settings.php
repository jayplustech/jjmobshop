<?php
require('includes/db.php');

echo "Updating site_settings with hero content...\n";

try {
    $hero_settings = [
        ['hero_title', 'Find Your Next <br><span>Smartphone</span>'],
        ['hero_subtitle', 'Best Deals on Latest Models. Compare prices and get the best value for your money today.'],
        ['hero_media_type', 'image'], // 'image' or 'video'
        ['hero_media_url', 'assets/images/samsung s24 ultra.jpg']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($hero_settings as $setting) {
        $stmt->execute($setting);
    }
    echo "Hero settings inserted or already exist.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
?>
