CREATE DATABASE IF NOT EXISTS syp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE syp;

DROP TABLE IF EXISTS books;

-- İlaçlar Tablosu
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kullanıcılar (Yetki) Tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('manager', 'personnel') DEFAULT 'personnel',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hastalar Tablosu
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tc_no VARCHAR(11) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    blood_group VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Satışlar Tablosu
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    patient_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Örnek İlaç Verileri
INSERT IGNORE INTO medicines (barcode, name, category, price, stock, expiry_date) VALUES 
('8699502010001', 'Parol 500mg Tablet', 'Ağrı Kesici', 45.50, 150, '2027-10-15'),
('8699504010002', 'Amoklavin 1000mg', 'Antibiyotik', 185.00, 45, '2026-05-20'),
('8699505010003', 'Majezik 100mg', 'Ağrı Kesici', 85.75, 80, '2028-01-10'),
('8699506010004', 'Talcid Çiğneme Tableti', 'Mide İlacı', 65.00, 120, '2025-12-01'),
('8699507010005', 'Xyzal 5mg Film Tablet', 'Alerji', 110.25, 30, '2026-08-30');

-- Örnek Kullanıcı Verileri (Şifreler md5 ile şifrelenmiştir. Şifreler: 12345)
INSERT IGNORE INTO users (name, username, password, role) VALUES 
('Ahmet Müdür', 'mudur', md5('12345'), 'manager'),
('Ayşe Personel', 'personel', md5('12345'), 'personnel');
