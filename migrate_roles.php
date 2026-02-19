<?php
/**
 * Migration Script: Add role column to users table and update admin password
 * Run this file ONCE via browser: http://localhost/Gezgin-tilki-deneme/migrate_roles.php
 */
require_once 'includes/db.php';

try {
    // 1. Add 'role' column to users table if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin','editor','viewer') DEFAULT 'viewer' AFTER email");
        echo "✅ 'role' sütunu eklendi.<br>";
    } else {
        echo "ℹ️ 'role' sütunu zaten mevcut.<br>";
    }

    // 2. Set existing 'admin' user role to 'admin'
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE username = 'admin'");
    $stmt->execute();
    echo "✅ 'admin' kullanıcısına 'admin' rolü verildi.<br>";

    // 3. Update admin password to a proper bcrypt hash (password: admin123)
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashedPassword]);
    echo "✅ 'admin' şifresi bcrypt ile güncellendi (şifre: admin123).<br>";

    echo "<br><strong>✅ Migration tamamlandı! Bu dosyayı artık silebilirsiniz.</strong>";

} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>