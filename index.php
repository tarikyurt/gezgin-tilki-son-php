<?php include 'includes/header.php'; ?>

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
        <div class="form-group" style="flex: 2; min-width: 200px;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;"><i
                    class="fa-solid fa-location-dot"></i> Nereye?</label>
            <input type="text" placeholder="Destinasyon ara..."
                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
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
            <button class="btn-primary"
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
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="İtalya">
                    <div class="destination-overlay">
                        <h3>İtalya</h3>
                        <p>12 Tur</p>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1527668752968-14dc70a27c95?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="Japonya">
                    <div class="destination-overlay">
                        <h3>Japonya</h3>
                        <p>5 Tur</p>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1523531294919-4bcd7c65e216?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="İspanya">
                    <div class="destination-overlay">
                        <h3>İspanya</h3>
                        <p>8 Tur</p>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1589330273594-fade1ee91647?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="Mısır">
                    <div class="destination-overlay">
                        <h3>Mısır</h3>
                        <p>6 Tur</p>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1499678329028-101435549a4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="Venedik">
                    <div class="destination-overlay">
                        <h3>Venedik</h3>
                        <p>4 Tur</p>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="destination-card">
                    <img src="https://images.unsplash.com/photo-1548013146-72479768bada?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="Hindistan">
                    <div class="destination-overlay">
                        <h3>Hindistan</h3>
                        <p>7 Tur</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Tours Section -->
<section class="featured-tours" style="background-color: var(--bg-light);">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <h2>Öne Çıkan Turlar</h2>
            <p>Sizin için özel olarak seçtiklerimiz</p>
        </div>

        <?php
        require_once 'includes/db.php';
        // Put this query at the top of the file ideally, but here works for now
        $stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC LIMIT 3");
        $featured_tours = $stmt->fetchAll();
        ?>

        <div class="tours-grid">
            <?php foreach ($featured_tours as $tour): ?>
                <div class="tour-card"
                    style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.1); transition: transform 0.3s;">
                    <div class="tour-img" style="height: 200px; position: relative;">
                        <img src="uploads/<?php echo $tour['image_url']; ?>"
                            alt="<?php echo htmlspecialchars($tour['title']); ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">
                        <span
                            style="position: absolute; top: 1rem; right: 1rem; background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;">Popüler</span>
                    </div>
                    <div class="tour-info" style="padding: 1.5rem;">
                        <div
                            style="display: flex; justify-content: space-between; color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <span><i class="fa-regular fa-clock"></i>
                                <?php echo htmlspecialchars($tour['duration']); ?></span>
                            <span><i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($tour['location']); ?></span>
                        </div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($tour['title']); ?>
                        </h3>
                        <p
                            style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($tour['description']); ?>
                        </p>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 1rem;">
                            <span
                                style="font-weight: 700; color: var(--secondary-color); font-size: 1.25rem;">€<?php echo $tour['price']; ?></span>
                            <a href="tour-detail.php?id=<?php echo $tour['id']; ?>"
                                style="color: var(--primary-color); font-weight: 600;">İncele <i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="tours.php" class="btn-primary"
                style="background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color);">Tüm
                Turları Gör</a>
        </div>
    </div>
</section>

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

<?php include 'includes/footer.php'; ?>