<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar | PharmaSoft</title>
    <script>
        if (!localStorage.getItem('token')) {
            window.location.href = 'login.php';
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            max-width: 600px;
            margin-bottom: 2rem;
        }
        .settings-card h3 {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
        }
        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--info-bg);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }
        .profile-info h4 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .profile-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h2>Sistem Ayarları</h2>
            </header>

            <div style="padding: 2rem;">
                <div class="settings-card">
                    <h3>Profil Bilgileri</h3>
                    <div class="user-profile-badge">
                        <div class="profile-avatar"><i class="fa-solid fa-user-doctor"></i></div>
                        <div class="profile-info">
                            <h4 id="profileName">Yükleniyor...</h4>
                            <p id="profileRole">Yetki Yükleniyor...</p>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <h3>Şifre Değiştir</h3>
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="current_password">Mevcut Şifreniz</label>
                            <input type="password" id="current_password" required placeholder="Mevcut şifrenizi girin...">
                        </div>
                        <div class="form-group">
                            <label for="new_password">Yeni Şifreniz</label>
                            <input type="password" id="new_password" required placeholder="Yeni şifrenizi girin..." minlength="5">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Yeni Şifreniz (Tekrar)</label>
                            <input type="password" id="confirm_password" required placeholder="Yeni şifrenizi tekrar girin..." minlength="5">
                        </div>
                        <div class="form-actions" style="margin-top: 1rem; padding-top: 0; border: none;">
                            <button type="submit" class="btn-primary" id="saveBtn">
                                <i class="fa-solid fa-key"></i> Şifreyi Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="toast"></div>

    <script src="settings.js"></script>
</body>
</html>
