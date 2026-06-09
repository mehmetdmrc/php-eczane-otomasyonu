<?php
require_once 'baglanti.php';
require_once 'Modeller.php';

session_start();
// Giriş kontrolü
if (!isset($_SESSION['k_id'])) {
    header("Location: giris.php");
    exit();
}

$raporModel = new Rapor($vt);

// Varsayılan tarih aralığı (Bu Ay)
$baslangic = date('Y-m-01');
$bitis = date('Y-m-t');
$filtre_baslik = "Bu Ayın Satış Raporu (" . date('d.m.Y', strtotime($baslangic)) . " - " . date('d.m.Y', strtotime($bitis)) . ")";
$aktif_filtre = 'aylik';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filtre_turu'])) {
    $aktif_filtre = $_GET['filtre_turu'];
    if ($aktif_filtre === 'gunluk') {
        $baslangic = date('Y-m-d');
        $bitis = date('Y-m-d');
        $filtre_baslik = "Bugünün Satış Raporu (" . date('d.m.Y') . ")";
    } elseif ($aktif_filtre === 'haftalik') {
        $baslangic = date('Y-m-d', strtotime('-6 days'));
        $bitis = date('Y-m-d');
        $filtre_baslik = "Son 7 Günün Satış Raporu (" . date('d.m.Y', strtotime($baslangic)) . " - " . date('d.m.Y') . ")";
    } elseif ($aktif_filtre === 'aylik') {
        $baslangic = date('Y-m-01');
        $bitis = date('Y-m-t');
        $filtre_baslik = "Bu Ayın Satış Raporu (" . date('d.m.Y', strtotime($baslangic)) . " - " . date('d.m.Y', strtotime($bitis)) . ")";
    } elseif ($aktif_filtre === 'ozel' && !empty($_GET['baslangic']) && !empty($_GET['bitis'])) {
        $baslangic = $_GET['baslangic'];
        $bitis = $_GET['bitis'];
        $filtre_baslik = date('d.m.Y', strtotime($baslangic)) . " - " . date('d.m.Y', strtotime($bitis)) . " Arası Satış Raporu";
    }
}

