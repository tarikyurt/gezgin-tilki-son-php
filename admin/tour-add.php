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
        // GÜN VE GECE AYRIMI
        $day_count = $_POST['duration_day'] ?? 0;
        $night_count = $_POST['duration_night'] ?? 0;
        $duration = $day_count . " Gün " . $night_count . " Gece";
        $location = $_POST['location'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = $_POST['is_active'] ?? 0;

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

        $stmt = $pdo->prepare("INSERT INTO tours (title, slug, description, price, currency, duration, location, image_url, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $price, $_POST['currency'], $duration, $location, $image_url, $is_featured, $is_active]);
        $tour_id = $pdo->lastInsertId();

        // 2. Insert Itinerary
        if (isset($_POST['itinerary_title'])) {
            $stmt = $pdo->prepare("INSERT INTO tour_itineraries (tour_id, day_number, title, description, image_url, image_alt) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['itinerary_title'] as $key => $val) {
                if (!empty($val)) {
                    $desc = $_POST['itinerary_desc'][$key];
                    $day = $key + 1;
                    $img_alt = $_POST['itinerary_alt'][$key] ?? '';

                    // Itinerary day image upload
                    $day_image = null;
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

                    $stmt->execute([$tour_id, $day, $val, $desc, $day_image, $img_alt]);
                }
            }
        }

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

            <form method="POST" enctype="multipart/form-data" id="tourForm">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button type="button" class="tab-btn active" onclick="openTab('general', event)">Genel
                        Bilgiler</button>
                    <button type="button" class="tab-btn" onclick="openTab('itinerary', event)">Tur Programı</button>
                    <button type="button" class="tab-btn" onclick="openTab('inclusions', event)">Hizmetler</button>
                    <button type="button" class="tab-btn" onclick="openTab('features', event)">Öne Çıkanlar</button>
                    <button type="button" class="tab-btn" onclick="openTab('dates', event)">Tarih & Fiyat</button>
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
                                        <label>Süre Detayı</label>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <div style="flex:1">
                                                <div style="position: relative;">
                                                    <input type="number" name="duration_day" min="1" placeholder="Gün"
                                                        required style="padding-right: 35px;">
                                                    <span
                                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: #888;">Gün</span>
                                                </div>
                                            </div>
                                            <div style="flex:1">
                                                <div style="position: relative;">
                                                    <input type="number" name="duration_night" min="0"
                                                        placeholder="Gece" required style="padding-right: 40px;">
                                                    <span
                                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: #888;">Gece</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Lokasyon</label>
                                    <input type="text" name="location" placeholder="Örn: İtalya" required>
                                </div>
                                <div class="form-group">
                                    <label>Tur Durumu (Yayında mı?)</label>
                                    <select name="is_active"
                                        style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 5px; background-color: #fff;">
                                        <option value="0" selected>🔒 Taslak (Pasif - Sitede Görünmez)</option>
                                        <option value="1">✅ Yayında (Aktif - Sitede Görünür)</option>
                                    </select>
                                    <small style="color: #888; font-size: 0.8rem; margin-top: 3px; display:block;">
                                        Eğer tur hazır değilse 'Taslak' seçin.
                                    </small>
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
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Gün Gün Tur Programı
                            </h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Her gün için başlık, açıklama ve
                                opsiyonel görsel ekleyin. Sıralama otomatik numaralandırılır.</p>
                        </div>
                        <div id="itinerary-container">
                            <div class="itinerary-card">
                                <div class="itinerary-card-badge">
                                    <span class="day-num">1</span>
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
                                            placeholder="Örn: İstanbul Havalimanı Buluşma ve Hareket" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label
                                            style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i
                                                class="fa-solid fa-align-left"
                                                style="margin-right: 0.3rem; color: var(--primary-color);"></i>
                                            Açıklama</label>
                                        <!-- Quill Editor Container -->
                                        <div class="quill-editor" style="height: 120px;"></div>
                                        <textarea name="itinerary_desc[]" style="display:none;"></textarea>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label
                                            style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;"><i
                                                class="fa-solid fa-image"
                                                style="margin-right: 0.3rem; color: var(--primary-color);"></i> Gün
                                            Görseli</label>
                                        <img class="itin-img-preview" src="" alt=""
                                            style="display:none; width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-bottom:0.5rem;">
                                        <input type="file" name="itinerary_image[]" accept="image/*"
                                            style="font-size: 0.8rem; margin-bottom: 0.5rem;"
                                            onchange="previewItineraryImage(this)">
                                        <input type="text" name="itinerary_alt[]" placeholder="Görsel alt etiketi (SEO)"
                                            style="font-size: 0.85rem;">
                                    </div>
                                </div>
                                <button type="button" class="itinerary-card-delete" title="Bu günü sil"
                                    onclick="removeItineraryCard(this)"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
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
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Öne Çıkan Özellikler</h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Tur detay sayfasında görünecek 4 temel özelliği buraya ekleyin (Uçuş, Otel, vb).</p>
                        </div>
                        <div id="features-container">
                            <!-- Features will be added here -->
                        </div>
                        <button type="button" class="btn-add" onclick="addFeatureRow()" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: 2px dashed #ccc; background: #fafafa; color: #666; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            <i class="fa-solid fa-plus"></i> Yeni Özellik Ekle
                        </button>
                    </div>

                    <!-- 5. DATES TAB -->
                    <div id="dates" class="tab-content">
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: #1a1a2e;">Tarih & Fiyat Seçenekleri</h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #888;">Bu tur için geçerli tarih aralıklarını, fiyatları ve kontenjan bilgilerini girin.</p>
                        </div>
                        <div id="dates-container">
                            <div class="date-card">
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
                                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Özel Fiyat (€)</label>
                                            <input type="number" name="date_price[]" value="0" step="0.01" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Kontenjan</label>
                                            <input type="number" name="date_quota[]" value="20" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="date-card-delete" title="Tarihi sil" onclick="removeDateCard(this)"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addDateRow()" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: 2px dashed #ccc; background: #fafafa; color: #666; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            <i class="fa-solid fa-plus"></i> Yeni Tarih Ekle
                        </button>
                    </div>

                    <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 2rem; text-align: right;">
                        <button type="button" id="tourAddContinueBtn" class="btn-primary"
                            style="padding: 1rem 3rem; font-size: 1.1rem; background: #667eea;"
                            onclick="nextTab()">Devam Et <i class="fa-solid fa-arrow-right"
                                style="margin-left:8px;"></i></button>
                        <button type="submit" id="tourAddSaveBtn" class="btn-primary"
                            style="padding: 1rem 3rem; font-size: 1.1rem; display: none;"><i class="fa-solid fa-check"
                                style="margin-right:8px;"></i> Turu Kaydet</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        const tourAddTabs = ['general', 'itinerary', 'inclusions', 'features', 'dates'];
        let tourAddCurrentIdx = 0;

        function openTab(tabId, evt) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');

            // Activate the correct tab button
            if (evt && evt.target) {
                evt.target.classList.add('active');
            } else {
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    if (btn.getAttribute('onclick').indexOf(tabId) !== -1) btn.classList.add('active');
                });
            }

            // Update button visibility
            tourAddCurrentIdx = tourAddTabs.indexOf(tabId);
            var contBtn = document.getElementById('tourAddContinueBtn');
            var saveBtn = document.getElementById('tourAddSaveBtn');
            if (tourAddCurrentIdx === tourAddTabs.length - 1) {
                contBtn.style.display = 'none';
                saveBtn.style.display = 'inline-block';
            } else {
                contBtn.style.display = 'inline-block';
                saveBtn.style.display = 'none';
            }
        }

        function nextTab() {
            if (tourAddCurrentIdx < tourAddTabs.length - 1) {
                openTab(tourAddTabs[tourAddCurrentIdx + 1]);
                document.querySelector('.tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
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
        <input type="text" name="${inputName}" placeholder="Hizmet ad\u0131" style="flex: 1;">
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
                        <label style="font-size: 0.75rem; font-weight: 700; color: #666; margin-bottom: 0.4rem; display: block; text-transform: uppercase; letter-spacing: 0.5px;">\u0130KON SE\u00c7\u0130M\u0130</label>
                        <div class="feature-icon-preview" style="margin-bottom: 0.8rem;">
                            <i class="fa-solid fa-plane"></i>
                        </div>
                        <select name="feature_icon[]" style="width: 100%; padding: 0.5rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 6px; background: #fafafa;"
                            onchange="this.previousElementSibling.querySelector('i').className = this.value">
                            <option value="fa-solid fa-plane">\u2708\ufe0f U\u00e7u\u015f</option>
                            <option value="fa-solid fa-hotel">\ud83c\udfe8 Otel</option>
                            <option value="fa-solid fa-utensils">\ud83c\udf7d\ufe0f Yemek</option>
                            <option value="fa-solid fa-users">\ud83d\udc65 Rehber</option>
                            <option value="fa-solid fa-bus">\ud83d\ude8c Transfer</option>
                            <option value="fa-solid fa-calendar-days">\ud83d\udcc5 Tarih</option>
                            <option value="fa-solid fa-ticket">\ud83c\udfab Bilet</option>
                            <option value="fa-solid fa-passport">\ud83d\udec2 Vize</option>
                            <option value="fa-solid fa-camera">\ud83d\udcf7 Foto\u011fraf</option>
                        </select>
                    </div>

                    <div class="feature-form-grid">
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Ba\u015fl\u0131k</label>
                            <input type="text" name="feature_title[]" placeholder="\u00d6rn: U\u00e7u\u015f Dahil" required
                                style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Alt Ba\u015fl\u0131k</label>
                            <input type="text" name="feature_subtitle[]" placeholder="\u00d6rn: THY ile Gidi\u015f-D\u00f6n\u00fc\u015f"
                                style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <button type="button" class="feature-card-delete" title="\u00d6zelli\u011fi sil" onclick="removeFeatureCard(this)"><i class="fa-solid fa-trash-can"></i></button>
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
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Gidi\u015f Tarihi</label>
                            <input type="date" name="date_start[]" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">D\u00f6n\u00fc\u015f Tarihi</label>
                            <input type="date" name="date_end[]" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">\u00d6zel Fiyat (\u20ac)</label>
                            <input type="number" name="date_price[]" value="0" step="0.01" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 0.25rem; display: block;">Kontenjan</label>
                            <input type="number" name="date_quota[]" value="20" style="width: 100%; padding: 0.6rem; border: 1px solid #e0e0e0; border-radius: 8px;">
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