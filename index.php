<?php $has_hero = true;
include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero" style="position: relative; overflow: hidden;">
    <!-- Simple Slider Backgrounds -->
    <div class="hero-bg active"
        style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:-1; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; transition: opacity 1s; opacity: 1;">
    </div>
    <div class="hero-bg"
        style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:-1; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1506929562872-bb421503ef21?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; transition: opacity 1s; opacity: 0;">
    </div>
    <div class="hero-bg"
        style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:-1; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; transition: opacity 1s; opacity: 0;">
    </div>

    <div class="hero-content">
        <h1>Dünyayı Keşfetmeye Hazır Mısın?</h1>
        <p>Hayalinizdeki tatile bir adım uzaktasınız. En özel rotalar ve unutulmaz deneyimler Gezgin Tilki'de.</p>
        <a href="tours.php" class="btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">Turları İncele</a>
    </div>
</section>

<!-- Search/Filter Section -->
<div class="container" style="margin-top: -3rem; position: relative; z-index: 10;">
    <div class="search-box"
        style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">

        <!-- Destination -->
        <div class="form-group" style="flex: 2; min-width: 200px; position: relative;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;"><i
                    class="fa-solid fa-location-dot"></i> Nereye?</label>
            <input type="text" id="search-destination" autocomplete="off" placeholder="Destinasyon ara..."
                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <div id="search-results" class="search-dropdown"></div>
        </div>

        <!-- Date Range -->
        <div class="form-group" style="flex: 2; min-width: 250px;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;"><i
                    class="fa-regular fa-calendar"></i> Tarih Aralığı</label>
            <input type="text" id="date-range" placeholder="Giriş - Çıkış Tarihi Seçin"
                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; background-color: #fff;">
        </div>

        <!-- Guest Selector -->
        <div class="form-group" style="flex: 1; min-width: 200px; position: relative;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;"><i
                    class="fa-solid fa-user-group"></i> Kişi Sayısı</label>
            <div class="guest-selector-btn"
                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; background: #fff; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <span id="guest-summary">2 Yetişkin, 0 Çocuk</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
            </div>

            <!-- Guest Dropdown with Custom Inputs -->
            <div class="guest-dropdown"
                style="position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 1rem; display: none; z-index: 100; margin-top: 5px;">
                <!-- Adults -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <span style="font-weight: 600; display: block;">Yetişkin</span>
                        <span style="font-size: 0.8rem; color: #666;">12+ Yaş</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <button type="button" class="guest-counter-btn" onclick="updateGuests('adult', -1)"
                            style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer;">-</button>
                        <span id="adult-count" style="width: 20px; text-align: center;">2</span>
                        <button type="button" class="guest-counter-btn" onclick="updateGuests('adult', 1)"
                            style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer;">+</button>
                    </div>
                </div>
                <!-- Children -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-weight: 600; display: block;">Çocuk</span>
                        <span style="font-size: 0.8rem; color: #666;">2-12 Yaş</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <button type="button" class="guest-counter-btn" onclick="updateGuests('child', -1)"
                            style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer;">-</button>
                        <span id="child-count" style="width: 20px; text-align: center;">0</span>
                        <button type="button" class="guest-counter-btn" onclick="updateGuests('child', 1)"
                            style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer;">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group" style="flex: 0 0 auto;">
            <button class="btn-primary" onclick="performSearch()"
                style="height: 48px; display: flex; align-items: center; gap: 0.5rem; padding: 0 2rem;">
                <i class="fa-solid fa-magnifying-glass"></i> Ara
            </button>
        </div>
    </div>
</div>

<!-- Featured Destinations -->
<section class="destinations">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <h2>Popüler Destinasyonlar</h2>
            <p>Sezonun en gözde tatil rotaları</p>
        </div>
        <div class="owl-carousel owl-theme destination-carousel">
            <?php
            $destinations = [
                [
                    'name' => 'Paris',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=paris',
                    'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'paris'
                ],
                [
                    'name' => 'Mısır',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=m%C4%B1s%C4%B1r',
                    'image' => 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'mısır'
                ],
                [
                    'name' => 'İtalya',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=italya',
                    'image' => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'italya'
                ],
                [
                    'name' => 'İspanya',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=ispanya',
                    'image' => 'https://images.unsplash.com/photo-1523531294919-4bcd7c65e216?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'ispanya'
                ],
                [
                    'name' => 'Benelüks',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=benel%C3%BCks',
                    'image' => 'https://images.unsplash.com/photo-1534351590666-13e3e96b5017?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'benelüks'
                ],
                [
                    'name' => 'Budva',
                    'link' => 'https://tur.gezgintilki.com/turlar?s=budva',
                    'image' => 'https://images.unsplash.com/photo-1590523278191-995cbcda646b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'search' => 'budva'
                ]
            ];
            foreach ($destinations as $dest):
                ?>
                <div class="item">
                    <a href="<?php echo $dest['link']; ?>" target="_blank" style="text-decoration: none; color: inherit;">
                        <div class="destination-card">
                            <img src="<?php echo $dest['image']; ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>">
                            <div class="destination-overlay">
                                <h3><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($dest['name']); ?>
                                </h3>
                                <div class="dest-meta">
                                    <span class="dest-tours" data-search="<?php echo urlencode($dest['search']); ?>">Turları
                                        Gör</span>
                                </div>
                                <div class="dest-cta">Keşfet <i class="fa-solid fa-arrow-right"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Tours Section -->
