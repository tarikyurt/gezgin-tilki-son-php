<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: tours.php");
exit;
?>