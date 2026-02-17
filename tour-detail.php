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

include 'includes/header.php';
?>

<!-- Detail Hero Section -->
<section class="tour-hero"
    style="height: 60vh; background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('uploads/<?php echo $tour['image_url']; ?>') center/cover; position: relative; margin-top: 80px;">
    <div class="container" style="height: 100%; display: flex; align-items: flex-end; padding-bottom: 3rem;">
        <div class="tour-header" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.6);">
            <span
                style="background: var(--primary-color); padding: 0.5rem 1rem; border-radius: 5px; font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($tour['location']); ?>
                Turları</span>
            <h1 style="font-size: 3rem; margin: 1rem 0;"><?php echo htmlspecialchars($tour['title']); ?></h1>
            <div style="display: flex; gap: 2rem; font-size: 1.1rem;">
                <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($tour['duration']); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($tour['location']); ?></span>
            </div>
        </div>
    </div>
</section>

<section class="tour-detail" style="padding: 4rem 0;">
    <div class="container">
        <div class="row" style="display: flex; gap: 3rem; flex-wrap: wrap;">
            <!-- Main Content -->
            <div class="tour-content" style="flex: 2; min-width: 300px;">
                <div class="tour-tabs" style="border-bottom: 1px solid #ddd; margin-bottom: 2rem;">
                    <button class="active"
                        style="background: none; border: none; padding: 1rem 2rem; font-size: 1.1rem; border-bottom: 3px solid var(--primary-color); font-weight: 600; color: var(--secondary-color);">Genel
                        Bakış</button>
                    <!-- More tabs like Itinerary not implemented yet -->
                </div>

                <div class="content-block" style="margin-bottom: 2rem;">
                    <h3>Tur Hakkında</h3>
                    <p><?php echo nl2br(htmlspecialchars($tour['description'])); ?></p>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <aside class="booking-sidebar" style="flex: 1; min-width: 300px;">
                <div class="booking-card"
                    style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); position: sticky; top: 100px;">
                    <div class="price-header"
                        style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                        <span style="display: block; color: var(--text-light); font-size: 0.9rem;">Başlangıç
                            Fiyatı</span>
                        <span id="base-price" data-price="<?php echo $tour['price']; ?>"
                            style="font-size: 2rem; font-weight: 700; color: var(--secondary-color);">€<?php echo $tour['price']; ?></span>
                        <span style="color: var(--text-light);">/kişi başı</span>
                    </div>

                    <form action="#" class="booking-form">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Tarih Seçin</label>
                            <input type="text" id="booking-date" placeholder="Tarih seçiniz..."
                                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kişi Sayısı</label>
                            <input type="number" id="person-count" min="1" value="2"
                                style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div class="total-price"
                            style="display: flex; justify-content: space-between; font-weight: 700; margin: 1.5rem 0; font-size: 1.1rem;">
                            <span>Toplam:</span>
                            <span id="total-price" style="color: var(--primary-color);">€0</span>
                        </div>

                        <button type="submit" class="btn-primary"
                            style="width: 100%; padding: 1rem; font-size: 1.1rem;">Rezervasyon
                            Yap</button>
                    </form>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // Initialize Date Picker
                        flatpickr("#booking-date", {
                            minDate: "today",
                            dateFormat: "d.m.Y",
                            locale: "tr"
                        });

                        const basePriceElement = document.getElementById('base-price');
                        const basePrice = parseFloat(basePriceElement.getAttribute('data-price'));
                        const personCountInput = document.getElementById('person-count');
                        const totalPriceElement = document.getElementById('total-price');

                        function calculateTotal() {
                            const count = parseInt(personCountInput.value) || 1;
                            const total = basePrice * count;
                            totalPriceElement.textContent = '€' + total.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }

                        // Event Listeners
                        personCountInput.addEventListener('input', calculateTotal);
                        personCountInput.addEventListener('change', calculateTotal);

                        // Initial Calculation
                        calculateTotal();
                    });
                </script>
            </aside>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>