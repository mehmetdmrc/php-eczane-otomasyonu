<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSoft | Eczane Otomasyonu</title>
    <script>
        // Giriş yapılı değilse login sayfasına yönlendir
        if (!localStorage.getItem('token')) {
            window.location.href = 'login.php';
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Barkod veya ilaç adı ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-icon"><i class="fa-regular fa-bell"></i></button>
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Yeni İlaç Ekle</button>
                </div>
            </header>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-pills"></i></div>
                    <div class="stat-details">
                        <h3>Toplam İlaç Çeşidi</h3>
                        <p id="totalMedCount">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="stat-details">
                        <h3>Toplam Stok</h3>
                        <p id="totalStockCount">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="stat-details">
                        <h3>Kritik Stok (<10)</h3>
                        <p id="criticalStockCount">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fa-solid fa-calendar-xmark"></i></div>
                    <div class="stat-details">
                        <h3>SKT Yaklaşanlar</h3>
                        <p id="expiringCount">0</p>
                    </div>
                </div>
            </div>

            <section class="data-section">
                <div class="section-header">
                    <h2>Envanter Listesi</h2>
                    <button class="btn-icon" id="refreshBtn" title="Listeyi Yenile">
                        <i class="fa-solid fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="medicinesTable">
                        <thead>
                            <tr>
                                <th>Barkod</th>
                                <th>İlaç Adı</th>
                                <th>Kategori</th>
                                <th>Fiyat (₺)</th>
                                <th>Stok Durumu</th>
                                <th>Son Kullanma T.</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="medicinesList">
                            <tr>
                                <td colspan="7" class="text-center loading-text">Veriler yükleniyor... (Lütfen database.sql'i içe aktardığınızdan emin olun)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" id="formModal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="formTitle">Yeni İlaç Kaydı</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="medicineForm">
                    <input type="hidden" id="medId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="barcode">Barkod Numarası</label>
                            <input type="text" id="barcode" required placeholder="Örn: 869950...">
                        </div>
                        <div class="form-group">
                            <label for="name">İlaç Adı</label>
                            <input type="text" id="name" required placeholder="Örn: Parol 500mg">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <select id="category" required>
                                <option value="Ağrı Kesici">Ağrı Kesici</option>
                                <option value="Antibiyotik">Antibiyotik</option>
                                <option value="Alerji">Alerji</option>
                                <option value="Mide İlacı">Mide İlacı</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Kalp Damar">Kalp Damar</option>
                                <option value="Genel" selected>Genel</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Fiyat (₺)</label>
                            <input type="number" step="0.01" id="price" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="stock">Stok Miktarı</label>
                            <input type="number" id="stock" required placeholder="Adet">
                        </div>
                        <div class="form-group">
                            <label for="expiry_date">Son Kullanma Tarihi</label>
                            <input type="date" id="expiry_date" required>
                        </div>
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

    <!-- Notification Toast -->
    <div id="toast" class="toast"></div>

    <script src="app.js"></script>
</body>
</html>


