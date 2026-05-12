const API_URL = 'http://localhost/syp/api.php?action=settings';

const passwordForm = document.getElementById('passwordForm');
const currentPassword = document.getElementById('current_password');
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const saveBtn = document.getElementById('saveBtn');
const toast = document.getElementById('toast');

const tokenStr = localStorage.getItem('token');
let userId = null;
if (tokenStr) {
    try {
        const tokenData = JSON.parse(atob(tokenStr));
        userId = tokenData.id;
    } catch(e) {
        console.error("Token parse error", e);
    }
}

const userRole = localStorage.getItem('userRole'); 
const userName = localStorage.getItem('userName');

document.addEventListener('DOMContentLoaded', () => {
    const roleText = userRole === 'manager' ? 'Eczane Müdürü' : 'Personel';
    
    document.getElementById('sidebarUserName').textContent = userName || 'Bilinmiyor';
    document.getElementById('sidebarUserRole').textContent = roleText;
    
    document.getElementById('profileName').textContent = userName || 'Bilinmiyor';
    document.getElementById('profileRole').textContent = roleText;
});

document.getElementById('logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = 'login.php';
});

passwordForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (newPassword.value !== confirmPassword.value) {
        showToast('Yeni şifreler birbiriyle eşleşmiyor!', 'error');
        return;
    }

    if (!userId) {
        showToast('Kullanıcı kimliği doğrulanamadı, lütfen tekrar giriş yapın.', 'error');
        return;
    }

    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Kaydediliyor...';
    saveBtn.disabled = true;

    const payload = {
        user_id: userId,
        current_password: currentPassword.value,
        new_password: newPassword.value
    };

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-User-Role': userRole || 'personnel'
            },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            passwordForm.reset();
        } else {
            showToast(result.message || 'Åifre güncellenemedi', 'error');
        }
    } catch (error) {
        showToast('Sunucu bağlantı hatası', 'error');
    } finally {
        saveBtn.innerHTML = '<i class="fa-solid fa-key"></i> Åifreyi Güncelle';
        saveBtn.disabled = false;
    }
});

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    setTimeout(() => { toast.className = 'toast'; }, 3000);
}


