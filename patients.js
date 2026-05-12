const API_URL = 'http://localhost/syp/api.php?action=patients';

const patientsList = document.getElementById('patientsList');
const formModal = document.getElementById('formModal');
const patientForm = document.getElementById('patientForm');
const patientIdInput = document.getElementById('patientId');
const formTitle = document.getElementById('formTitle');
const searchInput = document.getElementById('searchInput');
const refreshBtn = document.getElementById('refreshBtn');
const toast = document.getElementById('toast');

const tcInput = document.getElementById('tc_no');
const nameInput = document.getElementById('name');
const phoneInput = document.getElementById('phone');
const bloodGroupInput = document.getElementById('blood_group');

const totalPatientCount = document.getElementById('totalPatientCount');

let allPatients = [];

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
    
    fetchPatients();
});

patientForm.addEventListener('submit', handleFormSubmit);
searchInput.addEventListener('input', handleSearch);
refreshBtn.addEventListener('click', () => {
    refreshBtn.querySelector('i').style.transform = 'rotate(180deg)';
    setTimeout(() => refreshBtn.querySelector('i').style.transform = 'none', 300);
    fetchPatients();
});

document.getElementById('logoutBtn').addEventListener('click', (e) => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = 'login.php';
});

function openModal() {
    patientForm.reset();
    patientIdInput.value = '';
    formTitle.textContent = 'Yeni Hasta Kaydı';
    formModal.classList.add('show');
}

function closeModal() {
    formModal.classList.remove('show');
}

async function fetchPatients() {
    try {
        const response = await fetch(API_URL);
        const result = await response.json();
        
        if (result.status === 'success') {
            allPatients = result.data;
            renderPatients(allPatients);
            totalPatientCount.textContent = allPatients.length;
        } else {
            showToast(result.message || 'Veriler alınamadı', 'error');
        }
    } catch (error) {
        patientsList.innerHTML = `<tr><td colspan="6" class="text-center" style="color:red; padding: 2rem;">Sunucuya bağlanılamadı.</td></tr>`;
    }
}

function renderPatients(patients) {
    patientsList.innerHTML = '';
    
    if (patients.length === 0) {
        patientsList.innerHTML = '<tr><td colspan="6" class="text-center">Henüz hasta bulunmuyor.</td></tr>';
        return;
    }

    patients.forEach(pat => {
        const tr = document.createElement('tr');
        
        const dateObj = new Date(pat.created_at);
        const formattedDate = dateObj.toLocaleDateString('tr-TR');
        
        let actionsHtml = `
            <button class="btn-icon edit" onclick="editPatient(${pat.id})" title="Düzenle">
                <i class="fa-solid fa-pen"></i>
            </button>
        `;

        if (userRole === 'manager') {
            actionsHtml += `
                <button class="btn-icon delete" onclick="deletePatient(${pat.id})" title="Sil">
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;
        }

        tr.innerHTML = `
            <td><strong>${pat.tc_no}</strong></td>
            <td>${pat.name}</td>
            <td>${pat.phone || '-'}</td>
            <td><span class="stock-badge stock-good">${pat.blood_group || '-'}</span></td>
            <td>${formattedDate}</td>
            <td>${actionsHtml}</td>
        `;
        patientsList.appendChild(tr);
    });
}

function handleSearch(e) {
    const term = e.target.value.toLowerCase();
    const filtered = allPatients.filter(pat => 
        pat.name.toLowerCase().includes(term) || 
        pat.tc_no.includes(term)
    );
    renderPatients(filtered);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const id = patientIdInput.value;
    const patientData = {
        tc_no: tcInput.value.trim(),
        name: nameInput.value.trim(),
        phone: phoneInput.value.trim(),
        blood_group: bloodGroupInput.value
    };

    try {
        let response, result;
        
        if (id) {
            response = await fetch(`${API_URL}?id=${id}`, {
                method: 'PUT',
                headers: defaultHeaders,
                body: JSON.stringify(patientData)
            });
        } else {
            response = await fetch(API_URL, {
                method: 'POST',
                headers: defaultHeaders,
                body: JSON.stringify(patientData)
            });
        }

        result = await response.json();

        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            closeModal();
            fetchPatients();
        } else {
            showToast(result.message || 'Bir hata oluştu', 'error');
        }
    } catch (error) {
        showToast('Kayıt işlemi başarısız', 'error');
    }
}

async function editPatient(id) {
    try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            const pat = result.data;
            
            patientIdInput.value = pat.id;
            tcInput.value = pat.tc_no;
            nameInput.value = pat.name;
            phoneInput.value = pat.phone;
            bloodGroupInput.value = pat.blood_group;
            
            formTitle.textContent = 'Hasta Düzenle';
            formModal.classList.add('show');
        } else {
            showToast(result.message || 'Bilgiler alınamadı', 'error');
        }
    } catch (error) {
        showToast('Bağlantı hatası', 'error');
    }
}

async function deletePatient(id) {
    if (!confirm('Bu hastayı sistemden silmek istediğinize emin misiniz?')) return;
    
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE',
            headers: defaultHeaders
        });
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
            showToast(result.message, 'success');
            fetchPatients();
        } else {
            showToast(result.message || 'Silme başarısız', 'error');
        }
    } catch (error) {
        showToast('Silme başarısız', 'error');
    }
}

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    setTimeout(() => { toast.className = 'toast'; }, 3000);
}


