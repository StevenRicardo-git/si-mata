document.addEventListener('DOMContentLoaded', function() {
    const staffTableBody = document.getElementById('staffTableBody');
    if (staffTableBody && staffTableBody.children.length > 0) {
        new Sortable(staffTableBody, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateUrutan();
            }
        });
    }

    setupModalBackdropClose('addModal');
    setupModalBackdropClose('editModal');
    setupModalBackdropClose('deleteModal');

    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Menyimpan data...');
        setTimeout(() => {
            this.submit();
        }, 1500);
    });

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Memperbarui data...');
        setTimeout(() => {
            this.submit();
        }, 1500);
    });

    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Menghapus data...');
        setTimeout(() => {
            this.submit();
        }, 1500);
    });
});

function updateUrutan() {
    const staffTableBody = document.getElementById('staffTableBody');
    const rows = staffTableBody.querySelectorAll('tr[data-id]');
    const items = [];
    
    rows.forEach((row, index) => {
        items.push({
            id: row.dataset.id,
            urutan: index + 1
        });
    });

    showLoading('Memperbarui urutan...');

    fetch(window.updateUrutanUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('Gagal memperbarui urutan', 'error');
    });
}

function cekKepalaDinasAktif(excludeId = null) {
    const kepalaDinasTable = document.querySelector('.bg-white.rounded-lg.shadow-md.p-6.mb-8 tbody');
    if (!kepalaDinasTable) return null;
    const rows = kepalaDinasTable.querySelectorAll('tr[class*="hover:bg-gray-50"]');
    
    for (let row of rows) {
        if (excludeId && row.querySelector(`button[onclick*="toggleStatus(${excludeId}"]`)) {
            continue;
        }
        
        const statusBadge = row.querySelector('span.rounded-full');
        if (statusBadge && statusBadge.textContent.trim() === 'Aktif') {
            const namaCell = row.querySelector('td:first-child');
            return namaCell ? namaCell.textContent.trim() : 'tidak diketahui';
        }
    }
    
    return null;
}

async function openAddModal(tipe) {
    if (tipe === 'kepala_dinas') {
        const kepalaDinasAktif = cekKepalaDinasAktif();
        if (kepalaDinasAktif) {
            await customConfirm(
                `Sudah ada Kepala Dinas aktif (<strong>${kepalaDinasAktif}</strong>).<br><br>Anda harus menonaktifkan atau menghapus data Kepala Dinas yang ada terlebih dahulu.`,
                { title: 'Tidak Dapat Menambahkan', confirmText: 'Mengerti', cancelText: '', type: 'warning' }
            );
            return;
        }
    }
    
    document.getElementById('addTipe').value = tipe;
    document.getElementById('addModalTitle').textContent = tipe === 'kepala_dinas' ? 'Tambah Kepala Dinas' : 'Tambah Staff';
    
    const pangkatField = document.getElementById('addPangkatField');
    if (tipe === 'kepala_dinas') {
        pangkatField.classList.remove('hidden');
    } else {
        pangkatField.classList.add('hidden');
    }
    
    document.getElementById('addForm').reset();
    document.getElementById('addTipe').value = tipe;
    openModal('addModal');
}

function openEditModal(data) {
    document.getElementById('editForm').action = `/disperkim/${data.id}`;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editJabatan').value = data.jabatan;
    document.getElementById('editNip').value = data.nip || '';

    const pangkatField = document.getElementById('editPangkatField'); 
    
    if (data.tipe === 'kepala_dinas') {
        pangkatField.classList.remove('hidden');
        document.getElementById('editPangkat').value = data.pangkat || '';
    } else {
        pangkatField.classList.add('hidden');
        document.getElementById('editPangkat').value = '';
    }
    
    openModal('editModal'); 
}


async function toggleStatus(id, action) {
    const row = document.querySelector(`button[onclick*="toggleStatus(${id}"]`).closest('tr');
    const isKepalaDinas = row && row.closest('table').closest('.bg-white').querySelector('h2')?.textContent.includes('Kepala Dinas');
    
    if (isKepalaDinas && action === 'aktifkan') {
        const kepalaDinasAktif = cekKepalaDinasAktif(id);
        if (kepalaDinasAktif) {
            await customConfirm(
                `Sudah ada Kepala Dinas aktif (<strong>${kepalaDinasAktif}</strong>).<br><br>Anda harus menonaktifkan Kepala Dinas yang aktif terlebih dahulu.`,
                { title: 'Tidak Dapat Mengaktifkan', confirmText: 'Mengerti', cancelText: '', type: 'warning' }
            );
            return;
        }
    }
    
    const result = await customConfirm(
        `Apakah Anda yakin ingin ${action} data ini?`,
        { title: 'Konfirmasi Aksi', confirmText: 'Ya, Lanjutkan', cancelText: 'Batal', type: 'warning' }
    );
    
    if (result) {
        const form = document.getElementById('toggleStatusForm');
        form.action = `/disperkim/${id}/toggle`;
        showLoading(`${action.charAt(0).toUpperCase() + action.slice(1)} data...`);

        setTimeout(() => {
            form.submit();
        }, 1500);
    }
}

async function confirmDelete(id, nama) {
    const row = document.querySelector(`button[onclick*="confirmDelete(${id}"]`).closest('tr');
    const isKepalaDinas = row && row.closest('table').closest('.bg-white').querySelector('h2')?.textContent.includes('Kepala Dinas');
    
    let message = `Apakah Anda yakin ingin menghapus <strong>${nama}</strong>?`;
    
    if (isKepalaDinas) {
        message += `<br><br><small class="text-gray-500">Setelah dihapus, Anda dapat menambahkan Kepala Dinas baru.</small>`;
    }
    
    const result = await customConfirm(
        message,
        { title: 'Konfirmasi Hapus', confirmText: 'Ya, Hapus', cancelText: 'Batal', type: 'danger' }
    );
    
    if (result) {
        document.getElementById('deleteNama').textContent = nama;
        document.getElementById('deleteForm').action = `/disperkim/${id}`;
        document.getElementById('deleteForm').submit();
    }
}

window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.toggleStatus = toggleStatus;
window.confirmDelete = confirmDelete;
window.updateUrutan = updateUrutan;