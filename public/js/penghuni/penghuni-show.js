const PenghuniDetail = {
    selectedRusun: null,
    selectedBlok: null,
    tarifAir: 0,

    tarifKeringanan: {
        'A': { 
            1: { dapat: 120000, tidak: 245000 },
            2: { dapat: 120000, tidak: 235000 },
            3: { dapat: 110000, tidak: 225000 },
            4: { dapat: 100000, tidak: 220000 },
            5: { dapat: 90000, tidak: 215000 }
        },
        'B': { 
            1: { dapat: 120000, tidak: 245000 },
            2: { dapat: 120000, tidak: 235000 },
            3: { dapat: 110000, tidak: 225000 },
            4: { dapat: 100000, tidak: 220000 },
            5: { dapat: 90000, tidak: 215000 }
        },
        'C': { 
            1: { dapat: 120000, tidak: 245000 },
            2: { dapat: 120000, tidak: 235000 },
            3: { dapat: 110000, tidak: 225000 },
            4: { dapat: 100000, tidak: 220000 },
            5: { dapat: 90000, tidak: 215000 }
        },
        'D': { 
            1: { normal: 630000 },
            2: { normal: 580000 },
            3: { normal: 530000 }
        }
    },

    async generateUnitOptions() {
        const lantaiSelect = document.getElementById('lantaiUnitSelect');
        if (!lantaiSelect) return;

        const blok = this.selectedBlok;
        if (!blok) return;
        
        lantaiSelect.innerHTML = '<option value="">Memuat unit...</option>';
        lantaiSelect.disabled = true;
        
        try {
            const response = await fetch(`/units/available?blok=${blok}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Gagal memuat data unit');
            }
            
            const data = await response.json();
            
            if (!data.success || !data.units) {
                throw new Error('Format data tidak valid');
            }
            
            lantaiSelect.innerHTML = '<option value="">Pilih Lantai & Unit</option>';
            
            const groupedUnits = {};
            
            data.units.forEach(unit => {
                const kodeUnit = unit.kode_unit;
                let lantai = null;
                
                if (['A', 'B', 'C'].includes(blok)) {
                    lantai = kodeUnit.charAt(1);
                } else if (blok === 'D') {
                    const unitNum = parseInt(kodeUnit.substring(1));
                    if (unitNum >= 101 && unitNum <= 110) lantai = '1';
                    else if (unitNum >= 211 && unitNum <= 226) lantai = '2';
                    else if (unitNum >= 327 && unitNum <= 342) lantai = '3';
                }
                
                if (!lantai) return;
                
                if (!groupedUnits[lantai]) {
                    groupedUnits[lantai] = [];
                }
                
                groupedUnits[lantai].push(unit);
            });
            
            const sortedLantai = Object.keys(groupedUnits).sort();
            
            sortedLantai.forEach(lantai => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = `Lantai ${lantai}`;
                
                groupedUnits[lantai].forEach(unit => {
                    let displayText = unit.kode_unit;
                    
                    if (!unit.is_available) {
                        displayText += ' (Terisi)';
                    }
                    
                    const option = new Option(displayText, unit.kode_unit);
                    
                    if (!unit.is_available) {
                        option.disabled = true;
                        option.style.color = '#9ca3af';
                        option.style.fontStyle = 'italic';
                    }
                    
                    optgroup.appendChild(option);
                });
                
                lantaiSelect.appendChild(optgroup);
            });
            
            lantaiSelect.disabled = false;
            
        } catch (error) {
            lantaiSelect.innerHTML = '<option value="">Gagal memuat unit. Coba lagi.</option>';
            
            if (typeof showToast === 'function') {
                showToast('Gagal memuat data unit: ' + error.message, 'error');
            } else {
                alert('Gagal memuat data unit: ' + error.message);
            }
        }
    },

    updateKeringananOptions() {
        const keringananSelect = document.getElementById('keringananSelect');
        if (!keringananSelect) return;

        const blok = this.selectedBlok;
        
        if (['A', 'B', 'C'].includes(blok)) {
            keringananSelect.innerHTML = '<option value="" disabled selected>Pilih Keringanan</option>';
            keringananSelect.innerHTML += '<option value="dapat">Dapat</option>';
            keringananSelect.innerHTML += '<option value="tidak">Tidak</option>';
            keringananSelect.disabled = false;
            keringananSelect.value = '';
            keringananSelect.style.backgroundColor = '';
            keringananSelect.style.cursor = '';
        } else if (blok === 'D') {
            keringananSelect.innerHTML = '<option value="normal" selected>Normal</option>';
            keringananSelect.disabled = false;
            keringananSelect.value = 'normal';
            keringananSelect.style.backgroundColor = '#f3f4f6';
            keringananSelect.style.cursor = 'default';
            
            keringananSelect.addEventListener('mousedown', function(e) {
                if (this.value === 'normal') {
                    e.preventDefault();
                }
            });
        } else {
            keringananSelect.innerHTML = '<option value="" disabled selected>Pilih Blok terlebih dahulu</option>';
            keringananSelect.disabled = true;
            keringananSelect.style.backgroundColor = '';
            keringananSelect.style.cursor = '';
        }
        
        this.handleKeringananChange();
    },

    getLantaiFromKodeUnit(kodeUnit) {
        if (!kodeUnit) return null;
        const blok = kodeUnit.charAt(0);
        
        if (['A', 'B', 'C'].includes(blok)) return parseInt(kodeUnit.charAt(1), 10);
        if (blok === 'D') {
            const unitNum = parseInt(kodeUnit.substring(1), 10);
            if (unitNum >= 101 && unitNum <= 110) return 1;
            if (unitNum >= 211 && unitNum <= 226) return 2;
            if (unitNum >= 327 && unitNum <= 342) return 3;
        }
        return null;
    },

    handleKeringananChange() {
        const keringananSelect = document.getElementById('keringananSelect');
        const lantaiUnitSelect = document.getElementById('lantaiUnitSelect');
        const nominalInput = document.getElementById('nominalKeringanan');
        const jumlahInput = document.getElementById('jumlahRetribusi');
        const jaminanInput = document.getElementById('nilaiJaminan');
        
        if (!keringananSelect || !nominalInput || !jumlahInput) return;

        const keringanan = keringananSelect.value;
        const kodeUnit = lantaiUnitSelect ? lantaiUnitSelect.value : '';
        
        let nominal = 0;
        let nilaiJaminan = 0;

        if ((keringanan === 'dapat' || keringanan === 'tidak' || keringanan === 'normal') && this.selectedBlok && kodeUnit) {
            const lantai = this.getLantaiFromKodeUnit(kodeUnit);
            if (lantai && this.tarifKeringanan[this.selectedBlok]?.[lantai]) {
                const tarifLantai = this.tarifKeringanan[this.selectedBlok][lantai];
                nominal = tarifLantai[keringanan] || 0;
                
                if (['A', 'B', 'C'].includes(this.selectedBlok)) {
                    nilaiJaminan = (tarifLantai['tidak'] || 0) * 3;
                } else if (this.selectedBlok === 'D') {
                    nilaiJaminan = (tarifLantai['normal'] || 0) * 3;
                }
            }
        }

        nominalInput.value = nominal;
        
        if (jaminanInput) {
            jaminanInput.value = nilaiJaminan > 0 ? new Intl.NumberFormat('id-ID').format(nilaiJaminan) : '';
            jaminanInput.readOnly = true;
            jaminanInput.style.backgroundColor = '#f3f4f6';
            jaminanInput.style.cursor = 'not-allowed';
        }
        
        const jumlah = nominal + this.tarifAir;
        jumlahInput.value = jumlah > 0 ? new Intl.NumberFormat('id-ID').format(jumlah) : '';
    },

    updateTarifAir() {
        const tarifAirInput = document.getElementById('tarifAir');
        if (!tarifAirInput) return;

        if (this.selectedRusun === 'kraton') {
            this.tarifAir = 60000;
        } else if (this.selectedRusun === 'mbr_tegalsari') {
            this.tarifAir = 70000;
        } else {
            this.tarifAir = 0;
        }

        tarifAirInput.value = this.tarifAir > 0 ? new Intl.NumberFormat('id-ID').format(this.tarifAir) : '';
        
        this.handleKeringananChange();
    },

    setupTanggalValidation() {
        const tanggalMasuk = document.getElementById('tanggalMasuk');
        const tanggalKeluar = document.getElementById('tanggalKeluar');
        const tanggalSip = document.getElementById('tanggal_sip');
        const tanggalSps = document.getElementById('tanggal_sps');
        
        if (!tanggalMasuk || !tanggalKeluar) return;
        tanggalMasuk.addEventListener('change', function() {
            if (this.value) {
                tanggalKeluar.min = this.value;
                
                if (tanggalSip) {
                    tanggalSip.value = this.value;
                    tanggalSip.readOnly = true;
                    tanggalSip.classList.add('bg-gray-100');
                    tanggalSip.style.cursor = 'not-allowed';
                    tanggalSip.setAttribute('title', 'Tanggal SIP otomatis mengikuti tanggal masuk');
                }
                
                if (tanggalSps) {
                    tanggalSps.value = this.value;
                    tanggalSps.readOnly = true;
                    tanggalSps.classList.add('bg-gray-100');
                    tanggalSps.style.cursor = 'not-allowed';
                    tanggalSps.setAttribute('title', 'Tanggal SPS otomatis mengikuti tanggal masuk');
                }
                
                if (tanggalKeluar.value && tanggalKeluar.value < this.value) {
                    tanggalKeluar.value = this.value;
                }
            }
        });
        
        if (tanggalSip) {
            tanggalSip.addEventListener('mousedown', (e) => {
                e.preventDefault();
                if (typeof showToast === 'function') {
                    showToast('Tanggal SIP otomatis mengikuti tanggal masuk sewa', 'info');
                }
            });
            
            tanggalSip.addEventListener('keydown', (e) => {
                e.preventDefault();
            });
        }
        
        if (tanggalSps) {
            tanggalSps.addEventListener('mousedown', (e) => {
                e.preventDefault();
                if (typeof showToast === 'function') {
                    showToast('Tanggal SPS otomatis mengikuti tanggal masuk sewa', 'info');
                }
            });
            
            tanggalSps.addEventListener('keydown', (e) => {
                e.preventDefault();
            });
        }
    },
    
    selectRusun(rusun) {
        this.selectedRusun = rusun;

        document.querySelectorAll('.rusun-box').forEach(b => b.classList.remove('border-primary', 'bg-blue-50'));
        const rusunId = `rusun${rusun === 'kraton' ? 'Kraton' : rusun === 'mbr_tegalsari' ? 'MBR' : 'Prototipe'}`;
        const rusunElement = document.getElementById(rusunId);
        if (rusunElement) {
            rusunElement.classList.add('border-primary', 'bg-blue-50');
        }

        this.updateTarifAir();

        this.selectedBlok = null;
        document.querySelectorAll('.blok-box').forEach(btn => {
            btn.disabled = true;
            btn.classList.remove('border-primary', 'bg-blue-50');
        });

        if (rusun === 'kraton') {
            ['A', 'B', 'C'].forEach(b => { 
                const blokBtn = document.getElementById(`blok${b}`);
                if (blokBtn) blokBtn.disabled = false;
            });
        }
        if (rusun === 'mbr_tegalsari') {
            const blokD = document.getElementById('blokD');
            if (blokD) blokD.disabled = false;
            this.selectBlok('D');
        }
        if (rusun === 'prototipe_tegalsari') {
            const blokP = document.getElementById('blokP');
            if (blokP) blokP.disabled = false;
        }
        
        if (rusun !== 'mbr_tegalsari') {
            const lantaiSelect = document.getElementById('lantaiUnitSelect');
            const keringananSelect = document.getElementById('keringananSelect');
            const jaminanInput = document.getElementById('nilaiJaminan');
            
            if (lantaiSelect) {
                lantaiSelect.disabled = true;
                lantaiSelect.innerHTML = '<option value="">Pilih Blok</option>';
            }
            
            if (keringananSelect) {
                keringananSelect.disabled = true;
                keringananSelect.innerHTML = '<option value="">Pilih Blok terlebih dahulu</option>';
            }
            
            if (jaminanInput) {
                jaminanInput.value = '';
            }
        }
    },

    selectBlok(blok) {
        this.selectedBlok = blok;

        document.querySelectorAll('.blok-box').forEach(b => b.classList.remove('border-primary', 'bg-blue-50'));
        const blokElement = document.getElementById(`blok${blok}`);
        if (blokElement) {
            blokElement.classList.add('border-primary', 'bg-blue-50');
        }

        this.generateUnitOptions();
        this.updateKeringananOptions();
    },

    openAddKontrakModal() {
        const addKontrakForm = document.getElementById('addKontrakForm');
        if (addKontrakForm) addKontrakForm.reset();
        
        this.selectedRusun = null;
        this.selectedBlok = null;
        this.tarifAir = 0;
        
        document.querySelectorAll('.rusun-box, .blok-box').forEach(b => b.classList.remove('border-primary', 'bg-blue-50'));
        document.querySelectorAll('.blok-box').forEach(b => b.disabled = true);
        
        const lantaiSelect = document.getElementById('lantaiUnitSelect');
        if (lantaiSelect) {
            lantaiSelect.disabled = true;
            lantaiSelect.innerHTML = '<option value="">Pilih Rusun & Blok</option>';
        }
        
        const keringananSelect = document.getElementById('keringananSelect');
        if (keringananSelect) {
            keringananSelect.disabled = true;
            keringananSelect.innerHTML = '<option value="">Pilih Rusun & Blok terlebih dahulu</option>';
        }
        
        const tarifAirInput = document.getElementById('tarifAir');
        if (tarifAirInput) tarifAirInput.value = '';
        
        const jumlahInput = document.getElementById('jumlahRetribusi');
        if (jumlahInput) jumlahInput.value = '';
        
        const nominalInput = document.getElementById('nominalKeringanan');
        if (nominalInput) nominalInput.value = '';
        
        const jaminanInput = document.getElementById('nilaiJaminan');
        if (jaminanInput) jaminanInput.value = '';
        
        if (typeof openModal === 'function') {
            openModal('addKontrakModal');
        }
    },

    openTerminateModal(kontrakId, unitName, tanggalMasuk, tanggalKeluar) {
        const form = document.getElementById('terminateForm');
        const tanggalKeluarInput = document.getElementById('tanggalKeluarTerminate');

        if (!form || !tanggalKeluarInput) return;

        form.action = `/kontrak/terminate/${kontrakId}`;
        
        const unitNameElement = document.getElementById('unitName');
        if (unitNameElement) unitNameElement.textContent = unitName;
        
        form.reset();

        tanggalKeluarInput.value = new Date().toISOString().split('T')[0];
        tanggalKeluarInput.min = tanggalMasuk;
        tanggalKeluarInput.max = tanggalKeluar;

        if (typeof openModal === 'function') {
            openModal('terminateModal');
        }
    },

    openAddKeluargaModal() {
        const form = document.getElementById('addKeluargaForm');
        if (form) form.reset();
        
        const container = document.getElementById('catatanContainer');
        const catatanField = document.getElementById('catatan');
        
        if (container) {
            container.classList.remove('show', 'hide');
            container.style.maxHeight = '0';
            container.style.opacity = '0';
            container.style.marginTop = '0';
            container.style.overflow = 'hidden';
        }
        
        if (catatanField) {
            catatanField.required = false;
            catatanField.value = '';
        }
        
        const jenisKelaminSelect = document.getElementById('jenisKelamin');
        if (jenisKelaminSelect) {
            jenisKelaminSelect.innerHTML = '<option value="" disabled selected>Pilih Jenis Kelamin</option>';
            jenisKelaminSelect.innerHTML += '<option value="laki-laki">Laki-laki</option>';
            jenisKelaminSelect.innerHTML += '<option value="perempuan">Perempuan</option>';
        }
        
        if (typeof openModal === 'function') {
            openModal('addKeluargaModal');
        }
    },
    
    openEditKeluargaModal(id, nama, nik, umur, jenisKelamin, hubungan, catatan) {
        const form = document.getElementById('editKeluargaForm');
        if (!form) return;
        
        form.action = `/keluarga/${id}`;
        
        const namaInput = document.getElementById('editNama');
        if (namaInput) namaInput.value = nama;
        
        const nikInput = document.getElementById('editNik');
        if (nikInput) nikInput.value = nik || '';
        
        const umurInput = document.getElementById('editUmur');
        if (umurInput) umurInput.value = umur || '';
        
        const jenisKelaminSelect = document.getElementById('editJenisKelamin');
        const hubunganSelect = document.getElementById('editHubungan');
        
        if (hubunganSelect) {
            Array.from(hubunganSelect.options).forEach(opt => {
                if (opt.value === '') {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
            hubunganSelect.value = hubungan;
        }
        
        this.updateJenisKelaminOptions(hubungan, 'editJenisKelamin');
        
        if (jenisKelaminSelect) {
            jenisKelaminSelect.value = jenisKelamin;
        }
        
        const editCatatanField = document.getElementById('editCatatan');
        if (editCatatanField) {
            editCatatanField.value = catatan || '';
        }
        
        const container = document.getElementById('editCatatanContainer');
        if (container) {
            container.classList.remove('show', 'hide');
            
            if (hubungan === 'lainnya') {
                container.style.maxHeight = '500px';
                container.style.opacity = '1';
                container.style.marginTop = '1rem';
                container.style.overflow = 'visible';
                if (editCatatanField) editCatatanField.required = true;
            } else {
                container.style.maxHeight = '0';
                container.style.opacity = '0';
                container.style.marginTop = '0';
                container.style.overflow = 'hidden';
                if (editCatatanField) editCatatanField.required = false;
            }
        }
        
        if (typeof openModal === 'function') {
            openModal('editKeluargaModal');
        }
    },

    openDeleteKeluargaModal(id, nama) {
        const form = document.getElementById('deleteKeluargaForm');
        if (form) form.action = `/keluarga/${id}`;
        
        const namaElement = document.getElementById('deleteNamaKeluarga');
        if (namaElement) namaElement.textContent = nama;
        
        if (typeof openModal === 'function') {
            openModal('deleteKeluargaModal');
        }
    },

    openBlacklistModal(id, nama, type) {
        const form = document.getElementById('blacklistForm');
        if (!form) return;
        
        if (type === 'penghuni') {
            form.action = `/penghuni/${id}/blacklist`;
        } else if (type === 'keluarga') {
            form.action = `/keluarga/${id}/blacklist`;
        } else {
            return;
        }
        
        const namaEl = document.getElementById('blacklistNama');
        if (namaEl) namaEl.textContent = nama;
        
        const alasanEl = document.getElementById('alasan_blacklist');
        if (alasanEl) alasanEl.value = '';
        
        if (typeof openModal === 'function') {
            openModal('blacklistModal');
        }
    },

    openReactivateModal(id, nama, nik) {
        const form = document.getElementById('reactivateForm');
        if (!form) {
            return;
        }
 
        form.action = `/blacklist/${id}`; 
        
        const namaEl = document.getElementById('reactivateNama');
        if (namaEl) namaEl.textContent = nama;
        
        const nikEl = document.getElementById('reactivateNik');
        if (nikEl) nikEl.textContent = nik;
        
        const alasanEl = document.getElementById('alasan_aktivasi');
        if (alasanEl) alasanEl.value = '';
        if (window.openModal && typeof window.openModal === 'function') {
            window.openModal('reactivateModal');
        } else {
            const modal = document.getElementById('reactivateModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }
    },

    toggleCatatanField(select, containerId, fieldId) {
        const container = document.getElementById(containerId);
        const field = document.getElementById(fieldId);
        
        if (!container || !field) return;
        
        if (select.value === 'lainnya') {
            container.classList.remove('hide');
            container.classList.add('show');
            field.required = true;
        } else {
            if (container.classList.contains('show')) {
                container.classList.remove('show');
                container.classList.add('hide');
                
                setTimeout(() => {
                    container.classList.remove('hide');
                    field.value = '';
                    field.required = false;
                }, 300);
            } else {
                field.value = '';
                field.required = false;
            }
        }
    },

    updateJenisKelaminOptions(hubungan, jenisKelaminId) {
        const jenisKelaminSelect = document.getElementById(jenisKelaminId);
        if (!jenisKelaminSelect) return;
        
        const currentValue = jenisKelaminSelect.value;
        
        jenisKelaminSelect.innerHTML = '<option value="" disabled>Pilih Jenis Kelamin</option>';
        
        if (hubungan === 'istri') {
            jenisKelaminSelect.innerHTML += '<option value="perempuan">Perempuan</option>';
            jenisKelaminSelect.value = 'perempuan';
        } else if (hubungan === 'suami') {
            jenisKelaminSelect.innerHTML += '<option value="laki-laki">Laki-laki</option>';
            jenisKelaminSelect.value = 'laki-laki';
        } else {
            jenisKelaminSelect.innerHTML += '<option value="laki-laki">Laki-laki</option>';
            jenisKelaminSelect.innerHTML += '<option value="perempuan">Perempuan</option>';
            
            if (currentValue === 'laki-laki' || currentValue === 'perempuan') {
                jenisKelaminSelect.value = currentValue;
            }
        }
    }
};

window.PenghuniDetail = PenghuniDetail;

document.addEventListener('DOMContentLoaded', function() {
    const setupAutoSyncTanggal = () => {
        const tanggalMasukModal = document.getElementById('tanggalMasuk');
        const tanggalSipModal = document.getElementById('tanggal_sip');
        const tanggalSpsModal = document.getElementById('tanggal_sps');
        
        if (tanggalMasukModal && tanggalSipModal && tanggalSpsModal) {
            const resetTanggalFields = () => {
                tanggalSipModal.value = '';
                tanggalSpsModal.value = '';
                tanggalSipModal.readOnly = true;
                tanggalSpsModal.readOnly = true;
                tanggalSipModal.classList.add('bg-gray-100');
                tanggalSpsModal.classList.add('bg-gray-100');
            };
            
            tanggalMasukModal.addEventListener('change', function() {
                if (this.value) {
                    tanggalSipModal.value = this.value;
                    tanggalSpsModal.value = this.value;
                    tanggalSipModal.readOnly = true;
                    tanggalSpsModal.readOnly = true;
                    tanggalSipModal.classList.add('bg-gray-100', 'cursor-not-allowed');
                    tanggalSpsModal.classList.add('bg-gray-100', 'cursor-not-allowed');
                }
            });
            
            tanggalSipModal.addEventListener('mousedown', (e) => {
                e.preventDefault();
                showToast('Tanggal SIP otomatis mengikuti tanggal masuk sewa', 'info');
            });
            
            tanggalSipModal.addEventListener('keydown', (e) => {
                e.preventDefault();
            });
            
            tanggalSpsModal.addEventListener('mousedown', (e) => {
                e.preventDefault();
                showToast('Tanggal SPS otomatis mengikuti tanggal masuk sewa', 'info');
            });
            
            tanggalSpsModal.addEventListener('keydown', (e) => {
                e.preventDefault();
            });
            
            const originalOpenAddKontrakModal = PenghuniDetail.openAddKontrakModal;
            PenghuniDetail.openAddKontrakModal = function() {
                resetTanggalFields();
                originalOpenAddKontrakModal.call(this);
            };
        }
    };

    setupAutoSyncTanggal();
    PenghuniDetail.setupTanggalValidation();
        
    const keringananSelect = document.getElementById('keringananSelect');
    if (keringananSelect) {
        keringananSelect.addEventListener('change', () => PenghuniDetail.handleKeringananChange());
    }

    const lantaiUnitSelect = document.getElementById('lantaiUnitSelect');
    if (lantaiUnitSelect) {
        lantaiUnitSelect.addEventListener('change', (e) => {
            const unitKodeInput = document.getElementById('unitKodeInput');
            if (unitKodeInput) {
                unitKodeInput.value = e.target.value;
            }
            PenghuniDetail.handleKeringananChange();
        });
    }

    const nilaiJaminan = document.getElementById('nilaiJaminan');
    if (nilaiJaminan) {
        nilaiJaminan.readOnly = true;
        nilaiJaminan.style.backgroundColor = '#f3f4f6';
        nilaiJaminan.style.cursor = 'not-allowed';
        nilaiJaminan.title = 'Nilai jaminan otomatis dihitung 3x dari tarif sewa normal';
    }

    const addKontrakForm = document.getElementById('addKontrakForm');
    if (addKontrakForm) {
        addKontrakForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!PenghuniDetail.selectedRusun) {
                if (typeof customAlert === 'function') {
                    await customAlert('Silakan pilih Rusun terlebih dahulu!', 'warning');
                } else {
                    alert('Silakan pilih Rusun terlebih dahulu!');
                }
                return false;
            }
            
            if (!PenghuniDetail.selectedBlok) {
                if (typeof customAlert === 'function') {
                    await customAlert('Silakan pilih Blok terlebih dahulu!', 'warning');
                } else {
                    alert('Silakan pilih Blok terlebih dahulu!');
                }
                return false;
            }
            
            const unitKodeInput = document.getElementById('unitKodeInput');
            const lantaiUnitSelect = document.getElementById('lantaiUnitSelect');
            
            if (!lantaiUnitSelect || !lantaiUnitSelect.value) {
                if (typeof customAlert === 'function') {
                    await customAlert('Silakan pilih Lantai & Unit terlebih dahulu!', 'warning');
                } else {
                    alert('Silakan pilih Lantai & Unit terlebih dahulu!');
                }
                return false;
            }
            
            if (unitKodeInput) {
                unitKodeInput.value = lantaiUnitSelect.value;
            } else {
                alert('Error: Input unit tidak ditemukan!');
                return false;
            }
            
            const keringananSelect = document.getElementById('keringananSelect');
            if (!keringananSelect || !keringananSelect.value) {
                if (typeof customAlert === 'function') {
                    await customAlert('Silakan pilih status keringanan!', 'warning');
                } else {
                    alert('Silakan pilih status keringanan!');
                }
                return false;
            }
            
            const tarifAirInput = document.getElementById('tarifAir');
            if (tarifAirInput && tarifAirInput.value) {
                const tarifAirValue = tarifAirInput.value.replace(/\./g, '').replace(/[^\d]/g, '');
                if (!tarifAirValue || tarifAirValue === '0') {
                    if (typeof customAlert === 'function') {
                        await customAlert('Tarif air tidak valid!', 'error');
                    } else {
                        alert('Tarif air tidak valid!');
                    }
                    return false;
                }
                tarifAirInput.value = tarifAirValue;
            }
            
            const nominalKeringananInput = document.getElementById('nominalKeringanan');
            if (nominalKeringananInput && nominalKeringananInput.value) {
                const nominalValue = nominalKeringananInput.value.replace(/\./g, '').replace(/[^\d]/g, '');
                nominalKeringananInput.value = nominalValue;
            }
            
            const jaminanInput = document.getElementById('nilaiJaminan');
            if (jaminanInput && jaminanInput.value) {
                const jaminanValue = jaminanInput.value.replace(/\./g, '').replace(/[^\d]/g, '');
                if (!jaminanValue || jaminanValue === '0') {
                    if (typeof customAlert === 'function') {
                        await customAlert('Nilai jaminan harus ada!', 'error');
                    } else {
                        alert('Nilai jaminan harus ada!');
                    }
                    return false;
                }
                jaminanInput.value = jaminanValue;
            } else {
                if (typeof customAlert === 'function') {
                    await customAlert('Nilai jaminan harus diisi!', 'error');
                } else {
                    alert('Nilai jaminan harus diisi!');
                }
                return false;
            }
            
            const tanggalMasuk = document.getElementById('tanggalMasuk');
            const tanggalKeluar = document.getElementById('tanggalKeluar');
            
            if (!tanggalMasuk || !tanggalMasuk.value) {
                if (typeof customAlert === 'function') {
                    await customAlert('Tanggal masuk harus diisi!', 'warning');
                } else {
                    alert('Tanggal masuk harus diisi!');
                }
                return false;
            }
            
            if (!tanggalKeluar || !tanggalKeluar.value) {
                if (typeof customAlert === 'function') {
                    await customAlert('Tanggal keluar harus diisi!', 'warning');
                } else {
                    alert('Tanggal keluar harus diisi!');
                }
                return false;
            }
            
            if (new Date(tanggalKeluar.value) <= new Date(tanggalMasuk.value)) {
                if (typeof customAlert === 'function') {
                    await customAlert('Tanggal keluar harus setelah tanggal masuk!', 'warning');
                } else {
                    alert('Tanggal keluar harus setelah tanggal masuk!');
                }
                return false;
            }
            
            if (typeof showLoading === 'function') {
                showLoading('Menyimpan kontrak...');
            }
            
            setTimeout(() => {
                e.target.submit();
            }, 2000); 
        });
    }
    
    const terminateForm = document.getElementById('terminateForm');
    if (terminateForm) {
        terminateForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const tunggakanInput = document.getElementById('tunggakan');
            if (tunggakanInput && tunggakanInput.value) {
                tunggakanInput.value = tunggakanInput.value.replace(/\D/g, '');
            }

            if (typeof showLoading === 'function') {
                showLoading('Mengakhiri kontrak...');
            }
            
            setTimeout(() => {
                event.target.submit();
            }, 2000);
        });
    }

    const addKeluargaForm = document.getElementById('addKeluargaForm');
    if (addKeluargaForm) {
        addKeluargaForm.addEventListener('submit', (event) => { 
            event.preventDefault();

            const hubungan = document.getElementById('hubungan')?.value;
            const jenisKelamin = document.getElementById('jenisKelamin')?.value;
            
            if (hubungan === 'istri' && jenisKelamin !== 'perempuan') {
                if (typeof showToast === 'function') {
                    showToast('Jenis kelamin untuk Istri harus Perempuan', 'error');
                }
                return;
            }
            
            if (hubungan === 'suami' && jenisKelamin !== 'laki-laki') {
                if (typeof showToast === 'function') {
                    showToast('Jenis kelamin untuk Suami harus Laki-laki', 'error');
                }
                return;
            }
            
            if (typeof showLoading === 'function') {
                showLoading('Menambahkan anggota...');
            }

            setTimeout(() => {
                event.target.submit();
            }, 2000);
        });
    }

    const editKeluargaForm = document.getElementById('editKeluargaForm');
    if (editKeluargaForm) {
        editKeluargaForm.addEventListener('submit', (event) => {
            event.preventDefault();
            
            const hubungan = document.getElementById('editHubungan')?.value;
            const jenisKelamin = document.getElementById('editJenisKelamin')?.value;
            
            if (hubungan === 'istri' && jenisKelamin !== 'perempuan') {
                if (typeof showToast === 'function') {
                    showToast('Jenis kelamin untuk Istri harus Perempuan', 'error');
                }
                return;
            }
            
            if (hubungan === 'suami' && jenisKelamin !== 'laki-laki') {
                if (typeof showToast === 'function') {
                    showToast('Jenis kelamin untuk Suami harus Laki-laki', 'error');
                }
                return;
            }
            
            if (typeof showLoading === 'function') {
                showLoading('Memperbarui data...');
            }
            
            setTimeout(() => {
                event.target.submit();
            }, 2000);
        });
    }

    const deleteKeluargaForm = document.getElementById('deleteKeluargaForm');
    if (deleteKeluargaForm) {
        deleteKeluargaForm.addEventListener('submit', (event) => {
            event.preventDefault();

            if (typeof showLoading === 'function') {
                showLoading('Menghapus data...');
            }

            setTimeout(() => {
                event.target.submit();
            }, 2000);
        });
    }
    
    const blacklistForm = document.getElementById('blacklistForm');
    if (blacklistForm) {
        blacklistForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (typeof showLoading === 'function') {
                showLoading('Memproses blacklist...');
            }

            setTimeout(() => {
                event.target.submit();
            }, 2000);
        });
    }

    const reactivateForm = document.getElementById('reactivateForm');
    if (reactivateForm) {
        reactivateForm.addEventListener('submit', function(event) {
            event.preventDefault(); 
            
            if (window.showLoading && typeof window.showLoading === 'function') {
                window.showLoading('Mengaktifkan data penghuni kembali...');
            }

            const formElement = this;

            setTimeout(function() {
                formElement.submit(); 
            }, 2000);
        });
    }
    
    const hubunganSelect = document.getElementById('hubungan');
    if (hubunganSelect) {
        hubunganSelect.addEventListener('change', function() {
            PenghuniDetail.toggleCatatanField(this, 'catatanContainer', 'catatan');
            PenghuniDetail.updateJenisKelaminOptions(this.value, 'jenisKelamin');
        });
    }

    const editHubunganSelect = document.getElementById('editHubungan');
    if (editHubunganSelect) {
        editHubunganSelect.addEventListener('change', function() {
            PenghuniDetail.toggleCatatanField(this, 'editCatatanContainer', 'editCatatan');
            PenghuniDetail.updateJenisKelaminOptions(this.value, 'editJenisKelamin');
        });
    }

    const tunggakanInput = document.getElementById('tunggakan');
    if (tunggakanInput) {
        tunggakanInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
    }
    
    function setupUppercaseForKeluargaForm() {
        const namaKeluargaInput = document.getElementById('nama');
        if (namaKeluargaInput) {
            namaKeluargaInput.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
            
            namaKeluargaInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }, 10);
            });
        }

        const editNamaInput = document.getElementById('editNama');
        if (editNamaInput) {
            editNamaInput.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
            
            editNamaInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }, 10);
            });
        }

        const catatanInput = document.getElementById('catatan');
        if (catatanInput) {
            catatanInput.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
            
            catatanInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }, 10);
            });
        }

        const editCatatanInput = document.getElementById('editCatatan');
        if (editCatatanInput) {
            editCatatanInput.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
            
            editCatatanInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const cursorPosition = e.target.selectionStart;
                    e.target.value = e.target.value.toUpperCase();
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }, 10);
            });
        }
    }

    setupUppercaseForKeluargaForm();

    const originalOpenAddKeluargaModal = PenghuniDetail.openAddKeluargaModal;
    PenghuniDetail.openAddKeluargaModal = function() {
        originalOpenAddKeluargaModal.call(this);
        
        setTimeout(() => {
            setupUppercaseForKeluargaForm();
        }, 100);
    };

    const originalOpenEditKeluargaModal = PenghuniDetail.openEditKeluargaModal;
    PenghuniDetail.openEditKeluargaModal = function(id, nama, nik, umur, jenisKelamin, hubungan, catatan) {
        originalOpenEditKeluargaModal.call(this, id, nama, nik, umur, jenisKelamin, hubungan, catatan);
        
        setTimeout(() => {
            setupUppercaseForKeluargaForm();
            
            const editNamaInput = document.getElementById('editNama');
            if (editNamaInput && editNamaInput.value) {
                editNamaInput.value = editNamaInput.value.toUpperCase();
            }
            
            const editCatatanInput = document.getElementById('editCatatan');
            if (editCatatanInput && editCatatanInput.value) {
                editCatatanInput.value = editCatatanInput.value.toUpperCase();
            }
        }, 100);
    };
});