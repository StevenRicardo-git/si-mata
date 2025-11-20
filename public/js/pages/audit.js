const attributeNames = {
    'Penghuni': {
        'nik': 'NIK',
        'nama': 'Nama Lengkap',
        'tempat_lahir': 'Tempat Lahir',
        'tanggal_lahir': 'Tanggal Lahir',
        'pekerjaan': 'Pekerjaan',
        'jenis_kelamin': 'Jenis Kelamin',
        'alamat_ktp': 'Alamat KTP',
        'no_hp': 'Nomor HP',
        'status': 'Status',
    },
    'Kontrak': {
        'penghuni_id': 'ID Penghuni',
        'unit_id': 'ID Unit',
        'tanggal_masuk': 'Tgl Masuk',
        'tanggal_keluar': 'Tgl Keluar',
        'tanggal_keluar_aktual': 'Tgl Keluar Aktual',
        'status': 'Status Kontrak',
        'keringanan': 'Keringanan',
        'nominal_keringanan': 'Nominal Keringanan (Sewa)',
        'tarif_air': 'Tarif Air',
        'no_sip': 'No. SIP',
        'tanggal_sip': 'Tanggal SIP',
        'no_sps': 'No. SPS',
        'tanggal_sps': 'Tanggal SPS',
        'nilai_jaminan': 'Nilai Jaminan',
        'alasan_keluar': 'Alasan Keluar',
        'tunggakan': 'Tunggakan',
    },
    'Keluarga': {
        'penghuni_id': 'ID Penghuni',
        'nama': 'Nama Anggota Keluarga',
        'nik': 'NIK Anggota Keluarga',
        'umur': 'Umur',
        'jenis_kelamin': 'Jenis Kelamin',
        'hubungan': 'Hubungan',
        'catatan': 'Catatan',
    },
    'Unit': {
        'kode_unit': 'Kode Unit',
        'tipe': 'Tipe',
        'status': 'Status Unit',
        'harga_sewa': 'Harga Sewa',
    },
    'Blacklist': { 
        'nik': 'NIK', 
        'nama': 'Nama',
        'alasan_blacklist': 'Alasan Blacklist',
        'tanggal_blacklist': 'Tgl Blacklist',
        'alasan_aktivasi': 'Alasan Aktivasi',
        'tanggal_aktivasi': 'Tgl Aktivasi',
        'status': 'Status',
    },
    'Disperkim': { 
        'nama': 'Nama Staff', 
        'jabatan': 'Jabatan',
        'nip': 'NIP',
        'pangkat': 'Pangkat',
        'urutan': 'Urutan',
        'aktif': 'Aktif',
    },
    'Tagihan': { 
        'kontrak_id': 'ID Kontrak', 
        'status': 'Status Tagihan', 
        'sisa_tagihan': 'Sisa Tagihan',
        'total_tagihan': 'Total Tagihan',
        'periode_bulan': 'Bulan',
        'periode_tahun': 'Tahun',
    },
    'Pembayaran': { 
        'tagihan_id': 'ID Tagihan', 
        'jumlah_bayar': 'Jumlah Bayar', 
        'metode_pembayaran': 'Metode Bayar',
        'tanggal_bayar': 'Tgl Bayar',
    }
};

