<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş | PharmaSoft</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --bg-app: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --danger: #ef4444;
            --success: #10b981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-app) 0%, #cbd5e1 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
        }
        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo {
            font-size: 2rem;
            color: var(--primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        .btn {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        .btn:hover { background-color: var(--primary-dark); }
        .btn:disabled { opacity: 0.7; cursor: not-allowed; }
        
        .demo-box {
            margin-top: 2rem;
            padding: 1rem;
            background: #f0f9ff;
            border: 1px dashed #7dd3fc;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }
        .demo-box ul { margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); }
        .demo-box code { font-weight: bold; color: var(--primary-dark); }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            background: var(--text-main);
            color: white;
            font-weight: 500;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s;
            z-index: 1000;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { background-color: var(--success); }
        .toast.error { background-color: var(--danger); }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                <i class="fa-solid fa-staff-snake"></i>
                <span>PharmaSoft</span>
            </div>
            <p>Eczane Yönetim Sistemine Giriş</p>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" required placeholder="Kullanıcı adınızı girin">
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" required placeholder="Şifrenizi girin">
            </div>
            <button type="submit" class="btn" id="loginBtn">Giriş Yap <i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <div class="demo-box">
            <strong>Test Hesapları:</strong>
            <ul>
                <li>Müdür: <code>mudur</code> - Şifre: <code>12345</code></li>
                <li>Personel: <code>personel</code> - Şifre: <code>12345</code></li>
            </ul>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // Eğer zaten giriş yapılmışsa direkt index'e yönlendir
        if (localStorage.getItem('token')) {
            window.location.href = 'index.php';
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bekleyin...';
            btn.disabled = true;

            const payload = {
                username: document.getElementById('username').value.trim(),
                password: document.getElementById('password').value.trim()
            };

            try {
                const res = await fetch('http://localhost/syp/api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                
                if (res.ok && result.status === 'success') {
                    // Bilgileri localStorage'a kaydet
                    localStorage.setItem('token', result.data.token);
                    localStorage.setItem('userName', result.data.name);
                    localStorage.setItem('userRole', result.data.role);
                    
                    showToast('Giriş başarılı, yönlendiriliyorsunuz...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showToast(result.message || 'Giriş başarısız', 'error');
                    btn.innerHTML = 'Giriş Yap <i class="fa-solid fa-arrow-right"></i>';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                showToast('Sunucuya bağlanılamadı. database.sql import edildi mi?', 'error');
                btn.innerHTML = 'Giriş Yap <i class="fa-solid fa-arrow-right"></i>';
                btn.disabled = false;
            }
        });

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast show ${type}`;
            setTimeout(() => { toast.className = 'toast'; }, 3000);
        }
    </script>
</body>
</html>
