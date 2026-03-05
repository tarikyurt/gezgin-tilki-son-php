<?php
require_once 'includes/db.php';

// Validate params
$tour_id = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;
$date_id = isset($_GET['date_id']) ? intval($_GET['date_id']) : 0;

if (!$tour_id || !$date_id) {
    header("Location: tours.php");
    exit;
}

// Fetch tour
$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();
if (!$tour) {
    header("Location: tours.php");
    exit;
}

// Fetch selected date
$stmt = $pdo->prepare("SELECT * FROM tour_dates WHERE id = ? AND tour_id = ?");
$stmt->execute([$date_id, $tour_id]);
$date = $stmt->fetch();
if (!$date) {
    header("Location: tour-detail.php?id=" . $tour_id);
    exit;
}

// Format date for display
$months_tr = [
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
$startDt = new DateTime($date['start_date']);
$endDt = new DateTime($date['end_date']);
$startStr = $startDt->format('d') . ' ' . $months_tr[$startDt->format('F')] . ' ' . $startDt->format('Y');
$endStr = $endDt->format('d') . ' ' . $months_tr[$endDt->format('F')] . ' ' . $endDt->format('Y');
$currency = $date['currency'] ?? ($tour['currency'] ?? 'EUR');
$pricePerPerson = floatval($date['price']);

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/reservation.css">

<div class="rsv-page">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

        <!-- Breadcrumb -->
        <div class="rsv-breadcrumb">
            <a href="index.php">Anasayfa</a>
            <span>›</span>
            <a href="tour-detail.php?id=<?php echo $tour['id']; ?>">
                <?php echo htmlspecialchars($tour['title']); ?>
            </a>
            <span>›</span>
            Rezervasyon
        </div>

        <!-- Step Indicator -->
        <div class="rsv-steps">
            <div class="rsv-step rsv-step-active" id="rsv-step-ind-1">
                <span class="rsv-step-num">1</span>
                <span class="rsv-step-label">Kişi Seçimi</span>
            </div>
            <div class="rsv-step-divider" id="rsv-step-div-1"></div>
            <div class="rsv-step" id="rsv-step-ind-2">
                <span class="rsv-step-num">2</span>
                <span class="rsv-step-label">Kişi Bilgileri</span>
            </div>
        </div>

        <!-- Layout: Form + Sidebar -->
        <div class="rsv-layout">

            <!-- LEFT: Form Area -->
            <div class="rsv-main">

                <!-- STEP 1: Person Selection -->
                <div class="rsv-card rsv-animate-in" id="rsv-step1">
                    <h2 class="rsv-card-title">Kişi Sayısı Seçin</h2>
                    <p class="rsv-card-subtitle">Tura katılacak yetişkin ve çocuk sayısını belirleyin.</p>

                    <div class="rsv-selector-group">
                        <!-- Adult -->
                        <div class="rsv-selector">
                            <div class="rsv-selector-info">
                                <div class="rsv-selector-icon rsv-icon-adult">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <span class="rsv-selector-label">Yetişkin</span>
                                    <span class="rsv-selector-desc">12 yaş ve üzeri</span>
                                </div>
                            </div>
                            <div class="rsv-counter">
                                <button type="button" class="rsv-counter-btn" id="rsv-adult-minus"
                                    onclick="rsvChangeCount('adult', -1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <span class="rsv-counter-val" id="rsv-adult-count">1</span>
                                <button type="button" class="rsv-counter-btn" onclick="rsvChangeCount('adult', 1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Child -->
                        <div class="rsv-selector">
                            <div class="rsv-selector-info">
                                <div class="rsv-selector-icon rsv-icon-child">
                                    <i class="fa-solid fa-child"></i>
                                </div>
                                <div>
                                    <span class="rsv-selector-label">Çocuk</span>
                                    <span class="rsv-selector-desc">1 – 12 yaş arası</span>
                                </div>
                            </div>
                            <div class="rsv-counter">
                                <button type="button" class="rsv-counter-btn" id="rsv-child-minus"
                                    onclick="rsvChangeCount('child', -1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <span class="rsv-counter-val" id="rsv-child-count">0</span>
                                <button type="button" class="rsv-counter-btn" onclick="rsvChangeCount('child', 1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rsv-actions">
                        <a href="tour-detail.php?id=<?php echo $tour['id']; ?>" class="rsv-btn-back">
                            <i class="fa-solid fa-arrow-left"></i> Geri Dön
                        </a>
                        <button type="button" class="rsv-btn-next" onclick="rsvGoToStep2()">
                            Devam Et <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Person Details -->
                <div class="rsv-card" id="rsv-step2" style="display: none;">
                    <h2 class="rsv-card-title">Yolcu Bilgileri</h2>
                    <p class="rsv-card-subtitle">Her yolcu için gerekli bilgileri doldurun. <strong>*</strong> işaretli
                        alanlar zorunludur.</p>

                    <form id="rsv-form" onsubmit="rsvSubmit(event)">
                        <div class="rsv-forms-container" id="rsv-forms-container">
                            <!-- Dynamically generated -->
                        </div>

                        <div class="rsv-validation-msg" id="rsv-validation-msg">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>Lütfen tüm zorunlu alanları (*) doldurunuz.</span>
                        </div>

                        <div class="rsv-actions">
                            <button type="button" class="rsv-btn-back" onclick="rsvGoToStep1()">
                                <i class="fa-solid fa-arrow-left"></i> Geri Dön
                            </button>
                            <button type="submit" class="rsv-btn-next" id="rsv-submit-btn" disabled>
                                <i class="fa-solid fa-check"></i> Rezervasyonu Tamamla
                            </button>
                        </div>
                    </form>
                </div>

                <!-- SUCCESS -->
                <div class="rsv-card" id="rsv-success" style="display: none;">
                    <div class="rsv-success rsv-animate-in">
                        <div class="rsv-success-icon">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h2>Rezervasyonunuz Alındı!</h2>
                        <p>En kısa sürede sizinle iletişime geçeceğiz. Teşekkür ederiz.</p>
                        <div style="margin-top: 2rem;">
                            <a href="index.php" class="rsv-btn-next" style="text-decoration: none;">
                                <i class="fa-solid fa-home"></i> Ana Sayfaya Dön
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Price Summary Sidebar -->
            <div class="rsv-sidebar">
                <div class="rsv-summary-card">
                    <div class="rsv-summary-header">
                        <h3><i class="fa-solid fa-receipt" style="margin-right: 0.5rem;"></i> Rezervasyon Özeti</h3>
                    </div>

                    <div class="rsv-summary-tour">
                        <p class="rsv-summary-tour-name">
                            <?php echo htmlspecialchars($tour['title']); ?>
                        </p>
                        <p class="rsv-summary-tour-meta">
                            <i class="fa-regular fa-calendar-days"></i>
                            <?php echo $startStr . ' – ' . $endStr; ?>
                        </p>
                        <p class="rsv-summary-tour-meta" style="margin-top: 0.3rem;">
                            <i class="fa-solid fa-clock"></i>
                            <?php echo htmlspecialchars($tour['duration']); ?>
                        </p>
                    </div>

                    <div class="rsv-summary-body">
                        <div class="rsv-summary-line">
                            <span>Kişi Başı Fiyat</span>
                            <span>
                                <?php echo formatCurrency($pricePerPerson, $currency); ?>
                            </span>
                        </div>
                        <div class="rsv-summary-divider"></div>
                        <div class="rsv-summary-line" id="rsv-summary-adults">
                            <span><i class="fa-solid fa-user" style="margin-right: 0.3rem; color: #667eea;"></i> 1
                                Yetişkin</span>
                            <span>
                                <?php echo formatCurrency($pricePerPerson, $currency); ?>
                            </span>
                        </div>
                        <div class="rsv-summary-line" id="rsv-summary-children" style="display: none;">
                            <span><i class="fa-solid fa-child" style="margin-right: 0.3rem; color: #f5576c;"></i> 0
                                Çocuk</span>
                            <span>
                                <?php echo formatCurrency(0, $currency); ?>
                            </span>
                        </div>
                        <div class="rsv-summary-divider"></div>
                        <div class="rsv-summary-total">
                            <span>Toplam</span>
                            <span class="rsv-summary-total-price" id="rsv-total-price">
                                <?php echo formatCurrency($pricePerPerson, $currency); ?>
                            </span>
                        </div>
                    </div>

                    <p class="rsv-summary-note">
                        <i class="fa-solid fa-info-circle" style="margin-right: 0.3rem;"></i>
                        İki kişilik odada kişi başı fiyattır. Çocuk ücreti aynı kişi başı fiyattan hesaplanır.
                    </p>
                </div>
            </div>

        </div><!-- /rsv-layout -->
    </div>
</div>

<script>
    // ---- RESERVATION JS ----
    const RSV_PRICE = <?php echo $pricePerPerson; ?>;
    const RSV_CURRENCY = '<?php echo $currency; ?>';
    const RSV_TOUR_ID = <?php echo $tour['id']; ?>;
    const RSV_DATE_ID = <?php echo $date['id']; ?>;
    const RSV_QUOTA = <?php echo $date['quota']; ?>;

    let rsvAdults = 1;
    let rsvChildren = 0;

    function rsvFormatCurrency(amount) {
        const sym = RSV_CURRENCY === 'TRY' ? '₺' : (RSV_CURRENCY === 'USD' ? '$' : '€');
        return sym + amount.toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function rsvChangeCount(type, delta) {
        if (type === 'adult') {
            rsvAdults = Math.max(1, Math.min(rsvAdults + delta, RSV_QUOTA));
        } else {
            rsvChildren = Math.max(0, Math.min(rsvChildren + delta, 6));
        }
        document.getElementById('rsv-adult-count').textContent = rsvAdults;
        document.getElementById('rsv-child-count').textContent = rsvChildren;
        document.getElementById('rsv-adult-minus').disabled = (rsvAdults <= 1);
        document.getElementById('rsv-child-minus').disabled = (rsvChildren <= 0);
        rsvUpdateSummary();
    }

    function rsvUpdateSummary() {
        const adultTotal = rsvAdults * RSV_PRICE;
        const childTotal = rsvChildren * RSV_PRICE;
        const total = adultTotal + childTotal;

        // Adults line
        const aSummary = document.getElementById('rsv-summary-adults');
        aSummary.querySelector('span:first-child').innerHTML = '<i class="fa-solid fa-user" style="margin-right: 0.3rem; color: #667eea;"></i> ' + rsvAdults + ' Yeti\u015fkin';
        aSummary.querySelector('span:last-child').textContent = rsvFormatCurrency(adultTotal);

        // Children line
        const cSummary = document.getElementById('rsv-summary-children');
        if (rsvChildren > 0) {
            cSummary.style.display = 'flex';
            cSummary.querySelector('span:first-child').innerHTML = '<i class="fa-solid fa-child" style="margin-right: 0.3rem; color: #f5576c;"></i> ' + rsvChildren + ' \u00c7ocuk';
            cSummary.querySelector('span:last-child').textContent = rsvFormatCurrency(childTotal);
        } else {
            cSummary.style.display = 'none';
        }

        // Total
        document.getElementById('rsv-total-price').textContent = rsvFormatCurrency(total);
    }

    // ---- STEP NAVIGATION ----
    function rsvGoToStep2() {
        document.getElementById('rsv-step1').style.display = 'none';
        document.getElementById('rsv-step2').style.display = 'block';
        document.getElementById('rsv-step2').classList.add('rsv-animate-in');

        // Update step indicators
        document.getElementById('rsv-step-ind-1').classList.remove('rsv-step-active');
        document.getElementById('rsv-step-ind-1').classList.add('rsv-step-done');
        document.getElementById('rsv-step-div-1').classList.add('rsv-step-divider-done');
        document.getElementById('rsv-step-ind-2').classList.add('rsv-step-active');

        rsvBuildForms();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function rsvGoToStep1() {
        document.getElementById('rsv-step2').style.display = 'none';
        document.getElementById('rsv-step1').style.display = 'block';
        document.getElementById('rsv-step1').classList.add('rsv-animate-in');

        document.getElementById('rsv-step-ind-2').classList.remove('rsv-step-active');
        document.getElementById('rsv-step-ind-1').classList.remove('rsv-step-done');
        document.getElementById('rsv-step-ind-1').classList.add('rsv-step-active');
        document.getElementById('rsv-step-div-1').classList.remove('rsv-step-divider-done');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ---- BUILD DYNAMIC FORMS ----
    function rsvBuildForms() {
        const container = document.getElementById('rsv-forms-container');
        container.innerHTML = '';

        // Adults
        for (let i = 1; i <= rsvAdults; i++) {
            container.innerHTML += rsvPersonCard('adult', i);
        }
        // Children
        for (let i = 1; i <= rsvChildren; i++) {
            container.innerHTML += rsvPersonCard('child', i);
        }

        // Setup gender selectors
        container.querySelectorAll('.rsv-gender-option').forEach(opt => {
            opt.addEventListener('click', function () {
                const group = this.parentElement;
                group.querySelectorAll('.rsv-gender-option').forEach(o => o.classList.remove('rsv-gender-selected'));
                this.classList.add('rsv-gender-selected');
                group.querySelector('input[type=hidden]').value = this.dataset.value;
                rsvCheckValid();
            });
        });

        // Setup validation listeners
        container.querySelectorAll('.rsv-required-field, input[name$="tc"]').forEach(input => {
            input.addEventListener('input', rsvCheckValid);
            input.addEventListener('change', rsvCheckValid);
        });

        // Toggle card collapse
        container.querySelectorAll('.rsv-person-header').forEach(header => {
            header.addEventListener('click', function () {
                this.parentElement.classList.toggle('rsv-collapsed');
            });
        });

        rsvCheckValid();
    }

    function rsvPersonCard(type, index) {
        const isChild = type === 'child';
        const label = isChild ? ('\u00c7ocuk ' + index) : ('Yeti\u015fkin ' + index);
        const icon = isChild ? 'fa-solid fa-child' : 'fa-solid fa-user';
        const headerClass = isChild ? 'rsv-child-header' : 'rsv-adult-header';
        const prefix = type + '_' + index + '_';

        let childOnlyFields = '';
        if (isChild) {
            childOnlyFields = `
            <div class="rsv-form-group">
                <label class="rsv-form-label">Ya\u015f</label>
                <select name="${prefix}age" class="rsv-form-select">
                    ${Array.from({ length: 12 }, (_, k) => '<option value="' + (k + 1) + '">' + (k + 1) + ' ya\u015f</option>').join('')}
                </select>
            </div>
            <div class="rsv-form-group">
                <label class="rsv-form-label">Yak\u0131nl\u0131k Derecesi</label>
                <select name="${prefix}relationship" class="rsv-form-select">
                    <option value="1">1. Derece</option>
                    <option value="2">2. Derece</option>
                    <option value="other">Di\u011fer</option>
                </select>
            </div>
        `;
        }

        return `
        <div class="rsv-person-card">
            <div class="rsv-person-header ${headerClass}">
                <i class="${icon}"></i>
                <span>${label}</span>
                <i class="fa-solid fa-chevron-down rsv-person-chevron"></i>
            </div>
            <div class="rsv-person-body">
                <div class="rsv-form-grid">
                    <div class="rsv-form-group rsv-form-full">
                        <label class="rsv-form-label">Ad Soyad <span class="rsv-required">*</span></label>
                        <input type="text" name="${prefix}name" class="rsv-form-input rsv-required-field" placeholder="\u00d6rn: Ahmet Y\u0131lmaz" required>
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">TC Kimlik No</label>
                        <input type="text" name="${prefix}tc" class="rsv-form-input" placeholder="11 haneli TC" maxlength="11">
                        <span class="rsv-inline-error ${prefix}tc-error" style="display: none;">Geçersiz TC Kimlik Numarası girdiniz.</span>
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">E-posta</label>
                        <input type="email" name="${prefix}email" class="rsv-form-input" placeholder="ornek@email.com">
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">Do\u011fum Tarihi <span class="rsv-required">*</span></label>
                        <input type="date" name="${prefix}birth" class="rsv-form-input rsv-required-field" required>
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">Cinsiyet</label>
                        <div class="rsv-gender-group">
                            <input type="hidden" name="${prefix}gender" value="">
                            <div class="rsv-gender-option" data-value="male">
                                <i class="fa-solid fa-mars"></i> Erkek
                            </div>
                            <div class="rsv-gender-option" data-value="female">
                                <i class="fa-solid fa-venus"></i> Kad\u0131n
                            </div>
                        </div>
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">Pasaport No</label>
                        <input type="text" name="${prefix}passport" class="rsv-form-input" placeholder="Pasaport numaras\u0131">
                    </div>
                    <div class="rsv-form-group">
                        <label class="rsv-form-label">Pasaport Son Kullanma</label>
                        <input type="date" name="${prefix}passport_expiry" class="rsv-form-input">
                    </div>
                    ${childOnlyFields}
                </div>
            </div>
        </div>
    `;
    }

    function rsvValidateTC(tc) {
        if (!tc) return true; // Optional empty check - if it's empty, it's not invalid formatting here (required check handles emptiness)
        if (!/^[1-9]\d{10}$/.test(tc)) return false;

        const digits = tc.split('').map(Number);

        let oddSum = digits[0] + digits[2] + digits[4] + digits[6] + digits[8];
        let evenSum = digits[1] + digits[3] + digits[5] + digits[7];

        let digit10 = ((oddSum * 7) - evenSum) % 10;
        let sumİlk10 = digits.slice(0, 10).reduce((a, b) => a + b, 0);
        let digit11 = sumİlk10 % 10;

        return digits[9] === digit10 && digits[10] === digit11;
    }

    // ---- VALIDATION ----
    function rsvCheckValid() {
        const fields = document.querySelectorAll('#rsv-forms-container .rsv-required-field');
        let allValid = true;
        fields.forEach(f => {
            if (!f.value.trim()) {
                allValid = false;
            }
        });

        // Check TCs
        let tcErrorMsg = '';
        const tcFields = document.querySelectorAll('#rsv-forms-container input[name$="tc"]');
        tcFields.forEach(tcField => {
            const val = tcField.value.trim();
            const errorSpan = tcField.parentElement.querySelector('.rsv-inline-error');
            if (val && !rsvValidateTC(val)) {
                allValid = false;
                tcErrorMsg = 'Lütfen geçerli bir TC Kimlik Numarası giriniz.';
                tcField.classList.add('rsv-input-error');
                if (errorSpan) errorSpan.style.display = 'block';
            } else {
                tcField.classList.remove('rsv-input-error');
                if (errorSpan) errorSpan.style.display = 'none';
            }
        });

        document.getElementById('rsv-submit-btn').disabled = !allValid;

        const valMsgBox = document.getElementById('rsv-validation-msg');
        if (!allValid) {
            if (tcErrorMsg) {
                valMsgBox.querySelector('span').textContent = tcErrorMsg;
            } else {
                valMsgBox.querySelector('span').textContent = 'Lütfen tüm zorunlu alanları (*) doldurunuz.';
            }
        } else {
            valMsgBox.classList.remove('rsv-show');
        }
    }

    // ---- SUBMIT ----
    function rsvSubmit(e) {
        e.preventDefault();

        // Double check validation
        const fields = document.querySelectorAll('#rsv-forms-container .rsv-required-field');
        let allValid = true;

        // Check required
        fields.forEach(f => {
            if (!f.value.trim()) {
                allValid = false;
                f.classList.add('rsv-input-error');
            } else {
                f.classList.remove('rsv-input-error');
            }
        });

        // Check TCs
        let tcErrorMsg = '';
        const tcFields = document.querySelectorAll('#rsv-forms-container input[name$="tc"]');
        tcFields.forEach(tcField => {
            const val = tcField.value.trim();
            const errorSpan = tcField.parentElement.querySelector('.rsv-inline-error');
            if (val && !rsvValidateTC(val)) {
                allValid = false;
                tcField.classList.add('rsv-input-error');
                tcErrorMsg = 'Lütfen geçerli bir TC Kimlik Numarası giriniz.';
                if (errorSpan) errorSpan.style.display = 'block';
            } else {
                tcField.classList.remove('rsv-input-error');
                if (errorSpan) errorSpan.style.display = 'none';
            }
        });

        const valMsgBox = document.getElementById('rsv-validation-msg');
        if (!allValid) {
            if (tcErrorMsg) {
                valMsgBox.querySelector('span').textContent = tcErrorMsg;
            } else {
                valMsgBox.querySelector('span').textContent = 'Lütfen tüm zorunlu alanları (*) doldurunuz.';
            }
            valMsgBox.classList.add('rsv-show');
            return;
        }

        const btn = document.getElementById('rsv-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Kaydediliyor...';

        const formData = new FormData(document.getElementById('rsv-form'));
        formData.append('tour_id', RSV_TOUR_ID);
        formData.append('date_id', RSV_DATE_ID);
        formData.append('adults', rsvAdults);
        formData.append('children', rsvChildren);
        formData.append('total_price', (rsvAdults + rsvChildren) * RSV_PRICE);

        fetch('api/save-reservation.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('rsv-step2').style.display = 'none';
                    document.getElementById('rsv-success').style.display = 'block';

                    // Update step indicators to all done
                    document.getElementById('rsv-step-ind-2').classList.remove('rsv-step-active');
                    document.getElementById('rsv-step-ind-2').classList.add('rsv-step-done');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Rezervasyonu Tamamla';
                    alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Rezervasyonu Tamamla';
                alert('Bağlantı hatası. Lütfen tekrar deneyin.');
            });
    }

    // Init
    rsvChangeCount('adult', 0);
</script>

<?php include 'includes/footer.php'; ?>