<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';

// Fetch tours
$stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC");
$tours = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turlar - Admin Panel</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--secondary-color);
            color: white;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 5px;
            color: white;
            margin-right: 0.5rem;
            font-size: 0.8rem;
        }

        .edit-btn {
            background: #3498db;
        }

        .delete-btn {
            background: #e74c3c;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <aside class="sidebar">
            <h3 style="text-align: center; margin-bottom: 2rem;">Gezgin Tilki</h3>
            <a href="index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="tours.php" class="active"><i class="fa-solid fa-plane"></i> Turlar</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Rezervasyonlar</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Turlar</h1>
                <a href="tour-add.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Yeni Tur Ekle</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Başlık</th>
                        <th>Fiyat</th>
                        <th>Süre</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $tour): ?>
                        <tr>
                            <td>
                                <?php echo $tour['id']; ?>
                            </td>
                            <td><img src="../uploads/<?php echo $tour['image_url']; ?>" alt="Tour Image"
                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td>
                                <?php echo $tour['title']; ?>
                            </td>
                            <td>
                                <?php echo $tour['price']; ?> €
                            </td>
                            <td>
                                <?php echo $tour['duration']; ?>
                            </td>
                            <td>
                                <a href="tour-edit.php?id=<?php echo $tour['id']; ?>" class="action-btn edit-btn"><i
                                        class="fa-solid fa-pen"></i> Düzenle</a>
                                <a href="tour-delete.php?id=<?php echo $tour['id']; ?>" class="action-btn delete-btn"
                                    onclick="return confirm('Bu turu silmek istediğinize emin misiniz?')"><i
                                        class="fa-solid fa-trash"></i> Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

</body>

</html>