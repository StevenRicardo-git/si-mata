(function() {
    'use strict';
    
    const BlacklistIndex = {
        searchTimeout: null,

        init() {
            this.setupEventListeners();
            this.setupAutoSearch();
            this.setupPaginationLinks();
            this.setupNameLinks();
        },

        setupEventListeners() {
            const searchInput = document.getElementById('searchInput');
            
            if (searchInput) {
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        
                        if (this.searchTimeout) {
                            clearTimeout(this.searchTimeout);
                        }
                        
                        searchInput.style.borderColor = '';
                        searchInput.style.boxShadow = '';
                        
                        this.globalSearch();
                    }
                });
            }
        },

        setupAutoSearch() {
            const searchInput = document.getElementById('searchInput');
            
            if (!searchInput) return;

            searchInput.addEventListener('input', (e) => {
                const value = e.target.value.trim();

                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }

                if (value) {
                    searchInput.style.borderColor = '#3b82f6';
                    searchInput.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                }

                this.searchTimeout = setTimeout(() => {
                    searchInput.style.borderColor = '';
                    searchInput.style.boxShadow = '';
                    
                    this.globalSearch();
                }, 800);
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && searchInput.value) {
                    e.preventDefault();
                    searchInput.value = '';
                
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }
                   
                    searchInput.style.borderColor = '';
                    searchInput.style.boxShadow = '';
         
                    this.globalSearch();
                }
            });
        },

        setupPaginationLinks() {
            const paginationContainer = document.querySelector('.mt-6.flex.items-center.justify-between nav');
            
            if (!paginationContainer) return;

            const paginationLinks = paginationContainer.querySelectorAll('a[href]');
            
            paginationLinks.forEach(link => {
                if (link.dataset.paginationListener === 'true') return;
                
                const href = link.getAttribute('href');
                
                if (!href || 
                    href === '#' || 
                    link.classList.contains('pointer-events-none') ||
                    link.classList.contains('text-gray-400')) {
                    return;
                }
                
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (typeof showLoading === 'function') {
                        showLoading('Memuat halaman...');
                    }
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 1000);
                });
                
                link.dataset.paginationListener = 'true';
            });
        },

        setupNameLinks() {
            const nameLinks = document.querySelectorAll('a[href*="penghuni"][href*="show"]');
            
            nameLinks.forEach(link => {
                if (link.dataset.hasListener) return;
                
                const href = link.getAttribute('href');
                if (!href || href === '#' || link.target === '_blank') {
                    return;
                }
                
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    if (typeof navigateWithFullPageLoading === 'function') {
                        navigateWithFullPageLoading(href, 'Membuka detail penghuni...');
                    } else {
                        if (typeof showLoading === 'function') {
                            showLoading('Membuka detail penghuni...');
                        }
                        setTimeout(() => {
                            window.location.href = href;
                        }, 1000);
                    }
                });
                
                link.dataset.hasListener = 'true';
            });
        },

        changePerPage() {
            const perPage = document.getElementById('perPageSelect').value;
            
            if (typeof showLoading === 'function') {
                showLoading('Memuat data...');
            }
            
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page');
            
            setTimeout(() => {
                window.location.href = url.toString();
            }, 1000);
        },

        globalSearch() {
            const searchValue = document.getElementById('searchInput').value.trim();
            
            if (typeof showLoading === 'function') {
                showLoading(searchValue ? 'Mencari data...' : 'Menampilkan semua data...');
            }
            
            const url = new URL(window.location.href);
            
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            
            url.searchParams.delete('page');
            
            setTimeout(() => {
                window.location.href = url.toString();
            }, 500);
        },

        openReactivateModal(id, nama, nik) {
            const form = document.getElementById('reactivateForm');
            if (!form) {
                alert('Error: Form tidak ditemukan');
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
                    setTimeout(() => {
                        if (alasanEl) alasanEl.focus();
                    }, 100);
                }
            }
        },

        showDetailAlasan(nama, alasan, title = 'Alasan Blacklist') {
            const modalTitle = document.getElementById('detailAlasanTitle');
            const modalNama = document.getElementById('detailAlasanNama');
            const modalContent = document.getElementById('detailAlasanContent');
            
            if (modalTitle) modalTitle.textContent = title;
            if (modalNama) modalNama.textContent = nama;
            if (modalContent) modalContent.textContent = alasan;
            
            if (window.openModal && typeof window.openModal === 'function') {
                window.openModal('detailAlasanModal');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        BlacklistIndex.init();
        
        if (typeof setupModalBackdropClose === 'function') {
            setupModalBackdropClose('reactivateModal');
            setupModalBackdropClose('detailAlasanModal');
        }

        const reactivateForm = document.getElementById('reactivateForm');
        if (reactivateForm) {
            reactivateForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const alasanInput = document.getElementById('alasan_aktivasi');
                const alasan = alasanInput ? alasanInput.value.trim() : '';
                
                if (!alasan) {
                    alert('Alasan aktivasi wajib diisi!');
                    if (alasanInput) alasanInput.focus();
                    return false;
                }
                
                if (alasan.length < 10) {
                    alert('Alasan aktivasi minimal 10 karakter!');
                    if (alasanInput) alasanInput.focus();
                    return false;
                }
                
                if (!this.action || this.action.includes('undefined') || this.action.includes('null')) {
                    alert('Error: URL tidak valid. Silakan refresh halaman.');
                    return false;
                }
                
                if (window.showLoading && typeof window.showLoading === 'function') {
                    window.showLoading('Mengaktifkan penghuni kembali...');
                }

                setTimeout(() => {
                    this.submit();
                }, 1000);
            });
        }
    });

    window.BlacklistIndex = BlacklistIndex;
})();