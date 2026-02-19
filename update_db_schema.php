<?php
require_once 'includes/db.php';

try {
    // Add 'currency' column if not exists
    $pdo->exec("ALTER TABLE tour_dates ADD COLUMN currency VARCHAR(3) DEFAULT 'EUR'");
    echo "Column 'currency' added successfully.<br>";
} catch (PDOException $e) {
    echo "Column 'currency' might already exist or error: " . $e->getMessage() . "<br>";
}

try {
    // Add 'hotel_stars' column if not exists
    $pdo->exec("ALTER TABLE tour_dates ADD COLUMN hotel_stars INT DEFAULT 0");
    echo "Column 'hotel_stars' added successfully.<br>";
} catch (PDOException $e) {
    echo "Column 'hotel_stars' might already exist or error: " . $e->getMessage() . "<br>";
}

echo "Database schema update completed.";
?>