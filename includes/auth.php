<?php
/**
 * Authentication & Authorization Helper
 * Include this file in all admin pages instead of manual session checks.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Check if current user has one of the allowed roles
 * @param array $allowedRoles e.g. ['admin', 'editor']
 */
function requireRole($allowedRoles)
{
    requireLogin();
    $userRole = $_SESSION['role'] ?? 'viewer';
    if (!in_array($userRole, $allowedRoles)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Yetkisiz Erişim</title>
        <link rel="stylesheet" href="../assets/css/style.css"></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;background:var(--bg-light);">
        <div style="text-align:center;background:white;padding:3rem;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);">
        <h1 style="color:#c62828;margin-bottom:1rem;">⛔ Yetkisiz Erişim</h1>
        <p style="color:#666;margin-bottom:2rem;">Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>
        <a href="index.php" style="padding:0.8rem 2rem;background:var(--primary-color);color:white;border-radius:8px;text-decoration:none;">Dashboard\'a Dön</a>
        </div></body></html>';
        exit;
    }
}

/**
 * Check if user has permission for a specific action
 * @param string $action e.g. 'tour_add', 'tour_edit', 'tour_delete', 'user_manage'
 * @return bool
 */
function hasPermission($action)
{
    $role = $_SESSION['role'] ?? 'viewer';

    $permissions = [
        'admin' => ['tour_view', 'tour_add', 'tour_edit', 'tour_delete', 'user_manage', 'booking_view'],
        'editor' => ['tour_view', 'tour_add', 'tour_edit', 'booking_view'],
        'viewer' => ['tour_view', 'booking_view'],
    ];

    $rolePermissions = $permissions[$role] ?? [];
    return in_array($action, $rolePermissions);
}

/**
 * Get current user info from session
 * @return array
 */
function getCurrentUser()
{
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Misafir',
        'role' => $_SESSION['role'] ?? 'viewer',
    ];
}

/**
 * Get role display name in Turkish
 * @param string $role
 * @return string
 */
function getRoleLabel($role)
{
    $labels = [
        'admin' => 'Yönetici',
        'editor' => 'Editör',
        'viewer' => 'İzleyici',
    ];
    return $labels[$role] ?? $role;
}

/**
 * Get role badge HTML with color
 * @param string $role
 * @return string
 */
function getRoleBadge($role)
{
    $colors = [
        'admin' => 'background:#c62828;color:white;',
        'editor' => 'background:#1565c0;color:white;',
        'viewer' => 'background:#757575;color:white;',
    ];
    $style = $colors[$role] ?? 'background:#999;color:white;';
    $label = getRoleLabel($role);
    return "<span style=\"{$style} padding:0.3rem 0.8rem;border-radius:20px;font-size:0.8rem;font-weight:600;\">{$label}</span>";
}
?>