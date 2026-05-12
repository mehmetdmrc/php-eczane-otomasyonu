<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastalar | PharmaSoft</title>
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="TC No veya Hasta Adı ara...">
                </div>
                <div class="topbar-actions">
                    <button class="btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Yeni Hasta
                        Ekle</button>
                </div>
            </header>

            <div class="dashboard-stats" style="grid-template-columns: repeat(2, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-details">
                        <h3>Toplam Hasta Sayısı</h3>
                        <p id="totalPatientCount">0</p>
                    </div>
                </div>
            </div>

            <section class="data-section">
                <div class="section-header">
                    <h2>Hasta Listesi</h2>
                    <button class="btn-icon" id="refreshBtn" title="Listeyi Yenile">
                        <i class="fa-solid fa-sync-alt"></i>
                    </button>
                </div>

                <div class="table-container">
                    <table id="patientsTable">
                        <thead>
                            <tr>
                                <th>TC Kimlik No</th>
                                <th>Ad Soyad</th>
                                <th>Telefon</th>
                                <th>Kan Grubu</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="patientsList">
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
                <h2 id="formTitle">Yeni Hasta Kaydı</h2>
                <button class="btn-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="patientForm">
                    <input type="hidden" id="patientId">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tc_no">TC Kimlik No</label>
                            <input type="text" id="tc_no" required maxlength="11" placeholder="11 haneli TC no...">
                        </div>
                        <div class="form-group">
                            <label for="name">Ad Soyad</label>
                            <input type="text" id="name" required placeholder="Örn: Ahmet Yılmaz">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Telefon (Başında 0 olmadan)</label>
                            <input type="text" id="phone" maxlength="10" pattern="\d{10}" placeholder="Örn: 5551234567"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group">
                            <label for="blood_group">Kan Grubu</label>
                            <select id="blood_group">
                                <option value="">Belirtilmemiş</option>
                                <option value="A+">A RH+</option>
                                <option value="A-">A RH-</option>
                                <option value="B+">B RH+</option>
                                <option value="B-">B RH-</option>
                                <option value="AB+">AB RH+</option>
                                <option value="AB-">AB RH-</option>
                                <option value="0+">0 RH+</option>
                                <option value="0-">0 RH-</option>
                            </select>
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

    <script src="patients.js"></script>
</body>

</html>

