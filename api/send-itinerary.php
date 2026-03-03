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

// Prepare CID image references for embedding later
$cidImages = []; // array of ['path' => ..., 'cid' => ..., 'name' => ...]

// Tour cover image
$coverCid = '';
$coverImagePath = __DIR__ . '/../uploads/' . $tour['image_url'];
if (!empty($tour['image_url']) && file_exists($coverImagePath)) {
    $coverCid = 'cover_' . md5($tour['image_url']);
    $cidImages[] = ['path' => $coverImagePath, 'cid' => $coverCid, 'name' => basename($tour['image_url'])];
}

// Build Itinerary HTML with day images
$itineraryHtml = '';
if (!empty($itinerary)) {
    foreach ($itinerary as $day) {
        $dayNum = $day['day_number'];
        $dayTitle = htmlspecialchars($day['title']);
        $dayDesc = $day['description']; // Removed htmlspecialchars and nl2br to allow Quill HTML

        // Check for day image
        $dayImageHtml = '';
        if (!empty($day['image_url'])) {
            $dayImagePath = __DIR__ . '/../uploads/itinerary/' . $day['image_url'];
            if (file_exists($dayImagePath)) {
                $dayCid = 'day_' . $dayNum . '_' . md5($day['image_url']);
                $cidImages[] = ['path' => $dayImagePath, 'cid' => $dayCid, 'name' => basename($day['image_url'])];
                $dayAlt = htmlspecialchars($day['image_alt'] ?? $day['title']);
                $dayImageHtml = "
                    <tr>
                        <td colspan='2' style='padding-top: 10px;'>
                            <img src='cid:{$dayCid}' alt='{$dayAlt}' style='width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; display: block;'>
                        </td>
                    </tr>";
            }
        }

        $itineraryHtml .= "
        <tr>
            <td style='padding: 0;'>
                <table cellpadding='0' cellspacing='0' border='0' width='100%' style='margin-bottom: 4px;'>
                    <tr>
                        <td style='padding: 20px; background: #ffffff; border-radius: 12px; border: 1px solid #f0f0f0;'>
                            <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                                <tr>
                                    <td width='48' valign='top' style='padding-right: 14px;'>
                                        <div style='width: 42px; height: 42px; background: linear-gradient(135deg, #D90429, #EF233C); color: white; border-radius: 50%; text-align: center; line-height: 42px; font-weight: 800; font-size: 15px; box-shadow: 0 3px 10px rgba(217,4,41,0.25);'>{$dayNum}</div>
                                    </td>
                                    <td valign='top'>
                                        <div style='font-weight: 700; color: #111827; font-size: 15px; margin-bottom: 6px; letter-spacing: -0.2px;'>{$dayTitle}</div>
                                        <div style='color: #6B7280; font-size: 13px; line-height: 1.6;'>{$dayDesc}</div>
                                    </td>
                                </tr>
                                {$dayImageHtml}
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>";
    }
}

// Build Inclusions HTML
$inclusionsHtml = '';
if (!empty($included) || !empty($excluded)) {
    $inclusionsHtml .= "
    <tr>
        <td style='background: white; padding: 0 40px 10px;'>
            <div style='border-top: 2px solid #f0f0f0; padding-top: 24px;'>
                <h3 style='color: #111827; margin: 0 0 16px 0; font-size: 17px; font-weight: 700;'>📋 Hizmet Detayları</h3>";

    if (!empty($included)) {
        $inclusionsHtml .= "<div style='background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; margin-bottom: 12px;'>";
        $inclusionsHtml .= "<div style='color: #15803d; font-weight: 700; font-size: 14px; margin-bottom: 10px;'>✅ Fiyata Dahil Olanlar</div>";
        $inclusionsHtml .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
        foreach ($included as $item) {
            $inclusionsHtml .= "<tr><td style='padding: 4px 0; color: #374151; font-size: 13px;'>
                <span style='color: #22c55e; margin-right: 6px;'>●</span>" . htmlspecialchars($item) . "</td></tr>";
        }
        $inclusionsHtml .= "</table></div>";
    }
    if (!empty($excluded)) {
        $inclusionsHtml .= "<div style='background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; padding: 16px;'>";
        $inclusionsHtml .= "<div style='color: #be123c; font-weight: 700; font-size: 14px; margin-bottom: 10px;'>❌ Fiyata Dahil Olmayanlar</div>";
        $inclusionsHtml .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
        foreach ($excluded as $item) {
            $inclusionsHtml .= "<tr><td style='padding: 4px 0; color: #374151; font-size: 13px;'>
                <span style='color: #ef4444; margin-right: 6px;'>●</span>" . htmlspecialchars($item) . "</td></tr>";
        }
        $inclusionsHtml .= "</table></div>";
    }

    $inclusionsHtml .= "
            </div>
        </td>
    </tr>";
}

