<?php
/**
 * Tur Programını E-posta ile Gönder API
 * POST: email, tour_id
 */
header('Content-Type: application/json; charset=utf-8');

// Prevent GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../vendor/autoload.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get POST data
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$tour_id = intval($_POST['tour_id'] ?? 0);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Geçerli bir e-posta adresi giriniz.']);
    exit;
}

if (!$tour_id) {
    echo json_encode(['success' => false, 'message' => 'Tur bilgisi bulunamadı.']);
    exit;
}

// Fetch tour
$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();

if (!$tour) {
    echo json_encode(['success' => false, 'message' => 'Tur bulunamadı.']);
    exit;
}

// Fetch itinerary
$stmt = $pdo->prepare("SELECT * FROM tour_itineraries WHERE tour_id = ? ORDER BY day_number");
$stmt->execute([$tour_id]);
$itinerary = $stmt->fetchAll();

// Fetch inclusions
$stmt = $pdo->prepare("SELECT * FROM tour_inclusions WHERE tour_id = ?");
$stmt->execute([$tour_id]);
$inclusions = $stmt->fetchAll();

$included = [];
$excluded = [];
foreach ($inclusions as $inc) {
    if ($inc['is_included'] == 1) {
        $included[] = $inc['item'];
    } else {
        $excluded[] = $inc['item'];
    }
}

// Fetch next date
$stmt = $pdo->prepare("SELECT * FROM tour_dates WHERE tour_id = ? AND start_date >= CURDATE() ORDER BY start_date ASC LIMIT 1");
$stmt->execute([$tour_id]);
$next_date = $stmt->fetch();

// Build HTML Email
$tourTitle = htmlspecialchars($tour['title']);
$tourLocation = htmlspecialchars($tour['location']);
$tourDuration = htmlspecialchars($tour['duration']);
$tourPrice = formatCurrency($tour['price'], $tour['currency'] ?? 'EUR');
$tourDesc = nl2br(htmlspecialchars($tour['description']));
$tourUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/../tour-detail.php?id=" . $tour_id;

// Date info
$dateInfo = '';
if ($next_date) {
    $months = [
        '01' => 'Ocak',
        '02' => 'Şubat',
        '03' => 'Mart',
        '04' => 'Nisan',
        '05' => 'Mayıs',
        '06' => 'Haziran',
        '07' => 'Temmuz',
        '08' => 'Ağustos',
        '09' => 'Eylül',
        '10' => 'Ekim',
        '11' => 'Kasım',
        '12' => 'Aralık'
    ];
    $s = new DateTime($next_date['start_date']);
    $e = new DateTime($next_date['end_date']);
    $dateInfo = $s->format('d') . ' ' . $months[$s->format('m')] . ' ' . $s->format('Y') . ' - ' . $e->format('d') . ' ' . $months[$e->format('m')] . ' ' . $e->format('Y');
}

// Build Itinerary HTML
$itineraryHtml = '';
if (!empty($itinerary)) {
    foreach ($itinerary as $day) {
        $dayNum = $day['day_number'];
        $dayTitle = htmlspecialchars($day['title']);
        $dayDesc = nl2br(htmlspecialchars($day['description']));
        $itineraryHtml .= "
        <tr>
            <td style='padding: 16px 20px; border-bottom: 1px solid #f0f0f0;'>
                <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                    <tr>
                        <td width='50' valign='top'>
                            <div style='width: 36px; height: 36px; background: linear-gradient(135deg, #D90429, #EF233C); color: white; border-radius: 50%; text-align: center; line-height: 36px; font-weight: 700; font-size: 14px;'>{$dayNum}</div>
                        </td>
                        <td valign='top'>
                            <div style='font-weight: 700; color: #111827; font-size: 15px; margin-bottom: 4px;'>{$dayTitle}</div>
                            <div style='color: #6B7280; font-size: 13px; line-height: 1.5;'>{$dayDesc}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>";
    }
}

// Build Inclusions HTML
$inclusionsHtml = '';
if (!empty($included)) {
    $inclusionsHtml .= "<div style='margin-bottom: 16px;'><strong style='color: #059669; font-size: 14px;'>✅ Fiyata Dahil Olanlar</strong><ul style='margin: 8px 0 0 0; padding-left: 20px;'>";
    foreach ($included as $item) {
        $inclusionsHtml .= "<li style='color: #374151; font-size: 13px; margin-bottom: 4px;'>" . htmlspecialchars($item) . "</li>";
    }
    $inclusionsHtml .= "</ul></div>";
}
if (!empty($excluded)) {
    $inclusionsHtml .= "<div><strong style='color: #DC2626; font-size: 14px;'>❌ Fiyata Dahil Olmayanlar</strong><ul style='margin: 8px 0 0 0; padding-left: 20px;'>";
    foreach ($excluded as $item) {
        $inclusionsHtml .= "<li style='color: #374151; font-size: 13px; margin-bottom: 4px;'>" . htmlspecialchars($item) . "</li>";
    }
    $inclusionsHtml .= "</ul></div>";
}

