<?php
$current_page = 'dashboard';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

// Dashboard Statistics
$tourCount = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Recent bookings
$recentBookings = $pdo->query("SELECT b.*, t.title as tour_title FROM bookings b LEFT JOIN tours t ON b.tour_id = t.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

// Recent tours
$recentTours = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gezgin Tilki Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-title">
                <div>
                    <h1>Hoş geldin, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>
                    <div class="breadcrumb">İşte bugünkü admin panel özetin</div>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fa-solid fa-plane"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $tourCount; ?></h3>
                        <p>Toplam Tur</p>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $bookingCount; ?></h3>
                        <p>Toplam Rezervasyon</p>
                    </div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $pendingBookings; ?></h3>
                        <p>Bekleyen Rezervasyon</p>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $userCount; ?></h3>
                        <p>Kullanıcı</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if (hasPermission('tour_add')): ?>
                <div class="quick-actions">
                    <a href="tour-add.php" class="quick-action-card">
                        <div class="qa-icon" style="background: #FEF2F2; color: #D90429;">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <div class="qa-text">
                            <h4>Yeni Tur Ekle</h4>
                            <p>Yeni bir tur oluştur</p>
                        </div>
                    </a>
                    <a href="tours.php" class="quick-action-card">
                        <div class="qa-icon" style="background: #EFF6FF; color: #3B82F6;">
                            <i class="fa-solid fa-list"></i>
                        </div>
                        <div class="qa-text">
                            <h4>Turları Yönet</h4>
                            <p>Tüm turları görüntüle</p>
                        </div>
                    </a>
                    <a href="bookings.php" class="quick-action-card">
                        <div class="qa-icon" style="background: #FFFBEB; color: #F59E0B;">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <div class="qa-text">
                            <h4>Rezervasyonlar</h4>
                            <p><?php echo $pendingBookings; ?> bekleyen</p>
                        </div>
                    </a>
                    <?php if (hasPermission('user_manage')): ?>
                        <a href="users.php" class="quick-action-card">
                            <div class="qa-icon" style="background: #ECFDF5; color: #10B981;">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <div class="qa-text">
                                <h4>Kullanıcılar</h4>
                                <p><?php echo $userCount; ?> kayıtlı</p>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Data Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Recent Tours -->
                <div class="admin-card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0; font-family: 'Inter', sans-serif; font-size: 1rem; font-weight: 700;">
                            <i class="fa-solid fa-plane" style="color: #D90429;"></i> Son Eklenen Turlar
                        </h3>
                        <a href="tours.php" style="color: #D90429; font-size: 0.8rem; font-weight: 600;">Tümünü Gör
                            →</a>
                    </div>
                    <?php if (count($recentTours) > 0): ?>
                        <?php foreach ($recentTours as $tour): ?>
                            <div
                                style="display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0; border-bottom: 1px solid #F3F4F6;">
                                <img src="../uploads/<?php echo htmlspecialchars($tour['image_url']); ?>"
                                    style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" alt="Tour">
                                <div style="flex: 1; min-width: 0;">
                                    <div
                                        style="font-weight: 600; font-size: 0.85rem; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($tour['title']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #9CA3AF;">
                                        <?php echo htmlspecialchars($tour['duration']); ?>
                                    </div>
                                </div>
                                <div style="font-weight: 700; font-size: 0.85rem; color: #D90429;">
                                    <?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #9CA3AF; padding: 2rem;">Henüz tur eklenmemiş.</p>
                    <?php endif; ?>
                </div>

                <!-- Recent Bookings -->
                <div class="admin-card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0; font-family: 'Inter', sans-serif; font-size: 1rem; font-weight: 700;">
                            <i class="fa-solid fa-calendar-check" style="color: #3B82F6;"></i> Son Rezervasyonlar
                        </h3>
                        <a href="bookings.php" style="color: #D90429; font-size: 0.8rem; font-weight: 600;">Tümünü Gör
                            →</a>
                    </div>
                    <?php if (count($recentBookings) > 0): ?>
                        <?php foreach ($recentBookings as $booking): ?>
                            <div
                                style="display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0; border-bottom: 1px solid #F3F4F6;">
                                <div
                                    style="width: 36px; height: 36px; border-radius: 50%; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #3B82F6; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                                    <?php echo strtoupper(mb_substr($booking['customer_name'], 0, 1)); ?>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 600; font-size: 0.85rem; color: #111827;">
                                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #9CA3AF;">
                                        <?php echo htmlspecialchars($booking['tour_title'] ?? 'Tur silinmiş'); ?>
                                    </div>
                                </div>
                                <?php
                                $statusColors = [
                                    'pending' => 'background: #FEF3C7; color: #92400E;',
                                    'confirmed' => 'background: #D1FAE5; color: #065F46;',
                                    'cancelled' => 'background: #FEE2E2; color: #991B1B;',
                                ];
                                $statusLabels = ['pending' => 'Bekliyor', 'confirmed' => 'Onaylı', 'cancelled' => 'İptal'];
                                $status = $booking['status'];
                                ?>
                                <span
                                    style="<?php echo $statusColors[$status] ?? ''; ?> padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600;">
                                    <?php echo $statusLabels[$status] ?? $status; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #9CA3AF; padding: 2rem;">Henüz rezervasyon yok.</p>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</body>

</html>