function getDisplayName(modelName, key) {
    if (attributeNames[modelName] && attributeNames[modelName][key]) {
        return attributeNames[modelName][key];
    }
    
    for (const model in attributeNames) {
        if (attributeNames[model][key]) {
            return attributeNames[model][key];
        }
    }

    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function getAuditableDisplayName(audit) {
    const modelName = audit.auditable_type.split('\\').pop();
    const id = audit.auditable_id;
    
    // Get the actual data from old_values or new_values
    const data = audit.new_values || audit.old_values || {};
    
    switch(modelName) {
        case 'Penghuni':
            if (data.nama) {
                return `Penghuni: ${data.nama}`;
            }
            return `Penghuni (ID: ${id})`;
            
        case 'Kontrak':
            const parts = [];
            if (audit.penghuni_name) {
                parts.push(`Penghuni: ${audit.penghuni_name}`);
            }
            if (audit.unit_code) {
                parts.push(`Unit: ${audit.unit_code}`);
            }
            if (parts.length > 0) {
                return `Kontrak (${parts.join(' | ')})`;
            }
            return `Kontrak (ID: ${id})`;
            
        case 'Keluarga':
            const keluargaParts = [];
            if (data.nama) {
                keluargaParts.push(data.nama);
            }
            if (audit.penghuni_name) {
                keluargaParts.push(`Penghuni: ${audit.penghuni_name}`);
            }
            if (keluargaParts.length > 0) {
                return `Keluarga: ${keluargaParts.join(' | ')}`;
            }
            return `Keluarga (ID: ${id})`;
            
        case 'Unit':
            if (data.kode_unit) {
                return `Unit: ${data.kode_unit}`;
            }
            return `Unit (ID: ${id})`;
            
        case 'Blacklist':
            if (data.nama) {
                return `Blacklist: ${data.nama}`;
            }
            return `Blacklist (ID: ${id})`;
            
        case 'Disperkim':
            if (data.nama) {
                return `Staff: ${data.nama}`;
            }
            return `Staff (ID: ${id})`;
            
        case 'Tagihan':
            if (audit.penghuni_name) {
                return `Tagihan: Penghuni ${audit.penghuni_name}`;
            }
            return `Tagihan (ID: ${id})`;
            
        case 'Pembayaran':
            if (audit.penghuni_name) {
                return `Pembayaran: Penghuni ${audit.penghuni_name}`;
            }
            if (data.jumlah_bayar) {
                return `Pembayaran: Rp ${formatRupiah(data.jumlah_bayar)}`;
            }
            return `Pembayaran (ID: ${id})`;
            
        default:
            return `${modelName} (ID: ${id})`;
    }
}

function formatRupiah(angka) {
    if (!angka) return '0';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}


document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('auditDetailModal');
    const modalInfo = document.getElementById('auditDetailInfo');
    const modalContent = document.getElementById('auditDetailContent');

    if (!modal || !modalInfo || !modalContent) {
        return;
    }

    const toggleButtons = document.querySelectorAll('.js-audit-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const childRows = document.querySelectorAll(`.audit-child-row[data-parent="${targetId}"]`);
            const icon = this.querySelector('svg');

            if (this.classList.contains('open')) {
                this.classList.remove('open');
                icon.style.transform = 'rotate(0deg)';
                childRows.forEach(row => row.classList.add('hidden'));
            } else {
                this.classList.add('open');
                icon.style.transform = 'rotate(90deg)';
                childRows.forEach(row => row.classList.remove('hidden'));
            }
        });
    });

    function setupDetailButtons() {
        const detailButtons = document.querySelectorAll('.js-audit-detail-btn');
        
        detailButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const auditData = JSON.parse(this.dataset.audit);
                buildAuditContent(auditData, modalInfo, modalContent);
                if (typeof openModal === 'function') {
                    openModal('auditDetailModal');
                }
            });
        });
    }

    setupDetailButtons();
    
    const tableBody = document.querySelector('.divide-y.divide-gray-200.bg-white');
    if (tableBody) {
        const observer = new MutationObserver(function(mutations) {
            for (const mutation of mutations) {
                if (mutation.type === 'childList') {
                    setupDetailButtons();
                    break;
                }
            }
        });
        observer.observe(tableBody, { childList: true });
    }

});

