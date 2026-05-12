const API_URL = 'http://localhost/syp/api.php?action=medicines';

// DOM Elements
const medicinesList = document.getElementById('medicinesList');
const formModal = document.getElementById('formModal');
const medicineForm = document.getElementById('medicineForm');
const medIdInput = document.getElementById('medId');
const formTitle = document.getElementById('formTitle');
const searchInput = document.getElementById('searchInput');
const refreshBtn = document.getElementById('refreshBtn');
const toast = document.getElementById('toast');

// Form Inputs
const barcodeInput = document.getElementById('barcode');
const nameInput = document.getElementById('name');
const categoryInput = document.getElementById('category');
const priceInput = document.getElementById('price');
const stockInput = document.getElementById('stock');
const expiryInput = document.getElementById('expiry_date');

// Stats Elements
const totalMedCount = document.getElementById('totalMedCount');
const totalStockCount = document.getElementById('totalStockCount');
const criticalStockCount = document.getElementById('criticalStockCount');
const expiringCount = document.getElementById('expiringCount');

let allMedicines = [];

// Yetki ve Kullanıcı Bilgileri
const token = localStorage.getItem('token');
const userRole = localStorage.getItem('userRole'); // 'manager' veya 'personnel'
const userName = localStorage.getItem('userName');

const defaultHeaders = {
    'Content-Type': 'application/json',
    'X-User-Role': userRole || 'personnel'
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Profil bilgilerini güncelle
    document.getElementById('sidebarUserName').textContent = userName || 'Bilinmiyor';
    document.getElementById('sidebarUserRole').textContent = userRole === 'manager' ? 'Eczane Müdürü' : 'Personel';
    
    // Yetki kontrolü (Personel ise Ekle butonunu gizle)
    if (userRole === 'personnel') {
        document.querySelector('.topbar-actions .btn-primary').style.display = 'none';
    }

    fetchMedicines();
});

medicineForm.addEventListener('submit', handleFormSubmit);
searchInput.addEventListener('input', handleSearch);
refreshBtn.addEventListener('click', () => {
    refreshBtn.querySelector('i').style.transform = 'rotate(180deg)';
    setTimeout(() => refreshBtn.querySelector('i').style.transform = 'none', 300);
    fetchMedicines();
});

// Çıkış Yap
document.getElementById('logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = 'login.php';
});

// Modal Functions
function openModal() {
    medicineForm.reset();
    medIdInput.value = '';
    formTitle.textContent = 'Yeni İlaç Kaydı';
    formModal.classList.add('show');
}

function closeModal() {
    formModal.classList.remove('show');
}

