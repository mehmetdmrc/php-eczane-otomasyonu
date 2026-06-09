<?php
require_once 'baglanti.php';
require_once 'Modeller.php';

session_start();
// Giriş kontrolü
if (!isset($_SESSION['k_id'])) {
    header("Location: giris.php");
    exit();
}

$satisModel = new Satis($vt);
$ilacModel = new Ilac($vt);
$hastaModel = new Hasta($vt);

// Satış İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'kaydet') {
    $ilaclar = [];
    if (isset($_POST['ilac_id']) && is_array($_POST['ilac_id']) && isset($_POST['adet']) && is_array($_POST['adet'])) {
        for ($i = 0; $i < count($_POST['ilac_id']); $i++) {
            if (!empty($_POST['ilac_id'][$i]) && !empty($_POST['adet'][$i])) {
                $ilaclar[] = [
                    'ilac_id' => $_POST['ilac_id'][$i],
                    'adet' => $_POST['adet'][$i]
                ];
            }
        }
    }

    $veri = [
        'hasta_id' => $_POST['hasta_id'],
        'ilaclar' => $ilaclar,
        'kullanici_id' => $_SESSION['k_id']
    ];

    $sonuc = $satisModel->olustur($veri);
    if ($sonuc['durum']) {
        header("Location: satislar.php?durum=basarili&mesaj=" . urlencode("Satış işlemi başarıyla tamamlandı"));
    } else {
        header("Location: satislar.php?durum=hata&mesaj=" . urlencode($sonuc['mesaj']));
    }
    exit();
}

$satislar = $satisModel->detayli_tumunu_getir();
$ilaclar = $ilacModel->tumunu_getir();
$hastalar = $hastaModel->tumunu_getir();

$toplamCiro = 0;
foreach ($satislar as $s) {
    $toplamCiro += $s['toplam_fiyat'];
}
$toplamSatisSayisi = count($satislar);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satışlar | mEczane</title>
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
                    <input type="text" id="arama_kutusu" placeholder="İlaç adı veya hasta adına göre ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-cart-plus"></i> Yeni Satış Yap</button>
                </div>
            </header>
            
            <div class="scroll-area">
                <div class="dashboard-stats" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                        <div class="stat-details">
                            <h3>Toplam Satış Cirosu</h3>
                            <p>₺<?php echo number_format($toplamCiro, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
                        <div class="stat-details">
                            <h3>İşlem Adedi</h3>
                            <p><?php echo $toplamSatisSayisi; ?></p>
                        </div>
                    </div>
                </div>
                
                <section class="veri-section" style="margin-top: 1.5rem;">
                    <div class="section-header">
                        <div>
                            <h2>Satış Geçmişi</h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Yapılan tüm satış işlemleri, hastalar ve satılan kalemler.</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button class="btn-secondary" onclick="location.reload()" title="Listeyi Yenile">
                                <i class="fa-solid fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table id="satislarTablosu">
                            <thead>
                                <tr>
                                    <th>İşlem ID</th>
                                    <th>Hasta Adı Soyadı</th>
                                    <th>Satılan İlaç</th>
                                    <th>Adet</th>
                                    <th>Toplam Tutar</th>
                                    <th>İşlem Tarihi</th>
                                </tr>
                            </thead>
                            <tbody id="satislar_listesi">
                                <?php if (empty($satislar)): ?>
                                    <tr><td colspan="6" class="text-center" style="padding: 2rem;">Henüz satış işlemi bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($satislar as $s): ?>
                                        <tr class="sale-satir" veri-search="<?php echo strtolower(($s['hasta_ad'] ?? '') . ' ' . ($s['ilac_ad'] ?? '')); ?>">
                                            <td><strong>#<?php echo $s['id']; ?></strong></td>
                                            <td><span style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($s['hasta_ad'] ?? '-'); ?></span></td>
                                            <td><span style="font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($s['ilac_ad'] ?? '-'); ?></span></td>
                                            <td><span style="background: var(--bg-app); padding: 0.25rem 0.6rem; border-radius: 0.5rem; font-weight: 500;"><?php echo $s['adet']; ?> Toplam Adet</span></td>
                                            <td><strong>₺<?php echo number_format($s['toplam_fiyat'], 2, ',', '.'); ?></strong></td>
                                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('d.m.Y H:i', strtotime($s['satis_tarihi'])); ?></span></td>
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
                <h2 id="form_basligi">Yeni Satış İşlemi</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="satis_formu" method="POST" action="satislar.php">
                    <input type="hidden" name="islem" value="kaydet">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="hasta_id">Hasta Seçin</label>
                        <select name="hasta_id" id="hasta_id" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($hastalar as $h): ?>
                                <option value="<?php echo $h['id']; ?>"><?php echo htmlspecialchars(($h['tc'] ?? '-') . ' - ' . trim(($h['ad'] ?? '') . ' ' . ($h['soyad'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="ilaclar_container">
                        <div class="ilac-satir" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: flex-end;">
                            <div class="form-group" style="flex: 3; margin-bottom: 0;">
                                <label>İlaç Seçin</label>
                                <select name="ilac_id[]" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($ilaclar as $i): ?>
                                        <?php if ($i['stok'] > 0): ?>
                                            <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['ad']); ?> - Stok: <?php echo $i['stok']; ?> - ₺<?php echo number_format($i['fiyat'], 2); ?></option>
                                        <?php else: ?>
                                            <option value="" disabled><?php echo htmlspecialchars($i['ad']); ?> - TÜKENDİ</option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                <label>Adet</label>
                                <input type="number" name="adet[]" required min="1" value="1">
                            </div>
                            <div style="flex: 0 0 auto; margin-bottom: 0;">
                                <button type="button" class="btn-icon delete" style="width: 48px; height: 48px; background: var(--danger-bg); color: var(--danger); border-radius: var(--radius-md);" onclick="satirSil(this)" title="İlacı Sil"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary" onclick="ilacSatiriEkle()" style="margin-bottom: 2rem; width: 100%; border-style: dashed; border-color: var(--primary-light); color: var(--primary);"><i class="fa-solid fa-plus"></i> Başka İlaç Ekle</button>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeModal()">İptal</button>
                        <button type="submit" class="btn-primary" id="saveBtn">
                            <i class="fa-solid fa-check"></i> Satışı Tamamla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function ilacSatiriEkle() {
            const container = document.getElementById('ilaclar_container');
            const ilkSatir = container.querySelector('.ilac-satir');
            const yeniSatir = ilkSatir.cloneNode(true);
            yeniSatir.querySelector('select').value = '';
            yeniSatir.querySelector('input').value = '1';
            container.appendChild(yeniSatir);
        }

        function satirSil(btn) {
            const container = document.getElementById('ilaclar_container');
            if (container.querySelectorAll('.ilac-satir').length > 1) {
                btn.closest('.ilac-satir').remove();
            } else {
                alert('En az bir ilaç seçmelisiniz.');
            }
        }

        function openModal() {
            document.getElementById('satis_formu').reset();
            const container = document.getElementById('ilaclar_container');
            const satirlar = container.querySelectorAll('.ilac-satir');
            for(let i = 1; i < satirlar.length; i++) {
                satirlar[i].remove();
            }
            document.getElementById('formModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('show');
        }

        document.getElementById('arama_kutusu').addEventListener('input', function(e) {
            const arama_terimi = e.target.value.toLowerCase();
            const tablo_satirlari = document.querySelectorAll('.sale-satir');
            tablo_satirlari.forEach(satir => {
                const aramaMetni = satir.getAttribute('veri-search');
                if (aramaMetni.includes(arama_terimi)) {
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
