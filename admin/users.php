<?php
$current_page = 'users';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole(['admin']);

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Kullanıcı Yönetimi</h1>
                    <div class="breadcrumb">Dashboard / Kullanıcılar</div>
                </div>
                <a href="user-add.php" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Yeni Kullanıcı</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?php
                    $msgs = ['1' => 'Kullanıcı başarıyla oluşturuldu!', '2' => 'Kullanıcı başarıyla silindi!'];
                    echo $msgs[$_GET['success']] ?? 'İşlem başarılı!';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php
                    $errors = ['1' => 'Kendinizi silemezsiniz!', '2' => 'Son admin kullanıcı silinemez!'];
                    echo $errors[$_GET['error']] ?? 'Bir hata oluştu!';
                    ?>
                </div>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı</th>
                        <th>E-Posta</th>
                        <th>Rol</th>
                        <th>Oluşturma Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div
                                        style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #D90429, #EF233C); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                                        <?php echo strtoupper(mb_substr($u['username'], 0, 1)); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo getRoleBadge($u['role'] ?? 'viewer'); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="user-delete.php?id=<?php echo $u['id']; ?>" class="action-btn delete-btn"
                                        onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">
                                        <i class="fa-solid fa-trash"></i> Sil
                                    </a>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 0.8rem; font-style: italic;">Siz</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

</body>

</html>