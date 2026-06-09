<?php
require_once 'baglanti.php';
require_once 'Modeller.php';

session_start();
// Giriş kontrolü
if (!isset($_SESSION['k_id'])) {
    header("Location: giris.php");
    exit();
}

$kullaniciModel = new Kullanici($vt);

// Şifre Değiştirme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'sifre_degistir') {
    if ($_POST['yeni_sifre'] !== $_POST['sifre_tekrar']) {
        header("Location: ayarlar.php?durum=hata&mesaj=" . urlencode("Yeni şifreler eşleşmiyor!"));
        exit();
    }
    
    $veri = [
        'mevcut_sifre' => $_POST['mevcut_sifre'],
        'yeni_sifre' => $_POST['yeni_sifre']
    ];

    $sonuc = $kullaniciModel->guncelleme($_SESSION['k_id'], $veri);
    if ($sonuc) {
        header("Location: ayarlar.php?durum=basarili&mesaj=" . urlencode("Şifreniz başarıyla güncellendi"));
    } else {
        header("Location: ayarlar.php?durum=hata&mesaj=" . urlencode("Mevcut şifreniz hatalı!"));
    }
    exit();
}

// Personel Ekleme İşlemi (Sadece Müdür)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'personel_ekle' && $_SESSION['kullanici_rol'] === 'mudur') {
    $veri = [
        'isim' => trim($_POST['p_isim']),
        'soyisim' => trim($_POST['p_soyisim']),
        'kullanici_adi' => trim($_POST['p_kullanici_adi']),
        'sifre' => trim($_POST['p_sifre']),
        'rol' => $_POST['p_rol']
    ];

    $sonuc = $kullaniciModel->olustur($veri);
    if ($sonuc) {
        header("Location: ayarlar.php?durum=basarili&mesaj=" . urlencode("Yeni kullanıcı başarıyla eklendi"));
    } else {
        header("Location: ayarlar.php?durum=hata&mesaj=" . urlencode("Kullanıcı eklenemedi (Kullanıcı adı alınmış olabilir)"));
    }
    exit();
}

// Personel Silme İşlemi (Sadece Müdür)
if (isset($_GET['personel_sil']) && $_SESSION['kullanici_rol'] === 'mudur') {
    if ($_GET['personel_sil'] != $_SESSION['k_id']) { 
        $kullaniciModel->sil_islemi($_GET['personel_sil']);
        header("Location: ayarlar.php?durum=basarili&mesaj=" . urlencode("Personel kaydı silindi"));
    } else {
        header("Location: ayarlar.php?durum=hata&mesaj=" . urlencode("Kendi hesabınızı silemezsiniz!"));
    }
    exit();
}

