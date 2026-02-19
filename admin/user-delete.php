<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole(['admin']);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Can't delete yourself
    if ($id == $_SESSION['user_id']) {
        header("Location: users.php?error=1");
        exit;
    }

    // Can't delete the last admin
    $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $targetUser = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $targetUser->execute([$id]);
    $target = $targetUser->fetch();

    if ($target && $target['role'] === 'admin' && $adminCount <= 1) {
        header("Location: users.php?error=2");
        exit;
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: users.php?success=2");
exit;
?>