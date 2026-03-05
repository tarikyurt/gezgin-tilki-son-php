<?php
require 'includes/db.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS booking_passengers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        type ENUM('adult','child') NOT NULL,
        full_name VARCHAR(150),
        tc_no VARCHAR(11),
        email VARCHAR(100),
        birth_date DATE,
        passport_no VARCHAR(20),
        passport_expiry DATE,
        gender ENUM('male','female'),
        child_age INT DEFAULT NULL,
        relationship VARCHAR(20) DEFAULT NULL,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )");
    echo "Table booking_passengers created.\n";

    $cols = array_column($pdo->query('SHOW COLUMNS FROM bookings')->fetchAll(), 'Field');
    $additions = [
        'date_id' => 'INT DEFAULT NULL',
        'adults' => 'INT DEFAULT 1',
        'children' => 'INT DEFAULT 0',
        'total_price' => 'DECIMAL(10,2) DEFAULT 0'
    ];
    foreach ($additions as $col => $def) {
        if (!in_array($col, $cols)) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN $col $def");
            echo "Added $col.\n";
        } else {
            echo "$col already exists.\n";
        }
    }
    echo "Migration complete!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