$ozetler = $raporModel->tum_ozetleri_getir();
$filtreli_ozet = $raporModel->ozet_getir($baslangic, $bitis);
$filtreli_satislar = $raporModel->tarih_aralikli_satislar($baslangic, $bitis);
$en_cok_satanlar = $raporModel->en_cok_satilan_ilaclar(5);
$grafik_veri = $raporModel->gunluk_ciro_grafigi();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar ve Analiz | mEczane</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .rapor-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn-group {
            display: flex;
            background: var(--bg-app);
            padding: 0.35rem;
            border-radius: 0.75rem;
            gap: 0.35rem;
        }

        .filter-btn {
            border: none;
            background: transparent;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover { color: var(--text-main); }
        .filter-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
            font-weight: 600;
        }

        .custom-date-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--bg-app);
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
        }

        .custom-date-form input[type="date"] {
            border: 1px solid var(--border);
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            background: white;
            font-size: 0.85rem;
            font-family: inherit;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            padding: 0 2rem 1.5rem 2rem;
        }

        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
        }

        .chart-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .chart-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .chart-card-header h3 {
            font-size: 1.1rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .top-medicine-list {
            list-style: none;
            margin-top: 1rem;
        }

        .top-medicine-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .top-medicine-item:last-child { border-bottom: none; }
        .med-name { font-weight: 600; color: var(--text-main); }
        .med-stat {
            background: var(--info-bg);
            color: var(--primary);
            padding: 0.25rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        @media print {
            body * { visibility: hidden; }
            .main-content, .scroll-area, .veri-section, .veri-section * { visibility: visible; }
            .veri-section {
                position: absolute;
                left: 0; top: 0; width: 100%; margin: 0; box-shadow: none;
            }
            .topbar, .rapor-filter-bar, .sidebar, .btn-primary, .btn-secondary { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h2>Satış Raporları & Analiz</h2>
                <div class="topbar-actions">
                    <button class="btn-secondary" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Raporu Yazdır
                    </button>
                </div>
            </header>

            <div class="rapor-filter-bar">
                <div class="filter-btn-group">
                    <button class="filter-btn <?php echo $aktif_filtre === 'gunluk' ? 'active' : ''; ?>" onclick="window.location.href='raporlar.php?filtre_turu=gunluk'">
                        <i class="fa-solid fa-calendar-day"></i> Günlük
                    </button>
                    <button class="filter-btn <?php echo $aktif_filtre === 'haftalik' ? 'active' : ''; ?>" onclick="window.location.href='raporlar.php?filtre_turu=haftalik'">
                        <i class="fa-solid fa-calendar-week"></i> Haftalık
                    </button>
                    <button class="filter-btn <?php echo $aktif_filtre === 'aylik' ? 'active' : ''; ?>" onclick="window.location.href='raporlar.php?filtre_turu=aylik'">
                        <i class="fa-solid fa-calendar-days"></i> Aylık
                    </button>
                </div>

                <form class="custom-date-form" method="GET" action="raporlar.php">
                    <input type="hidden" name="filtre_turu" value="ozel">
                    <span style="font-size: 0.85rem; font-weight: 500;">Tarih Aralığı:</span>
                    <input type="date" name="baslangic" required value="<?php echo htmlspecialchars($baslangic); ?>">
                    <span>-</span>
                    <input type="date" name="bitis" required value="<?php echo htmlspecialchars($bitis); ?>">
                    <button type="submit" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">Filtrele</button>
                </form>
            </div>

            <div class="scroll-area">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-calendar-day"></i></div>
                        <div class="stat-details">
                            <h3>Bugünkü Ciro</h3>
                            <p>₺<?php echo number_format($ozetler['gunluk']['ciro'] ?? 0, 2, ',', '.'); ?></p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $ozetler['gunluk']['toplam_satis'] ?? 0; ?> satış işlemi</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-calendar-week"></i></div>
                        <div class="stat-details">
                            <h3>Haftalık Ciro</h3>
                            <p>₺<?php echo number_format($ozetler['haftalik']['ciro'] ?? 0, 2, ',', '.'); ?></p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $ozetler['haftalik']['toplam_satis'] ?? 0; ?> satış işlemi</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fa-solid fa-calendar-days"></i></div>
                        <div class="stat-details">
                            <h3>Aylık Ciro</h3>
                            <p>₺<?php echo number_format($ozetler['aylik']['ciro'] ?? 0, 2, ',', '.'); ?></p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $ozetler['aylik']['toplam_satis'] ?? 0; ?> satış işlemi</span>
                        </div>
                    </div>
                    <div class="stat-card" style="border: 2px solid var(--primary); background: #f8fafc;">
                        <div class="stat-icon" style="background: var(--primary); color: white;"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="stat-details">
                            <h3 style="color: var(--primary); font-weight: 600;">Seçili Dönem Ciro</h3>
                            <p style="color: var(--primary-dark);">₺<?php echo number_format($filtreli_ozet['ciro'] ?? 0, 2, ',', '.'); ?></p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $filtreli_ozet['toplam_urun'] ?? 0; ?> adet ilaç satıldı</span>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h3><i class="fa-solid fa-chart-column" style="color: var(--primary);"></i> Son 7 Günün Satış Grafiği (₺)</h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Günlük ciro toplamları</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="gunlukCiroChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h3><i class="fa-solid fa-trophy" style="color: var(--warning);"></i> Çok Satanlar (Top 5)</h3>
                        </div>
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="topMedicineChart"></canvas>
                        </div>
                        <ul class="top-medicine-list">
                            <?php if (empty($en_cok_satanlar)): ?>
                                <li class="text-center" style="color: var(--text-muted); padding: 1rem 0;">Veri bulunmuyor.</li>
                            <?php else: ?>
                                <?php foreach ($en_cok_satanlar as $med): ?>
                                    <li class="top-medicine-item">
                                        <span class="med-name"><?php echo htmlspecialchars($med['ad']); ?></span>
                                        <span class="med-stat"><?php echo $med['toplam_adet']; ?> Adet</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <section class="veri-section data-section" style="margin: 0 2rem 2rem;">
                    <div class="section-header">
                        <div>
                            <h2><?php echo htmlspecialchars($filtre_baslik); ?></h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Filtrelenen zaman aralığındaki tüm satış işlemlerinin dökümü.</p>
                        </div>
                        <div>
                            <span style="font-weight: 600; font-size: 0.9rem; background: var(--info-bg); color: var(--info); padding: 0.5rem 1rem; border-radius: 2rem;">
                                Toplam: <?php echo count($filtreli_satislar); ?> İşlem
                            </span>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>İşlem ID</th>
                                    <th>Hasta Adı Soyadı</th>
                                    <th>Satılan İlaç</th>
                                    <th>Adet</th>
                                    <th>Tutar (₺)</th>
                                    <th>İşlem Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filtreli_satislar)): ?>
                                    <tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">Seçili aralıkta satış işlemi bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($filtreli_satislar as $satis): ?>
                                        <tr>
                                            <td><strong>#<?php echo $satis['id']; ?></strong></td>
                                            <td><span style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($satis['hasta_ad'] ?? '-'); ?></span></td>
                                            <td><span style="font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($satis['ilac_ad'] ?? '-'); ?></span></td>
                                            <td><span style="background: var(--bg-app); padding: 0.25rem 0.6rem; border-radius: 0.5rem; font-weight: 500;"><?php echo $satis['adet']; ?> Adet</span></td>
                                            <td><strong>₺<?php echo number_format($satis['toplam_fiyat'], 2, ',', '.'); ?></strong></td>
                                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('d.m.Y H:i', strtotime($satis['satis_tarihi'])); ?></span></td>
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

    <script>
        const tarihler = <?php echo json_encode($grafik_veri['tarihler'] ?? []); ?>;
        const cirolar = <?php echo json_encode($grafik_veri['cirolar'] ?? []); ?>;
        
        const topMedNames = <?php echo json_encode(array_column($en_cok_satanlar, 'ad')); ?>;
        const topMedCounts = <?php echo json_encode(array_column($en_cok_satanlar, 'toplam_adet')); ?>;

        const ctxLine = document.getElementById('gunlukCiroChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'bar',
            data: {
                labels: tarihler,
                datasets: [{
                    label: 'Günlük Ciro (₺)',
                    data: cirolar,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 8,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: '600' } } }
                }
            }
        });

        const ctxPie = document.getElementById('topMedicineChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: topMedNames,
                datasets: [{
                    data: topMedCounts,
                    backgroundColor: ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
