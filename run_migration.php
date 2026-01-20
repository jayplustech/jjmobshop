<?php
require('includes/db.php');

echo "Starting migration...\n";

// Add payment_method
try {
    echo "Adding 'payment_method' column...\n";
    $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cod' AFTER address");
    echo "Done.\n";
} catch (PDOException $e) {
    echo "Note: " . $e->getMessage() . "\n";
}

// Add transaction_id
try {
    echo "Adding 'transaction_id' column...\n";
    $pdo->exec("ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(255) DEFAULT NULL AFTER payment_method");
    echo "Done.\n";
} catch (PDOException $e) {
    echo "Note: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";
?>
