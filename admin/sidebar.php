<?php
// Sidebar Partial - Include this in all admin pages
// Usage: $current_page = 'dashboard'; include 'sidebar.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$initial = strtoupper(mb_substr($user['username'], 0, 1));
?>
<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <img src="../assets/img/logo.png" alt="Gezgin Tilki">
        <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Ana Menü</div>
        <a href="index.php" class="<?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge"></i>
            <span>Dashboard</span>
        </a>
        <a href="tours.php" class="<?php echo ($current_page ?? '') === 'tours' ? 'active' : ''; ?>">
            <i class="fa-solid fa-plane"></i>
            <span>Turlar</span>
        </a>
        <a href="bookings.php" class="<?php echo ($current_page ?? '') === 'bookings' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Rezervasyonlar</span>
        </a>

        <?php if (hasPermission('user_manage')): ?>
            <div class="sidebar-divider"></div>
            <div class="sidebar-section-label">Yönetim</div>
            <a href="users.php" class="<?php echo ($current_page ?? '') === 'users' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users-gear"></i>
                <span>Kullanıcılar</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar">
            <?php echo $initial; ?>
        </div>
        <div class="user-info">
            <div class="user-name">
                <?php echo htmlspecialchars($user['username']); ?>
            </div>
            <div class="user-role">
                <?php echo getRoleLabel($user['role']); ?>
            </div>
        </div>
        <a href="logout.php" class="logout-btn" title="Çıkış Yap"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</aside>

<script>
    (function () {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainContent = document.querySelector('.main-content');

        // Restore state from localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        }

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    })();
</script>