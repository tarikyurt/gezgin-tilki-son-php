<?php
require_once 'includes/db.php';

try {
    // Modify 'hotel_stars' column to VARCHAR(50)
    $pdo->exec("ALTER TABLE tour_dates MODIFY COLUMN hotel_stars VARCHAR(50) DEFAULT ''");
    echo "Column 'hotel_stars' modified to VARCHAR(50) successfully.<br>";
} catch (PDOException $e) {
    echo "Error modifying column 'hotel_stars': " . $e->getMessage() . "<br>";
}
?>