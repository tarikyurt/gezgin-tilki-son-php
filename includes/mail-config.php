<?php
/**
 * SMTP Mail Yapılandırması
 * ========================
 * Bu dosyayı kendi SMTP bilgilerinizle doldurun.
 * info@sthteam.com adresinden mail göndermek için gereklidir.
 */

define('SMTP_HOST', 'srvc206.trwww.com');     // SMTP sunucusu (örn: smtp.yandex.com, smtp.gmail.com, mail.sthteam.com)
define('SMTP_PORT', 465);                     // Port: 587 (TLS) veya 465 (SSL)
define('SMTP_USERNAME', 'noreply@gezgintilki.com');  // Mail adresi
define('SMTP_PASSWORD', 'Y9n.jNz1214'); // Mail şifresi veya uygulama şifresi
define('SMTP_FROM_NAME', 'Gezgin Tilki');     // Gönderen adı
define('SMTP_ENCRYPTION', 'ssl');             // 'tls' veya 'ssl'
?>