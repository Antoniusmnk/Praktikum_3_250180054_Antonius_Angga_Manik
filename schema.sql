-- Schema Database Aplikasi Manajemen Produk
CREATE DATABASE IF NOT EXISTS db_produk_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_produk_manager;

-- Tabel Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(12, 2) NOT NULL CHECK (price > 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_name (name),
    INDEX idx_products_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data Awal untuk pengujian
INSERT INTO products (name, category, price, stock) VALUES
('Laptop Asus ZenBook 14 OLED', 'Elektronik', 15499000.00, 10),
('Smartphone Samsung Galaxy S24', 'Elektronik', 13999000.00, 15),
('Keyboard Mekanikal Wireless RGB', 'Aksesoris Komputer', 899000.00, 25),
('Mouse Ergonomis Wireless', 'Aksesoris Komputer', 349000.00, 5),
('Monitor Gaming 27 Inch 165Hz', 'Elektronik', 3250000.00, 0),
('Meja Kerja Minimalis Modern', 'Furniture', 1250000.00, 8),
('Kursi Kantor Ergonomis Mesh', 'Furniture', 1850000.00, 3)
ON DUPLICATE KEY UPDATE id=id;