// Cover image HTML
$coverImageHtml = '';
if ($coverCid) {
    $coverImageHtml = "
    <tr>
        <td style='padding: 0; line-height: 0;'>
            <img src='cid:{$coverCid}' alt='{$tourTitle}' style='width: 100%; max-height: 280px; object-fit: cover; display: block;'>
            <div style='height: 4px; background: linear-gradient(135deg, #D90429 0%, #EF233C 50%, #FF6B6B 100%);'></div>
        </td>
    </tr>";
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
                <table cellpadding='0' cellspacing='0' border='0' width='600' style='max-width: 600px; width: 100%; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08);'>

                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #D90429 0%, #EF233C 50%, #FF4D6D 100%); padding: 28px 40px; text-align: center;'>
                            <h1 style='color: white; margin: 0 0 6px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;'>🦊 Gezgin Tilki</h1>
                            <p style='color: rgba(255,255,255,0.9); margin: 0; font-size: 13px; font-weight: 500;'>Tur Programınız Hazır!</p>
                        </td>
                    </tr>

                    <!-- Cover Image -->
                    {$coverImageHtml}

                    <!-- Tour Info -->
                    <tr>
                        <td style='background: white; padding: 28px 40px;'>
                            <h2 style='color: #111827; margin: 0 0 14px 0; font-size: 22px; font-weight: 800; letter-spacing: -0.3px;'>{$tourTitle}</h2>

                            <!-- Info Pills -->
                            <table cellpadding='0' cellspacing='0' border='0' style='margin-bottom: 18px;'>
                                <tr>
                                    <td style='padding-right: 8px;'>
                                        <div style='background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 20px; padding: 6px 14px; display: inline-block;'>
                                            <span style='color: #495057; font-size: 12px; font-weight: 600;'>📍 {$tourLocation}</span>
                                        </div>
                                    </td>
                                    <td style='padding-right: 8px;'>
                                        <div style='background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 20px; padding: 6px 14px; display: inline-block;'>
                                            <span style='color: #495057; font-size: 12px; font-weight: 600;'>⏱ {$tourDuration}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style='background: linear-gradient(135deg, #fff5f5, #ffe3e3); border: 1px solid #fecaca; border-radius: 20px; padding: 6px 14px; display: inline-block;'>
                                            <span style='color: #D90429; font-weight: 800; font-size: 13px;'>💰 {$tourPrice}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>"
    . ($dateInfo ? "
                            <div style='background: linear-gradient(135deg, #fef2f2, #fff1f2); border: 1px solid #fecdd3; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px;'>
                                <span style='color: #9f1239; font-size: 13px;'>📅 En yakın tarih: <strong>{$dateInfo}</strong></span>
                            </div>" : "") . "
                            <p style='color: #4B5563; font-size: 14px; line-height: 1.7; margin: 0;'>{$tourDesc}</p>
                        </td>
                    </tr>

                    <!-- Itinerary -->
                    " . (!empty($itineraryHtml) ? "
                    <tr>
                        <td style='background: white; padding: 0 40px 10px;'>
                            <div style='border-top: 2px solid #f0f0f0; padding-top: 24px;'>
                                <h3 style='color: #111827; margin: 0 0 4px 0; font-size: 17px; font-weight: 700;'>🗓 Gün Gün Tur Programı</h3>
                                <p style='color: #9CA3AF; font-size: 12px; margin: 0 0 18px 0;'>Detaylı gezi rotanız aşağıdadır</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style='background: white; padding: 0 24px 10px;'>
                            <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                                {$itineraryHtml}
                            </table>
                        </td>
                    </tr>" : "") . "

                    <!-- Inclusions -->
                    {$inclusionsHtml}

                    <!-- CTA -->
                    <tr>
                        <td style='background: white; padding: 28px 40px 32px; text-align: center;'>
                            <a href='{$tourUrl}' style='display: inline-block; background: linear-gradient(135deg, #D90429 0%, #EF233C 100%); color: white; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 6px 20px rgba(217,4,41,0.3); letter-spacing: 0.3px;'>Turu İncele & Rezervasyon Yap →</a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 24px 40px; text-align: center; background: linear-gradient(135deg, #1F2937, #111827);'>
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

    // Embed CID images
    foreach ($cidImages as $img) {
        if (file_exists($img['path'])) {
            $mail->addEmbeddedImage($img['path'], $img['cid'], $img['name']);
        }
    }

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