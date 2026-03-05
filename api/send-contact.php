<?php
/**
 * İletişim Formunu E-posta ile Gönder API
 * POST: name, email, subject, message
 */
require_once '../vendor/autoload.php';
require_once '../includes/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// PHP execution time limit - SMTP bağlantısı zaman aşımına uğramasın
set_time_limit(60);

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek türü.']);
    exit;
}

// Gelen verileri al ve temizle
$name = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = trim($_POST['subject'] ?? '');
$messageText = trim($_POST['message'] ?? '');

// Arka plan (Backend) Doğrulaması
if (empty($name) || empty($email) || empty($messageText)) {
    echo json_encode(['success' => false, 'message' => 'Lütfen zorunlu alanları (Ad, E-posta, Mesaj) doldurunuz.']);
    exit;
}

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Lütfen geçerli bir e-posta adresi giriniz.']);
    exit;
}

// Konu boşsa varsayılan ata
if (empty($subject)) {
    $subject = 'Web Sitesi İletişim Formu Mesajı';
}

try {
    $mail = new PHPMailer(true);

    // Sunucu ayarları
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 10; // SMTP bağlantı zaman aşımı (saniye)
    // Shared hosting SSL sertifika doğrulama sorunlarını aşmak için
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];

    // Gönderici ve Alıcı
    // Mail sunucumuz (SMTP_USERNAME) üstünden gönderilecek
    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);

    // Gelen mesajı reply-to (Yanıtla) olarak ekliyoruz ki operasyon ekibi direkt e-postayı yanıtlayabilsin
    $mail->addReplyTo($email, $name);

    // Alıcı: Operasyon adresi
    $mail->addAddress('operasyon@sthteam.com', 'Gezgin Tilki Operasyon');

    // İçerik (Şık HTML Tasarımı)
    $mail->isHTML(true);
    $mail->Subject = 'Yeni İletişim Formu Mesajı: ' . $subject;

    // Uzun mesajları HTML formatına uygun hale getirmek için yeni satırları <br> yap
    $messageHtml = nl2br(htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8'));
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    $emailBody = "
    <div style='font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <div style='background: #2B2D42; padding: 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>Yeni İletişim Mesajı 📩</h1>
            </div>
            <div style='padding: 30px;'>
                <p style='font-size: 16px; margin-bottom: 20px;'>İletişim sayfasından yeni bir mesaj aldınız. Detaylar aşağıdadır:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 30%; color: #555;'>Ad Soyad:</td>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-size: 16px;'>{$safeName}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #555;'>E-posta:</td>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee;'><a href='mailto:{$safeEmail}' style='color: #D90429; text-decoration: none;'>{$safeEmail}</a></td>
                    </tr>
                    <tr>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #555;'>Konu:</td>
                        <td style='padding: 12px 0; border-bottom: 1px solid #eee; font-weight: bold;'>{$safeSubject}</td>
                    </tr>
                </table>

                <div style='margin-top: 25px;'>
                    <h3 style='font-size: 14px; color: #888; text-transform: uppercase; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px;'>Gönderilen Mesaj</h3>
                    <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #D90429; font-size: 15px; line-height: 1.6; color: #444; border-radius: 0 5px 5px 0;'>
                        {$messageHtml}
                    </div>
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                    <a href='mailto:{$safeEmail}' style='display: inline-block; padding: 12px 24px; background-color: #1a1a2e; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Bu Mesajı Yanıtla</a>
                </div>
            </div>
            <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee;'>
                Bu e-posta Gezgin Tilki sistemi tarafından otomatik olarak oluşturulmuştur.
            </div>
        </div>
    </div>
    ";

    $mail->Body = $emailBody;
    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Mesajınız başarıyla iletildi. En kısa sürede size dönüş yapacağız.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mesaj gönderilirken sunucu tarafında bir hata oluştu. Lütfen tekrar deneyin.']);
}
