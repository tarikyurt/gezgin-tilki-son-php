<?php
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: tours.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$_GET['id']]);
$tour = $stmt->fetch();

if (!$tour) {
    header("Location: tours.php");
    exit;
}

// Get Itinerary (Mock data if empty for demo)
$itinerary_stmt = $pdo->prepare("SELECT * FROM tour_itineraries WHERE tour_id = ? ORDER BY day_number");
$itinerary_stmt->execute([$tour['id']]);
$itinerary = $itinerary_stmt->fetchAll();

// Get Inclusions
$inclusions_stmt = $pdo->prepare("SELECT * FROM tour_inclusions WHERE tour_id = ?");
$inclusions_stmt->execute([$tour['id']]);
$inclusions = $inclusions_stmt->fetchAll();

// Get Key Features
$features_stmt = $pdo->prepare("SELECT * FROM tour_key_features WHERE tour_id = ?");
$features_stmt->execute([$tour['id']]);
$features = $features_stmt->fetchAll();

// Get Dates (Fetch ALL future dates)
$dates_stmt = $pdo->prepare("SELECT * FROM tour_dates WHERE tour_id = ? AND start_date >= CURDATE() ORDER BY start_date ASC");
$dates_stmt->execute([$tour['id']]);
$all_dates = $dates_stmt->fetchAll(PDO::FETCH_ASSOC);

// Hero Date gets the first nearest date
$hero_date = $all_dates[0] ?? null;

// Process Inclusions
$included = [];
$excluded = [];
foreach ($inclusions as $inc) {
    if ($inc['is_included'] == 1) {
        $included[] = $inc['item'];
    } else {
        $excluded[] = $inc['item'];
    }
}

$has_hero = true;
include 'includes/header.php';
?>

