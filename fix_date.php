<?php
require_once 'includes/db.php';

// Find tour by title part
$stmt = $pdo->prepare("SELECT id FROM tours WHERE title LIKE ? LIMIT 1");
$stmt->execute(['%DENENE%']);
$tour = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tour) {
    // Update all past dates for this tour to be +1 month from today
    $newStartDate = date('Y-m-d', strtotime('+1 month'));
    $newEndDate = date('Y-m-d', strtotime('+1 month +4 days'));

    $updateStmt = $pdo->prepare("UPDATE tour_dates SET start_date = ?, end_date = ? WHERE tour_id = ?");
    $updateStmt->execute([$newStartDate, $newEndDate, $tour['id']]);

    echo "Updated dates for Tour ID " . $tour['id'] . " to start on " . $newStartDate;
} else {
    echo "Tour not found.";
}
?>