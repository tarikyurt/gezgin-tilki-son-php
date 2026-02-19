<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM tour_dates WHERE start_date >= CURDATE()");
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total upcoming dates found: " . count($dates) . "\n";
    foreach ($dates as $date) {
        echo "Tour ID: " . $date['tour_id'] . " | Date: " . $date['start_date'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>