// Full HTML Email Template
$htmlBody = "
<!DOCTYPE html>
<html lang='tr'>
<head><meta charset='UTF-8'></head>
<body style='margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f3f4f6;'>
        <tr>
            <td align='center' style='padding: 30px 15px;'>
                <table cellpadding='0' cellspacing='0' border='0' width='600' style='max-width: 600px; width: 100%;'>
                    
                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #D90429 0%, #EF233C 100%); padding: 30px 40px; border-radius: 16px 16px 0 0; text-align: center;'>
                            <h1 style='color: white; margin: 0 0 8px 0; font-size: 22px; font-weight: 800;'>🦊 Gezgin Tilki</h1>
                            <p style='color: rgba(255,255,255,0.85); margin: 0; font-size: 13px;'>Tur Programınız Hazır!</p>
                        </td>
                    </tr>

                    <!-- Tour Info -->
                    <tr>
                        <td style='background: white; padding: 30px 40px;'>
                            <h2 style='color: #111827; margin: 0 0 12px 0; font-size: 22px; font-weight: 800;'>{$tourTitle}</h2>
                            <table cellpadding='0' cellspacing='0' border='0' style='margin-bottom: 16px;'>
                                <tr>
                                    <td style='padding-right: 20px;'>
                                        <span style='color: #6B7280; font-size: 13px;'>📍 {$tourLocation}</span>
                                    </td>
                                    <td style='padding-right: 20px;'>
                                        <span style='color: #6B7280; font-size: 13px;'>⏱ {$tourDuration}</span>
                                    </td>
                                    <td>
                                        <span style='color: #D90429; font-weight: 700; font-size: 15px;'>💰 {$tourPrice}</span>
                                    </td>
                                </tr>
                            </table>"
    . ($dateInfo ? "<div style='background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;'><span style='color: #991B1B; font-size: 13px;'>📅 En yakın tarih: <strong>{$dateInfo}</strong></span></div>" : "") .
    "<p style='color: #4B5563; font-size: 14px; line-height: 1.6; margin: 0;'>{$tourDesc}</p>
                        </td>
                    </tr>

                    <!-- Itinerary -->
                    " . (!empty($itineraryHtml) ? "
                    <tr>
                        <td style='background: white; padding: 0 40px 10px;'>
                            <div style='border-top: 2px solid #f0f0f0; padding-top: 20px;'>
                                <h3 style='color: #111827; margin: 0 0 4px 0; font-size: 17px; font-weight: 700;'>🗓 Gün Gün Tur Programı</h3>
                                <p style='color: #9CA3AF; font-size: 12px; margin: 0 0 16px 0;'>Detaylı gezi rotanız aşağıdadır</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style='background: white; padding: 0 20px;'>
                            <table cellpadding='0' cellspacing='0' border='0' width='100%' style='border: 1px solid #f0f0f0; border-radius: 10px; overflow: hidden;'>
                                {$itineraryHtml}
                            </table>
                        </td>
                    </tr>" : "") . "

                    <!-- Inclusions -->
                    " . (!empty($inclusionsHtml) ? "
                    <tr>
                        <td style='background: white; padding: 24px 40px 10px;'>
                            <div style='border-top: 2px solid #f0f0f0; padding-top: 20px;'>
                                {$inclusionsHtml}
                            </div>
                        </td>
                    </tr>" : "") . "

                    <!-- CTA -->
                    <tr>
                        <td style='background: white; padding: 24px 40px 30px; text-align: center;'>
                            <a href='{$tourUrl}' style='display: inline-block; background: linear-gradient(135deg, #D90429 0%, #EF233C 100%); color: white; padding: 14px 36px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 15px rgba(217,4,41,0.3);'>Turu İncele & Rezervasyon Yap</a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 24px 40px; text-align: center; border-radius: 0 0 16px 16px; background: #1F2937;'>
                            <p style='color: rgba(255,255,255,0.6); font-size: 12px; margin: 0 0 8px 0;'>Bu e-posta sizin talebiniz üzerine gönderilmiştir.</p>
                            <p style='color: rgba(255,255,255,0.4); font-size: 11px; margin: 0;'>© " . date('Y') . " Gezgin Tilki - STH Team | Tüm hakları saklıdır.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>";

// Send Email via PHPMailer
try {
    $mail = new PHPMailer(true);

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    // Recipients
    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
    $mail->addAddress($email);
    $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "🦊 {$tourTitle} - Tur Programı | Gezgin Tilki";
    $mail->Body = $htmlBody;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Tur programı başarıyla gönderildi! Lütfen e-posta kutunuzu kontrol edin.']);

} catch (Exception $e) {
    error_log("Mail send error: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'E-posta gönderilemedi. Lütfen daha sonra tekrar deneyin.']);
}
?>