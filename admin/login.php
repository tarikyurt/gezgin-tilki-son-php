<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'viewer';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #111827 0%, #2B2D42 50%, #D90429 100%);
        }

        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            font-size: 1.8rem;
        }

        .login-card .subtitle {
            text-align: center;
            color: #999;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .login-card .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .login-card label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .login-card input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .login-card input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .login-card .input-icon {
            position: absolute;
            left: 1rem;
            top: 2.7rem;
            color: #aaa;
        }

        .login-card button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #D90429 0%, #EF233C 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-card button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(217, 4, 41, 0.4);
        }

        .error-msg {
            background: #ffecec;
            color: #c62828;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <img src="../assets/img/logo.png" alt="Gezgin Tilki" style="height: 60px; margin: 0 auto 0.5rem;">
        <p class="subtitle">Yönetim Paneli Girişi</p>

        <?php if (isset($error)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <i class="fa-solid fa-user input-icon"></i>
                <input type="text" name="username" placeholder="Kullanıcı adınızı girin" required>
            </div>
            <div class="form-group">
                <label>Şifre</label>
                <i class="fa-solid fa-lock input-icon"></i>
                <input type="password" name="password" placeholder="Şifrenizi girin" required>
            </div>
            <button type="submit"><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap</button>
        </form>
    </div>

</body>

</html>