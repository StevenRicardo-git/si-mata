const PenghuniCreate = {
    selectedRusun: null,
    selectedBlok: null,
    tarifAir: 0,
    preSelectedUnit: null,
    isAutoFilled: false,
    penghuniData: null,
    checkTimeout: null,

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

    initializePreSelectedUnit() {
        const urlParams = new URLSearchParams(window.location.search);
        const unitParam = urlParams.get('unit');
        
        if (!unitParam || unitParam.length < 2) return;
        
        this.preSelectedUnit = unitParam;
        const blok = unitParam.charAt(0).toUpperCase();
        
        let rusun = null;
        if (['A', 'B', 'C'].includes(blok)) {
            rusun = 'kraton';
        } else if (blok === 'D') {
            rusun = 'mbr_tegalsari';
        } else if (blok === 'P') {
            rusun = 'prototipe_tegalsari';
        }
        
        if (rusun) {
            this.selectRusun(rusun);
            setTimeout(() => {
                this.selectBlok(blok);
                setTimeout(() => {
                    const lantaiUnitSelect = document.getElementById('lantaiUnitSelect');
                    if (lantaiUnitSelect) {
                        lantaiUnitSelect.value = unitParam;
                        lantaiUnitSelect.dispatchEvent(new Event('change'));
                        const unitKodeInput = document.getElementById('unitKodeInput');
                        if (unitKodeInput) unitKodeInput.value = unitParam;
                    }
                }, 500);
            }, 300);
        }
    },

    setupNikAutoFill() {
        const nikInput = document.getElementById('nik');
        const nikStatus = document.getElementById('nikStatus');
        
        if (!nikInput || !nikStatus) return;

        nikInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
            if (e.target.value.length > 16) e.target.value = e.target.value.slice(0, 16);

            if (this.checkTimeout) clearTimeout(this.checkTimeout);

            const nikValue = e.target.value;

            if (nikValue.length < 16) {
                this.clearAutoFill();
                nikStatus.classList.add('hidden');
                nikStatus.innerHTML = '';
                return;
            }

            if (nikValue.length === 16) {
                nikStatus.innerHTML = `
                    <div class="flex items-center gap-2 text-blue-600 text-sm mt-1">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memeriksa data...</span>
                    </div>
                `;
                nikStatus.classList.remove('hidden');

                this.checkTimeout = setTimeout(() => {
                    this.checkNikAndAutoFill(nikValue, nikStatus);
                }, 800);
            }
        });
    },

    async checkNikAndAutoFill(nik, nikStatus) {
        try {
            const response = await fetch(`/penghuni/check-nik?nik=${nik}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Gagal memeriksa NIK');

            const data = await response.json();

            if (data.is_blacklisted) {
                this.clearAutoFill();
                nikStatus.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mt-2">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-bold text-red-800 text-sm">⚠️ NIK SUDAH DI-BLACKLIST!</p>
                                <div class="mt-1 text-xs text-red-700">
                                    <p><strong>Nama:</strong> ${data.blacklist_info.nama}</p>
                                    <p><strong>Alasan:</strong> ${data.blacklist_info.alasan}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                nikStatus.classList.remove('hidden');
                this.disableForm();
                return;
            }

            if (data.has_active_contract) {
                this.clearAutoFill();
                nikStatus.innerHTML = `
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mt-2">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-bold text-orange-800 text-sm">ℹ️ NIK Memiliki Kontrak Aktif</p>
                                <div class="mt-1 text-xs text-orange-700">
                                    <p>Unit: ${data.contract_info.unit_kode}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                nikStatus.classList.remove('hidden');
                this.disableForm();
                return;
            }

            if (data.exists && data.penghuni_info) {
                this.penghuniData = data.penghuni_info;
                this.isAutoFilled = true;
                
                this.fillPenghuniData(data.penghuni_info);
                
                nikStatus.innerHTML = `
                    <div class="text-green-600 text-sm mt-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Data penghuni ditemukan dan terisi otomatis.</span>
                    </div>
                `;
                nikStatus.classList.remove('hidden');
                this.enableForm();
            } 
            else {
                this.clearAutoFill();
                nikStatus.innerHTML = ''; 
                nikStatus.classList.add('hidden');
                this.enableForm();
            }

        } catch (error) {
            this.clearAutoFill();
            nikStatus.innerHTML = '';
            nikStatus.classList.add('hidden');
            this.enableForm();
        }
    },

    fillPenghuniData(data) {
        const fillAndLock = (id, value) => {
            const el = document.getElementById(id);
            if (el) {
                el.value = value || '';
                el.readOnly = true;
                el.classList.add('bg-gray-100');
                el.classList.remove('bg-white');
                if (el.tagName === 'SELECT') {
                    const options = Array.from(el.options);
                    const matchingOption = options.find(opt => opt.value.toLowerCase() === (value || '').toLowerCase());
                    if (matchingOption) el.value = matchingOption.value;
                    el.style.pointerEvents = 'none';
                    el.setAttribute('tabindex', '-1');
                }
            }
        };

        fillAndLock('nama', data.nama);
        fillAndLock('tempat_lahir', data.tempat_lahir);
        fillAndLock('tanggal_lahir', data.tanggal_lahir);
        fillAndLock('jenis_kelamin', data.jenis_kelamin);
        fillAndLock('pekerjaan', data.pekerjaan);
        fillAndLock('no_hp', data.no_hp);
        fillAndLock('alamat_ktp', data.alamat_ktp);

        if (data.keluarga && Array.isArray(data.keluarga) && data.keluarga.length > 0) {
            const pasangan = data.keluarga.find(k => k.hubungan === 'suami' || k.hubungan === 'istri');
            const anakList = data.keluarga.filter(k => k.hubungan === 'anak');

            if (pasangan) {
                const adaPasanganCheck = document.getElementById('adaPasangan');
                if (adaPasanganCheck && !adaPasanganCheck.checked) {
                    adaPasanganCheck.checked = true;
                    adaPasanganCheck.dispatchEvent(new Event('change', { bubbles: true }));
                }

                setTimeout(() => {
                    fillAndLock('pasangan_nama', pasangan.nama);
                    fillAndLock('pasangan_nik', pasangan.nik);
                    fillAndLock('pasangan_umur', pasangan.umur);
                    fillAndLock('pasangan_jenis_kelamin', pasangan.jenis_kelamin);
                }, 100);
            }

            if (anakList.length > 0) {
                const jumlahAnakInput = document.getElementById('jumlah_anak');
                if (jumlahAnakInput) {
                    jumlahAnakInput.value = anakList.length;
                    jumlahAnakInput.dispatchEvent(new Event('change', { bubbles: true }));
                    jumlahAnakInput.readOnly = true;
                    jumlahAnakInput.classList.add('bg-gray-100');
                }

                setTimeout(() => {
                    const container = document.getElementById('anakFormsContainer');
                    const forms = container.querySelectorAll('.anak-form-card');
                    
                    anakList.forEach((anak, index) => {
                        if (forms[index]) {
                            const inputs = forms[index].querySelectorAll('input, select');
                            const namaInput = forms[index].querySelector('input[name="anak_nama[]"]');
                            const jkInput = forms[index].querySelector('select[name="anak_jenis_kelamin[]"]');
                            const umurInput = forms[index].querySelector('input[name="anak_umur[]"]');

                            if(namaInput) {
                                namaInput.value = anak.nama;
                                namaInput.readOnly = true;
                                namaInput.classList.add('bg-gray-100');
                            }
                            if(jkInput) {
                                jkInput.value = anak.jenis_kelamin;
                                jkInput.style.pointerEvents = 'none';
                                jkInput.classList.add('bg-gray-100');
                            }
                            if(umurInput) {
                                umurInput.value = anak.umur;
                                umurInput.readOnly = true;
                                umurInput.classList.add('bg-gray-100');
                            }
                        }
                    });
                }, 300);
            }
        }
    },

    clearAutoFill() {
        this.isAutoFilled = false;
        this.penghuniData = null;

        const unlock = (id) => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.readOnly = false;
                el.disabled = false;
                el.classList.remove('bg-gray-100');
                el.classList.add('bg-white');
                if (el.tagName === 'SELECT') {
                    el.style.pointerEvents = 'auto';
                    el.removeAttribute('tabindex');
                    el.selectedIndex = 0;
                }
            }
        };

        ['nama', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'pekerjaan', 'no_hp', 'alamat_ktp']
            .forEach(id => unlock(id));

        const adaPasanganCheck = document.getElementById('adaPasangan');
        if (adaPasanganCheck && adaPasanganCheck.checked) {
            adaPasanganCheck.checked = false;
            adaPasanganCheck.dispatchEvent(new Event('change'));
        }
        ['pasangan_nama', 'pasangan_nik', 'pasangan_umur', 'pasangan_jenis_kelamin']
            .forEach(id => unlock(id));

        const jumlahAnakInput = document.getElementById('jumlah_anak');
        if (jumlahAnakInput) {
            jumlahAnakInput.value = '';
            jumlahAnakInput.readOnly = false;
            jumlahAnakInput.classList.remove('bg-gray-100');
            jumlahAnakInput.dispatchEvent(new Event('change'));
        }
    },

    disableForm() {
        const btn = document.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    },

    enableForm() {
        const btn = document.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
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
            
            if (!response.ok) throw new Error('Gagal memuat data unit');
            
            const data = await response.json();
            
            if (!data.success || !data.units || data.units.length === 0) {
                lantaiSelect.innerHTML = '<option value="">Tidak ada unit tersedia</option>';
                lantaiSelect.disabled = true;
                return;
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
            
            Object.keys(groupedUnits).sort().forEach(lantai => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = `Lantai ${lantai}`;
                
                groupedUnits[lantai].forEach(unit => {
                    let displayText = unit.kode_unit;
                    if (this.preSelectedUnit && unit.kode_unit === this.preSelectedUnit) {
                        displayText += ' ✓ (Dipilih)';
                    } else if (!unit.is_available) {
                        displayText += ' (Terisi)';
                    }
                    
                    const option = new Option(displayText, unit.kode_unit);
                    if (!unit.is_available) {
                        option.disabled = true;
                        option.style.color = '#9ca3af';
                        option.style.fontStyle = 'italic';
                    }
                    if (this.preSelectedUnit && unit.kode_unit === this.preSelectedUnit) {
                        option.style.fontWeight = 'bold';
                        option.style.color = '#059669';
                    }
                    optgroup.appendChild(option);
                });
                lantaiSelect.appendChild(optgroup);
            });
            
            lantaiSelect.disabled = false;
            
        } catch (error) {
            lantaiSelect.innerHTML = '<option value="">Gagal memuat unit</option>';
        }
    },
    
    updateKeringananOptions() {
        const keringananSelect = document.getElementById('keringananSelect');
        if (!keringananSelect) return;

        const blok = this.selectedBlok;
        
        if (['A', 'B', 'C'].includes(blok)) {
            keringananSelect.innerHTML = `
                <option value="" disabled selected>Pilih Keringanan</option>
                <option value="dapat">Dapat</option>
                <option value="tidak">Tidak</option>
            `;
            keringananSelect.disabled = false;
            keringananSelect.style.backgroundColor = '';
        } else if (blok === 'D') {
            keringananSelect.innerHTML = '<option value="normal" selected>Normal</option>';
            keringananSelect.disabled = false;
            keringananSelect.value = 'normal';
            keringananSelect.style.backgroundColor = '#f3f4f6';
        } else {
            keringananSelect.innerHTML = '<option value="" disabled selected>Pilih Blok terlebih dahulu</option>';
            keringananSelect.disabled = true;
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
        
        if (!tanggalMasuk || !tanggalKeluar) return;
        
        tanggalMasuk.addEventListener('change', function() {
            if (this.value) {
                tanggalKeluar.min = this.value;
                if (tanggalKeluar.value && tanggalKeluar.value < this.value) {
                    tanggalKeluar.value = this.value;
                }
            }
        });
    },
    
    selectRusun(rusun) {
        this.selectedRusun = rusun;

        document.querySelectorAll('.rusun-box').forEach(b => b.classList.remove('border-primary', 'bg-blue-50'));
        const rusunId = `rusun${rusun === 'kraton' ? 'Kraton' : rusun === 'mbr_tegalsari' ? 'MBR' : 'Prototipe'}`;
        const rusunElement = document.getElementById(rusunId);
        if (rusunElement) rusunElement.classList.add('border-primary', 'bg-blue-50');

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
            if (lantaiSelect) {
                lantaiSelect.disabled = true;
                lantaiSelect.innerHTML = '<option value="">Pilih Blok</option>';
            }
            if (keringananSelect) {
                keringananSelect.disabled = true;
                keringananSelect.innerHTML = '<option value="">Pilih Blok terlebih dahulu</option>';
            }
            const jaminan = document.getElementById('nilaiJaminan');
            if(jaminan) jaminan.value = '';
        }
    },

    selectBlok(blok) {
        this.selectedBlok = blok;
        document.querySelectorAll('.blok-box').forEach(b => b.classList.remove('border-primary', 'bg-blue-50'));
        const blokElement = document.getElementById(`blok${blok}`);
        if (blokElement) blokElement.classList.add('border-primary', 'bg-blue-50');

        this.generateUnitOptions();
        this.updateKeringananOptions();
    },

    setupUppercaseInput(inputElement) {
        if (!inputElement) return;
        inputElement.addEventListener('input', function(e) {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.PenghuniCreate = PenghuniCreate;
    
    PenghuniCreate.initializePreSelectedUnit();
    PenghuniCreate.setupTanggalValidation();
    PenghuniCreate.setupNikAutoFill();

    ['nama', 'pasangan_nama'].forEach(id => {
        PenghuniCreate.setupUppercaseInput(document.getElementById(id));
    });

    ['nik', 'pasangan_nik', 'no_hp'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 16);
            });
        }
    });

    const keringananSelect = document.getElementById('keringananSelect');
    if (keringananSelect) keringananSelect.addEventListener('change', () => PenghuniCreate.handleKeringananChange());

    const lantaiUnitSelect = document.getElementById('lantaiUnitSelect');
    if (lantaiUnitSelect) {
        lantaiUnitSelect.addEventListener('change', (e) => {
            const unitKodeInput = document.getElementById('unitKodeInput');
            if (unitKodeInput) unitKodeInput.value = e.target.value;
            PenghuniCreate.handleKeringananChange();
        });
    }

    const adaPasangan = document.getElementById('adaPasangan');
    const pasanganForm = document.getElementById('pasanganForm');
    const pasanganNama = document.getElementById('pasangan_nama');
    const pasanganJK = document.getElementById('pasangan_jenis_kelamin');
    const jkUtama = document.getElementById('jenis_kelamin');

    const updatePasanganJK = () => {
        if (!jkUtama || !pasanganJK || !adaPasangan) return;
        
        pasanganJK.disabled = false;
        pasanganJK.style.pointerEvents = 'auto';
        pasanganJK.style.backgroundColor = '';

        if (adaPasangan.checked && jkUtama.value) {
            if (jkUtama.value === 'laki-laki') {
                pasanganJK.value = 'perempuan';
                pasanganJK.style.pointerEvents = 'none';
                pasanganJK.classList.add('bg-gray-100');
                pasanganJK.setAttribute('tabindex', '-1');
            } else if (jkUtama.value === 'perempuan') {
                pasanganJK.value = 'laki-laki';
                pasanganJK.style.pointerEvents = 'none';
                pasanganJK.classList.add('bg-gray-100');
                pasanganJK.setAttribute('tabindex', '-1');
            }
        }
    };

    if (adaPasangan) {
        adaPasangan.addEventListener('change', function() {
            if (this.checked) {
                pasanganForm.classList.remove('hidden');
                pasanganNama.required = true;
                pasanganJK.required = true;
                updatePasanganJK();
            } else {
                if (!PenghuniCreate.isAutoFilled) {
                    pasanganForm.classList.add('hidden');
                    pasanganNama.required = false;
                    pasanganJK.required = false;
                    pasanganNama.value = '';
                    document.getElementById('pasangan_nik').value = '';
                    document.getElementById('pasangan_umur').value = '';
                    pasanganJK.value = '';
                }
            }
        });
    }

    if (jkUtama) jkUtama.addEventListener('change', updatePasanganJK);

    const jumlahAnak = document.getElementById('jumlah_anak');
    const btnGenerate = document.getElementById('btnGenerateAnak');
    const containerAnak = document.getElementById('anakFormsContainer');

    const generateAnakForms = (count) => {
        if (!containerAnak) return;
        const currentCount = containerAnak.children.length;
        
        if (count > currentCount) {
            for (let i = currentCount + 1; i <= count; i++) {
                const div = document.createElement('div');
                div.className = 'anak-form-card p-4 bg-gray-50 rounded-lg border border-gray-200 mb-3';
                div.innerHTML = `
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <span class="bg-primary text-white w-7 h-7 rounded-full flex items-center justify-center text-sm mr-2">${i}</span>
                        Anak ke-${i}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap *</label>
                            <input type="text" name="anak_nama[]" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Nama anak">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin *</label>
                            <select name="anak_jenis_kelamin[]" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="" disabled selected>Pilih</option>
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Umur</label>
                            <input type="number" name="anak_umur[]" min="0" max="150" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Umur">
                        </div>
                    </div>
                `;
                containerAnak.appendChild(div);
                PenghuniCreate.setupUppercaseInput(div.querySelector('input[name="anak_nama[]"]'));
            }
        } else if (count < currentCount) {
            while (containerAnak.children.length > count) {
                containerAnak.removeChild(containerAnak.lastElementChild);
            }
        }
    };

    if (jumlahAnak) {
        jumlahAnak.addEventListener('change', function() {
            generateAnakForms(parseInt(this.value) || 0);
        });
    }
    if (btnGenerate) {
        btnGenerate.addEventListener('click', function() {
            generateAnakForms(parseInt(jumlahAnak.value) || 0);
        });
    }

    const form = document.getElementById('createPenghuniForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nikVal = document.getElementById('nik').value;
            if (nikVal.length !== 16) {
                e.preventDefault();
                alert('NIK harus 16 digit!');
                return false;
            }
            
            if(!PenghuniCreate.selectedRusun || !PenghuniCreate.selectedBlok) {
                e.preventDefault();
                alert('Pilih Rusun dan Blok dulu!');
                return false;
            }
            
            const unit = document.getElementById('lantaiUnitSelect').value;
            if(!unit) {
                e.preventDefault();
                alert('Pilih Unit!');
                return false;
            }

            if (typeof showLoading === 'function') showLoading('Menyimpan data...');
        });
    }
});