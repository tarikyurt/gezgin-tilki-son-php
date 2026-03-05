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
                        <p style="color: var(--text-light);">Bestekar Şevki Bey Sokak No: 13/1 <br>İstanbul/Balmumcu</p>
                    </div>
                </div>
                <div class="info-item" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                    <i class="fa-solid fa-phone" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Telefon</h4>
                        <p style="color: var(--text-light);">0 (212) 347 45 56</p>
                    </div>
                </div>
                <div class="info-item" style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                    <i class="fa-solid fa-envelope" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">E-posta</h4>
                        <p style="color: var(--text-light);">operasyon@sthteam.com</p>
                    </div>
                </div>

                <!-- Map Embed -->
                <div class="map" style="margin-top: 2rem; border-radius: 10px; overflow: hidden; height: 300px;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12034.04959033394!2d28.997634043485395!3d41.057791008541294!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab6510acfb3e9%3A0x88222808e53193e5!2sSTH%20Travel%20%26%20Mice!5e0!3m2!1str!2str!4v1772628402610!5m2!1str!2str"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form"
                style="flex: 1; min-width: 300px; background: var(--bg-light); padding: 2rem; border-radius: 10px;">
                <h2 style="margin-bottom: 1.5rem;">Bize Mesaj Gönderin</h2>
                <div id="contact-feedback"
                    style="display: none; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; font-weight: 500;">
                </div>
                <form id="contact-form" action="" method="POST">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Adınız Soyadınız <span
                                style="color: #dc3545;">*</span></label>
                        <input type="text" id="contact-name" name="name"
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">E-posta Adresiniz <span
                                style="color: #dc3545;">*</span></label>
                        <input type="email" id="contact-email" name="email"
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Konu</label>
                        <input type="text" id="contact-subject" name="subject"
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Mesajınız <span
                                style="color: #dc3545;">*</span></label>
                        <textarea id="contact-message" name="message" rows="5"
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                    </div>
                    <button type="submit" id="contact-submit-btn" class="btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1.1rem;">Gönder</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('contact-form');
        const feedback = document.getElementById('contact-feedback');
        const submitBtn = document.getElementById('contact-submit-btn');

        function showFeedback(message, type) {
            feedback.style.display = 'block';
            feedback.textContent = message;
            if (type === 'error') {
                feedback.style.backgroundColor = '#f8d7da';
                feedback.style.color = '#721c24';
                feedback.style.border = '1px solid #f5c6cb';
            } else if (type === 'success') {
                feedback.style.backgroundColor = '#d4edda';
                feedback.style.color = '#155724';
                feedback.style.border = '1px solid #c3e6cb';
            } else {
                feedback.style.backgroundColor = '#e2e3e5';
                feedback.style.color = '#383d41';
                feedback.style.border = '1px solid #d6d8db';
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Frontend validation
            const name = document.getElementById('contact-name').value.trim();
            const email = document.getElementById('contact-email').value.trim();
            const message = document.getElementById('contact-message').value.trim();

            if (!name || !email || !message) {
                showFeedback('Lütfen zorunlu alanları (Ad, E-posta, Mesaj) doldurunuz.', 'error');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFeedback('Lütfen geçerli bir e-posta adresi giriniz (örnek: isim@domain.com).', 'error');
                return;
            }

            // Show loading state
            showFeedback('Mesajınız gönderiliyor, lütfen bekleyin...', 'info');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Gönderiliyor...';

            // AJAX Submission
            const formData = new FormData(form);

            fetch('api/send-contact.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFeedback(data.message || 'Mesajınız başarıyla gönderildi!', 'success');
                        form.reset();
                    } else {
                        showFeedback(data.message || 'Gönderim sırasında bir hata oluştu.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showFeedback('Sunucu ile bağlantı kurulamadı. Lütfen daha sonra tekrar deneyin.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gönder';
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>