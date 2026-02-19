<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

try {
    // Search in title and location
    $stmt = $pdo->prepare("SELECT id, title, slug, location, image_url FROM tours WHERE title LIKE ? OR location LIKE ? LIMIT 5");
    $stmt->execute(["%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>