<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))); // Simple slug generation
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $location = $_POST['location'];

    // Image Upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Simple check
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = basename($_FILES["image"]["name"]);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO tours (title, slug, description, price, duration, location, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $slug, $description, $price, $duration, $location, $image_url])) {
        header("Location: tours.php");
        exit;
    } else {
        $error = "Tur eklenirken bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Tur Ekle - Admin Panel</title>
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

        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
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
            <h1 style="text-align: center; margin-bottom: 2rem;">Yeni Tur Ekle</h1>

            <div class="form-card">
                <?php if (isset($error)): ?>
                    <div
                        style="background: #ffecec; color: red; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Tur Başlığı</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="row" style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Fiyat (€)</label>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Süre (Örn: 3 Gün)</label>
                            <input type="text" name="duration" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lokasyon (Örn: İtalya)</label>
                        <input type="text" name="location" required>
                    </div>

                    <div class="form-group">
                        <label>Resim Yükle</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>

                    <div class="form-group">
                        <label>Açıklama</label>
                        <textarea name="description" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1rem;">Kaydet</button>
                </form>
            </div>
        </main>
    </div>

</body>

</html>