<?php
require_once '../../includes/auth.php';
requireLogin();
require_once '../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$status = trim($_POST['status'] ?? '');

$valid_statuses = ['pending', 'confirmed', 'cancelled'];

if (!$booking_id || !in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz parametreler.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Durum güncellendi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kayıt bulunamadı veya durum zaten aynı.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
