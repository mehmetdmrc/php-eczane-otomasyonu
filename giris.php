<?php
session_start();
require_once 'baglanti.php';
require_once 'Modeller.php';

// Eğer zaten giriş yapılmışsa direkt index'e yönlendir
if (isset($_SESSION['k_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullaniciModel = new Kullanici($vt);
    $user = $kullaniciModel->giris_yap($_POST['kullanici_adi'], $_POST['sifre']);
    
    if ($user) {
        $_SESSION['k_id'] = $user['id'];
        $_SESSION['kullanici_ad'] = trim($user['isim'] . ' ' . $user['soyisim']);
        $_SESSION['kullanici_rol'] = ($user['rol'] == 'Müdür' || $user['rol'] == 'mudur' || $user['rol'] == 'müdür') ? 'mudur' : 'personel';
        header("Location: index.php");
        exit();
    } else {
        $error = "Kullanıcı adı veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş | mEczane</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --success: #06b6d4;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --border: #e2e8f0;
            --bg-accent: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
            pointer-events: none;
        }

        .giris_yap-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
            padding: 3.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .giris_yap-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
            letter-spacing: -0.5px;
        }

        .logo i {
            -webkit-text-fill-color: var(--primary);
            font-size: 2.5rem;
        }

        .giris_yap-header h1 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .giris_yap-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        input {
            width: 100%;
            padding: 0.9rem 1.1rem;
            border: 1.5px solid var(--border);
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #fafbfc 0%, #f8fafc 100%);
        }

        input::placeholder {
            color: var(--text-light);
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
            transform: translateY(-2px);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-family: inherit;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.7rem;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: var(--danger-dark);
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            text-align: center;
            font-weight: 600;
            border-left: 4px solid var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-left: 4px solid var(--danger);
            animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1.2rem 1.8rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(239, 68, 68, 0.3);
            z-index: 1000;
            display: none;
            border-left: 4px solid white;
            animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            min-width: 300px;
        }

        .toast.show {
            display: block;
        }

        @media (max-width: 480px) {
            .giris_yap-card {
                margin: 1rem;
                padding: 2rem;
            }

            .logo {
                font-size: 2rem;
            }

            .giris_yap-header h1 {
                font-size: 1.25rem;
            }
        }
    </style>

</head>
<body>

    <div class="giris_yap-card">
        <div class="giris_yap-header">
            <div class="logo">
                <i class="fa-solid fa-prescription"></i>
                <span>mEczane</span>
            </div>
            <h1>Hoş Geldiniz</h1>
            <p>Eczane Yönetim Sistemine Giriş Yapın</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="giris.php" autocomplete="off">
            <div class="form-group">
                <label for="kullanici_adi">
                    <i class="fa-solid fa-user"></i> Kullanıcı Adı
                </label>
                <input 
                    type="text" 
                    name="kullanici_adi" 
                    id="kullanici_adi" 
                    required 
                    autofocus
                    placeholder="Kullanıcı adınızı girin"
                    autocomplete="username"
                >
            </div>
            <div class="form-group">
                <label for="sifre">
                    <i class="fa-solid fa-lock"></i> Şifre
                </label>
                <input 
                    type="password" 
                    name="sifre" 
                    id="sifre" 
                    required 
                    placeholder="Şifrenizi girin"
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="btn" id="loginBtn">
                <i class="fa-solid fa-sign-in-alt"></i>
                Giriş Yap
            </button>
        </form>
         </div>

    <script>
        localStorage.clear();
    </script>
</body>
</html>
