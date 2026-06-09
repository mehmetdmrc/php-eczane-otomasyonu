<?php
require_once 'baglanti.php';
require_once 'Modeller.php';

session_start();
// Giriş kontrolü
if (!isset($_SESSION['k_id'])) {
    header("Location: giris.php");
    exit();
}

$ilacModel = new Ilac($vt);
$kategoriModel = new Kategori($vt);

// Silme İşlemi
if (isset($_GET['sil']) && $_SESSION['kullanici_rol'] === 'mudur') {
    $ilacModel->sil_islemi($_GET['sil']);
    header("Location: index.php?durum=basarili&mesaj=" . urlencode("İlaç başarıyla silindi"));
    exit();
}

// Kaydetme/Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'kaydet') {
    $veri = [
        'ad' => trim($_POST['ad']),
        'kategori_id' => $_POST['kategori_id'],
        'fiyat' => floatval($_POST['fiyat']),
        'stok' => intval($_POST['stok']),
        'aciklama' => trim($_POST['aciklama'])
    ];

    if (!empty($_POST['id'])) {
        $ilacModel->guncelleme($_POST['id'], $veri);
        $mesaj_metni = "İlaç başarıyla güncellendi";
    } else {
        $ilacModel->olustur($veri);
        $mesaj_metni = "Yeni ilaç başarıyla eklendi";
    }
    header("Location: index.php?durum=basarili&mesaj=" . urlencode($mesaj_metni));
    exit();
}

$ilaclar = $ilacModel->tumunu_getir();
$kategoriler = $kategoriModel->tumunu_getir();

// İstatistikler
$toplam_ilac_sayisi = count($ilaclar);
$toplam_kategori_sayisi = count($kategoriler);
$toplam_stok = 0;
$kritik_stok_sayisi = 0;

