<?php
$current_page = 'tours';
require_once '../includes/auth.php';
requireRole(['admin', 'editor']);
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: tours.php");
    exit;
}

$id = $_GET['id'];

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Update Basic Tour Info
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $location = $_POST['location'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // Image Upload
        $stmt = $pdo->prepare("SELECT image_url FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $current_tour = $stmt->fetch();
        $image_url = $current_tour['image_url'];

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

        $stmt = $pdo->prepare("UPDATE tours SET title = ?, description = ?, price = ?, currency = ?, duration = ?, location = ?, image_url = ?, is_featured = ? WHERE id = ?");
        $stmt->execute([$title, $description, $price, $_POST['currency'], $duration, $location, $image_url, $is_featured, $id]);

        // 2. Update Itinerary
        $pdo->prepare("DELETE FROM tour_itineraries WHERE tour_id = ?")->execute([$id]);
        if (isset($_POST['itinerary_title'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_itineraries (tour_id, day_number, title, description, image_url, image_alt) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['itinerary_title'] as $key => $val) {
                if (!empty($val)) {
                    $desc = $_POST['itinerary_desc'][$key];
                    $day = $key + 1;
                    $img_alt = $_POST['itinerary_alt'][$key] ?? '';

                    // Check for new image upload
                    $day_image = $_POST['itinerary_existing_image'][$key] ?? null;
                    if (isset($_FILES['itinerary_image']['name'][$key]) && $_FILES['itinerary_image']['error'][$key] == 0) {
                        $itin_dir = "../uploads/itinerary/";
                        if (!file_exists($itin_dir))
                            mkdir($itin_dir, 0777, true);
                        $itin_filename = time() . '_' . $key . '_' . basename($_FILES['itinerary_image']['name'][$key]);
                        $itin_target = $itin_dir . $itin_filename;
                        if (move_uploaded_file($_FILES['itinerary_image']['tmp_name'][$key], $itin_target)) {
                            $day_image = $itin_filename;
                        }
                    }

                    $stmt->execute([$id, $day, $val, $desc, $day_image, $img_alt]);
                }
            }
        }

        // 3. Update Inclusions
        $pdo->prepare("DELETE FROM tour_inclusions WHERE tour_id = ?")->execute([$id]);

        // Process Included Items
        if (isset($_POST['inc_included'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_inclusions (tour_id, item, is_included) VALUES (?, ?, 1)");
            foreach ($_POST['inc_included'] as $val) {
                if (!empty($val)) {
                    $stmt->execute([$id, $val]);
                }
            }
        }

        // Process Excluded Items
        if (isset($_POST['inc_excluded'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_inclusions (tour_id, item, is_included) VALUES (?, ?, 0)");
            foreach ($_POST['inc_excluded'] as $val) {
                if (!empty($val)) {
                    $stmt->execute([$id, $val]);
                }
            }
        }

        // 4. Update Key Features
        $pdo->prepare("DELETE FROM tour_key_features WHERE tour_id = ?")->execute([$id]);
        if (isset($_POST['feature_title'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_key_features (tour_id, icon, title, subtitle) VALUES (?, ?, ?, ?)");
            foreach ($_POST['feature_title'] as $key => $val) {
                if (!empty($val)) {
                    $icon = $_POST['feature_icon'][$key];
                    $sub = $_POST['feature_subtitle'][$key];
                    $stmt->execute([$id, $icon, $val, $sub]);
                }
            }
        }

        // 5. Update Dates
        $pdo->prepare("DELETE FROM tour_dates WHERE tour_id = ?")->execute([$id]);
        if (isset($_POST['date_start'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_dates (tour_id, start_date, end_date, price, currency, hotel_stars, quota) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($_POST['date_start'] as $key => $val) {
                if (!empty($val)) {
                    $end = $_POST['date_end'][$key];
                    $dPrice = $_POST['date_price'][$key];
                    $dCurrency = $_POST['date_currency'][$key] ?? 'EUR';
                    $dStars = $_POST['date_stars'][$key] ?? 0;
                    $quota = $_POST['date_quota'][$key];
                    $stmt->execute([$id, $val, $end, $dPrice, $dCurrency, $dStars, $quota]);
                }
            }
        }

        $pdo->commit();
        $success = "Tur başarıyla güncellendi!";

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Hata: " . $e->getMessage();
    }
} else {
    // Initial Fetch
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$id]);
    $tour = $stmt->fetch();
    if (!$tour) {
        header("Location: tours.php");
        exit;
    }
}

// Fetch Related Data
$itineraries = $pdo->prepare("SELECT * FROM tour_itineraries WHERE tour_id = ? ORDER BY day_number");
$itineraries->execute([$id]);
$itineraries = $itineraries->fetchAll();

$inclusions = $pdo->prepare("SELECT * FROM tour_inclusions WHERE tour_id = ?");
$inclusions->execute([$id]);
$inclusions = $inclusions->fetchAll();

$features = $pdo->prepare("SELECT * FROM tour_key_features WHERE tour_id = ?");
$features->execute([$id]);
$features = $features->fetchAll();

$dates = $pdo->prepare("SELECT * FROM tour_dates WHERE tour_id = ? ORDER BY start_date");
$dates->execute([$id]);
$dates = $dates->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turu Düzenle - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Quill.js Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <style>
        /* Page-specific overrides only */
        .quill-editor {
            background-color: #fff;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .ql-toolbar.ql-snow {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Turu Düzenle: <?php echo htmlspecialchars($tour['title']); ?></h1>
                    <div class="breadcrumb">Dashboard / Turlar / Düzenle</div>
                </div>
                <a href="../tour-detail.php?id=<?php echo $tour['id']; ?>" target="_blank" class="btn-primary">
                    Turu Görüntüle <i class="fa-solid fa-external-link-alt"></i>
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="tourForm">
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
                                    <input type="text" name="title"
                                        value="<?php echo htmlspecialchars($tour['title']); ?>" required>
                                </div>
                                <div class="row" style="display: flex; gap: 1rem;">
                                    <div class="form-group" style="flex: 1;">
                                        <label>Başlangıç Fiyatı</label>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <select name="currency" style="width: 80px;">
                                                <option value="EUR" <?php echo ($tour['currency'] ?? 'EUR') == 'EUR' ? 'selected' : ''; ?>>€</option>
                                                <option value="USD" <?php echo ($tour['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>$</option>
                                                <option value="GBP" <?php echo ($tour['currency'] ?? '') == 'GBP' ? 'selected' : ''; ?>>£</option>
                                                <option value="TRY" <?php echo ($tour['currency'] ?? '') == 'TRY' ? 'selected' : ''; ?>>₺</option>
                                            </select>
                                            <input type="number" name="price" step="0.01"
                                                value="<?php echo htmlspecialchars($tour['price']); ?>" required
                                                style="flex: 1;">
                                        </div>
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                        <label>Süre</label>
                                        <input type="text" name="duration"
                                            value="<?php echo htmlspecialchars($tour['duration']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Lokasyon</label>
                                    <input type="text" name="location"
                                        value="<?php echo htmlspecialchars($tour['location']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Öne Çıkarılan Tur?</label>
                                    <input type="checkbox" name="is_featured" style="width: auto;" <?php echo $tour['is_featured'] ? 'checked' : ''; ?>> Anasayfada göster
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <div class="form-group">
                                    <label>Kapak Resmi</label>
                                    <?php if ($tour['image_url']): ?>
                                        <img src="../uploads/<?php echo $tour['image_url']; ?>"
                                            style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px; margin-bottom: 1rem;">
                                    <?php endif; ?>
                                    <input type="file" name="image" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Genel Açıklama</label>
                            <textarea name="description" rows="5"
                                required><?php echo htmlspecialchars($tour['description']); ?></textarea>
                        </div>
                    </div>

                    <!-- 2. ITINERARY TAB -->
                    <div id="itinerary" class="tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Gün Gün Tur Programı
                            </h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Her gün için başlık, açıklama ve
                                opsiyonel görsel ekleyin. Sıralama otomatik numaralandırılır.</p>
                        </div>
                        <div id="itinerary-container">
                            <?php $dayNum = 1;
                            foreach ($itineraries as $it): ?>
                                <div class="itinerary-card">
                                    <div class="itinerary-card-badge">
                                        <span class="day-num"><?php echo $dayNum; ?></span>
                                        <span class="day-label">GÜN</span>
                                    </div>
                                    <div class="itinerary-card-body">
                                        <div class="form-group" style="margin-bottom: 0.75rem;">
                                            <label
                                                style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i
                                                    class="fa-solid fa-heading"
                                                    style="margin-right: 0.3rem; color: var(--primary-color);"></i> Gün
                                                Başlığı</label>
                                            <input type="text" name="itinerary_title[]"
                                                placeholder="Örn: İstanbul Havalimanı Buluşma ve Hareket"
                                                value="<?php echo htmlspecialchars($it['title']); ?>" required>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0.75rem;">
                                            <label
                                                style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i
                                                    class="fa-solid fa-align-left"
                                                    style="margin-right: 0.3rem; color: var(--primary-color);"></i>
                                                Açıklama</label>
                                            <!-- Quill Editor Container -->
                                            <div class="quill-editor" style="height: 120px;"></div>
                                            <textarea name="itinerary_desc[]"
                                                style="display:none;"><?php echo htmlspecialchars($it['description']); ?></textarea>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <label
                                                style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i
                                                    class="fa-solid fa-image"
                                                    style="margin-right: 0.3rem; color: var(--primary-color);"></i>
                                                Gün Görseli</label>
                                            <input type="hidden" name="itinerary_existing_image[]"
                                                value="<?php echo htmlspecialchars($it['image_url'] ?? ''); ?>">
                                            <?php if (!empty($it['image_url'])): ?>
                                                <img src="../uploads/itinerary/<?php echo htmlspecialchars($it['image_url']); ?>"
                                                    class="itin-img-preview"
                                                    style="width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-bottom:0.5rem;"
                                                    alt="<?php echo htmlspecialchars($it['image_alt'] ?? ''); ?>">
                                            <?php else: ?>
                                                <img class="itin-img-preview" src="" alt=""
                                                    style="display:none; width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-bottom:0.5rem;">
                                            <?php endif; ?>
                                            <input type="file" name="itinerary_image[]" accept="image/*"
                                                style="font-size: 0.8rem; margin-bottom: 0.5rem;"
                                                onchange="previewItineraryImage(this)">
                                            <input type="text" name="itinerary_alt[]" placeholder="Görsel alt etiketi (SEO)"
                                                value="<?php echo htmlspecialchars($it['image_alt'] ?? ''); ?>"
                                                style="font-size: 0.85rem;">
                                        </div>
                                    </div>
                                    <button type="button" class="itinerary-card-delete" title="Bu günü sil"
                                        onclick="removeItineraryCard(this)"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                                <?php $dayNum++; endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addItineraryRow()"
                            style="width: 100%; padding: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: 2px dashed #ccc; background: #fafafa; color: #666; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            <i class="fa-solid fa-plus"></i> Yeni Gün Ekle
                        </button>
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
                                    <?php foreach ($inclusions as $inc):
                                        if ($inc['is_included'] == 1): ?>
                                            <div class="dynamic-row" style="background: white;">
                                                <input type="text" name="inc_included[]"
                                                    value="<?php echo htmlspecialchars($inc['item']); ?>" style="flex: 1;">
                                                <button type="button" class="btn-remove"
                                                    onclick="this.parentElement.remove()"><i
                                                        class="fa-solid fa-trash"></i></button>
                                            </div>
                                        <?php endif; endforeach; ?>
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
                                    <?php foreach ($inclusions as $inc):
                                        if ($inc['is_included'] == 0): ?>
                                            <div class="dynamic-row" style="background: white;">
                                                <input type="text" name="inc_excluded[]"
                                                    value="<?php echo htmlspecialchars($inc['item']); ?>" style="flex: 1;">
                                                <button type="button" class="btn-remove"
                                                    onclick="this.parentElement.remove()"><i
                                                        class="fa-solid fa-trash"></i></button>
                                            </div>
                                        <?php endif; endforeach; ?>
                                </div>
                                <button type="button" class="btn-add"
                                    style="background: #ef9a9a; color: #b71c1c; width: 100%; margin-top: 1rem;"
                                    onclick="addInclusionRow(0)">+ Dahil Olmayan Hizmet Ekle</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. KEY FEATURES TAB -->
                    <div id="features" class="tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Öne Çıkan Özellikler
                            </h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Tur detay sayfasında görünecek 4
                                temel özelliği buraya ekleyin (Uçuş, Otel, vb).</p>
                        </div>
                        <div id="features-container">
                            <?php foreach ($features as $feat): ?>
                                <div class="feature-card">
                                    <div class="feature-card-content">
                                        <!-- Icon Pick -->
                                        <div style="width: 140px;">
                                            <label
                                                style="font-size: 0.75rem; font-weight: 700; color: #666; margin-bottom: 0.4rem; display: block; text-transform: uppercase; letter-spacing: 0.5px;">İKON
                                                SEÇİMİ</label>
                                            <div class="feature-icon-preview" style="margin-bottom: 0.8rem;">
                                                <i class="<?php echo $feat['icon']; ?>"></i>
                                            </div>
                                            <select name="feature_icon[]"
                                                style="width: 100%; padding: 0.5rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 6px; background: #fafafa;"
                                                onchange="this.previousElementSibling.querySelector('i').className = this.value">
                                                <option value="fa-solid fa-plane" <?php echo $feat['icon'] == 'fa-solid fa-plane' ? 'selected' : ''; ?>>✈️ Uçuş</option>
                                                <option value="fa-solid fa-hotel" <?php echo $feat['icon'] == 'fa-solid fa-hotel' ? 'selected' : ''; ?>>🏨 Otel</option>
                                                <option value="fa-solid fa-utensils" <?php echo $feat['icon'] == 'fa-solid fa-utensils' ? 'selected' : ''; ?>>🍽️ Yemek</option>
                                                <option value="fa-solid fa-users" <?php echo $feat['icon'] == 'fa-solid fa-users' ? 'selected' : ''; ?>>👥 Rehber</option>
                                                <option value="fa-solid fa-bus" <?php echo $feat['icon'] == 'fa-solid fa-bus' ? 'selected' : ''; ?>>🚌 Transfer</option>
                                                <option value="fa-solid fa-calendar-days" <?php echo $feat['icon'] == 'fa-solid fa-calendar-days' ? 'selected' : ''; ?>>📅
                                                    Tarih</option>
                                                <option value="fa-solid fa-ticket" <?php echo $feat['icon'] == 'fa-solid fa-ticket' ? 'selected' : ''; ?>>🎟️ Bilet</option>
                                                <option value="fa-solid fa-passport" <?php echo $feat['icon'] == 'fa-solid fa-passport' ? 'selected' : ''; ?>>🛂 Vize</option>
                                                <option value="fa-solid fa-camera" <?php echo $feat['icon'] == 'fa-solid fa-camera' ? 'selected' : ''; ?>>📷 Fotoğraf</option>
                                            </select>
                                        </div>

                                        <!-- Text Fields -->
                                        <div class="feature-form-grid">
                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Başlık</label>
                                                <input type="text" name="feature_title[]"
                                                    value="<?php echo htmlspecialchars($feat['title']); ?>" required
                                                    placeholder="Örn: Uçuş Dahil"
                                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            </div>
                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Alt
                                                    Başlık</label>
                                                <input type="text" name="feature_subtitle[]"
                                                    value="<?php echo htmlspecialchars($feat['subtitle']); ?>"
                                                    placeholder="Örn: THY ile Gidiş-Dönüş"
                                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="feature-card-delete" title="Özelliği sil"
                                        onclick="removeFeatureCard(this)"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addFeatureRow()"
                            style="width: 100%; padding: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: 2px dashed #ccc; background: #fafafa; color: #666; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            <i class="fa-solid fa-plus"></i> Yeni Özellik Ekle
                        </button>
                    </div>

                    <!-- 5. DATES TAB -->
                    <div id="dates" class="tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Tarih & Fiyat
                                Seçenekleri</h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Bu tur için geçerli tarih
                                aralıklarını, fiyatları ve kontenjan bilgilerini girin.</p>
                        </div>
                        <div id="dates-container">
                            <?php foreach ($dates as $date): ?>
                                <div class="date-card">
                                    <div class="date-card-content">
                                        <div class="date-badge">
                                            <i class="fa-regular fa-calendar-days"></i>
                                        </div>

                                        <div class="date-form-grid">
                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Gidiş
                                                    Tarihi</label>
                                                <input type="date" name="date_start[]"
                                                    value="<?php echo $date['start_date']; ?>"
                                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            </div>
                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Dönüş
                                                    Tarihi</label>
                                                <input type="date" name="date_end[]"
                                                    value="<?php echo $date['end_date']; ?>"
                                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            </div>

                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Fiyat</label>
                                                <div class="price-input-group">
                                                    <input type="number" name="date_price[]"
                                                        value="<?php echo $date['price']; ?>" step="0.01">
                                                    <select name="date_currency[]">
                                                        <option value="EUR" <?php echo ($date['currency'] ?? 'EUR') == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                                        <option value="USD" <?php echo ($date['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>USD</option>
                                                        <option value="GBP" <?php echo ($date['currency'] ?? '') == 'GBP' ? 'selected' : ''; ?>>GBP</option>
                                                        <option value="TRY" <?php echo ($date['currency'] ?? '') == 'TRY' ? 'selected' : ''; ?>>TRY</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Kontenjan</label>
                                                <input type="number" name="date_quota[]"
                                                    value="<?php echo $date['quota']; ?>"
                                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                            </div>

                                            <div class="date-form-full">
                                                <div style="grid-column: span 2;">
                                                    <label
                                                        style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Konaklama
                                                        / Otel Bilgisi</label>
                                                    <input type="text" name="date_stars[]"
                                                        value="<?php echo htmlspecialchars($date['hotel_stars']); ?>"
                                                        placeholder="Örn: 5 Yıldız Otel, Oda Kahvaltı"
                                                        style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="date-card-delete" title="Tarihi sil"
                                        onclick="removeDateCard(this)"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addDateRow()"
                            style="width: 100%; padding: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: 2px dashed #ccc; background: #fafafa; color: #666; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            <i class="fa-solid fa-plus"></i> Yeni Tarih Ekle
                        </button>
                    </div>

                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 2rem; text-align: right;">
                        <button type="submit" class="btn-primary"
                            style="padding: 1rem 3rem; font-size: 1.1rem;">Değişiklikleri Kaydet</button>
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
            const dayNum = container.querySelectorAll('.itinerary-card').length + 1;
            const div = document.createElement('div');
            div.className = 'itinerary-card';
            div.style.animation = 'fadeInUp 0.3s ease';
            div.innerHTML = `
                <div class="itinerary-card-badge">
                    <span class="day-num">${dayNum}</span>
                    <span class="day-label">G\u00dcN</span>
                </div>
                <div class="itinerary-card-body">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i class="fa-solid fa-heading" style="margin-right: 0.3rem; color: var(--primary-color);"></i> G\u00fcn Ba\u015fl\u0131\u011f\u0131</label>
                        <input type="text" name="itinerary_title[]" placeholder="\u00d6rn: \u015eehir Turu ve M\u00fcze Ziyareti" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i class="fa-solid fa-align-left" style="margin-right: 0.3rem; color: var(--primary-color);"></i> A\u00e7\u0131klama</label>
                        <!-- Quill Editor Container -->
                        <div class="quill-editor" style="height: 120px;"></div>
                        <textarea name="itinerary_desc[]" style="display:none;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i class="fa-solid fa-image" style="margin-right: 0.3rem; color: var(--primary-color);"></i> G\u00fcn G\u00f6rseli</label>
                        <input type="hidden" name="itinerary_existing_image[]" value="">
                        <img class="itin-img-preview" src="" alt="" style="display:none; width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-bottom:0.5rem;">
                        <input type="file" name="itinerary_image[]" accept="image/*" style="font-size: 0.8rem; margin-bottom: 0.5rem;" onchange="previewItineraryImage(this)">
                        <input type="text" name="itinerary_alt[]" placeholder="G\u00f6rsel alt etiketi (SEO)" style="font-size: 0.85rem;">
                    </div>
                </div>
                <button type="button" class="itinerary-card-delete" title="Bu g\u00fcn\u00fc sil" onclick="removeItineraryCard(this)"><i class="fa-solid fa-trash-can"></i></button>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
            div.querySelector('input[name="itinerary_title[]"]').focus();

            // Initialize Quill for the newly added row
            const newQuillEditor = div.querySelector('.quill-editor');
            const newTextarea = div.querySelector('textarea[name="itinerary_desc[]"]');
            initSingleQuill(newQuillEditor, newTextarea);
        }

        function previewItineraryImage(input) {
            const preview = input.parentElement.querySelector('.itin-img-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                preview.src = '';
            }
        }


        function removeItineraryCard(btn) {
            const card = btn.closest('.itinerary-card');
            card.style.animation = 'fadeOutDown 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                renumberItineraryDays();
            }, 280);
        }

        function renumberItineraryDays() {
            const cards = document.querySelectorAll('#itinerary-container .itinerary-card');
            cards.forEach((card, index) => {
                card.querySelector('.day-num').textContent = index + 1;
            });
        }

        function addInclusionRow(type) {
            const containerId = type === 1 ? 'included-container' : 'excluded-container';
            const inputName = type === 1 ? 'inc_included[]' : 'inc_excluded[]';
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.style.background = 'white';

            div.innerHTML = `
        <input type="text" name="${inputName}" placeholder="Hizmet adı" style="flex: 1;">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-trash"></i></button>
    `;
            document.getElementById(containerId).appendChild(div);
        }

        function addFeatureRow() {
            const container = document.getElementById('features-container');
            const div = document.createElement('div');
            div.className = 'feature-card';
            div.style.animation = 'fadeInUp 0.3s ease';
            div.innerHTML = `
                <div class="feature-card-content">
                    <div style="width: 140px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #666; margin-bottom: 0.4rem; display: block; text-transform: uppercase; letter-spacing: 0.5px;">İKON SEÇİMİ</label>
                        <div class="feature-icon-preview" style="margin-bottom: 0.8rem;">
                            <i class="fa-solid fa-plane"></i>
                        </div>
                        <select name="feature_icon[]" style="width: 100%; padding: 0.5rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 6px; background: #fafafa;"
                            onchange="this.previousElementSibling.querySelector('i').className = this.value">
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
                    </div>

                    <div class="feature-form-grid">
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Başlık</label>
                            <input type="text" name="feature_title[]" placeholder="Örn: Uçuş Dahil" required
                                style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Alt Başlık</label>
                            <input type="text" name="feature_subtitle[]" placeholder="Örn: THY ile Gidiş-Dönüş"
                                style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <button type="button" class="feature-card-delete" title="Özelliği sil" onclick="removeFeatureCard(this)"><i class="fa-solid fa-trash-can"></i></button>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function removeFeatureCard(btn) {
            const card = btn.closest('.feature-card');
            card.style.animation = 'fadeOutDown 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
            }, 280);
        }

        function addDateRow() {
            const container = document.getElementById('dates-container');
            const div = document.createElement('div');
            div.className = 'date-card';
            div.style.animation = 'fadeInUp 0.3s ease';
            div.innerHTML = `
                <div class="date-card-content">
                    <div class="date-badge">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>

                    <div class="date-form-grid">
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Gidiş Tarihi</label>
                            <input type="date" name="date_start[]" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Dönüş Tarihi</label>
                            <input type="date" name="date_end[]" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>

                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Fiyat</label>
                            <div class="price-input-group">
                                <input type="number" name="date_price[]" value="0" step="0.01" placeholder="0.00">
                                <select name="date_currency[]">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="GBP">GBP</option>
                                    <option value="TRY">TRY</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Kontenjan</label>
                            <input type="number" name="date_quota[]" value="20" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>

                        <div class="date-form-full">
                            <div style="grid-column: span 2;">
                                <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Konaklama / Otel Bilgisi</label>
                                <input type="text" name="date_stars[]" placeholder="Örn: 5 Yıldız Otel, Oda Kahvaltı"
                                    style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="date-card-delete" title="Tarihi sil" onclick="removeDateCard(this)"><i class="fa-solid fa-trash-can"></i></button>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function removeDateCard(btn) {
            const card = btn.closest('.date-card');
            card.style.animation = 'fadeOutDown 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
            }, 280);
        }

        // Quill Initialization Logic
        const allQuillInstances = [];

        const quillOptions = {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        };

        function initSingleQuill(editorEl, textareaEl) {
            const quill = new Quill(editorEl, quillOptions);

            // If the textarea already has HTML content, load it into Quill
            if (textareaEl.value.trim() !== '') {
                quill.root.innerHTML = textareaEl.value;
            }

            // Sync on text change
            quill.on('text-change', function () {
                textareaEl.value = quill.root.innerHTML;
            });

            allQuillInstances.push({
                quill: quill,
                textarea: textareaEl
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const itineraryEditors = document.querySelectorAll('#itinerary-container .quill-editor');
            const itineraryTextareas = document.querySelectorAll('#itinerary-container textarea[name="itinerary_desc[]"]');

            itineraryEditors.forEach((editorEl, index) => {
                const textareaEl = itineraryTextareas[index];
                if (textareaEl) {
                    initSingleQuill(editorEl, textareaEl);
                }
            });
        });

        // Sync right before form submission to ensure latest content is in textarea
        document.getElementById('tourForm').addEventListener('submit', function () {
            allQuillInstances.forEach(instance => {
                instance.textarea.value = instance.quill.root.innerHTML;
            });
        });
    </script>

</body>

</html>