<?php
require_once 'includes/db.php';
$stmt = $pdo->query("
    SELECT t.*, 
           nd.quota AS next_quota, 
           nd.start_date AS next_start, 
           nd.end_date AS next_end,
           nd.price AS next_price,
           nd.currency AS next_currency
    FROM tours t
    LEFT JOIN (
        SELECT td1.* FROM tour_dates td1
        INNER JOIN (
            SELECT tour_id, MIN(start_date) AS min_date
            FROM tour_dates
            WHERE start_date >= CURDATE()
            GROUP BY tour_id
        ) td2 ON td1.tour_id = td2.tour_id AND td1.start_date = td2.min_date
    ) nd ON t.id = nd.tour_id
    WHERE t.is_featured = 1
    ORDER BY t.created_at DESC
    LIMIT 6
");
$featured_tours = $stmt->fetchAll();
?>

<?php if (!empty($featured_tours)): ?>
    <section class="featured-tours-section">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
                <span class="section-label"><i class="fa-solid fa-fire"></i> Öne Çıkan</span>
                <h2>Sizin İçin Seçtiğimiz Turlar</h2>
                <p>En beğenilen ve özel olarak öne çıkardığımız tur fırsatları</p>
            </div>

            <div class="featured-tours-grid">
                <?php foreach ($featured_tours as $tour):
                    $remaining = $tour['next_quota'] ?? null;
                    $nextStart = $tour['next_start'] ?? null;
                    $nextEnd = $tour['next_end'] ?? null;

                    // Format date for urgency badge
                    $urgencyDate = '';
                    if ($nextStart && $nextEnd) {
                        $months = [
                            '01' => 'Oca',
                            '02' => 'Şub',
                            '03' => 'Mar',
                            '04' => 'Nis',
                            '05' => 'May',
                            '06' => 'Haz',
                            '07' => 'Tem',
                            '08' => 'Ağu',
                            '09' => 'Eyl',
                            '10' => 'Eki',
                            '11' => 'Kas',
                            '12' => 'Ara'
                        ];
                        $s = new DateTime($nextStart);
                        $e = new DateTime($nextEnd);
                        $urgencyDate = $s->format('d') . ' ' . $months[$s->format('m')] . ' - ' . $e->format('d') . ' ' . $months[$e->format('m')];
                    }
                    ?>
                    <a href="tour-detail.php?id=<?php echo $tour['id']; ?>" class="featured-tour-card">
                        <div class="featured-tour-img">
                            <img src="uploads/<?php echo $tour['image_url']; ?>"
                                alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            <div class="featured-tour-overlay"></div>
                            <span class="featured-badge"><i class="fa-solid fa-star"></i> Öne Çıkan</span>
                            <div class="featured-tour-price">
                                <?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?>
                                <small>'den başlayan</small>
                            </div>
                        </div>
                        <div class="featured-tour-body">
                            <div class="featured-tour-meta">
                                <span><i class="fa-regular fa-clock"></i>
                                    <?php echo htmlspecialchars($tour['duration']); ?></span>
                                <span><i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($tour['location']); ?></span>
                            </div>
                            <h3 class="featured-tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                            <p class="featured-tour-desc"><?php echo htmlspecialchars($tour['description']); ?></p>
                            <?php if ($remaining !== null && $remaining <= 5 && $remaining > 0): ?>
                                <div class="featured-tour-urgency">
                                    <i class="fa-solid fa-fire-flame-curved"></i>
                                    <span>
                                        <?php if (!empty($urgencyDate)): ?>
                                            <strong><?php echo $urgencyDate; ?></strong> tarihinde son
                                            <strong><?php echo $remaining; ?></strong> yer!
                                        <?php else: ?>
                                            Bu turda son <strong><?php echo $remaining; ?></strong> yer!
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="featured-tour-footer">
                                <div class="featured-tour-cta">
                                    Turu İncele <i class="fa-solid fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="tours.php" class="btn-primary"
                    style="background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color);">Tüm
                    Turları Gör</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <h2>Misafir Yorumları</h2>
            <p>Gezgin Tilki ile dünyayı keşfedenlerin deneyimleri</p>
        </div>
        <div class="testimonial-card">
            <p class="testimonial-text" style="font-style: italic; color: var(--text-dark);">"Hayatımın en güzel
                tatiliydi. Rehberimiz çok bilgiliydi ve organizasyon kusursuzdu. Kesinlikle herkese tavsiye ederim!"</p>
            <div class="testimonial-user">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User">
                <div class="user-info" style="text-align: left;">
                    <h4 style="margin: 0; font-size: 1rem;">Ayşe Yılmaz</h4>
                    <span style="font-size: 0.8rem; color: var(--text-light);">Venedik Turu</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter">
    <div class="container">
        <h2>Fırsatları Kaçırmayın!</h2>
        <p>En yeni turlar ve indirimlerden haberdar olmak için bültenimize abone olun.</p>
        <form class="newsletter-form">
            <input type="email" placeholder="E-posta adresiniz">
            <button type="submit" class="btn-primary">Abone Ol</button>
        </form>
    </div>
</section>

<script>
    // Search Autocomplete Logic
    const searchInput = document.getElementById('search-destination');
    const searchResults = document.getElementById('search-results');

    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 1) { searchResults.style.display = 'none'; return; } debounceTimer = setTimeout(() => {
            fetch(`api/search-destinations.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        searchResults.innerHTML = '';
                        data.forEach(tour => {
                            const div = document.createElement('div');
                            div.className = 'search-result-item';
                            div.innerHTML = `
                                <div>
                                    <h4>${tour.title}</h4>
                                    <p><i class="fa-solid fa-location-dot"></i> ${tour.location}</p>
                                </div>
                            `; div.onclick = () => {
                                window.location.href = `tour-detail.php?id=${tour.id}`;
                            };
                            searchResults.appendChild(div);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(err => console.error('Error fetching suggestions:', err));
        }, 300);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Perform Search on Button Click
    function performSearch() {
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `tours.php?search=${encodeURIComponent(query)}`;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>