foreach ($ilaclar as $ilac_item) {
    $toplam_stok += $ilac_item['stok'];
    if ($ilac_item['stok'] < 10) $kritik_stok_sayisi++;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mEczane | İlaç ve Envanter Yönetimi</title>
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
                    <input type="text" id="arama_kutusu" placeholder="İlaç adı, kategori veya açıklama ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-icon"><i class="fa-regular fa-bell"></i></button>
                    <?php if ($_SESSION['kullanici_rol'] === 'mudur'): ?>
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Yeni İlaç Ekle</button>
                    <?php endif; ?>
                </div>
            </header>
            
            <div class="scroll-area">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-pills"></i></div>
                        <div class="stat-details">
                            <h3>Toplam İlaç Çeşidi</h3>
                            <p><?php echo $toplam_ilac_sayisi; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="stat-details">
                            <h3>Toplam Stok Adedi</h3>
                            <p><?php echo $toplam_stok; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="stat-details">
                            <h3>Kategori Sayısı</h3>
                            <p><?php echo $toplam_kategori_sayisi; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="stat-details">
                            <h3>Kritik Stok (&lt;10)</h3>
                            <p><?php echo $kritik_stok_sayisi; ?></p>
                            <button class="stat-action-btn" onclick="showAttentionModal('critical')">Görüntüle <i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <section class="veri-section" style="margin-top: 1.5rem;">
                    <div class="section-header">
                        <div>
                            <h2>Envanter Yönetimi</h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Sistemde kayıtlı tüm ilaçlar, kategoriler ve güncel stok durumları.</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button class="btn-secondary" onclick="location.reload()" title="Listeyi Yenile">
                                <i class="fa-solid fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table id="ilaclar_tablosu">
                            <thead>
                                <tr>
                                    <th># ID</th>
                                    <th>İlaç Adı</th>
                                    <th>Kategori</th>
                                    <th>Fiyat (₺)</th>
                                    <th>Stok Durumu</th>
                                    <th>Açıklama</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="ilaclar_listesi">
                                <?php if (empty($ilaclar)): ?>
                                    <tr><td colspan="7" class="text-center" style="padding: 2rem;">Henüz ilaç bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($ilaclar as $ilac_item): ?>
                                        <?php 
                                            $stok_sinifi = 'stok-good';
                                            $stok_metni = 'Yeterli';
                                            if ($ilac_item['stok'] == 0) { $stok_sinifi = 'stok-critical'; $stok_metni = 'Tükendi'; }
                                            elseif ($ilac_item['stok'] < 10) { $stok_sinifi = 'stok-warning'; $stok_metni = 'Kritik'; }
                                            $kritik_mi = ($ilac_item['stok'] < 10) ? '1' : '0';
                                        ?>
                                        <tr class="medicine-satir" 
                                            veri-id="<?php echo $ilac_item['id']; ?>"
                                            veri-ad="<?php echo strtolower($ilac_item['ad']); ?>" 
                                            veri-kategori="<?php echo strtolower($ilac_item['kategori_adi'] ?? ''); ?>"
                                            veri-aciklama="<?php echo strtolower($ilac_item['aciklama'] ?? ''); ?>"
                                            veri-critical="<?php echo $kritik_mi; ?>">
                                            <td><strong>#<?php echo $ilac_item['id']; ?></strong></td>
                                            <td><span style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($ilac_item['ad']); ?></span></td>
                                            <td><span style="background: var(--bg-app); padding: 0.35rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($ilac_item['kategori_adi'] ?? 'Kategorisiz'); ?></span></td>
                                            <td><strong>₺<?php echo number_format($ilac_item['fiyat'], 2, ',', '.'); ?></strong></td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                                    <span style="font-weight: 500;"><?php echo $ilac_item['stok']; ?> Adet</span>
                                                    <span class="stok-badge <?php echo $stok_sinifi; ?>"><?php echo $stok_metni; ?></span>
                                                </div>
                                            </td>
                                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($ilac_item['aciklama'] ?? '-'); ?></span></td>
                                            <td>
                                                <?php if ($_SESSION['kullanici_rol'] === 'mudur'): ?>
                                                    <button class="btn-icon edit" onclick='editMedicine(<?php echo json_encode($ilac_item); ?>)' title="Düzenle">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button class="btn-icon sil_islemi" onclick="confirmAction('Bu ilacı silmek istediğinize emin misiniz?', 'index.php?sil=<?php echo $ilac_item['id']; ?>')" title="Sil">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size: 0.8rem;"><i class="fa-solid fa-lock"></i> Yetki Yok</span>
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
                <h2 id="form_basligi">Yeni İlaç Kaydı</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="ilac_formu" method="POST" action="index.php">
                    <input type="hidden" name="islem" value="kaydet">
                    <input type="hidden" name="id" id="ilac_id_modal">
                    
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="ad">İlaç Adı</label>
                        <input type="text" name="ad" id="ad" required placeholder="Örn: Parol 500mg">
                    </div>

                    <div class="form-satir">
                        <div class="form-group">
                            <label for="kategori_id">Kategori</label>
                            <select name="kategori_id" id="kategori_id" required>
                                <option value="">Seçiniz...</option>
                                <?php foreach ($kategoriler as $kat): ?>
                                    <option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['ad']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fiyat">Fiyat (₺)</label>
                            <input type="number" step="0.01" name="fiyat" id="fiyat" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="stok">Stok Miktarı</label>
                        <input type="number" name="stok" id="stok" required placeholder="Adet">
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label for="aciklama">Açıklama</label>
                        <textarea name="aciklama" id="aciklama" rows="3" placeholder="İlaçla ilgili not veya açıklama..."></textarea>
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

    <!-- Attention Modal -->
    <div class="modal-overlay" id="dikkat_modali">
        <div class="modal" style="max-width: 800px;">
            <div class="modal-header">
                <h2 id="dikkat_basligi"><i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i> Kritik Stok Uyarısı</h2>
                <button class="btn-close" onclick="closeAttentionModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="table-container">
                    <table class="attention-table">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>İlaç Adı</th>
                                <th>Kalan Stok</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="dikkat_listesi">
                            <?php if ($kritik_stok_sayisi == 0): ?>
                                <tr><td colspan="4" class="text-center" style="padding: 2rem;">Şu an kritik stokta ilaç bulunmuyor.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ilaclar as $i): ?>
                                    <?php if ($i['stok'] < 10): ?>
                                        <tr>
                                            <td>#<?php echo $i['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($i['ad']); ?></strong></td>
                                            <td><span class="stok-badge <?php echo $i['stok'] == 0 ? 'stok-critical' : 'stok-warning'; ?>"><?php echo $i['stok']; ?> Adet</span></td>
                                            <td>
                                                <button class="btn-primary btn-sm" onclick="closeAttentionModal(); scrollToRow(<?php echo $i['id']; ?>)">
                                                    <i class="fa-solid fa-eye"></i> İncele
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function openModal() {
            document.getElementById('ilac_formu').reset();
            document.getElementById('ilac_id_modal').value = '';
            document.getElementById('form_basligi').textContent = 'Yeni İlaç Kaydı';
            document.getElementById('formModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('show');
        }

        function editMedicine(ilac_item) {
            document.getElementById('ilac_id_modal').value = ilac_item.id;
            document.getElementById('ad').value = ilac_item.ad;
            document.getElementById('kategori_id').value = ilac_item.kategori_id || '';
            document.getElementById('fiyat').value = ilac_item.fiyat;
            document.getElementById('stok').value = ilac_item.stok;
            document.getElementById('aciklama').value = ilac_item.aciklama || '';
            
            document.getElementById('form_basligi').textContent = 'İlaç Düzenle';
            document.getElementById('formModal').classList.add('show');
        }

        document.getElementById('arama_kutusu').addEventListener('input', function(e) {
            const arama_terimi = e.target.value.toLowerCase();
            const tablo_satirlari = document.querySelectorAll('.medicine-satir');
            tablo_satirlari.forEach(satir => {
                const ad = satir.getAttribute('veri-ad');
                const kategori = satir.getAttribute('veri-kategori');
                const aciklama = satir.getAttribute('veri-aciklama');
                if (ad.includes(arama_terimi) || kategori.includes(arama_terimi) || aciklama.includes(arama_terimi)) {
                    satir.style.display = '';
                } else {
                    satir.style.display = 'none';
                }
            });
        });

        function showAttentionModal() {
            document.getElementById('dikkat_modali').classList.add('show');
        }

        function closeAttentionModal() {
            document.getElementById('dikkat_modali').classList.remove('show');
        }

        function scrollToRow(id) {
            const satir = document.querySelector(`[veri-id="${id}"]`);
            if (satir) {
                satir.scrollIntoView({ behavior: 'smooth', block: 'center' });
                satir.style.backgroundColor = 'var(--warning-bg)';
                setTimeout(() => satir.style.backgroundColor = '', 2000);
            }
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
