<?php $has_hero = true;
include 'includes/header.php'; ?>

<section class="page-header"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; height: 300px; display: flex; align-items: center; justify-content: center; color: white; padding-top: 80px;">
    <div class="container text-center">
        <h1>Turlarımız</h1>
        <p>Hayalinizdeki rotayı seçin</p>
    </div>
</section>

<section class="tours-listing">
    <div class="container">
        <div class="row" style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: flex-start;">

            <?php
            require_once 'includes/db.php';

            // Get distinct locations for sidebar
            $loc_stmt = $pdo->query("SELECT DISTINCT location FROM tours ORDER BY location");
            $locations = $loc_stmt->fetchAll(PDO::FETCH_COLUMN);

            // Initialize Filter Variables
            $selected_destinations = isset($_GET['destinations']) ? $_GET['destinations'] : [];
            $min_price = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
            $max_price = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 10000;
            $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Build Query
            $query = "SELECT t.*, 
                       nd.quota AS next_quota, 
                       nd.start_date AS next_start, 
                       nd.end_date AS next_end
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
                WHERE t.is_active = 1";
            $params = [];

            // Filter by Destination (Array)
            if (!empty($selected_destinations)) {
                $placeholders = implode(',', array_fill(0, count($selected_destinations), '?'));
                $query .= " AND t.location IN ($placeholders)";
                $params = array_merge($params, $selected_destinations);
            }

            // Filter by Price
            if ($min_price > 0 || $max_price < 10000) {
                $query .= " AND t.price BETWEEN ? AND ?";
                $params[] = $min_price;
                $params[] = $max_price;
            }

            // Search Term
            if (!empty($search_term)) {
                $query .= " AND (t.title LIKE ? OR t.description LIKE ? OR t.location LIKE ?)";
                $params[] = "%$search_term%";
                $params[] = "%$search_term%";
                $params[] = "%$search_term%";
            }

            $query .= " ORDER BY t.created_at DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $tours = $stmt->fetchAll();
            ?>

            <!-- Sidebar Filters -->
            <aside class="filters"
                style="flex: 1; min-width: 280px; background: white; padding: 2rem; border-radius: 10px; height: fit-content; box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: sticky; top: 100px;">
                <form action="tours.php" method="GET">
                    <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Filtrele</h3>

                    <!-- Search -->
                    <div class="filter-group" style="margin-bottom: 2rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem;">Arama</h4>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>"
                            placeholder="Tur adı ara..."
                            style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>

                    <!-- Destinations -->
                    <div class="filter-group" style="margin-bottom: 2rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem;">Destinasyonlar</h4>
                        <?php foreach ($locations as $loc): ?>
                            <label
                                style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="destinations[]" value="<?php echo htmlspecialchars($loc); ?>"
                                    <?php echo in_array($loc, $selected_destinations) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group" style="margin-bottom: 2rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem;">Fiyat Aralığı (€)</h4>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="Min"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="Max"
                                style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%;">Filtreleri Uygula</button>
                    <a href="tours.php"
                        style="display: block; text-align: center; margin-top: 1rem; color: var(--text-light); font-size: 0.9rem;">Temizle</a>
                </form>
            </aside>

            <!-- Tours Grid -->
            <div class="tours-content" style="flex: 3; min-width: 300px;">
                <div class="tours-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <?php if (count($tours) > 0): ?>
                        <?php foreach ($tours as $tour):
                            $remaining = $tour['next_quota'] ?? null;
                            $nextStart = $tour['next_start'] ?? null;
                            $nextEnd = $tour['next_end'] ?? null;

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
                            <a href="tour-detail.php?id=<?php echo $tour['id']; ?>"
                                style="text-decoration: none; color: inherit; display: block;">
                                <div class="tour-card"
                                    style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.1); transition: transform 0.3s; display: flex; flex-direction: column; height: 100%;">
                                    <div class="tour-img" style="height: 220px; position: relative;">
                                        <img src="uploads/<?php echo $tour['image_url']; ?>"
                                            alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php if ($tour['is_featured']): ?>
                                            <span
                                                style="position: absolute; top: 1rem; right: 1rem; background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;">Popüler</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tour-info"
                                        style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                                        <div
                                            style="display: flex; justify-content: space-between; color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                            <span><i class="fa-regular fa-clock"></i>
                                                <?php echo htmlspecialchars($tour['duration']); ?></span>
                                            <span><i class="fa-solid fa-location-dot"></i>
                                                <?php echo htmlspecialchars($tour['location']); ?></span>
                                        </div>
                                        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--secondary-color);">
                                            <?php echo htmlspecialchars($tour['title']); ?>
                                        </h3>
                                        <p
                                            style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px;">
                                            <?php echo htmlspecialchars($tour['description']); ?>
                                        </p>
                                        <?php if ($remaining !== null && $remaining <= 5 && $remaining > 0): ?>
                                            <div class="tour-urgency-badge">
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
                                        <div
                                            style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 1rem;">
                                            <span
                                                style="font-weight: 700; color: var(--primary-color); font-size: 1.25rem;"><?php echo formatCurrency($tour['price'], $tour['currency'] ?? 'EUR'); ?></span>
                                            <span
                                                style="color: var(--secondary-color); font-weight: 600; transition: color 0.3s;">İncele
                                                <i class="fa-solid fa-arrow-right"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div
                            style="width: 100%; text-align: center; padding: 3rem; background: white; border-radius: 10px; grid-column: 1 / -1; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">

                            <?php if (!empty($search_term)): ?>
                                <i class="fa-solid fa-magnifying-glass-location"
                                    style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem; opacity: 0.7;"></i>
                                <h3 style="margin-bottom: 0.5rem; color: #333;">Aradığınız
                                    "<?php echo htmlspecialchars($search_term); ?>" turunu burada bulamadık.</h3>
                                <p style="color: #666; margin-bottom: 2rem;">Ama üzülmeyin! Ana tur platformumuzda bu
                                    destinasyon için harika seçenekler sizi bekliyor olabilir.</p>

                                <div
                                    style="background: #f8f9fa; border: 2px dashed #e9ecef; border-radius: 12px; padding: 2rem; max-width: 550px; margin: 0 auto; transition: all 0.3s hover:border-color: var(--primary-color);">
                                    <h4 style="color: var(--secondary-color); margin-bottom: 0.5rem; font-size: 1.1rem;">
                                        <i class="fa-solid fa-globe"></i> Gezgin Tilki Global'de Keşfedin
                                    </h4>
                                    <p style="font-size: 0.95rem; margin-bottom: 1.5rem; color: #555;">
                                        Diğer web sitemizde <strong><?php echo htmlspecialchars($search_term); ?></strong> ile
                                        ilgili tüm turları inceleyebilirsiniz.
                                    </p>

                                    <a href="https://tur.gezgintilki.com/turlar?s=<?php echo urlencode($search_term); ?>"
                                        target="_blank" class="btn-primary"
                                        style="display: inline-flex; align-items: center; gap: 0.8rem; text-decoration: none; padding: 0.8rem 2rem; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                        <span>tur.gezgintilki.com'da Ara</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>

                                <div style="margin-top: 2rem;">
                                    <a href="tours.php" style="color: #999; text-decoration: underline; font-size: 0.9rem;">Veya
                                        tüm filtreleri temizle</a>
                                </div>

                            <?php else: ?>
                                <i class="fa-solid fa-plane-slash"
                                    style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                                <h3>Kriterlerinize uygun tur bulunamadı.</h3>
                                <p>Lütfen filtreleri değiştirip tekrar deneyin.</p>
                                <a href="tours.php" class="btn-primary" style="margin-top: 1rem;">Filtreleri Temizle</a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>