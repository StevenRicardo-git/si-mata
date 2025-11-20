(function() {
    'use strict';
    
    const BlacklistCreate = {
        checkTimeout: null,

        init() {
            this.setupNikValidation();
            this.setupFormSubmit();
        },

        setupNikValidation() {
            const nikInput = document.getElementById('nik');
            const namaInput = document.getElementById('nama');
            const nikStatus = document.getElementById('nikStatus');
            
            if (!nikInput || !namaInput || !nikStatus) {
                return;
            }

            nikInput.addEventListener('input', (e) => {
                const cursorPosition = e.target.selectionStart;
                const oldValue = e.target.value;
                
                e.target.value = e.target.value.replace(/\D/g, '');
                
                if (e.target.value.length > 16) {
                    e.target.value = e.target.value.slice(0, 16);
                }

                if (oldValue !== e.target.value) {
                    e.target.setSelectionRange(cursorPosition, cursorPosition);
                }

                if (this.checkTimeout) {
                    clearTimeout(this.checkTimeout);
                }

                const nikValue = e.target.value;

                if (nikValue.length < 16) {
                    namaInput.value = '';
                    namaInput.readOnly = false;
                    namaInput.classList.remove('bg-gray-100');
                    namaInput.style.cursor = '';
                    nikStatus.classList.add('hidden');
                    nikStatus.textContent = '';
                    
                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                    return;
                }

                if (nikValue.length === 16) {
                    nikStatus.textContent = 'Memeriksa NIK...';
                    nikStatus.className = 'mt-1 text-sm text-blue-600';
                    nikStatus.classList.remove('hidden');

                    this.checkTimeout = setTimeout(() => {
                        this.checkNikExists(nikValue, namaInput, nikStatus);
                    }, 500);
                }
            });

            nikInput.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numericOnly = pastedText.replace(/\D/g, '').slice(0, 16);
                nikInput.value = numericOnly;
                nikInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
        },

        async checkNikExists(nik, namaInput, nikStatus) {
            try {
                const response = await fetch(`/blacklist/check-nik?nik=${nik}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.is_blacklisted) {
                    namaInput.value = '';
                    namaInput.readOnly = true;
                    namaInput.classList.add('bg-gray-100');
                    namaInput.style.cursor = 'not-allowed';
                    
                    nikStatus.innerHTML = `
                        <strong class="text-red-700">⚠️ NIK SUDAH DI-BLACKLIST!</strong><br>
                        <span class="text-xs mt-1 block">
                            <strong>Nama:</strong> ${data.blacklist_info.nama}<br>
                            <strong>Alasan:</strong> ${data.blacklist_info.alasan}<br>
                            <strong>Tanggal:</strong> ${data.blacklist_info.tanggal}
                        </span>
                    `;
                    nikStatus.className = 'mt-1 text-sm text-red-600 font-medium p-3 bg-red-50 rounded-lg border border-red-200';
                    nikStatus.classList.remove('hidden');

                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        submitBtn.title = 'NIK ini sudah ada di blacklist';
                    }

                    return;
                }

                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.title = '';
                }

                if (data.exists && data.nama) {
                    namaInput.value = data.nama.toUpperCase();
                    namaInput.readOnly = true;
                    namaInput.classList.add('bg-gray-100');
                    namaInput.style.cursor = 'not-allowed';
                    
                    nikStatus.textContent = '✓ NIK ditemukan! Nama akan terisi otomatis.';
                    nikStatus.className = 'mt-1 text-sm text-green-600 font-medium';
                    nikStatus.classList.remove('hidden');
                } else {
                    namaInput.value = '';
                    namaInput.readOnly = false;
                    namaInput.classList.remove('bg-gray-100');
                    namaInput.style.cursor = '';
                    
                    nikStatus.textContent = 'ℹ️ NIK tidak ditemukan! Silakan isi nama secara manual.';
                    nikStatus.className = 'mt-1 text-sm text-yellow-600 font-medium';
                    nikStatus.classList.remove('hidden');
                }
            } catch (error) {
                namaInput.readOnly = false;
                namaInput.classList.remove('bg-gray-100');
                namaInput.style.cursor = '';
                
                nikStatus.textContent = '⚠️ Gagal memeriksa NIK. Silakan isi nama secara manual.';
                nikStatus.className = 'mt-1 text-sm text-red-600 font-medium';
                nikStatus.classList.remove('hidden');
                
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        },

        setupFormSubmit() {
            const form = document.getElementById('createBlacklistForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (!form) {
                return;
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (submitBtn.disabled) {
                    if (typeof customAlert === 'function') {
                        await customAlert('NIK ini sudah ada di daftar blacklist!', 'error');
                    } else {
                        alert('NIK ini sudah ada di daftar blacklist!');
                    }
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Menyimpan...';
                
                const nikInput = document.getElementById('nik');
                const namaInput = document.getElementById('nama');
                const alasanInput = document.getElementById('alasan_blacklist');

                if (!nikInput.value || nikInput.value.length !== 16) {
                    if (typeof customAlert === 'function') {
                        await customAlert('NIK harus 16 digit!', 'error');
                    } else {
                        alert('NIK harus 16 digit!');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan ke Blacklist';
                    nikInput.focus();
                    return;
                }

                if (!namaInput.value || namaInput.value.trim() === '') {
                    if (typeof customAlert === 'function') {
                        await customAlert('Nama lengkap wajib diisi!', 'error');
                    } else {
                        alert('Nama lengkap wajib diisi!');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan ke Blacklist';
                    namaInput.focus();
                    return;
                }

                if (!alasanInput.value || alasanInput.value.trim() === '') {
                    if (typeof customAlert === 'function') {
                        await customAlert('Alasan blacklist wajib diisi!', 'error');
                    } else {
                        alert('Alasan blacklist wajib diisi!');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan ke Blacklist';
                    alasanInput.focus();
                    return;
                }

                if (typeof showLoading === 'function') {
                    showLoading('Menyimpan data ke blacklist...');
                }

                setTimeout(() => {
                    form.submit();
                }, 1500);
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            BlacklistCreate.init();
        });
    } else {
        BlacklistCreate.init();
    }

    window.BlacklistCreate = BlacklistCreate;
})();