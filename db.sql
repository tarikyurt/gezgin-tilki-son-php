CREATE DATABASE IF NOT EXISTS gezgin_tilki;
USE gezgin_tilki;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
-- Note: In a real app, use password_hash(). For simplicity in this demo, we might use plain text or hash later.
-- For now, let's assume we will use password_hash in PHP.
INSERT INTO users (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gezgintilki.com'); 
-- The hash above is for 'password' (standard laravel default), let's just use a known hash or handle it in PHP. 
-- Actually, let's insert a simple one for now and update it via PHP or just use a known hash for '123456'
-- $2y$10$jb/.. is often used. Let's just create the table. We can seed it later.

-- Tours Table
CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration VARCHAR(50), 
    location VARCHAR(100),
    image_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings/Reservations Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    guests INT DEFAULT 1,
    booking_date DATE,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);