$personeller = [];
if ($_SESSION['kullanici_rol'] === 'mudur') {
    $personeller = $kullaniciModel->tumunu_getir();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar | mEczane</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
        }

        .settings-nav {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            height: fit-content;
        }

        .settings-nav-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .settings-nav-header .profile-avatar {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: var(--info-bg);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1rem;
        }

        .settings-nav-header h4 { font-size: 1.1rem; color: var(--text-main); }
        .settings-nav-header span { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }

        .tab-btn {
            width: 100%; display: flex; align-items: center; gap: 1rem;
            padding: 1rem 1.25rem; border: none; background: transparent;
            color: var(--text-muted); font-family: inherit; font-size: 0.95rem; font-weight: 500;
            border-radius: 0.75rem; cursor: pointer; transition: all 0.2s; margin-bottom: 0.5rem;
        }
        .tab-btn:hover { background: var(--bg-app); color: var(--text-main); }
        .tab-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25); }

        .settings-content { background: var(--bg-card); border-radius: 1rem; padding: 2.5rem; box-shadow: var(--shadow-sm); min-height: 500px; }
        .settings-section { display: none; animation: fadeIn 0.3s ease-out; }
        .settings-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .settings-section h3 { font-size: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; }
        .settings-section h3 i { color: var(--primary); }
        .settings-form { max-width: 500px; }
        .form-info-text { background: var(--info-bg); color: var(--info); padding: 1rem; border-radius: 0.75rem; font-size: 0.85rem; margin-bottom: 2rem; line-height: 1.5; }

        .personnel-layout { display: grid; grid-template-columns: 350px 1fr; gap: 3rem; }
        .personnel-list-container { border-left: 1px solid var(--border); padding-left: 2rem; }
        .status-pill { padding: 0.25rem 0.6rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; }
        @media (max-width: 900px) {
            .personnel-layout { grid-template-columns: 1fr; }
            .personnel-list-container { border-left: none; padding-left: 0; padding-top: 2rem; border-top: 1px solid var(--border); }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h2>Hesap ve Sistem Ayarları</h2>
            </header>

            <div class="scroll-area">
                <div class="settings-container">
                <aside class="settings-nav">
                    <div class="settings-nav-header">
                        <div class="profile-avatar"><i class="fa-solid fa-user-doctor"></i></div>
                        <h4><?php echo htmlspecialchars($_SESSION['kullanici_ad']); ?></h4>
                        <span><?php echo $_SESSION['kullanici_rol'] === 'mudur' ? 'Müdür' : 'Personel'; ?></span>
                    </div>
                    
                    <button class="tab-btn active" onclick="sekme_degistir(event, 'hesap')">
                        <i class="fa-solid fa-shield-halved"></i> Güvenlik & Şifre
                    </button>
                    
                    <?php if ($_SESSION['kullanici_rol'] === 'mudur'): ?>
                    <button class="tab-btn" onclick="sekme_degistir(event, 'personel')">
                        <i class="fa-solid fa-users-gear"></i> Personel Yönetimi
                    </button>
                    <?php endif; ?>
                </aside>

                <div class="settings-content">
                    <section id="hesap" class="settings-section active">
                        <h3><i class="fa-solid fa-key"></i> Şifre Değiştir</h3>
                        <div class="form-info-text">
                            <i class="fa-solid fa-circle-info"></i> Hesabınızın güvenliği için güçlü bir şifre seçtiğinizden emin olun. Yeni şifreniz en az 4 karakterden oluşmalıdır.
                        </div>

                        <form class="settings-form" method="POST" action="ayarlar.php">
                            <input type="hidden" name="islem" value="sifre_degistir">
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label>Mevcut Şifre</label>
                                <input type="password" name="mevcut_sifre" required placeholder="••••••••">
                            </div>
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label>Yeni Şifre</label>
                                <input type="password" name="yeni_sifre" required placeholder="En az 4 karakter..." minlength="4">
                            </div>
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label>Yeni Şifre (Tekrar)</label>
                                <input type="password" name="sifre_tekrar" required placeholder="Yeni şifreyi onaylayın..." minlength="4">
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-save"></i> Değişiklikleri Kaydet
                            </button>
                        </form>
                    </section>

                    <?php if ($_SESSION['kullanici_rol'] === 'mudur'): ?>
                    <section id="personel" class="settings-section">
                        <h3><i class="fa-solid fa-user-plus"></i> Ekip Yönetimi</h3>
                        
                        <div class="personnel-layout">
                            <div>
                                <h4 style="margin-bottom: 1.5rem; font-size: 1rem;">Yeni Personel Tanımla</h4>
                                <form method="POST" action="ayarlar.php">
                                    <input type="hidden" name="islem" value="personel_ekle">
                                    <div class="form-group" style="margin-bottom: 1.25rem;">
                                        <label>İsim</label>
                                        <input type="text" name="p_isim" required placeholder="Örn: Elif">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 1.25rem;">
                                        <label>Soyisim</label>
                                        <input type="text" name="p_soyisim" required placeholder="Örn: Yıldız">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 1.25rem;">
                                        <label>Kullanıcı Adı</label>
                                        <input type="text" name="p_kullanici_adi" required placeholder="Giriş için kullanılacak">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 1.25rem;">
                                        <label>Geçici Şifre</label>
                                        <input type="password" name="p_sifre" required placeholder="••••••••">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 2rem;">
                                        <label>Yetki Seviyesi</label>
                                        <select name="p_rol" required>
                                            <option value="Personel">Eczane Personeli</option>
                                            <option value="Müdür">Eczane Müdürü</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-primary" style="width: 100%;">
                                        <i class="fa-solid fa-user-check"></i> Hesabı Oluştur
                                    </button>
                                </form>
                            </div>

                            <div class="personnel-list-container">
                                <h4 style="margin-bottom: 1.5rem; font-size: 1rem;">Kayıtlı Çalışanlar</h4>
                                <div class="table-container">
                                    <table style="font-size: 0.85rem;">
                                        <thead>
                                            <tr>
                                                <th>Ad Soyad</th>
                                                <th>Yetki</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($personeller as $p): ?>
                                                <tr>
                                                    <td>
                                                        <div style="font-weight: 600;"><?php echo htmlspecialchars(trim(($p['isim'] ?? '') . ' ' . ($p['soyisim'] ?? ''))); ?></div>
                                                        <div style="font-size: 0.75rem; color: var(--text-muted);">@<?php echo htmlspecialchars($p['kullanici_adi']); ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="status-pill <?php echo ($p['rol'] === 'Müdür' || $p['rol'] === 'mudur') ? 'stok-good' : 'stok-warning'; ?>">
                                                            <?php echo htmlspecialchars($p['rol']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($p['id'] != $_SESSION['k_id']): ?>
                                                            <button class="btn-icon sil_islemi" onclick="confirmAction('Bu personeli silmek istediğinize emin misiniz?', 'ayarlar.php?personel_sil=<?php echo $p['id']; ?>')">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span style="font-size: 0.75rem; font-style: italic; color: var(--primary);">Oturum Açık</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </main>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function sekme_degistir(event, sekme_id) {
            document.querySelectorAll('.tab-btn').forEach(buton => buton.classList.remove('active'));
            event.currentTarget.classList.add('active');

            document.querySelectorAll('.settings-section').forEach(bolum => bolum.classList.remove('active'));
            document.getElementById(sekme_id).classList.add('active');
        }

        <?php if (isset($_GET['mesaj'])): ?>
            const toast = document.getElementById('toast');
            toast.textContent = "<?php echo htmlspecialchars($_GET['mesaj']); ?>";
            toast.className = "toast show <?php echo $_GET['durum'] ?? 'basarili'; ?>";
            setTimeout(() => { toast.className = "toast"; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
