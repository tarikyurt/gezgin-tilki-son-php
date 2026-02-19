<?php
require_once '../includes/auth.php';
requireRole(['admin']);
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: tours.php");
exit;
?>