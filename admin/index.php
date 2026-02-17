<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gezgin Tilki</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--secondary-color);
            color: white;
            padding: 2rem 0;
            flex-shrink: 0;
        }

        .sidebar a {
            display: block;
            padding: 1rem 2rem;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--bg-light);
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <aside class="sidebar">
            <h3 style="text-align: center; margin-bottom: 2rem;">Gezgin Tilki</h3>
            <a href="index.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="tours.php"><i class="fa-solid fa-plane"></i> Turlar</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Rezervasyonlar</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Hoşgeldin,
                <?php echo $_SESSION['username']; ?>
            </h1>

            <div class="stats-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-plane"></i></div>
                    <div>
                        <h3 style="margin: 0;">12</h3>
                        <p style="margin: 0; color: var(--text-light);">Aktif Tur</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <h3 style="margin: 0;">5</h3>
                        <p style="margin: 0; color: var(--text-light);">Yeni Rezervasyon</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <h3 style="margin: 0;">3</h3>
                        <p style="margin: 0; color: var(--text-light);">Yeni Mesaj</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>