<!-- 1. Hero Section -->
<section class="tour-hero-pro">
    <!-- Hero Background -->
    <div class="hero-bg"
        style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.85)), url('uploads/<?php echo $tour['image_url']; ?>') center/cover no-repeat;">
    </div>

    <!-- Container -->
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- TITLE -->
        <h1 class="tour-title">
            <?php echo htmlspecialchars($tour['title']); ?>
        </h1>

        <!-- INFO STRIP -->
        <div class="hero-info-strip">
            <?php if ($hero_date): ?>
                <div class="hero-info-item">
                    <i class="fa-regular fa-calendar-days"></i>
                    <div class="hero-info-text">
                        <span class="hero-info-label">Tarih</span>
                        <span class="hero-info-value">
                            <?php
                            $start = new DateTime($hero_date['start_date']);
                            $end = new DateTime($hero_date['end_date']);
                            $months = [
                                'January' => 'Ocak',
                                'February' => 'Şubat',
                                'March' => 'Mart',
                                'April' => 'Nisan',
                                'May' => 'Mayıs',
                                'June' => 'Haziran',
                                'July' => 'Temmuz',
                                'August' => 'Ağustos',
                                'September' => 'Eylül',
                                'October' => 'Ekim',
                                'November' => 'Kasım',
                                'December' => 'Aralık'
                            ];
                            $sMonth = $months[$start->format('F')];
                            $eMonth = $months[$end->format('F')];
                            if ($start->format('Y') == $end->format('Y')) {
                                echo $start->format('d') . ' - ' . $end->format('d') . ' ' . $eMonth . ' ' . $end->format('Y');
                            } else {
                                echo $start->format('d M Y') . ' - ' . $end->format('d M Y');
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <div class="hero-info-divider"></div>
            <?php endif; ?>

            <div class="hero-info-item">
                <i class="fa-solid fa-location-dot"></i>
                <div class="hero-info-text">
                    <span class="hero-info-label">Lokasyon</span>
                    <span class="hero-info-value"><?php echo htmlspecialchars($tour['location']); ?></span>
                </div>
            </div>

            <div class="hero-info-divider"></div>

            <div class="hero-info-item">
                <i class="fa-solid fa-clock"></i>
                <div class="hero-info-text">
                    <span class="hero-info-label">Süre</span>
                    <span class="hero-info-value"><?php echo htmlspecialchars($tour['duration']); ?></span>
                </div>
            </div>

            <div class="hero-info-divider"></div>

            <div class="hero-info-item hero-info-price">
                <i class="fa-solid fa-tag"></i>
                <div class="hero-info-text">
                    <!-- <span class="hero-info-label">Kişi Başı</span> -->
                    <span
                        class="hero-info-value"><?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?>'den
                        başlayan fiyatlarla</span>
                </div>
            </div>
        </div>

        <!-- Hero CTA -->
        <div class="hero-cta">
            <a href="#booking-section" class="btn-primary">
                <i class="fa-solid fa-calendar-check"></i> Rezervasyon Yap
            </a>
        </div>
    </div>
</section>

<!-- 2. Quick Tour Summary Block -->
<section class="quick-summary">
    <div class="container">
        <div class="row" style="display: flex; align-items: flex-start; gap: 4rem; flex-wrap: wrap;">
            <div class="summary-text" style="flex: 1.5; min-width: 300px;">
                <span class="section-label">Tur Hakkında</span>
                <h3><?php echo htmlspecialchars($tour['title']); ?></h3>
                <p>
                    <?php echo nl2br(htmlspecialchars($tour['description'])); ?>
                </p>
            </div>

            <div class="highlights-grid"
                style="flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; min-width: 280px;">
                <?php
                if (count($features) > 0) {
                    foreach ($features as $feat) {
                        echo '<div class="highlight-item">
                            <div class="icon-box">
                                <i class="' . htmlspecialchars($feat['icon']) . '"></i>
                            </div>
                            <div>
                                <span style="display: block; font-weight: 700; color: var(--secondary-color);">' . htmlspecialchars($feat['title']) . '</span>
                                <span style="font-size: 0.85rem; color: var(--text-light);">' . htmlspecialchars($feat['subtitle']) . '</span>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div style="grid-column: 1 / -1; color: var(--text-light); font-style: italic;">Öne çıkan özellik eklenmedi.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</section>

<!-- 3. Day-by-Day Itinerary Section -->
<section class="itinerary-section">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 1.5rem;">
            <span>TUR PROGRAMI</span>
            <h2>Gün Gün Gezi Rotası</h2>
        </div>

        <!-- Email CTA Button -->
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <button type="button" class="email-itinerary-btn" onclick="openEmailModal()">
                <i class="fa-solid fa-envelope"></i>
                Tur Programını Mail ile Al
            </button>
        </div>

        <div class="itinerary-list">
            <?php
            if (empty($itinerary)) {
                $itinerary = [
                    ['day_number' => 1, 'title' => 'İstanbul Havalimanı Buluşma ve Hareket', 'description' => 'İstanbul Havalimanı Dış Hatlar Terminali\'nde rehberimizle buluşma. Bilet ve bagaj işlemlerinden sonra, Türk Hava Yolları tarifeli seferi ile hareket ediyoruz.'],
                    ['day_number' => 2, 'title' => 'Destinasyona Varış ve Şehir Turu', 'description' => 'Havalimanında bizi bekleyen özel aracımızla otele transfer. Kısa bir dinlenmenin ardından panoramik şehir turumuz başlıyor.'],
                    ['day_number' => 3, 'title' => 'Tarihi Yerler ve Öğle Yemeği', 'description' => 'Kahvaltının ardından şehrin simge yapılarını ziyaret ediyoruz. Öğle yemeğimiz yerel bir restoranda.'],
                    ['day_number' => 4, 'title' => 'Serbest Zaman ve Alışveriş', 'description' => 'Bugün tamamen size ait! İsterseniz ekstra turlara katılabilir veya alışveriş yapabilirsiniz.'],
                    ['day_number' => 5, 'title' => 'Dönüş Yolculuğu', 'description' => 'Otelde yapacağımız kahvaltının ardından havalimanına transfer ve İstanbul\'a dönüş.']
                ];
            }

            foreach ($itinerary as $day):
                ?>
                <div class="itinerary-item">
                    <div class="itinerary-header">
                        <div style="display: flex; align-items: center; gap: 1.5rem;">
                            <span class="day-badge">
                                <?php echo $day['day_number']; ?>
                            </span>
                            <h4>
                                <?php echo htmlspecialchars($day['title']); ?>
                            </h4>
                        </div>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="itinerary-body" style="display: none;">
                        <?php echo nl2br(htmlspecialchars($day['description'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Email Modal -->
<div id="emailModal" class="email-modal-overlay" style="display: none;"
    onclick="if(event.target===this) closeEmailModal()">
    <div class="email-modal">
        <button type="button" class="email-modal-close" onclick="closeEmailModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="email-modal-icon">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <h3>Tur Programını Mail ile Al</h3>
        <p>E-posta adresinizi girin, <strong><?php echo htmlspecialchars($tour['title']); ?></strong> turunun detaylı
            programını hemen gönderelim.</p>

        <form id="emailForm" onsubmit="sendItinerary(event)">
            <div class="email-input-group">
                <i class="fa-solid fa-at"></i>
                <input type="email" id="emailInput" placeholder="ornek@email.com" required autocomplete="email">
            </div>
            <button type="submit" class="email-send-btn" id="emailSendBtn">
                <span class="btn-text"><i class="fa-solid fa-paper-plane"></i> Gönder</span>
                <span class="btn-loading" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i>
                    Gönderiliyor...</span>
            </button>
        </form>

        <div id="emailResult" style="display: none;"></div>

        <p class="email-modal-note">
            <i class="fa-solid fa-shield-halved"></i> E-posta adresiniz sadece bu işlem için kullanılır.
        </p>
    </div>
</div>

<!-- 4. Included / Not Included Section -->
<section class="inclusions-section">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <span
                style="color: var(--primary-color); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px;">DETAYLAR</span>
            <h2 style="font-size: 2.2rem; color: var(--secondary-color); margin-top: 0.5rem;">Fiyata Neler Dahil?</h2>
        </div>
        <div class="row" style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <!-- Included -->
            <div class="col-included">
                <h3>
                    <i class="fa-solid fa-circle-check"></i> Fiyata Dahil Olanlar
                </h3>
                <ul class="check-list" style="list-style: none;">
                    <?php
                    if (empty($included)) {
                        echo '<li style="color: var(--text-light); font-style: italic;">Belirtilmemiş.</li>';
                    } else {
                        foreach ($included as $item):
                            ?>
                            <li>
                                <i class="fa-solid fa-check"></i>
                                <?php echo htmlspecialchars($item); ?>
                            </li>
                        <?php endforeach;
                    } ?>
                </ul>
            </div>

            <!-- Not Included -->
            <div class="col-excluded">
                <h3>
                    <i class="fa-solid fa-circle-xmark"></i> Fiyata Dahil Olmayanlar
                </h3>
                <ul class="cross-list" style="list-style: none;">
                    <?php
                    if (empty($excluded)) {
                        echo '<li style="color: var(--text-light); font-style: italic;">Belirtilmemiş.</li>';
                    } else {
                        foreach ($excluded as $item):
                            ?>
                            <li>
                                <i class="fa-solid fa-xmark"></i>
                                <?php echo htmlspecialchars($item); ?>
                            </li>
                        <?php endforeach;
                    } ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<section id="booking-section" class="pricing-dates">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <h2>Tur Tarihleri ve Fiyatlar</h2>
            <p>Size en uygun tarihi seçin ve yerinizi ayırtın.</p>
        </div>

        <div class="dates-table-wrapper" style="overflow-x: auto;">
            <table class="dates-table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th style="padding: 1rem;">Başlangıç</th>
                        <th style="padding: 1rem;">Bitiş</th>
                        <th style="padding: 1rem;">Otel</th>
                        <th style="padding: 1rem; text-align: center;">İki Kişilik Odada Kişi Başı</th>
                        <th style="padding: 1rem;">Durum</th>
                        <th style="padding: 1rem;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($all_dates) > 0):
                        foreach ($all_dates as $date):
                            $startDate = new DateTime($date['start_date']);
                            $endDate = new DateTime($date['end_date']);
                            $months = [
                                'January' => 'Ocak',
                                'February' => 'Şubat',
                                'March' => 'Mart',
                                'April' => 'Nisan',
                                'May' => 'Mayıs',
                                'June' => 'Haziran',
                                'July' => 'Temmuz',
                                'August' => 'Ağustos',
                                'September' => 'Eylül',
                                'October' => 'Ekim',
                                'November' => 'Kasım',
                                'December' => 'Aralık'
                            ];
                            $sStr = $startDate->format('d') . ' ' . $months[$startDate->format('F')] . ' ' . $startDate->format('Y');
                            $eStr = $endDate->format('d') . ' ' . $months[$endDate->format('F')] . ' ' . $endDate->format('Y');
                            $remaining = $date['quota'];
                            ?>
                            <tr>
                                <td style="font-weight: 600;">
                                    <i class="fa-regular fa-calendar"
                                        style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                                    <?php echo $sStr; ?>
                                </td>
                                <td><?php echo $eStr; ?></td>
                                <td style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">
                                    <?php
                                    $stars = $date['hotel_stars'] ?? '';
                                    echo $stars ? htmlspecialchars($stars) : '<span style="opacity:0.5">-</span>';
                                    ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span
                                            style="font-size: 1.35rem; font-weight: 800; color: var(--primary-color); letter-spacing: -0.5px;">
                                            <?php echo formatCurrency($date['price'], $date['currency'] ?? ($tour['currency'] ?? 'EUR')); ?>
                                        </span>
                                        <?php if ($remaining <= 5 && $remaining > 0): ?>
                                            <div class="fomo-text"
                                                style="font-size: 0.75rem; color: #dc3545; font-weight: 700; margin-top: 4px; display: flex; align-items: center; gap: 4px; animation: pulse 2s infinite;">
                                                <i class="fa-solid fa-fire-flame-curved"></i>
                                                Bu fiyata son <?php echo $remaining; ?> yer!
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($remaining > 5): ?>
                                        <span
                                            style="background: rgba(46, 125, 50, 0.2); color: #81c784; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">Müsait</span>
                                    <?php elseif ($remaining > 0): ?>
                                        <span
                                            style="background: rgba(217, 4, 41, 0.2); color: #ef9a9a; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">Son
                                            <?php echo $remaining; ?> Kişi!</span>
                                    <?php else: ?>
                                        <span
                                            style="background: rgba(100, 100, 100, 0.2); color: #bbb; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">Doldu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($remaining > 0): ?>
                                        <button class="btn-primary"
                                            onclick="alert('Rezervasyon formu açılacak... ID: <?php echo $date['id']; ?>')"
                                            style="padding: 0.6rem 1.5rem; font-size: 0.9rem;">Seç</button>
                                    <?php else: ?>
                                        <button class="btn-primary" disabled
                                            style="padding: 0.6rem 1.5rem; font-size: 0.9rem; opacity: 0.5; cursor: not-allowed;">Dolu</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="6"
                                style="text-align: center; padding: 2rem; color: rgba(255,255,255,0.6); font-style: italic;">
                                Planlanmış tur tarihi bulunmamaktadır.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Mobile Sticky Booking CTA -->
<div class="mobile-booking-bar">
    <div>
        <span style="display: block; font-size: 0.8rem; color: var(--text-light);">Başlangıç Fiyatı</span>
        <span
            style="font-weight: 700; font-size: 1.25rem; color: var(--primary-color);"><?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?></span>
    </div>
    <a href="#booking-section" class="btn-primary" style="padding: 0.8rem 1.5rem;">Rezervasyon</a>
</div>

<script>
    // Itinerary Accordion
    document.querySelectorAll('.itinerary-header').forEach(header => {
        header.addEventListener('click', () => {
            const body = header.nextElementSibling;
            const icon = header.querySelector('i');
            const isActive = body.style.display === 'block';

            // Close all others
            document.querySelectorAll('.itinerary-body').forEach(b => b.style.display = 'none');
            document.querySelectorAll('.itinerary-header i').forEach(i => i.style.transform = 'rotate(0deg)');

            if (!isActive) {
                body.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });

    // Email Modal Functions
    const TOUR_ID = <?php echo $tour['id']; ?>;

    function openEmailModal() {
        const modal = document.getElementById('emailModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('emailInput').focus(), 300);
        // Reset state
        document.getElementById('emailForm').style.display = 'block';
        document.getElementById('emailResult').style.display = 'none';
        document.getElementById('emailInput').value = '';
    }

    function closeEmailModal() {
        document.getElementById('emailModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeEmailModal();
    });

    function sendItinerary(e) {
        e.preventDefault();
        const email = document.getElementById('emailInput').value;
        const btn = document.getElementById('emailSendBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        const resultDiv = document.getElementById('emailResult');

        // Loading state
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';

        const formData = new FormData();
        formData.append('email', email);
        formData.append('tour_id', TOUR_ID);

        fetch('api/send-itinerary.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btnText.style.display = 'inline-flex';
                btnLoading.style.display = 'none';

                resultDiv.style.display = 'block';
                if (data.success) {
                    resultDiv.className = 'email-result-success';
                    resultDiv.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    document.getElementById('emailForm').style.display = 'none';
                } else {
                    resultDiv.className = 'email-result-error';
                    resultDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btnText.style.display = 'inline-flex';
                btnLoading.style.display = 'none';
                resultDiv.style.display = 'block';
                resultDiv.className = 'email-result-error';
                resultDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Bir hata oluştu. Lütfen tekrar deneyin.';
            });
    }
</script>

<?php include 'includes/footer.php'; ?>