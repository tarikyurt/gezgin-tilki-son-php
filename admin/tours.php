<?php
$current_page = 'tours';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

// Search Logic
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$is_ajax = isset($_GET['ajax']);

$sql = "SELECT * FROM tours";
$params = [];

if ($search_term) {
    $sql .= " WHERE title LIKE ?";
    $params[] = "%$search_term%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();

// AJAX Response (Return only table rows)
if ($is_ajax) {
    if (count($tours) > 0) {
        foreach ($tours as $tour) {
            echo '<tr>';
            echo '<td>' . $tour['id'] . '</td>';
            echo '<td><img src="../uploads/' . htmlspecialchars($tour['image_url']) . '" alt="Tour Image" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px;"></td>';
            echo '<td><strong>' . htmlspecialchars($tour['title']) . '</strong></td>';
            echo '<td>' . formatCurrency($tour['price'], $tour['currency'] ?? 'EUR') . '</td>';
            echo '<td>' . htmlspecialchars($tour['duration']) . '</td>';
            echo '<td>';
            if (hasPermission('tour_edit')) {
                echo '<a href="tour-edit.php?id=' . $tour['id'] . '" class="action-btn edit-btn"><i class="fa-solid fa-pen"></i> Düzenle</a>';
            }
            if (hasPermission('tour_add')) {
                echo '<a href="tour-duplicate.php?id=' . $tour['id'] . '" class="action-btn duplicate-btn"><i class="fa-solid fa-copy"></i> Çoğalt</a>';
            }
            if (hasPermission('tour_delete')) {
                echo '<a href="tour-delete.php?id=' . $tour['id'] . '" class="action-btn delete-btn" onclick="return confirm(\'Bu turu silmek istediğinize emin misiniz?\')"><i class="fa-solid fa-trash"></i> Sil</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align:center; padding: 2rem; color: #9CA3AF;">Kriterlere uygun tur bulunamadı.</td></tr>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turlar - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-title">
                <div>
                    <h1>Turlar</h1>
                    <div class="breadcrumb">Dashboard / Turlar</div>
                </div>
                <?php if (hasPermission('tour_add')): ?>
                    <a href="tour-add.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Yeni Tur Ekle</a>
                <?php endif; ?>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass" style="color: #9CA3AF;"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Tur adı ile arama yapın..."
                    value="<?php echo htmlspecialchars($search_term); ?>">
                <div id="loadingSpinner" style="display: none; color: #D90429;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resim</th>
                        <th>Başlık</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>Süre</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody id="toursTableBody">
                    <?php if (count($tours) > 0): ?>
                        <?php foreach ($tours as $tour): ?>
                            <tr>
                                <td><?php echo $tour['id']; ?></td>
                                <td><img src="../uploads/<?php echo $tour['image_url']; ?>" alt="Tour Image"
                                        style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px;"></td>
                                <td><strong><?php echo htmlspecialchars($tour['title']); ?></strong></td>
                                <td><?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?></td>
                                <td><?php echo htmlspecialchars($tour['duration']); ?></td>
                                <td>
                                    <?php if (hasPermission('tour_edit')): ?>
                                        <a href="tour-edit.php?id=<?php echo $tour['id']; ?>" class="action-btn edit-btn"><i
                                                class="fa-solid fa-pen"></i> Düzenle</a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('tour_add')): ?>
                                        <a href="tour-duplicate.php?id=<?php echo $tour['id']; ?>"
                                            class="action-btn duplicate-btn"><i class="fa-solid fa-copy"></i> Çoğalt</a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('tour_delete')): ?>
                                        <a href="tour-delete.php?id=<?php echo $tour['id']; ?>" class="action-btn delete-btn"
                                            onclick="return confirm('Bu turu silmek istediğinize emin misiniz?')"><i
                                                class="fa-solid fa-trash"></i> Sil</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 2rem; color: #9CA3AF;">Kayıt bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('toursTableBody');
        const loadingSpinner = document.getElementById('loadingSpinner');
        let timeout = null;

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value;
            loadingSpinner.style.display = 'block';
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                fetch(`tours.php?ajax=1&search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                        loadingSpinner.style.display = 'none';
                        const newUrl = new URL(window.location);
                        if (searchTerm) {
                            newUrl.searchParams.set('search', searchTerm);
                        } else {
                            newUrl.searchParams.delete('search');
                        }
                        window.history.pushState({}, '', newUrl);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadingSpinner.style.display = 'none';
                    });
            }, 300);
        });
    </script>
</body>

</html>