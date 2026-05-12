<?php
// Aktif sayfayı belirle
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fa-solid fa-staff-snake"></i>
            <span>PharmaSoft</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>><a href="index.php"><i class="fa-solid fa-pills"></i> İlaç Envanteri</a></li>
            <li <?= $currentPage === 'satislar.php' ? 'class="active"' : '' ?>><a href="satislar.php"><i class="fa-solid fa-chart-line"></i> Satışlar</a></li>
            <li <?= $currentPage === 'hastalar.php' ? 'class="active"' : '' ?>><a href="hastalar.php"><i class="fa-solid fa-users"></i> Hastalar</a></li>
            <li <?= $currentPage === 'ayarlar.php' ? 'class="active"' : '' ?>><a href="ayarlar.php"><i class="fa-solid fa-cog"></i> Ayarlar</a></li>
            <li style="margin-top:2rem;"><a href="#" id="logoutBtn" style="color:var(--danger);"><i class="fa-solid fa-sign-out-alt"></i> Çıkış Yap</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar"><i class="fa-solid fa-user-doctor"></i></div>
            <div class="details">
                <span class="name" id="sidebarUserName">Yükleniyor...</span>
                <span class="role" id="sidebarUserRole">...</span>
            </div>
        </div>
    </div>
</aside>
