<?php
$current_page = 'bookings';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: bookings.php");
    exit;
}

// Fetch Booking & Tour Detail
$stmt = $pdo->prepare("
    SELECT b.*, t.title as tour_title, t.duration, td.start_date, td.end_date, td.currency 
    FROM bookings b 
    LEFT JOIN tours t ON b.tour_id = t.id 
    LEFT JOIN tour_dates td ON b.date_id = td.id 
    WHERE b.id = ?
");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: bookings.php");
    exit;
}

// Fetch Passengers
$stmt = $pdo->prepare("SELECT * FROM booking_passengers WHERE booking_id = ? ORDER BY type ASC, id ASC");
$stmt->execute([$id]);
$passengers = $stmt->fetchAll();

$adults = array_filter($passengers, function ($p) {
    return $p['type'] === 'adult'; });
$children = array_filter($passengers, function ($p) {
    return $p['type'] === 'child'; });

// Formatting
$statusClass = '';
$statusLabel = '';
switch ($booking['status']) {
    case 'pending':
        $statusClass = '#b7791f';
        $statusBg = '#fefcbf';
        $statusLabel = 'Bekliyor';
        break;
    case 'confirmed':
        $statusClass = '#2f855a';
        $statusBg = '#c6f6d5';
        $statusLabel = 'Onaylandı';
        break;
    case 'cancelled':
        $statusClass = '#c53030';
        $statusBg = '#fed7d7';
        $statusLabel = 'İptal Edildi';
        break;
}

