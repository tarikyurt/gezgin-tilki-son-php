<?php
require_once 'includes/db.php';

// Find tour by title part
$stmt = $pdo->prepare("SELECT id, title FROM tours WHERE title LIKE ?");
$stmt->execute(['%DENENE%']);
$tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current Date: " . date('Y-m-d') . "\n\n";

if ($tours) {
    foreach ($tours as $tour) {
        echo "Tour found: " . $tour['title'] . " (ID: " . $tour['id'] . ")\n";

        // Check dates
        $dStmt = $pdo->prepare("SELECT * FROM tour_dates WHERE tour_id = ?");
        $dStmt->execute([$tour['id']]);
        $dates = $dStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dates) {
            foreach ($dates as $d) {
                echo "  - Date in DB: " . $d['start_date'] . " to " . $d['end_date'] . "\n";
                if ($d['start_date'] < date('Y-m-d')) {
                    echo "    -> This date is in the PAST and will be hidden by the query.\n";
                } else {
                    echo "    -> This date is in the FUTURE and should be visible.\n";
                }
            }
        } else {
            echo "  - No dates found for this tour.\n";
        }
    }
} else {
    echo "No tour found with title containing 'DENENE'.";
}
?>