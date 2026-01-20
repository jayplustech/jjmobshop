<?php
require('includes/db.php');

echo "Starting migration: site_settings...\n";

try {
    // Create settings table
    $sql = "CREATE TABLE IF NOT EXISTS `settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` LONGTEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'settings' created or already exists.\n";

    // Insert default terms and conditions
    $default_terms = "<h2>Welcome to JJ.MOBISHOP</h2><p>These terms and conditions outline the rules and regulations for the use of JJ.MOBISHOP's Website.</p><h3>1. Acceptance of Terms</h3><p>By accessing this website we assume you accept these terms and conditions. Do not continue to use JJ.MOBISHOP if you do not agree to take all of the terms and conditions stated on this page.</p><h3>2. Products and Pricing</h3><p>We reserve the right to change our prices at any time without further notice.</p><h3>3. Governing Law</h3><p>These terms and conditions are governed by and construed in accordance with the laws of Tanzania.</p>";

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('terms_and_conditions', ?)");
    $stmt->execute([$default_terms]);
    echo "Default Terms and Conditions inserted.\n";

} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}

echo "Migration finished.\n";
?>
