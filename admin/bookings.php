<?php
$current_page = 'bookings';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

// Search Logic
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$is_ajax = isset($_GET['ajax']);

$sql = "SELECT b.*, t.title as tour_title, td.start_date, td.end_date, td.currency 
        FROM bookings b 
        LEFT JOIN tours t ON b.tour_id = t.id 
        LEFT JOIN tour_dates td ON b.date_id = td.id 
        WHERE 1=1";
$params = [];

if ($search_term) {
    $sql .= " AND (b.customer_name LIKE ? OR b.customer_email LIKE ? OR t.title LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

if ($status_filter) {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// AJAX Response (Return only table rows)
if ($is_ajax) {
    if (count($bookings) > 0) {
        foreach ($bookings as $booking) {
            $statusClass = '';
            $statusLabel = '';
            switch ($booking['status']) {
                case 'pending':
                    $statusClass = 'status-pending';
                    $statusLabel = 'Bekliyor';
                    break;
                case 'confirmed':
                    $statusClass = 'status-confirmed';
                    $statusLabel = 'Onaylandı';
                    break;
                case 'cancelled':
                    $statusClass = 'status-cancelled';
                    $statusLabel = 'İptal Edildi';
                    break;
            }

            $dateStr = '-';
            if ($booking['start_date']) {
                $start = new DateTime($booking['start_date']);
                $dateStr = $start->format('d.m.Y');
            }

            echo '<tr data-booking-id="' . $booking['id'] . '">';
            echo '<td>#' . $booking['id'] . '</td>';
            echo '<td><strong>' . htmlspecialchars($booking['customer_name'] ?? '') . '</strong><br><span style="font-size:0.8rem;color:#888;">' . htmlspecialchars($booking['customer_email'] ?? '') . '</span></td>';
            echo '<td>' . htmlspecialchars($booking['tour_title'] ?? 'Silinmiş Tur') . '</td>';
            echo '<td>' . $dateStr . '</td>';
            echo '<td>' . $booking['guests'] . ' Kişi<br><span style="font-size:0.8rem;color:#888;">(' . $booking['adults'] . 'Y, ' . $booking['children'] . 'Ç)</span></td>';
            echo '<td><strong>' . formatCurrency($booking['total_price'], $booking['currency'] ?? 'EUR') . '</strong></td>';

            // Status Dropdown
            echo '<td>
                    <select class="status-select ' . $statusClass . '" onchange="updateStatus(' . $booking['id'] . ', this.value)">
                        <option value="pending" ' . ($booking['status'] == 'pending' ? 'selected' : '') . '>Bekliyor</option>
                        <option value="confirmed" ' . ($booking['status'] == 'confirmed' ? 'selected' : '') . '>Onaylandı</option>
                        <option value="cancelled" ' . ($booking['status'] == 'cancelled' ? 'selected' : '') . '>İptal Edildi</option>
                    </select>
                  </td>';

            echo '<td>';
            echo '<a href="booking-detail.php?id=' . $booking['id'] . '" class="action-btn edit-btn" style="background:var(--primary-light); color:var(--primary-color); border:none;"><i class="fa-solid fa-eye"></i> Detay</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #9CA3AF;">Kriterlere uygun rezervasyon bulunamadı.</td></tr>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervasyonlar - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .status-select {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            border: 1px solid transparent;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2rem;
            transition: all 0.2s;
        }

        .status-pending {
            background-color: #fefcbf;
            color: #b7791f;
            border-color: #fbd38d;
        }

        .status-confirmed {
            background-color: #c6f6d5;
            color: #2f855a;
            border-color: #9ae6b4;
        }

        .status-cancelled {
            background-color: #fed7d7;
            color: #c53030;
            border-color: #feb2b2;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Rezervasyonlar</h1>
                    <div class="breadcrumb">Dashboard / Rezervasyonlar</div>
                </div>
            </div>

            <!-- Filters -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                <div class="search-box" style="margin-bottom: 0; flex: 1;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #9CA3AF;"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Müşteri veya Tur ara..."
                        value="<?php echo htmlspecialchars($search_term); ?>">
                    <div id="loadingSpinner" style="display: none; color: #D90429;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                </div>

                <select id="statusFilter" class="search-input"
                    style="padding-left: 1rem; width: 200px; border-radius: 12px; border: 1px solid #E5E7EB;">
                    <option value="">Tüm Durumlar</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Bekliyor
                    </option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Onaylandı
                    </option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>İptal Edildi
                    </option>
                </select>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Müşteri</th>
                        <th>Tur Adı</th>
                        <th>Tarih</th>
                        <th>Kişi</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <!-- Populated by PHP initially, then handled by AJAX -->
                </tbody>
            </table>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast"
        style="position: fixed; bottom: 20px; right: 20px; background: #333; color: #fff; padding: 1rem 1.5rem; border-radius: 8px; display: none; align-items: center; gap: 10px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: opacity 0.3s;">
        <i class="fa-solid fa-circle-check" style="color: #4ade80;"></i>
        <span id="toastMsg">İşlem başarılı.</span>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.getElementById('bookingsTableBody');
        const loadingSpinner = document.getElementById('loadingSpinner');
        let timeout = null;

        // Initial Load
        fetchData();

        function fetchData() {
            const searchTerm = searchInput.value;
            const statusVal = statusFilter.value;
            loadingSpinner.style.display = 'block';

            fetch(`bookings.php?ajax=1&search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusVal)}`)
                .then(response => response.text())
                .then(html => {
                    tableBody.innerHTML = html;
                    loadingSpinner.style.display = 'none';

                    // URL Update
                    const newUrl = new URL(window.location);
                    if (searchTerm) newUrl.searchParams.set('search', searchTerm);
                    else newUrl.searchParams.delete('search');

                    if (statusVal) newUrl.searchParams.set('status', statusVal);
                    else newUrl.searchParams.delete('status');

                    window.history.pushState({}, '', newUrl);
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingSpinner.style.display = 'none';
                });
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(fetchData, 300);
        });

        statusFilter.addEventListener('change', fetchData);

        function updateStatus(bookingId, newStatus) {
            const select = document.querySelector(`tr[data-booking-id="${bookingId}"] select.status-select`);
            // Optimistic styling
            select.className = 'status-select status-' + newStatus;

            fetch('api/booking-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `booking_id=${bookingId}&status=${newStatus}`
            })
                .then(res => res.json())
                .then(data => {
                    const toast = document.getElementById('toast');
                    const toastMsg = document.getElementById('toastMsg');
                    if (data.success) {
                        toastMsg.textContent = 'Durum güncellendi.';
                        toast.querySelector('i').className = 'fa-solid fa-circle-check';
                        toast.querySelector('i').style.color = '#4ade80';
                    } else {
                        toastMsg.textContent = 'Hata: ' + data.message;
                        toast.querySelector('i').className = 'fa-solid fa-triangle-exclamation';
                        toast.querySelector('i').style.color = '#f87171';
                    }
                    toast.style.display = 'flex';
                    toast.style.opacity = '1';
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.style.display = 'none', 300);
                    }, 3000);
                })
                .catch(err => {
                    alert('Bağlantı hatası yaşandı.');
                });
        }
    </script>
</body>

</html>