function buildAuditContent(audit, infoElement, contentElement) {
    const modelName = audit.auditable_type.split('\\').pop();
    
    // Get display name for the auditable record
    const displayName = getAuditableDisplayName(audit);
    
    let infoHtml = `
        <div>
            <strong class="block text-gray-500">PENGGUNA</strong>
            <span class="text-gray-900">${audit.user ? audit.user.name : 'SISTEM'}</span>
        </div>
        <div>
            <strong class="block text-gray-500">WAKTU</strong>
            <span class="text-gray-900">${new Date(audit.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'medium' })}</span>
        </div>
        <div>
            <strong class="block text-gray-500">DATA TERKAIT</strong>
            <span class="text-gray-900">${displayName}</span>
        </div>
        <div>
            <strong class="block text-gray-500">IP ADDRESS</strong>
            <span class="text-gray-900">${audit.ip_address || 'Tidak tercatat'}</span>
        </div>
    `;
    infoElement.innerHTML = infoHtml;

    let contentHtml = '';
    const oldData = audit.old_values;
    const newData = audit.new_values;

    if (audit.event === 'created') {
        contentHtml = '<h4 class="text-lg font-bold text-green-700 mb-3">Data Dibuat</h4>';
        contentHtml += '<table class="diff-table-full">';
        contentHtml += '<thead><tr><th>Kolom</th><th>Nilai</th></tr></thead><tbody>';
        for (const key in newData) {
            const displayName = getDisplayName(modelName, key);
            contentHtml += `
                <tr>
                    <th>${displayName}</th>
                    <td><ins>${newData[key] !== null ? escapeHTML(newData[key]) : 'null'}</ins></td>
                </tr>
            `;
        }
        contentHtml += '</tbody></table>';

    } else if (audit.event === 'deleted') {
        contentHtml = '<h4 class="text-lg font-bold text-red-700 mb-3">Data Dihapus</h4>';
        contentHtml += '<table class="diff-table-full">';
        contentHtml += '<thead><tr><th>Kolom</th><th>Nilai</th></tr></thead><tbody>';
        for (const key in oldData) {
            const displayName = getDisplayName(modelName, key);
            contentHtml += `
                <tr>
                    <th>${displayName}</th>
                    <td><del>${oldData[key] !== null ? escapeHTML(oldData[key]) : 'null'}</del></td>
                </tr>
            `;
        }
        contentHtml += '</tbody></table>';

    } else {
        contentHtml = '<h4 class="text-lg font-bold text-yellow-700 mb-3">Data Diperbarui</h4>';
        contentHtml += '<table class="diff-table">';
        contentHtml += '<thead><tr><th>Kolom</th><th>Data Lama</th><th>Data Baru</th></tr></thead><tbody>';
        
        const allKeys = [...new Set([...Object.keys(oldData), ...Object.keys(newData)])];
        let hasChanges = false;
        for (const key of allKeys) {
            const oldValue = oldData[key] !== null ? oldData[key] : 'null';
            const newValue = newData[key] !== null ? newData[key] : 'null';
            
            if (String(oldValue) !== String(newValue)) {
                hasChanges = true;
                const displayName = getDisplayName(modelName, key);
                contentHtml += `
                    <tr>
                        <th>${displayName}</th>
                        <td><del>${escapeHTML(oldValue)}</del></td>
                        <td><ins>${escapeHTML(newValue)}</ins></td>
                    </tr>
                `;
            }
        }
        if (!hasChanges) {
            contentHtml += '<tr><td colspan="3" class="text-center text-gray-500">Tidak ada perubahan nilai yang tercatat.</td></tr>';
        }
        contentHtml += '</tbody></table>';
    }

    contentElement.innerHTML = contentHtml;
}

function escapeHTML(str) {
    if (typeof str !== 'string') return str;
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

function toggleBatchDetails(batchId) {
    const childRows = document.querySelectorAll(`.audit-child-row[data-parent="${batchId}"]`);
    const toggleBtn = document.querySelector(`[data-target="${batchId}"]`);
    const icon = toggleBtn?.querySelector('svg');
    
    childRows.forEach(row => {
        row.classList.toggle('hidden');
    });
    
    if (icon) {
        if (icon.style.transform === 'rotate(90deg)') {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(90deg)';
        }
    }
}

window.toggleBatchDetails = toggleBatchDetails;