// Fetch all medicines
async function fetchMedicines() {
    try {
        const response = await fetch(API_URL);
        const result = await response.json();
        
        if (result.status === 'success') {
            allMedicines = result.data;
            renderMedicines(allMedicines);
            updateDashboardStats(allMedicines);
        } else {
            showToast(result.message || 'Veriler alınamadı', 'error');
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        medicinesList.innerHTML = `<tr><td colspan="7" class="text-center" style="color:red; padding: 2rem;">
            <b>Sunucuya bağlanılamadı.</b><br>
            Lütfen XAMPP üzerinden Apache ve MySQL'i başlattığınızdan emin olun.<br>
            Ayrıca <code>database.sql</code> dosyasını phpMyAdmin'e yüklediğinizden (import) emin olun!
        </td></tr>`;
    }
}

// Update Dashboard Statistics
function updateDashboardStats(medicines) {
    totalMedCount.textContent = medicines.length;
    
    let tStock = 0;
    let critical = 0;
    let expiring = 0;
    
    const today = new Date();
    const nextMonth = new Date();
    nextMonth.setMonth(today.getMonth() + 1);

    medicines.forEach(med => {
        tStock += parseInt(med.stock);
        if (parseInt(med.stock) < 10) critical++;
        
        const expDate = new Date(med.expiry_date);
        if (expDate <= nextMonth) expiring++;
    });

    totalStockCount.textContent = tStock;
    criticalStockCount.textContent = critical;
    expiringCount.textContent = expiring;
}

// Render Table
function renderMedicines(medicines) {
    medicinesList.innerHTML = '';
    
    if (medicines.length === 0) {
        medicinesList.innerHTML = '<tr><td colspan="7" class="text-center">Henüz ilaç bulunmuyor.</td></tr>';
        return;
    }

    medicines.forEach(med => {
        const tr = document.createElement('tr');
        
        // Format Currency
        const price = parseFloat(med.price).toLocaleString('tr-TR', { minimumFractionDigits: 2 });
        
        // Format Date
        const dateObj = new Date(med.expiry_date);
        const formattedDate = dateObj.toLocaleDateString('tr-TR');
        
        // Stock Badge
        let stockClass = 'stock-good';
        let stockText = 'Yeterli';
        const stockVal = parseInt(med.stock);
        
        if (stockVal === 0) {
            stockClass = 'stock-critical';
            stockText = 'Tükendi';
        } else if (stockVal < 10) {
            stockClass = 'stock-warning';
            stockText = 'Kritik';
        }

        let actionsHtml = '';
        if (userRole === 'manager') {
            actionsHtml = `
                <button class="btn-icon edit" onclick="editMedicine(${med.id})" title="Düzenle">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn-icon delete" onclick="deleteMedicine(${med.id})" title="Sil">
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;
        } else {
            actionsHtml = `<span style="color:var(--text-muted); font-size: 0.8rem;"><i class="fa-solid fa-lock"></i> Yetki Yok</span>`;
        }

        tr.innerHTML = `
            <td><strong>${med.barcode}</strong></td>
            <td>${med.name}</td>
            <td>${med.category}</td>
            <td>₺${price}</td>
            <td>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span>${med.stock} Adet</span>
                    <span class="stock-badge ${stockClass}">${stockText}</span>
                </div>
            </td>
            <td>${formattedDate}</td>
            <td>
                ${actionsHtml}
            </td>
        `;
        medicinesList.appendChild(tr);
    });
}

// Handle Search
function handleSearch(e) {
    const term = e.target.value.toLowerCase();
    const filtered = allMedicines.filter(med => 
        med.name.toLowerCase().includes(term) || 
        med.barcode.includes(term)
    );
    renderMedicines(filtered);
}

// Handle Form Submit
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const id = medIdInput.value;
    const medicineData = {
        barcode: barcodeInput.value.trim(),
        name: nameInput.value.trim(),
        category: categoryInput.value,
        price: parseFloat(priceInput.value),
        stock: parseInt(stockInput.value),
        expiry_date: expiryInput.value
    };

    try {
        let response, result;
        
        if (id) {
            response = await fetch(`${API_URL}?id=${id}`, {
                method: 'PUT',
                headers: defaultHeaders,
                body: JSON.stringify(medicineData)
            });
        } else {
            response = await fetch(API_URL, {
                method: 'POST',
                headers: defaultHeaders,
                body: JSON.stringify(medicineData)
            });
        }

        result = await response.json();

        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            closeModal();
            fetchMedicines();
        } else {
            showToast(result.message || 'Bir hata oluştu', 'error');
        }
    } catch (error) {
        console.error('Error saving:', error);
        showToast('Kayıt işlemi başarısız', 'error');
    }
}

// Edit Medicine (GET by ID)
async function editMedicine(id) {
    try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            const med = result.data;
            
            medIdInput.value = med.id;
            barcodeInput.value = med.barcode;
            nameInput.value = med.name;
            categoryInput.value = med.category;
            priceInput.value = med.price;
            stockInput.value = med.stock;
            expiryInput.value = med.expiry_date;
            
            formTitle.textContent = 'İlaç Düzenle';
            formModal.classList.add('show');
        } else {
            showToast(result.message || 'Bilgiler alınamadı', 'error');
        }
    } catch (error) {
        showToast('Bağlantı hatası', 'error');
    }
}

// Delete Medicine
async function deleteMedicine(id) {
    if (!confirm('Bu ilacı sistemden silmek istediğinize emin misiniz?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE',
            headers: defaultHeaders
        });
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            fetchMedicines();
        } else {
            showToast(result.message || 'Silme başarısız', 'error');
        }
    } catch (error) {
        showToast('Silme başarısız', 'error');
    }
}

// Toast Notification
function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}


