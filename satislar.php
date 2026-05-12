<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satışlar | PharmaSoft</title>
    <script>
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

        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="İlaç veya Hasta adına göre ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-cart-plus"></i> Yeni Satış Yap</button>
                </div>
            </header>

            <div class="dashboard-stats" style="grid-template-columns: repeat(2, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                    <div class="stat-details">
                        <h3>Toplam Satış Tutarı</h3>
                        <p id="totalRevenue">₺0.00</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
                    <div class="stat-details">
                        <h3>Satış İşlemi Sayısı</h3>
                        <p id="totalSalesCount">0</p>
                    </div>
                </div>
            </div>

            <section class="data-section">
                <div class="section-header">
                    <h2>Satış Geçmişi</h2>
                    <button class="btn-icon" id="refreshBtn" title="Listeyi Yenile">
                        <i class="fa-solid fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="salesTable">
                        <thead>
                            <tr>
                                <th>İşlem ID</th>
                                <th>Hasta Adı</th>
                                <th>Satılan İlaç</th>
                                <th>Adet</th>
                                <th>Toplam Tutar</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody id="salesList">
                            <tr>
                                <td colspan="6" class="text-center loading-text">Veriler yükleniyor...</td>
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
                <h2 id="formTitle">Yeni Satış İşlemi</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="saleForm">
                    <div class="form-group">
                        <label for="patient_id">Hasta Seçin</label>
                        <select id="patient_id" required>
                            <option value="">Hasta Yükleniyor...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="medicine_id">İlaç Seçin</label>
                        <select id="medicine_id" required>
                            <option value="">İlaç Yükleniyor...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Adet</label>
                        <input type="number" id="quantity" required min="1" value="1">
                    </div>

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

    <script src="sales.js"></script>
</body>
</html>


