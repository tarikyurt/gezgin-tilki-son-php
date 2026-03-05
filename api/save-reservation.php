<?php
require_once '../vendor/autoload.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

try {
    $tour_id = intval($_POST['tour_id'] ?? 0);
    $date_id = intval($_POST['date_id'] ?? 0);
    $adults = intval($_POST['adults'] ?? 1);
    $children = intval($_POST['children'] ?? 0);
    $total_price = floatval($_POST['total_price'] ?? 0);

    if (!$tour_id || !$date_id || $adults < 1) {
        echo json_encode(['success' => false, 'message' => 'Eksik bilgi.']);
        exit;
    }

    // Helper functions for TC Validation
    function is_valid_tc($tc)
    {
        if (empty($tc))
            return true; // Optional field empty check
        if (!preg_match('/^[1-9][0-9]{10}$/', $tc))
            return false;

        $oddSum = $tc[0] + $tc[2] + $tc[4] + $tc[6] + $tc[8];
        $evenSum = $tc[1] + $tc[3] + $tc[5] + $tc[7];

        $digit10 = (($oddSum * 7) - $evenSum) % 10;
        $sum10 = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum10 += $tc[$i];
        }
        $digit11 = $sum10 % 10;

        return ($tc[9] == $digit10 && $tc[10] == $digit11);
    }

    // Check quota
    $stmt = $pdo->prepare("SELECT quota FROM tour_dates WHERE id = ? AND tour_id = ?");
    $stmt->execute([$date_id, $tour_id]);
    $dateRow = $stmt->fetch();
    if (!$dateRow) {
        echo json_encode(['success' => false, 'message' => 'Tarih bulunamadı.']);
        exit;
    }
    $totalGuests = $adults + $children;
    if ($dateRow['quota'] < $totalGuests) {
        echo json_encode(['success' => false, 'message' => 'Yeterli kontenjan yok.']);
        exit;
    }

    // Get first adult's name & email for main booking record
    $primaryName = trim($_POST['adult_1_name'] ?? 'Misafir');
    $primaryEmail = trim($_POST['adult_1_email'] ?? '');

    // Pre-validate all TCs before inserting
    for ($i = 1; $i <= $adults; $i++) {
        $tc = trim($_POST["adult_{$i}_tc"] ?? '');
        if (!is_valid_tc($tc)) {
            echo json_encode(['success' => false, 'message' => "Yetişkin $i için geçersiz TC Kimlik Numarası."]);
            exit;
        }
    }
    for ($i = 1; $i <= $children; $i++) {
        $tc = trim($_POST["child_{$i}_tc"] ?? '');
        if (!is_valid_tc($tc)) {
            echo json_encode(['success' => false, 'message' => "Çocuk $i için geçersiz TC Kimlik Numarası."]);
            exit;
        }
    }

    $pdo->beginTransaction();

    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (tour_id, customer_name, customer_email, guests, status, date_id, adults, children, total_price)
                           VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?)");
    $stmt->execute([$tour_id, $primaryName, $primaryEmail, $totalGuests, $date_id, $adults, $children, $total_price]);
    $bookingId = $pdo->lastInsertId();

    // Insert passengers
    $passengerStmt = $pdo->prepare("INSERT INTO booking_passengers
        (booking_id, type, full_name, tc_no, email, birth_date, passport_no, passport_expiry, gender, child_age, relationship)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Adults
    for ($i = 1; $i <= $adults; $i++) {
        $prefix = "adult_{$i}_";
        $passengerStmt->execute([
            $bookingId,
            'adult',
            trim($_POST[$prefix . 'name'] ?? ''),
            trim($_POST[$prefix . 'tc'] ?? ''),
            trim($_POST[$prefix . 'email'] ?? ''),
            $_POST[$prefix . 'birth'] ?: null,
            trim($_POST[$prefix . 'passport'] ?? ''),
            $_POST[$prefix . 'passport_expiry'] ?: null,
            $_POST[$prefix . 'gender'] ?: null,
            null,
            null
        ]);
    }

    // Children
    for ($i = 1; $i <= $children; $i++) {
        $prefix = "child_{$i}_";
        $passengerStmt->execute([
            $bookingId,
            'child',
            trim($_POST[$prefix . 'name'] ?? ''),
            trim($_POST[$prefix . 'tc'] ?? ''),
            trim($_POST[$prefix . 'email'] ?? ''),
            $_POST[$prefix . 'birth'] ?: null,
            trim($_POST[$prefix . 'passport'] ?? ''),
            $_POST[$prefix . 'passport_expiry'] ?: null,
            $_POST[$prefix . 'gender'] ?: null,
            intval($_POST[$prefix . 'age'] ?? 0),
            $_POST[$prefix . 'relationship'] ?? null
        ]);
    }

    // Decrease quota
    $stmt = $pdo->prepare("UPDATE tour_dates SET quota = quota - ? WHERE id = ?");
    $stmt->execute([$totalGuests, $date_id]);

    $pdo->commit();

    // -----------------------------------------------------
    // GÖREVLİLERE MAİL GÖNDERİMİ (operasyon@sthteam.com)
    // -----------------------------------------------------
    $stmt = $pdo->prepare("SELECT title FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tourData = $stmt->fetch();
    $tourTitle = $tourData ? $tourData['title'] : "Bilinmeyen Tur";

    $stmt = $pdo->prepare("SELECT start_date, end_date FROM tour_dates WHERE id = ?");
    $stmt->execute([$date_id]);
    $dateData = $stmt->fetch();
    $dateRange = $dateData ? date('d.m.Y', strtotime($dateData['start_date'])) . ' - ' . date('d.m.Y', strtotime($dateData['end_date'])) : "Bilinmeyen Tarih";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        // İlgili operasyon maili
        $mail->addAddress('tarikyurt12@gmail.com', 'Gezgin Tilki Operasyon');

        $mail->isHTML(true);
        $mail->Subject = 'Yeni Rezervasyon Geldi! - ' . $tourTitle;

        $priceFormatted = formatCurrency($total_price, 'EUR'); // veya kendi cinsi

        $emailBody = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                <div style='background: #d90429; padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px;'>Yeni Rezervasyon Geldi! 🦊</h1>
                </div>
                <div style='padding: 30px;'>
                    <p style='font-size: 16px; margin-bottom: 20px;'>Merhaba,</p>
                    <p style='font-size: 16px; margin-bottom: 30px;'>Sistem üzerinden yeni bir tur rezervasyonu oluşturuldu. Detaylar aşağıdadır:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 40%;'>Tur Adı:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>{$tourTitle}</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Tur Tarihi:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>{$dateRange}</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Rezerve Eden:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>{$primaryName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>E-posta:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>{$primaryEmail}</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Kişi Sayısı:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>{$adults} Yetişkin, {$children} Çocuk</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Toplam Fiyat:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; color: #d90429; font-weight: bold;'>{$priceFormatted}</td>
                        </tr>
                    </table>

                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='https://tur.gezgintilki.com/admin/booking-detail.php?id={$bookingId}' style='display: inline-block; padding: 12px 24px; background-color: #1a1a2e; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Detayları Panele Gidip Gör</a>
                    </div>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee;'>
                    Bu e-posta Gezgin Tilki sistemi tarafından otomatik olarak gönderilmiştir.
                </div>
            </div>
        </div>
        ";

        $mail->Body = $emailBody;
        $mail->send();
    } catch (Exception $e) {
        // Mail gitmese bile rezervasyon kaydedildi, işlemi bozma. Sadece loglanabilir.
    }

    echo json_encode(['success' => true, 'message' => 'Rezervasyon başarıyla kaydedildi.', 'booking_id' => $bookingId]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
}
