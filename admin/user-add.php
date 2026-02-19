<?php
$current_page = 'users';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tüm alanları doldurunuz.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = "Bu kullanıcı adı zaten mevcut.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            header("Location: users.php?success=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Kullanıcı - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .role-cards {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .role-card {
            flex: 1;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 1.25rem 1rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.25s;
        }

        .role-card:hover {
            border-color: #D90429;
        }

        .role-card.selected {
            border-color: #D90429;
            background: #FEF2F2;
        }

        .role-card input[type="radio"] {
            display: none;
        }

        .role-card .role-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .role-card .role-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
        }

        .role-card .role-desc {
            font-size: 0.75rem;
            color: #9CA3AF;
            margin-top: 0.3rem;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Yeni Kullanıcı Ekle</h1>
                    <div class="breadcrumb">Dashboard / Kullanıcılar / Yeni</div>
                </div>
                <a href="users.php" style="color: #6B7280; text-decoration: none; font-weight: 500; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Kullanıcılara Dön
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-card" style="max-width: 600px;">
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fa-solid fa-user" style="color: #9CA3AF;"></i> Kullanıcı Adı</label>
                        <input type="text" name="username" placeholder="Kullanıcı adı girin"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-envelope" style="color: #9CA3AF;"></i> E-Posta</label>
                        <input type="email" name="email" placeholder="E-posta adresi girin"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-lock" style="color: #9CA3AF;"></i> Şifre</label>
                        <input type="password" name="password" placeholder="Güçlü bir şifre belirleyin" required
                            minlength="6">
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-shield-halved" style="color: #9CA3AF;"></i> Rol Seçin</label>
                        <div class="role-cards">
                            <label class="role-card" onclick="selectRole(this)">
                                <input type="radio" name="role" value="admin">
                                <div class="role-icon">🛡️</div>
                                <div class="role-name">Yönetici</div>
                                <div class="role-desc">Tam yetki</div>
                            </label>
                            <label class="role-card selected" onclick="selectRole(this)">
                                <input type="radio" name="role" value="editor" checked>
                                <div class="role-icon">✏️</div>
                                <div class="role-name">Editör</div>
                                <div class="role-desc">Ekle / Düzenle</div>
                            </label>
                            <label class="role-card" onclick="selectRole(this)">
                                <input type="radio" name="role" value="viewer">
                                <div class="role-icon">👁️</div>
                                <div class="role-name">İzleyici</div>
                                <div class="role-desc">Sadece görüntüle</div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1rem; margin-top: 0.5rem; justify-content: center;">
                        <i class="fa-solid fa-user-plus"></i> Kullanıcı Oluştur
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function selectRole(el) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
        }
    </script>

</body>

</html>