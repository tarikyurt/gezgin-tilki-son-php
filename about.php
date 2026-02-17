<?php include 'includes/header.php'; ?>

<section class="page-header"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; height: 300px; display: flex; align-items: center; justify-content: center; color: white; padding-top: 80px;">
    <div class="container text-center">
        <h1>Hakkımızda</h1>
        <p>Gezgin Tilki'nin hikayesi ve değerleri</p>
    </div>
</section>

<section class="about-content">
    <div class="container">
        <div class="row" style="display: flex; gap: 4rem; align-items: center; flex-wrap: wrap;">
            <div class="col-text" style="flex: 1; min-width: 300px;">
                <h2 style="margin-bottom: 1.5rem;">Biz Kimiz?</h2>
                <p style="margin-bottom: 1rem; color: var(--text-light);">Gezgin Tilki, seyahat tutkusunu profesyonel
                    bir hizmet anlayışıyla birleştiren, yenilikçi ve dinamik bir turizm acentesidir. 2026 yılında
                    kurulan firmamız, misafirlerine sadece bir tatil değil, unutulmaz deneyimler sunmayı
                    hedeflemektedir.</p>
                <p style="margin-bottom: 1rem; color: var(--text-light);">Dünyanın en gizli kalmış köşelerinden en
                    popüler destinasyonlarına kadar geniş bir yelpazede turlar düzenliyoruz. Her bütçeye ve zevke uygun
                    seçeneklerimizle, hayalinizdeki tatili gerçeğe dönüştürmek için çalışıyoruz.</p>

                <h3 style="margin: 2rem 0 1rem;">Misyonumuz</h3>
                <p style="color: var(--text-light);">Misafirlerimize güvenilir, konforlu ve keyifli seyahat deneyimleri
                    sunarak, Türkiye'nin önde gelen turizm markalarından biri olmak.</p>
            </div>
            <div class="col-img" style="flex: 1; min-width: 300px;">
                <img src="https://images.unsplash.com/photo-1504812753440-29faab99648a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                    alt="Team" style="border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
</section>

<section class="team" style="background-color: var(--bg-light);">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
            <h2>Ekibimiz</h2>
            <p>Sizler için çalışan profesyonel kadromuz</p>
        </div>
        <div class="team-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
            <div class="team-member">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Member"
                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h4>Ahmet Yılmaz</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">Kurucu & CEO</p>
            </div>
            <div class="team-member">
                <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Member"
                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h4>Zeynep Kaya</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">Operasyon Müdürü</p>
            </div>
            <div class="team-member">
                <img src="https://randomuser.me/api/portraits/men/85.jpg" alt="Member"
                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h4>Mehmet Demir</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">Baş Rehber</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>