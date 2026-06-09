-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 21 May 2026, 06:14:27
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `eczane_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hastalar`
--

CREATE TABLE `hastalar` (
  `id` int(11) NOT NULL,
  `tc` varchar(11) DEFAULT NULL,
  `ad` varchar(50) DEFAULT NULL,
  `soyad` varchar(50) DEFAULT NULL,
  `telefon` varchar(15) DEFAULT NULL,
  `adres` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `hastalar`
--

INSERT INTO `hastalar` (`id`, `tc`, `ad`, `soyad`, `telefon`, `adres`) VALUES
(1, '12345678901', 'Can', 'Öztürk', '05321112233', 'İstanbul/Kadıköy'),
(2, '23456789012', 'Elif', 'Yıldız', '05442223344', 'Ankara/Çankaya'),
(3, '34567890123', 'Hüseyin', 'Aksoy', '05053334455', 'İzmir/Konak'),
(4, '45678901234', 'Zeynep', 'Bulut', '05554445566', 'Bursa/Nilüfer'),
(5, '56789012345', 'Arda', 'Güler', '05305556677', 'Antalya/Muratpaşa'),
(11, '55555555555', 'dszrtxgh', 'gshdnbsj', 'wshdsm', ''),
(12, '22222222222', 'aaaaaaaa', 'aaaaaaaaa', '2222222222', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ilaclar`
--

CREATE TABLE `ilaclar` (
  `id` int(11) NOT NULL,
  `ad` varchar(200) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `fiyat` decimal(10,2) DEFAULT NULL,
  `aciklama` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ilaclar`
--

INSERT INTO `ilaclar` (`id`, `ad`, `kategori_id`, `stok`, `fiyat`, `aciklama`) VALUES
(1, 'Parol 500mg', 1, 91, 45.50, 'Genel ağrı kesici ve ateş düşürücü'),
(2, 'Augmentin 1000mg', 2, 50, 120.00, 'Geniş spektrumlu antibiyotik'),
(3, 'Pharmaton 30 Kapsül', 3, 23, 250.00, 'Multivitamin desteği'),
(4, 'Tylolhot Poşet', 4, 70, 85.00, 'Grip ve soğuk algınlığı semptomları için'),
(5, 'Bepanthol Krem', NULL, 37, 95.00, 'Cilt bakım kremi'),
(6, 'Parol 500mg', 1, 98, 50.00, 'Genel ağrı kesici ve ateş düşürücü'),
(7, 'Augmentin 1000mg', 2, 22, 120.00, 'Geniş spektrumlu antibiyotik'),
(8, 'Pharmaton 30 Kapsül', 3, 30, 250.00, 'Multivitamin desteği'),
(9, 'Tylolhot Poşet', 4, 500, 85.00, 'Grip ve soğuk algınlığı semptomları için'),
(10, 'Bepanthol Krem', 5, 40, 95.00, 'Cilt bakım kremi'),
(13, 'ssss', 5, 55, 55.00, 'sss');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategoriler`
--

CREATE TABLE `kategoriler` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kategoriler`
--

INSERT INTO `kategoriler` (`id`, `ad`) VALUES
(1, 'Ağrı Kesici'),
(2, 'Antibiyotik'),
(3, 'Vitamin'),
(4, 'Soğuk Algınlığı'),
(5, 'Dermatoloji'),
(6, 'Göz Damlası'),
(7, 'Bebek Bakımı'),
(8, 'Diyabet');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `isim` varchar(50) DEFAULT NULL,
  `soyisim` varchar(50) DEFAULT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` varchar(20) DEFAULT 'Personel'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullanici_adi`, `isim`, `soyisim`, `sifre`, `rol`) VALUES
(1, 'admin', 'Admin', 'Kullanıcı', '1234', 'Müdür'),
(2, 'ahmet_ecz', 'Ahmet', 'Yılmaz', '123456', 'Personel'),
(3, 'ayse_k', 'Ayşe', 'Kaya', 'ayse', 'Personel'),
(5, 'fatma_c', 'Fatma', 'Çelik', 'fatma', 'Personel'),
(14, 'mehmet', 'Mehmet', 'Demirci', '123', 'Personel');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `satislar`
--

CREATE TABLE `satislar` (
  `id` int(11) NOT NULL,
  `tarih` datetime DEFAULT current_timestamp(),
  `kullanici_id` int(11) DEFAULT NULL,
  `hasta_id` int(11) DEFAULT NULL,
  `toplam_tutar` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `satislar`
--

INSERT INTO `satislar` (`id`, `tarih`, `kullanici_id`, `hasta_id`, `toplam_tutar`) VALUES
(1, '2026-05-14 14:21:11', 1, 1, 45.50),
(2, '2026-05-14 14:21:11', 2, 2, 120.00),
(3, '2026-05-14 14:21:11', 1, 3, 250.00),
(4, '2026-05-14 14:21:11', 3, 4, 85.00),
(5, '2026-05-14 14:21:11', 2, 5, 95.00),
(6, '2026-05-14 14:54:05', 1, 1, 45.50),
(7, '2026-05-14 14:54:05', 2, 2, 120.00),
(8, '2026-05-14 14:54:05', 1, 3, 250.00),
(9, '2026-05-14 14:54:05', 3, 4, 85.00),
(10, '2026-05-14 14:54:05', 2, 5, 95.00),
(11, '2026-05-14 15:46:57', NULL, 12, 91.00),
(12, '2026-05-14 16:51:49', NULL, 4, 2400.00),
(13, '2026-05-14 17:03:39', NULL, 4, 850.00),
(14, '2026-05-20 21:00:09', 1, 5, 595.00),
(15, '2026-05-20 21:04:43', NULL, 1, 1750.00),
(16, '2026-05-20 21:49:19', NULL, 1, 376.00),
(17, '2026-05-20 21:49:49', NULL, 1, 1022.00),
(18, '2026-05-20 21:53:35', NULL, 1, 136.50);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `satis_detay`
--

CREATE TABLE `satis_detay` (
  `id` int(11) NOT NULL,
  `satis_id` int(11) DEFAULT NULL,
  `ilac_id` int(11) DEFAULT NULL,
  `adet` int(11) DEFAULT NULL,
  `birim_fiyat` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `satis_detay`
--

INSERT INTO `satis_detay` (`id`, `satis_id`, `ilac_id`, `adet`, `birim_fiyat`) VALUES
(1, 1, 1, 1, 45.50),
(2, 2, 2, 1, 120.00),
(3, 3, 3, 1, 250.00),
(4, 4, 4, 1, 85.00),
(5, 5, 5, 1, 95.00),
(6, 1, 1, 1, 45.50),
(7, 2, 2, 1, 120.00),
(8, 3, 3, 1, 250.00),
(9, 4, 4, 1, 85.00),
(10, 5, 5, 1, 95.00),
(11, 11, 6, 2, 45.50),
(12, 12, 7, 20, 120.00),
(13, 13, 4, 10, 85.00),
(14, 14, 5, 5, 95.00),
(15, 14, 7, 1, 120.00),
(16, 15, 3, 7, 250.00),
(17, 16, 1, 2, 45.50),
(18, 16, 5, 3, 95.00),
(19, 17, 1, 4, 45.50),
(20, 17, 7, 7, 120.00),
(21, 18, 1, 3, 45.50);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `hastalar`
--
ALTER TABLE `hastalar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tc` (`tc`);

--
-- Tablo için indeksler `ilaclar`
--
ALTER TABLE `ilaclar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Tablo için indeksler `kategoriler`
--
ALTER TABLE `kategoriler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `satislar`
--
ALTER TABLE `satislar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `hasta_id` (`hasta_id`);

--
-- Tablo için indeksler `satis_detay`
--
ALTER TABLE `satis_detay`
  ADD PRIMARY KEY (`id`),
  ADD KEY `satis_id` (`satis_id`),
  ADD KEY `ilac_id` (`ilac_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `hastalar`
--
ALTER TABLE `hastalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `ilaclar`
--
ALTER TABLE `ilaclar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `kategoriler`
--
ALTER TABLE `kategoriler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Tablo için AUTO_INCREMENT değeri `satislar`
--
ALTER TABLE `satislar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `satis_detay`
--
ALTER TABLE `satis_detay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `ilaclar`
--
ALTER TABLE `ilaclar`
  ADD CONSTRAINT `ilaclar_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategoriler` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `satislar`
--
ALTER TABLE `satislar`
  ADD CONSTRAINT `satislar_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`),
  ADD CONSTRAINT `satislar_ibfk_2` FOREIGN KEY (`hasta_id`) REFERENCES `hastalar` (`id`);

--
-- Tablo kısıtlamaları `satis_detay`
--
ALTER TABLE `satis_detay`
  ADD CONSTRAINT `satis_detay_ibfk_1` FOREIGN KEY (`satis_id`) REFERENCES `satislar` (`id`),
  ADD CONSTRAINT `satis_detay_ibfk_2` FOREIGN KEY (`ilac_id`) REFERENCES `ilaclar` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