$dateStr = '-';
if ($booking['start_date'] && $booking['end_date']) {
    $s = new DateTime($booking['start_date']);
    $e = new DateTime($booking['end_date']);
    $dateStr = $s->format('d.m.Y') . ' - ' . $e->format('d.m.Y');
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervasyon Detayı #
        <?php echo $booking['id']; ?> - Admin Panel
    </title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid #E5E7EB;
        }

        .detail-header {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            font-weight: 600;
        }

        .info-value {
            font-size: 0.95rem;
            color: #111827;
            font-weight: 500;
        }

        /* Passenger Tables */
        .passenger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .passenger-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            background: #F9FAFB;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            border-bottom: 1px solid #E5E7EB;
        }

        .passenger-table td {
            padding: 1rem;
            border-bottom: 1px solid #E5E7EB;
            font-size: 0.875rem;
            color: #374151;
            vertical-align: middle;
        }

        .passenger-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .gender-male {
            color: #2563EB;
            background: #DBEAFE;
        }

        .gender-female {
            color: #DB2777;
            background: #FCE7F3;
        }

        @media print {

            .sidebar,
            .action-btn,
            .breadcrumb {
                display: none !important;
            }

            .admin-layout {
                display: block;
            }

            .main-content {
                padding: 0;
                margin: 0;
            }

            .detail-card {
                box-shadow: none;
                border: 1px solid #000;
                break-inside: avoid;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title" style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1>Rezervasyon Detayı <span style="color:#6B7280; font-size:1.2rem;">#
                            <?php echo $booking['id']; ?>
                        </span></h1>
                    <div class="breadcrumb">
                        <a href="index.php" style="color:#6B7280; text-decoration:none;">Dashboard</a> /
                        <a href="bookings.php" style="color:#6B7280; text-decoration:none;">Rezervasyonlar</a> / Detay
                    </div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="action-btn" onclick="window.print()"
                        style="background: white; border: 1px solid #E5E7EB; color: #374151; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-print"></i> Yazdır
                    </button>
                    <a href="bookings.php" class="action-btn"
                        style="background: #E5E7EB; color: #374151; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-arrow-left"></i> Geri Dön
                    </a>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="detail-card">
                <div class="detail-header">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i>
                    Genel Bilgiler
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Rezervasyon Tarihi</span>
                        <span class="info-value">
                            <?php echo date('d.m.Y H:i', strtotime($booking['created_at'])); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durum</span>
                        <span class="badge"
                            style="background: <?php echo $statusBg; ?>; color: <?php echo $statusClass; ?>;">
                            <?php echo $statusLabel; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">İletişim Kurulacak Kişi</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($booking['customer_name']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">İletişim E-Posta</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($booking['customer_email']); ?>"
                                style="color: var(--primary-color); text-decoration: none;">
                                <?php echo htmlspecialchars($booking['customer_email']); ?>
                            </a>
                        </span>
                    </div>
                </div>

                <div style="height: 1px; background: #E5E7EB; margin: 1.5rem 0;"></div>

                <div class="info-grid">
                    <div class="info-item" style="grid-column: span 2;">
                        <span class="info-label">Tur Adı</span>
                        <span class="info-value"
                            style="font-weight: 700; font-size: 1.1rem; color: var(--secondary-color);">
                            <?php echo htmlspecialchars($booking['tour_title'] ?? 'Silinmiş Tur'); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tur Tarihi</span>
                        <span class="info-value">
                            <?php echo $dateStr; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Yolcu Dağılımı</span>
                        <span class="info-value">
                            <?php echo $booking['guests']; ?> Kişi (
                            <?php echo $booking['adults']; ?> Y,
                            <?php echo $booking['children']; ?> Ç)
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Toplam Tutar</span>
                        <span class="info-value"
                            style="font-size: 1.2rem; font-weight: 800; color: var(--primary-color);">
                            <?php echo formatCurrency($booking['total_price'], $booking['currency'] ?? 'EUR'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Adults Card -->
            <?php if (count($adults) > 0): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <i class="fa-solid fa-users" style="color: #667eea;"></i>
                        Yetişkin Yolcular (
                        <?php echo count($adults); ?>)
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="passenger-table">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>TC Kimlik No</th>
                                    <th>Doğum Tarihi</th>
                                    <th>Cinsiyet</th>
                                    <th>Pasaport No</th>
                                    <th>Pasaport S.K.T.</th>
                                    <th>E-Posta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($adults as $p): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: #111827;">
                                            <?php echo htmlspecialchars($p['full_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['tc_no'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo $p['birth_date'] ? date('d.m.Y', strtotime($p['birth_date'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($p['gender'] == 'male'): ?>
                                                <span class="badge gender-male"><i class="fa-solid fa-mars"
                                                        style="margin-right:4px;"></i> Erkek</span>
                                            <?php elseif ($p['gender'] == 'female'): ?>
                                                <span class="badge gender-female"><i class="fa-solid fa-venus"
                                                        style="margin-right:4px;"></i> Kadın</span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['passport_no'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo $p['passport_expiry'] ? date('d.m.Y', strtotime($p['passport_expiry'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['email'] ?: '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Children Card -->
            <?php if (count($children) > 0): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <i class="fa-solid fa-child" style="color: #f5576c;"></i>
                        Çocuk Yolcular (
                        <?php echo count($children); ?>)
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="passenger-table">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>TC Kimlik No</th>
                                    <th>Doğum Tarihi</th>
                                    <th>Cinsiyet</th>
                                    <th>Yaş</th>
                                    <th>Yakınlık</th>
                                    <th>Pasaport No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $p): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: #111827;">
                                            <?php echo htmlspecialchars($p['full_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['tc_no'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <?php echo $p['birth_date'] ? date('d.m.Y', strtotime($p['birth_date'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($p['gender'] == 'male'): ?>
                                                <span class="badge gender-male"><i class="fa-solid fa-mars"
                                                        style="margin-right:4px;"></i> Erkek</span>
                                            <?php elseif ($p['gender'] == 'female'): ?>
                                                <span class="badge gender-female"><i class="fa-solid fa-venus"
                                                        style="margin-right:4px;"></i> Kadın</span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $p['child_age'] ? $p['child_age'] . ' Yaş' : '-'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($p['relationship'] == '1')
                                                echo '1. Derece';
                                            elseif ($p['relationship'] == '2')
                                                echo '2. Derece';
                                            elseif ($p['relationship'] == 'other')
                                                echo 'Diğer';
                                            else
                                                echo '-';
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['passport_no'] ?: '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>