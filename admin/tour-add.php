<?php
$current_page = 'tours';
require_once '../includes/auth.php';
requireRole(['admin', 'editor']);
require_once '../includes/db.php';

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Insert Basic Tour Info
        $title = $_POST['title'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $description = $_POST['description'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $location = $_POST['location'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // Image Upload
        $image_url = 'default.jpg'; // Default image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/";
            if (!file_exists($target_dir))
                mkdir($target_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO tours (title, slug, description, price, currency, duration, location, image_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $price, $_POST['currency'], $duration, $location, $image_url, $is_featured]);
        $tour_id = $pdo->lastInsertId();

        // 2. Insert Itinerary
        if (isset($_POST['itinerary_title'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_itineraries (tour_id, day_number, title, description) VALUES (?, ?, ?, ?)");
            foreach ($_POST['itinerary_title'] as $key => $val) {
                if (!empty($val)) {
                    $desc = $_POST['itinerary_desc'][$key];
                    $day = $key + 1; // Auto-number days
                    $stmt->execute([$tour_id, $day, $val, $desc]);
                }
            }
        }

        // 3. Insert Inclusions
        // 3. Insert Inclusions

        // Process Included Items
        if (isset($_POST['inc_included'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_inclusions (tour_id, item, is_included) VALUES (?, ?, 1)");
            foreach ($_POST['inc_included'] as $val) {
                if (!empty($val)) {
                    $stmt->execute([$tour_id, $val]);
                }
            }
        }

        // Process Excluded Items
        if (isset($_POST['inc_excluded'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_inclusions (tour_id, item, is_included) VALUES (?, ?, 0)");
            foreach ($_POST['inc_excluded'] as $val) {
                if (!empty($val)) {
                    $stmt->execute([$tour_id, $val]);
                }
            }
        }

        // 4. Insert Key Features
        if (isset($_POST['feature_title'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_key_features (tour_id, icon, title, subtitle) VALUES (?, ?, ?, ?)");
            foreach ($_POST['feature_title'] as $key => $val) {
                if (!empty($val)) {
                    $icon = $_POST['feature_icon'][$key];
                    $sub = $_POST['feature_subtitle'][$key];
                    $stmt->execute([$tour_id, $icon, $val, $sub]);
                }
            }
        }

        // 5. Insert Dates
        if (isset($_POST['date_start'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_dates (tour_id, start_date, end_date, price, quota) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['date_start'] as $key => $val) {
                if (!empty($val)) {
                    $end = $_POST['date_end'][$key];
                    $dPrice = $_POST['date_price'][$key];
                    $quota = $_POST['date_quota'][$key];
                    $stmt->execute([$tour_id, $val, $end, $dPrice, $quota]);
                }
            }
        }

        $pdo->commit();
        header("Location: tours.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Hata: " . $e->getMessage();
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Page-specific overrides only */
    </style>
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Yeni Tur Ekle</h1>
                    <div class="breadcrumb">Dashboard / Turlar / Yeni</div>
                </div>
                <a href="tours.php" style="color: #6B7280; text-decoration: none; font-weight: 500; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Turlara Dön
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div style="background: #ffecec; color: #c62828; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button type="button" class="tab-btn active" onclick="openTab('general')">Genel Bilgiler</button>
                    <button type="button" class="tab-btn" onclick="openTab('itinerary')">Tur Programı</button>
                    <button type="button" class="tab-btn" onclick="openTab('inclusions')">Hizmetler</button>
                    <button type="button" class="tab-btn" onclick="openTab('features')">Öne Çıkanlar</button>
                    <button type="button" class="tab-btn" onclick="openTab('dates')">Tarih & Fiyat</button>
                </div>

                <div class="form-card">
                    <!-- 1. GENERAL TAB -->
                    <div id="general" class="tab-content active">
                        <div class="row" style="display: flex; gap: 2rem;">
                            <div style="flex: 2;">
                                <div class="form-group">
                                    <label>Tur Başlığı</label>
                                    <input type="text" name="title" required>
                                </div>
                                <div class="row" style="display: flex; gap: 1rem;">
                                    <div class="form-group" style="flex: 1;">
                                        <label>Fiyat</label>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <select name="currency" style="width: 80px;">
                                                <option value="EUR">€</option>
                                                <option value="USD">$</option>
                                                <option value="GBP">£</option>
                                                <option value="TRY">₺</option>
                                            </select>
                                            <input type="number" name="price" step="0.01" required style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                        <label>Süre</label>
                                        <input type="text" name="duration" placeholder="Örn: 3 Gün" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Lokasyon</label>
                                    <input type="text" name="location" placeholder="Örn: İtalya" required>
                                </div>
                                <div class="form-group">
                                    <label>Öne Çıkarılan Tur?</label>
                                    <input type="checkbox" name="is_featured" style="width: auto;"> Anasayfada göster
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <div class="form-group">
                                    <label>Kapak Resmi</label>
                                    <input type="file" name="image" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Genel Açıklama</label>
                            <textarea name="description" rows="5" required></textarea>
                        </div>
                    </div>

                    <!-- 2. ITINERARY TAB -->
                    <div id="itinerary" class="tab-content">
                        <div id="itinerary-container">
                            <div class="dynamic-row">
                                <div style="width: 50px; font-weight: bold; padding-top: 0.5rem; text-align: center;">1.
                                    Gün</div>
                                <div style="flex: 1;">
                                    <input type="text" name="itinerary_title[]"
                                        placeholder="Gün Başlığı (Örn: İstanbul'a Varış)" required
                                        style="margin-bottom: 0.5rem;">
                                    <textarea name="itinerary_desc[]" rows="2"
                                        placeholder="Detaylı açıklama..."></textarea>
                                </div>
                                <button type="button" class="btn-remove"
                                    onclick="this.parentElement.remove()">Sil</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addItineraryRow()">+ Yeni Gün Ekle</button>
                    </div>

                    <!-- 3. INCLUSIONS TAB -->
                    <div id="inclusions" class="tab-content">
                        <div class="row" style="display: flex; gap: 2rem;">
                            <!-- Included Column -->
                            <div
                                style="flex: 1; border: 1px solid #c8e6c9; border-radius: 8px; padding: 1rem; background: #f1f8e9;">
                                <h4
                                    style="color: #2e7d32; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-check-circle"></i> Fiyata Dahil Olanlar
                                </h4>
                                <div id="included-container">
                                    <!-- Included items will be added here -->
                                </div>
                                <button type="button" class="btn-add"
                                    style="background: #a5d6a7; color: #1b5e20; width: 100%; margin-top: 1rem;"
                                    onclick="addInclusionRow(1)">+ Dahil Hizmet Ekle</button>
                            </div>

                            <!-- Excluded Column -->
                            <div
                                style="flex: 1; border: 1px solid #ffcdd2; border-radius: 8px; padding: 1rem; background: #ffebee;">
                                <h4
                                    style="color: #c62828; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-times-circle"></i> Fiyata Dahil Olmayanlar
                                </h4>
                                <div id="excluded-container">
                                    <!-- Excluded items will be added here -->
                                </div>
                                <button type="button" class="btn-add"
                                    style="background: #ef9a9a; color: #b71c1c; width: 100%; margin-top: 1rem;"
                                    onclick="addInclusionRow(0)">+ Dahil Olmayan Hizmet Ekle</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. KEY FEATURES TAB -->
                    <div id="features" class="tab-content">
                        <h3>Öne Çıkan Özellikler</h3>
                        <p style="margin-bottom: 1rem; color: #666;">Tur detay sayfasında görünecek 4 temel özelliği
                            buraya ekleyin (Uçuş, Otel, vb).</p>
                        <div id="features-container">
                            <!-- Features will be added here -->
                        </div>
                        <button type="button" class="btn-add" onclick="addFeatureRow()">+ Özellik Ekle</button>
                    </div>

                    <!-- 5. DATES TAB -->
                    <div id="dates" class="tab-content">
                        <div id="dates-container">
                            <div class="dynamic-row">
                                <div>
                                    <label style="font-size: 0.8rem;">Başlangıç</label>
                                    <input type="date" name="date_start[]">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem;">Bitiş</label>
                                    <input type="date" name="date_end[]">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem;">Özel Fiyat (€)</label>
                                    <input type="number" name="date_price[]" value="0" step="0.01">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem;">Kontenjan</label>
                                    <input type="number" name="date_quota[]" value="20" style="width: 80px;">
                                </div>
                                <button type="button" class="btn-remove" style="margin-top: 1.5rem;"
                                    onclick="this.parentElement.remove()">Sil</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addDateRow()">+ Yeni Tarih Ekle</button>
                    </div>

                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 2rem; text-align: right;">
                        <button type="submit" class="btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Turu
                            Oluştur</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        function openTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function addItineraryRow() {
            const container = document.getElementById('itinerary-container');
            const dayCount = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.innerHTML = `
        <div style="width: 50px; font-weight: bold; padding-top: 0.5rem; text-align: center;">\${dayCount}. Gün</div>
        <div style="flex: 1;">
            <input type="text" name="itinerary_title[]" placeholder="Gün Başlığı" required style="margin-bottom: 0.5rem;">
            <textarea name="itinerary_desc[]" rows="2" placeholder="Detaylı açıklama..."></textarea>
        </div>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Sil</button>
    `;
            container.appendChild(div);
        }

        function addInclusionRow(type) {
            const containerId = type === 1 ? 'included-container' : 'excluded-container';
            const inputName = type === 1 ? 'inc_included[]' : 'inc_excluded[]';
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.style.background = 'white'; // White background for rows inside colored containers

            div.innerHTML = `
        <input type="text" name="${inputName}" placeholder="Hizmet adı" style="flex: 1;">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
    `;
            document.getElementById(containerId).appendChild(div);
        }

        function addFeatureRow() {
            const container = document.getElementById('features-container');
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.style.alignItems = 'flex-start';

            div.innerHTML = `
                <div style="width: 150px;">
                    <label style="font-size: 0.8rem;">İkon</label>
                    <select name="feature_icon[]" style="width: 100%; padding: 0.5rem;" onchange="this.nextElementSibling.className = this.value">
                        <option value="fa-solid fa-plane">✈️ Uçuş</option>
                        <option value="fa-solid fa-hotel">🏨 Otel</option>
                        <option value="fa-solid fa-utensils">🍽️ Yemek</option>
                        <option value="fa-solid fa-users">👥 Rehber</option>
                        <option value="fa-solid fa-bus">🚌 Transfer</option>
                        <option value="fa-solid fa-calendar-days">📅 Tarih</option>
                        <option value="fa-solid fa-ticket">🎟️ Bilet</option>
                        <option value="fa-solid fa-passport">🛂 Vize</option>
                        <option value="fa-solid fa-camera">📷 Fotoğraf</option>
                    </select>
                    <i class="fa-solid fa-plane" style="font-size: 1.5rem; color: var(--primary-color); display: block; text-align: center; margin-top: 0.5rem;"></i>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.8rem;">Başlık</label>
                    <input type="text" name="feature_title[]" placeholder="Örn: Uçuş Dahil" required style="margin-bottom: 0.5rem;">
                    
                    <label style="font-size: 0.8rem;">Alt Başlık</label>
                    <input type="text" name="feature_subtitle[]" placeholder="Örn: THY ile Gidiş-Dönüş">
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(div);
        }

        function addDateRow() {
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.innerHTML = `
        <div><label style="font-size: 0.8rem;">Başlangıç</label><input type="date" name="date_start[]"></div>
        <div><label style="font-size: 0.8rem;">Bitiş</label><input type="date" name="date_end[]"></div>
        <div><label style="font-size: 0.8rem;">Özel Fiyat (€)</label><input type="number" name="date_price[]" value="0" step="0.01"></div>
        <div><label style="font-size: 0.8rem;">Kontenjan</label><input type="number" name="date_quota[]" value="20" style="width: 80px;"></div>
        <button type="button" class="btn-remove" style="margin-top: 1.5rem;" onclick="this.parentElement.remove()">Sil</button>
    `;
            document.getElementById('dates-container').appendChild(div);
        }
    </script>

</body>

</html>