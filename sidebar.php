<?php
// Aktif sayfayı belirle
$aktif_sayfa = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fa-solid fa-pills"></i>
            <span>mEczane</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li <?php echo ($aktif_sayfa === 'index.php') ? 'class="active"' : ''; ?>>
                <a href="index.php" title="İlaç Envanteri">
                    <i class="fa-solid fa-pills"></i>
                    <span>İlaç Envanteri</span>
                </a>
            </li>
            <li <?php echo ($aktif_sayfa === 'satislar.php') ? 'class="active"' : ''; ?>>
                <a href="satislar.php" title="Satışlar">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Satışlar</span>
                </a>
            </li>
            <li <?php echo ($aktif_sayfa === 'hastalar.php') ? 'class="active"' : ''; ?>>
                <a href="hastalar.php" title="Hastalar">
                    <i class="fa-solid fa-hospital-user"></i>
                    <span>Hastalar</span>
                </a>
            </li>
            <li <?php echo ($aktif_sayfa === 'raporlar.php') ? 'class="active"' : ''; ?>>
                <a href="raporlar.php" title="Raporlar">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Raporlar</span>
                </a>
            </li>
            <li <?php echo ($aktif_sayfa === 'ayarlar.php') ? 'class="active"' : ''; ?>>
                <a href="ayarlar.php" title="Ayarlar">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Ayarlar</span>
                </a>
            </li>
            <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
                <a href="cikis.php" style="color: var(--danger);" title="Çıkış Yap">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Çıkış Yap</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="kullanici-info">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="details">
                <span class="ad"><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Misafir'); ?></span>
                <span class="rol"><?php echo ($_SESSION['kullanici_rol'] ?? '') === 'mudur' ? 'Müdür' : 'Personel'; ?></span>
            </div>
        </div>
    </div>
</aside>

<!-- Custom Global Confirm Modal -->
<div class="modal-overlay" id="customConfirmModal" style="z-index: 9999;">
    <div class="modal" style="max-width: 400px; text-align: center; padding-top: 1rem;">
        <div class="modal-body">
            <div style="font-size: 3.5rem; color: var(--danger); margin-bottom: 1rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 700;">Emin misiniz?</h3>
            <p id="customConfirmMessage" style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem;">Bu işlemi geri alamazsınız.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button class="btn-secondary" onclick="closeCustomConfirm()">Vazgeç</button>
                <button class="btn-primary" id="customConfirmBtn" style="background: var(--danger); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">Evet, Sil</button>
            </div>
        </div>
    </div>
</div>

<script>
let confirmActionUrl = '';

function confirmAction(message, url) {
    document.getElementById('customConfirmMessage').textContent = message;
    confirmActionUrl = url;
    document.getElementById('customConfirmModal').classList.add('show');
}

function closeCustomConfirm() {
    document.getElementById('customConfirmModal').classList.remove('show');
    confirmActionUrl = '';
}

document.getElementById('customConfirmBtn').addEventListener('click', function() {
    if(confirmActionUrl) {
        window.location.href = confirmActionUrl;
    }
});
</script>
