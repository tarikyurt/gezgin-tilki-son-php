<?php
require_once 'includes/db.php';

// Disable caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get a tour ID (e.g. 1)
$stmt = $pdo->query("SELECT id FROM tours LIMIT 1");
$tour = $stmt->fetch();

if ($tour) {
    try {
        // Calculate dates in PHP
        $startDate = date('Y-m-d', strtotime('+1 month'));
        $endDate = date('Y-m-d', strtotime('+1 month +3 days'));

        $sql = "INSERT INTO tour_dates (tour_id, start_date, end_date, price, quota) VALUES (?, ?, ?, 1000, 20)";
        $pdo->prepare($sql)->execute([$tour['id'], $startDate, $endDate]);

        echo "SUCCESS: Added sample future date for Tour ID: " . $tour['id'] . "<br>";
        echo "Start: " . $startDate . " | End: " . $endDate;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No tours found to add date to.";
}
?>