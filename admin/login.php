<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === 'admin123') { // Simple password check for now as discussed in SQL
        // In real app use: password_verify($password, $user['password'])
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Giriş başarısız. Kullanıcı adı veya şifre yanlış.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gezgin Tilki - Admin Giriş</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: var(--bg-light);
        }

        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h2 style="text-align: center; margin-bottom: 2rem; color: var(--primary-color);">Admin Giriş</h2>
        <?php if (isset($error)): ?>
            <div
                style="background: #ffecec; color: red; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Kullanıcı Adı</label>
                <input type="text" name="username" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Şifre</label>
                <input type="password" name="password" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 0.8rem;">Giriş Yap</button>
        </form>
        <p style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: #888;">Default: admin / admin123</p>
    </div>

</body>

</html>