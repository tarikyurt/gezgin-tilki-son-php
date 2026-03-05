<?php
/**
 * Tur Programını E-posta ile Gönder API
 * POST: email, tour_id
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// PHP execution time limit - SMTP bağlantısı zaman aşımına uğramasın
set_time_limit(60);

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
                                        <div style='width: 42px; height: 42px; background: #e11d48; color: white; border-radius: 50%; text-align: center; line-height: 42px; font-weight: 800; font-size: 15px; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.2);'>{$dayNum}</div>
                                    </td>
                                    <td valign='top'>
                                        <div style='font-weight: 800; color: #0f172a; font-size: 16px; margin-bottom: 6px; letter-spacing: -0.2px;'>{$dayTitle}</div>
                                        <div style='color: #475569; font-size: 14px; line-height: 1.7;'>{$dayDesc}</div>
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
            <div style='border-top: 2px solid #f1f5f9; padding-top: 24px;'>
                <h3 style='color: #0f172a; margin: 0 0 16px 0; font-size: 18px; font-weight: 800;'>📋 Hizmet Detayları</h3>";

    if (!empty($included)) {
        $inclusionsHtml .= "<div style='background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 18px; margin-bottom: 16px;'>";
        $inclusionsHtml .= "<div style='color: #166534; font-weight: 800; font-size: 15px; margin-bottom: 12px;'>✅ Fiyata Dahil Olanlar</div>";
        $inclusionsHtml .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
        foreach ($included as $item) {
            $inclusionsHtml .= "<tr><td style='padding: 5px 0; color: #1e293b; font-size: 14px; font-weight: 500;'>
                <span style='color: #16a34a; margin-right: 8px;'>●</span>" . htmlspecialchars($item) . "</td></tr>";
        }
        $inclusionsHtml .= "</table></div>";
    }
    if (!empty($excluded)) {
        $inclusionsHtml .= "<div style='background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; padding: 18px;'>";
        $inclusionsHtml .= "<div style='color: #9f1239; font-weight: 800; font-size: 15px; margin-bottom: 12px;'>❌ Fiyata Dahil Olmayanlar</div>";
        $inclusionsHtml .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
        foreach ($excluded as $item) {
            $inclusionsHtml .= "<tr><td style='padding: 5px 0; color: #1e293b; font-size: 14px; font-weight: 500;'>
                <span style='color: #e11d48; margin-right: 8px;'>●</span>" . htmlspecialchars($item) . "</td></tr>";
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
            <div style='height: 4px; background: #e11d48;'></div>
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
                            <h2 style='color: #111827; margin: 0 0 14px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.3px;'>{$tourTitle}</h2>

                            <!-- Info Pills -->
                            <table cellpadding='0' cellspacing='0' border='0' style='margin-bottom: 22px; width: 100%;'>
                                <tr>
                                    <td style='padding-right: 8px;'>
                                        <div style='background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; display: inline-block;'>
                                            <span style='color: #334155; font-size: 13px; font-weight: 700;'>📍 {$tourLocation}</span>
                                        </div>
                                    </td>
                                    <td style='padding-right: 8px;'>
                                        <div style='background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; display: inline-block;'>
                                            <span style='color: #334155; font-size: 13px; font-weight: 700;'>⏱ {$tourDuration}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style='background: #fff1f2; border: 1px solid #ffe4e6; border-radius: 8px; padding: 6px 16px; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px;'>
                                            <span style='color: #64748b; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; text-align: center; line-height: 1.2;'>İki Kişilik Odada<br>Kişi Başı</span>
                                            <span style='color: #e11d48; font-weight: 800; font-size: 15px;'>💰 {$tourPrice}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>"
    . ($dateInfo ? "
                            <div style='background: #fef2f2; border-left: 4px solid #e11d48; border-radius: 4px; padding: 14px 18px; margin-bottom: 22px;'>
                                <span style='color: #9f1239; font-size: 14px; font-weight: 500;'>📅 En yakın tarih: <strong style='color: #881337;'>{$dateInfo}</strong></span>
                            </div>" : "") . "
                            <div style='color: #374151; font-size: 15px; line-height: 1.8; margin: 0; font-weight: 400;'>{$tourDesc}</div>
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
                            <a href='{$tourUrl}' style='display: inline-block; background: #e11d48; color: white; padding: 16px 42px; border-radius: 50px; text-decoration: none; font-weight: 800; font-size: 16px; box-shadow: 0 6px 20px -3px rgba(225, 29, 72, 0.3); letter-spacing: 0.3px;'>Turu İncele & Rezervasyon Yap →</a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 26px 40px; text-align: center; background: #0f172a;'>
                            <p style='color: #94a3b8; font-size: 13px; margin: 0 0 10px 0;'>Bu e-posta sizin talebiniz üzerine gönderilmiştir.</p>
                            <p style='color: #64748b; font-size: 12px; margin: 0;'>© " . date('Y') . " Gezgin Tilki - STH Team | Tüm hakları saklıdır.</p>
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
    $mail->Timeout = 10; // SMTP bağlantı zaman aşımı (saniye)
    // Shared hosting SSL sertifika doğrulama sorunlarını aşmak için
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];

    // Recipients
    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
    $mail->addAddress($email);
    $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);
    $mail->addBCC('operasyon@sthteam.com');

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