# Sunucu Yönetimi Proje Raporu

## 1. Proje Konusu ve Amacı
Bu proje, Sunucu Yönetimi dersi kapsamında geliştirilmiş bir REST API ve bu API'yi tüketen bir istemci (client) uygulamasını içermektedir. Projenin amacı, temel veritabanı işlemlerini (CRUD: Create, Read, Update, Delete) HTTP metodları (GET, POST, PUT, DELETE) üzerinden gerçekleştiren bağımsız bir servis oluşturmak ve veri alışverişini JSON formatında sağlamaktır.

Proje kapsamında **"Profesyonel Eczane Otomasyon Sistemi (PharmaSoft)"** tasarlanmıştır.

## 2. Kullanılan Teknolojiler
- **Sunucu / Backend:** PHP (PDO ile MySQL bağlantısı)
- **Veritabanı:** MySQL
- **İstemci / Frontend:** HTML5, CSS3 (Modern Dashboard Arayüzü), Vanilla JavaScript (Fetch API)
- **Veri Formatı:** JSON

## 3. Veritabanı Yapısı
Veritabanı adı: `syp_proje`

**Tablo: medicines (İlaçlar)**
- `id` (INT) - Birincil anahtar (Primary Key)
- `barcode` (VARCHAR 50) - İlaç Barkodu
- `name` (VARCHAR 255) - İlaç Adı
- `category` (VARCHAR 100) - Kategori
- `price` (DECIMAL) - Satış Fiyatı
- `stock` (INT) - Stok Miktarı
- `expiry_date` (DATE) - Son Kullanma Tarihi

**Tablo: patients (Hastalar)**
- `id` (INT) - Birincil anahtar
- `tc_no` (VARCHAR 11) - TC Kimlik No
- `name` (VARCHAR 255) - Ad Soyad
- `phone` (VARCHAR 20) - Telefon No
- `blood_group` (VARCHAR 10) - Kan Grubu

**Tablo: sales (Satışlar)**
- `id` (INT) - Birincil anahtar
- `medicine_id` (INT) - İlaç ID (Foreign Key)
- `patient_id` (INT) - Hasta ID (Foreign Key)
- `quantity` (INT) - Satılan Adet
- `total_price` (DECIMAL) - Toplam Satış Tutarı

**Tablo: users (Kullanıcılar)**
- `id` (INT) - Birincil anahtar
- `name` (VARCHAR 100) - Ad Soyad
- `username` (VARCHAR 50) - Kullanıcı Adı
- `password` (VARCHAR 255) - MD5 Şifre
- `role` (ENUM) - Yetki (manager, personnel)

## 3.5. Kimlik Doğrulama ve Yetkilendirme (Giriş Ekranı)
Sistemde "Eczane Müdürü" ve "Personel" olmak üzere 2 farklı yetki tipi bulunmaktadır.
- **Müdür (Manager):** İlaç ekleyebilir, silebilir ve güncelleyebilir. Aynı zamanda Hasta silebilir. (Kullanıcı: `mudur`, Şifre: `12345`)
- **Personel (Personnel):** Sadece ilaç envanterini görebilir ve satış yapabilir. (Kullanıcı: `personel`, Şifre: `12345`)
- `auth.php` uç noktası üzerinden giriş yapılarak `localStorage` üzerinde oturum tutulmakta ve `X-User-Role` HTTP header'ı ile API isteklerinde yetki kontrolü yapılmaktadır.

## 4. REST API İşlevleri (Uç Noktalar)
Sistemde İlaçlar, Hastalar ve Satışlar için 3 farklı API noktası mevcuttur: `api.php`, `patients.php`, `sales.php`.

### 4.1. İlaç İşlemleri (`api.php`)
- **GET:** Tüm ilaçları listeler. (`?id=` parametresiyle tekil getirir)
- **POST:** Yeni ilaç ekler.
- **PUT:** İlaç bilgilerini günceller.
- **DELETE:** İlacı sistemden siler.

