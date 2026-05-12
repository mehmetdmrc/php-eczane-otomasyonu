const API_URL = 'http://localhost/syp/api.php?action=sales';
const PATIENTS_URL = 'http://localhost/syp/api.php?action=patients';
const MEDICINES_URL = 'http://localhost/syp/api.php?action=medicines';

const salesList = document.getElementById('salesList');
const formModal = document.getElementById('formModal');
const saleForm = document.getElementById('saleForm');
const searchInput = document.getElementById('searchInput');
const refreshBtn = document.getElementById('refreshBtn');
const toast = document.getElementById('toast');

const patientSelect = document.getElementById('patient_id');
const medicineSelect = document.getElementById('medicine_id');
const quantityInput = document.getElementById('quantity');

const totalRevenueEl = document.getElementById('totalRevenue');
const totalSalesCountEl = document.getElementById('totalSalesCount');

let allSales = [];

const token = localStorage.getItem('token');
const userRole = localStorage.getItem('userRole'); 
const userName = localStorage.getItem('userName');

const defaultHeaders = {
    'Content-Type': 'application/json',
    'X-User-Role': userRole || 'personnel'
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('sidebarUserName').textContent = userName || 'Bilinmiyor';
    document.getElementById('sidebarUserRole').textContent = userRole === 'manager' ? 'Eczane Müdürü' : 'Personel';
    
    // Satışları, Hastaları ve İlaçları yükle
    fetchSales();
    loadDropdowns();
});

saleForm.addEventListener('submit', handleFormSubmit);
searchInput.addEventListener('input', handleSearch);
refreshBtn.addEventListener('click', () => {
    refreshBtn.querySelector('i').style.transform = 'rotate(180deg)';
    setTimeout(() => refreshBtn.querySelector('i').style.transform = 'none', 300);
    fetchSales();
});

document.getElementById('logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = 'login.php';
});

function openModal() {
    saleForm.reset();
    formModal.classList.add('show');
}

function closeModal() {
    formModal.classList.remove('show');
}

async function loadDropdowns() {
    try {
        const pRes = await fetch(PATIENTS_URL);
        const pData = await pRes.json();
        
        const mRes = await fetch(MEDICINES_URL);
        const mData = await mRes.json();

        if (pData.status === 'success') {
            patientSelect.innerHTML = '<option value="">Hasta Seçin...</option>';
            pData.data.forEach(p => {
                patientSelect.innerHTML += `<option value="${p.id}">${p.name} (${p.tc_no})</option>`;
            });
        }
        
        if (mData.status === 'success') {
            medicineSelect.innerHTML = '<option value="">İlaç Seçin...</option>';
            mData.data.forEach(m => {
                medicineSelect.innerHTML += `<option value="${m.id}">${m.name} - ₺${m.price} (Stok: ${m.stock})</option>`;
            });
        }
    } catch (err) {
        console.error("Dropdown yüklenemedi", err);
    }
}

async function fetchSales() {
    try {
        const response = await fetch(API_URL);
        const result = await response.json();
        
        if (result.status === 'success') {
            allSales = result.data;
            renderSales(allSales);
            updateStats(allSales);
        } else {
            showToast('Satışlar alınamadı', 'error');
        }
    } catch (error) {
        salesList.innerHTML = `<tr><td colspan="6" class="text-center" style="color:red;">Sunucuya bağlanılamadı.</td></tr>`;
    }
}

function updateStats(sales) {
    totalSalesCountEl.textContent = sales.length;
    
    let total = 0;
    sales.forEach(s => {
        total += parseFloat(s.total_price);
    });
    
    totalRevenueEl.textContent = '₺' + total.toLocaleString('tr-TR', { minimumFractionDigits: 2 });
}

function renderSales(sales) {
    salesList.innerHTML = '';
    
    if (sales.length === 0) {
        salesList.innerHTML = '<tr><td colspan="6" class="text-center">Henüz satış bulunmuyor.</td></tr>';
        return;
    }

    sales.forEach(sale => {
        const tr = document.createElement('tr');
        
        const dateObj = new Date(sale.sale_date);
        const formattedDate = dateObj.toLocaleDateString('tr-TR') + ' ' + dateObj.toLocaleTimeString('tr-TR', {hour: '2-digit', minute:'2-digit'});
        
        const price = parseFloat(sale.total_price).toLocaleString('tr-TR', { minimumFractionDigits: 2 });

        tr.innerHTML = `
            <td><strong>#${sale.id}</strong></td>
            <td>${sale.patient_name}</td>
            <td><span class="stock-badge" style="background:#e0f2fe; color:#0284c7;">${sale.medicine_name}</span></td>
            <td>${sale.quantity} Adet</td>
            <td style="color:var(--success); font-weight:600;">₺${price}</td>
            <td><span style="color:var(--text-muted); font-size:0.85rem;">${formattedDate}</span></td>
        `;
        salesList.appendChild(tr);
    });
}

function handleSearch(e) {
    const term = e.target.value.toLowerCase();
    const filtered = allSales.filter(s => 
        s.medicine_name.toLowerCase().includes(term) || 
        s.patient_name.toLowerCase().includes(term)
    );
    renderSales(filtered);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const saleData = {
        patient_id: patientSelect.value,
        medicine_id: medicineSelect.value,
        quantity: quantityInput.value
    };

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: defaultHeaders,
            body: JSON.stringify(saleData)
        });
        
        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            closeModal();
            fetchSales();
            loadDropdowns(); // Stok bilgisi yenilensin diye
        } else {
            showToast(result.message || 'Satış başarısız', 'error');
        }
    } catch (error) {
        showToast('Satış işlemi başarısız', 'error');
    }
}

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    setTimeout(() => { toast.className = 'toast'; }, 3000);
}


