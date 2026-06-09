<?php
require_once 'baglanti.php';
require_once 'Modeller.php';

session_start();
// Giriş kontrolü
if (!isset($_SESSION['k_id'])) {
    header("Location: giris.php");
    exit();
}

$hastaModel = new Hasta($vt);

// Silme İşlemi
if (isset($_GET['sil']) && $_SESSION['kullanici_rol'] === 'mudur') {
    $silme_durumu = $hastaModel->sil_islemi($_GET['sil']);
    if ($silme_durumu) {
        header("Location: hastalar.php?durum=basarili&mesaj=" . urlencode("Hasta kaydı başarıyla silindi"));
    } else {
        header("Location: hastalar.php?durum=hata&mesaj=" . urlencode("Hasta silinemedi! Hastaya ait satış kayıtları bulunuyor olabilir."));
    }
    exit();
}

// Kaydetme/Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'kaydet') {
    $veri = [
        'tc' => trim($_POST['tc']),
        'ad' => trim($_POST['ad']),
        'soyad' => trim($_POST['soyad']),
        'telefon' => trim($_POST['telefon']),
        'adres' => trim($_POST['adres'])
    ];

    if (!empty($_POST['id'])) {
        $hastaModel->guncelleme($_POST['id'], $veri);
        $mesaj_metni = "Hasta bilgileri başarıyla güncellendi";
    } else {
        $hastaModel->olustur($veri);
        $mesaj_metni = "Yeni hasta başarıyla eklendi";
    }
    header("Location: hastalar.php?durum=basarili&mesaj=" . urlencode($mesaj_metni));
    exit();
}

$hastalar = $hastaModel->tumunu_getir();
$toplamHastaSayisi = count($hastalar);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastalar | mEczane</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="arama_kutusu" placeholder="TC No, hasta adı veya telefon ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Yeni Hasta Ekle</button>
                </div>
            </header>
            
            <div class="scroll-area">
                <div class="dashboard-stats" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-details">
                            <h3>Kayıtlı Hasta Sayısı</h3>
                            <p><?php echo $toplamHastaSayisi; ?></p>
                        </div>
                    </div>
                </div>

                <section class="veri-section" style="margin-top: 1.5rem;">
                    <div class="section-header">
                        <div>
                            <h2>Hasta Veritabanı</h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Sistemde kayıtlı tüm hastalar, iletişim ve adres bilgileri.</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button class="btn-secondary" onclick="location.reload()" title="Listeyi Yenile">
                                <i class="fa-solid fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="hastalarTablosu">
                            <thead>
                                <tr>
                                    <th>TC Kimlik No</th>
                                    <th>Ad Soyad</th>
                                    <th>Telefon Numarası</th>
                                    <th>Açık Adres</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="hastalarListesi">
                                <?php if (empty($hastalar)): ?>
                                    <tr><td colspan="5" class="text-center" style="padding: 2rem;">Henüz hasta bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($hastalar as $hasta): ?>
                                        <tr class="patient-satir" 
                                            veri-ad="<?php echo strtolower($hasta['ad'] . ' ' . $hasta['soyad']); ?>" 
                                            veri-tc="<?php echo htmlspecialchars($hasta['tc'] ?? ''); ?>"
                                            veri-tel="<?php echo htmlspecialchars($hasta['telefon'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($hasta['tc'] ?? '-'); ?></strong></td>
                                            <td><span style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars(trim(($hasta['ad'] ?? '') . ' ' . ($hasta['soyad'] ?? ''))); ?></span></td>
                                            <td><?php echo htmlspecialchars($hasta['telefon'] ?? '-'); ?></td>
                                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars(empty($hasta['adres']) ? '-' : $hasta['adres']); ?></span></td>
                                            <td>
                                                <button class="btn-icon edit" onclick='editPatient(<?php echo json_encode($hasta); ?>)' title="Düzenle">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <?php if ($_SESSION['kullanici_rol'] === 'mudur'): ?>
                                                    <button class="btn-icon sil_islemi" onclick="confirmAction('Bu hasta kaydını silmek istediğinize emin misiniz?', 'hastalar.php?sil=<?php echo $hasta['id']; ?>')" title="Sil">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <div style="padding-bottom: 3rem;"></div>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" id="formModal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="form_basligi">Yeni Hasta Kaydı</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="hasta_formu" method="POST" action="hastalar.php">
                    <input type="hidden" name="islem" value="kaydet">
                    <input type="hidden" name="id" id="hasta_id_modal">

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="tc">TC Kimlik No</label>
                        <input type="text" name="tc" id="tc" required maxlength="11" placeholder="11 haneli TC kimlik numarası..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <div class="form-satir">
                        <div class="form-group">
                            <label for="ad">Ad</label>
                            <input type="text" name="ad" id="ad" required placeholder="Örn: Ahmet">
                        </div>
                        <div class="form-group">
                            <label for="soyad">Soyad</label>
                            <input type="text" name="soyad" id="soyad" required placeholder="Örn: Yılmaz">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="telefon">Telefon Numarası</label>
                        <input type="text" name="telefon" id="telefon" placeholder="Örn: 05551234567">
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label for="adres">Açık Adres</label>
                        <textarea name="adres" id="adres" rows="3" placeholder="İl, ilçe, mahalle, sokak..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeModal()">İptal</button>
                        <button type="submit" class="btn-primary" id="saveBtn">
                            <i class="fa-solid fa-save"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function openModal() {
            document.getElementById('hasta_formu').reset();
            document.getElementById('hasta_id_modal').value = '';
            document.getElementById('form_basligi').textContent = 'Yeni Hasta Kaydı';
            document.getElementById('formModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('show');
        }

        function editPatient(hasta) {
            document.getElementById('hasta_id_modal').value = hasta.id;
            document.getElementById('tc').value = hasta.tc || '';
            document.getElementById('ad').value = hasta.ad || '';
            document.getElementById('soyad').value = hasta.soyad || '';
            document.getElementById('telefon').value = hasta.telefon || '';
            document.getElementById('adres').value = hasta.adres || '';
            
            document.getElementById('form_basligi').textContent = 'Hasta Bilgilerini Düzenle';
            document.getElementById('formModal').classList.add('show');
        }

        document.getElementById('arama_kutusu').addEventListener('input', function(e) {
            const arama_terimi = e.target.value.toLowerCase();
            const tablo_satirlari = document.querySelectorAll('.patient-satir');
            tablo_satirlari.forEach(satir => {
                const ad = satir.getAttribute('veri-ad');
                const tc = satir.getAttribute('veri-tc');
                const tel = satir.getAttribute('veri-tel');
                if (ad.includes(arama_terimi) || tc.includes(arama_terimi) || tel.includes(arama_terimi)) {
                    satir.style.display = '';
                } else {
                    satir.style.display = 'none';
                }
            });
        });

        <?php if (isset($_GET['mesaj'])): ?>
            const toast = document.getElementById('toast');
            toast.textContent = "<?php echo htmlspecialchars($_GET['mesaj']); ?>";
            toast.className = "toast show <?php echo $_GET['durum'] ?? 'basarili'; ?>";
            setTimeout(() => { toast.className = "toast"; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