### 4.2. Hasta İşlemleri (`patients.php`)
- **GET:** Tüm hastaları listeler. (`?id=` parametresiyle tekil getirir)
- **POST:** Yeni hasta ekler.
- **PUT:** Hasta bilgilerini günceller.
- **DELETE:** Hastayı sistemden siler (Sadece Müdür).

### 4.3. Satış İşlemleri (`sales.php`)
- **GET:** Tüm satış geçmişini (JOIN işlemi ile hasta ve ilaç isimlerini birleştirerek) listeler.
- **POST:** Yeni bir satış işlemi kaydeder. Satış gerçekleşirken veritabanındaki stok miktarı dinamik olarak `transaction` yapısıyla düşürülür. Eğer stok yetersizse uyarı verir ve işlemi geri alır (rollback).

API URL: `http://localhost/syp/api.php`

### 4.1. Tüm İlaçları Listeleme (GET)
- **Metod:** `GET`
- **İşlev:** Veritabanındaki tüm ilaçları JSON formatında döndürür.
- **Kullanım:** `GET /syp/api.php`

### 4.2. Tekil İlaç Getirme (GET by ID)
- **Metod:** `GET`
- **İşlev:** Belirtilen ID'ye sahip ilacı getirir.
- **Kullanım:** `GET /syp/api.php?id=1`

### 4.3. Yeni İlaç Ekleme (POST)
- **Metod:** `POST`
- **İşlev:** JSON formatında gönderilen verilerle veritabanına yeni ilaç ekler.
- **Veri (Body):** `{"barcode": "869950...", "name": "Parol", "category": "Ağrı Kesici", "price": 45.50, "stock": 100, "expiry_date": "2027-10-15"}`
- **Kullanım:** `POST /syp/api.php`

### 4.4. İlaç Güncelleme (PUT)
- **Metod:** `PUT`
- **İşlev:** Belirtilen ID'ye sahip ilacın bilgilerini günceller.
- **Kullanım:** `PUT /syp/api.php?id=1`

### 4.5. İlaç Silme (DELETE)
- **Metod:** `DELETE`
- **İşlev:** Belirtilen ID'ye sahip ilacı sistemden siler.
- **Kullanım:** `DELETE /syp/api.php?id=1`

## 5. İstemci (Client) Uygulaması
Frontend tarafında HTML, CSS ve JavaScript kullanılmıştır. Arayüzde "Dashboard" konsepti benimsenmiş; yan menü, istatistik kartları (Toplam İlaç, Kritik Stok vs.) ve arama çubuğu gibi profesyonel özellikler eklenmiştir.
Uygulama, Fetch API kullanarak backend ile asenkron olarak haberleşmekte ve sayfa yenilenmesine gerek kalmadan verileri gerçek zamanlıya yakın işlemektedir.

## 6. Kurulum ve Çalıştırma Adımları
1. Proje dosyalarını XAMPP kurulum dizinindeki `htdocs` klasörünün içine `syp` adında bir klasör oluşturup yapıştırın. (`C:\xampp\htdocs\syp\`)
2. XAMPP Control Panel üzerinden **Apache** ve **MySQL** servislerini başlatın.
3. Tarayıcınızda `http://localhost/phpmyadmin` adresine gidin.
4. **ÖNEMLİ:** İçe aktar (Import) sekmesini kullanarak proje klasöründeki `database.sql` dosyasını içe aktarın. Bu işlem `syp_proje` adında bir veritabanı ve `medicines` tablosunu oluşturacaktır. (Eğer önceki versiyondan kalan kitap veritabanı varsa, `database.sql` içindeki kod onu temizleyip ilaç veritabanını kuracaktır).
5. Tarayıcınızda `http://localhost/syp/index.php` adresine giderek Eczane Otomasyonu (PharmaSoft) arayüzünü kullanmaya başlayabilirsiniz.
