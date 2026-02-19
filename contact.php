<?php $has_hero = true;
include 'includes/header.php'; ?>

<section class="page-header"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; height: 300px; display: flex; align-items: center; justify-content: center; color: white; padding-top: 80px;">
    <div class="container text-center">
        <h1>İletişim</h1>
        <p>Bizimle iletişime geçin</p>
    </div>
</section>

<section class="contact-page">
    <div class="container">
        <div class="row" style="display: flex; gap: 4rem; flex-wrap: wrap;">
            <!-- Contact Info -->
            <div class="contact-info" style="flex: 1; min-width: 300px;">
                <h2 style="margin-bottom: 1.5rem;">İletişim Bilgileri</h2>
                <div class="info-item" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                    <i class="fa-solid fa-location-dot" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Adres</h4>
                        <p style="color: var(--text-light);">İstiklal Caddesi No:123<br>Beyoğlu, İstanbul</p>
                    </div>
                </div>
                <div class="info-item" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                    <i class="fa-solid fa-phone" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Telefon</h4>
                        <p style="color: var(--text-light);">+90 212 123 45 67</p>
                    </div>
                </div>
                <div class="info-item" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                    <i class="fa-solid fa-envelope" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">E-posta</h4>
                        <p style="color: var(--text-light);">info@gezgintilki.com</p>
                    </div>
                </div>

                <!-- Map Embed -->
                <div class="map" style="margin-top: 2rem; border-radius: 10px; overflow: hidden; height: 300px;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3010.165688561118!2d28.97598867655459!3d41.02157121852906!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab9e7a7777c43%3A0x4c76cf3dcc8b330b!2sGalata%20Kulesi!5e0!3m2!1str!2str!4v1689622221234!5m2!1str!2str"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form"
                style="flex: 1; min-width: 300px; background: var(--bg-light); padding: 2rem; border-radius: 10px;">
                <h2 style="margin-bottom: 1.5rem;">Bize Mesaj Gönderin</h2>
                <form action="#" method="POST">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Adınız Soyadınız</label>
                        <input type="text" name="name" required
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">E-posta
                            Adresiniz</label>
                        <input type="email" name="email" required
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Konu</label>
                        <input type="text" name="subject" required
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Mesajınız</label>
                        <textarea name="message" rows="5" required
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1.1rem;">Gönder</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>