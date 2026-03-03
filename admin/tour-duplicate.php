<?php
require_once '../includes/auth.php';
requireLogin();

if (!hasPermission('tour_add')) {
    header("Location: tours.php?error=" . urlencode("Tur çoğaltma yetkiniz bulunmamaktadır."));
    exit;
}

require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    header("Location: tours.php?error=" . urlencode("Geçersiz tur ID'si."));
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch original tour
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$id]);
    $originalTour = $stmt->fetch();

    if (!$originalTour) {
        throw new Exception("Kopyalanacak tur bulunamadı.");
    }

    // 2. Insert new tour
    $newTitle = $originalTour['title'] . ' (Kopya)';
    $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $newTitle)));

    // Ensure unique slug
    $slug = $slugBase;
    $count = 1;
    while (true) {
        $checkStmt = $pdo->prepare("SELECT id FROM tours WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if (!$checkStmt->fetch()) {
            break;
        }
        $slug = $slugBase . '-' . $count;
        $count++;
    }

    $insertSql = "INSERT INTO tours (title, location, duration, price, currency, image_url, description, is_featured, slug)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        $newTitle,
        $originalTour['location'],
        $originalTour['duration'],
        $originalTour['price'],
        $originalTour['currency'],
        $originalTour['image_url'],
        $originalTour['description'],
        $originalTour['is_featured'],
        $slug
    ]);

    $newTourId = $pdo->lastInsertId();

    // 3. Copy itineraries
    $stmt = $pdo->prepare("SELECT * FROM tour_itineraries WHERE tour_id = ?");
    $stmt->execute([$id]);
    $itineraries = $stmt->fetchAll();

    if ($itineraries) {
        $insertItinSql = "INSERT INTO tour_itineraries (tour_id, day_number, title, description, image_url, image_alt)
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmtItin = $pdo->prepare($insertItinSql);
        foreach ($itineraries as $it) {
            $stmtItin->execute([
                $newTourId,
                $it['day_number'],
                $it['title'],
                $it['description'],
                $it['image_url'],
                $it['image_alt']
            ]);
        }
    }

    // 4. Copy inclusions/exclusions
    $stmt = $pdo->prepare("SELECT * FROM tour_inclusions WHERE tour_id = ?");
    $stmt->execute([$id]);
    $inclusions = $stmt->fetchAll();

    if ($inclusions) {
        $insertIncSql = "INSERT INTO tour_inclusions (tour_id, is_included, item) VALUES (?, ?, ?)";
        $stmtInc = $pdo->prepare($insertIncSql);
        foreach ($inclusions as $inc) {
            $stmtInc->execute([
                $newTourId,
                $inc['is_included'],
                $inc['item']
            ]);
        }
    }

    // 5. Copy dates
    $stmt = $pdo->prepare("SELECT * FROM tour_dates WHERE tour_id = ?");
    $stmt->execute([$id]);
    $dates = $stmt->fetchAll();

    if ($dates) {
        $insertDateSql = "INSERT INTO tour_dates (tour_id, start_date, end_date, price, currency, quota, hotel_stars) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtDate = $pdo->prepare($insertDateSql);
        foreach ($dates as $date) {
            $stmtDate->execute([
                $newTourId,
                $date['start_date'],
                $date['end_date'],
                $date['price'],
                $date['currency'],
                $date['quota'],
                $date['hotel_stars']
            ]);
        }
    }

    $pdo->commit();

    header("Location: tour-edit.php?id=" . $newTourId . "&success=" . urlencode("Tur başarıyla çoğaltıldı. Şimdi düzenleyebilirsiniz."));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: tours.php?error=" . urlencode("Tur kopyalanırken hata oluştu: " . $e->getMessage()));
    exit;
}
