const PenghuniIndex = {
    searchTimeout: null,
 
    init() {
        this.setupEventListeners();
        this.setupAllNavigationLinks();
        this.initializeFilterState();
        this.setupAutoSearch();
        this.setupPaginationLinks();
    },

    initializeFilterState() {
        const filterContainer = document.querySelector('.filter-berakhir-container-compact');
        const chevron = document.getElementById('filterChevron');
        
        if (filterContainer && chevron) {
            const hasActiveFilter = filterContainer.classList.contains('has-active-filter');
            
            if (hasActiveFilter) {
                chevron.classList.add('expanded');
            } else {
                chevron.classList.remove('expanded');
            }
        }
    },

    setupAutoSearch() {
        const searchInput = document.getElementById('searchInput');
        const typingIndicator = document.getElementById('searchTypingIndicator');
        
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            const cursorPosition = e.target.selectionStart;
            const value = e.target.value.toUpperCase();
            e.target.value = value;
            e.target.setSelectionRange(cursorPosition, cursorPosition);
            
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            if (typingIndicator && value) {
                typingIndicator.classList.remove('hidden');
            } else if (typingIndicator) {
                typingIndicator.classList.add('hidden');
            }

            if (value) {
                searchInput.style.borderColor = '#3b82f6';
                searchInput.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
            }

            this.searchTimeout = setTimeout(() => {
                if (typingIndicator) {
                    typingIndicator.classList.add('hidden');
                }

                searchInput.style.borderColor = '';
                searchInput.style.boxShadow = '';
                
                this.globalSearch();
            }, 1600);
        });

        searchInput.addEventListener('paste', (e) => {
            setTimeout(() => {
                const cursorPosition = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            }, 10);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && searchInput.value) {
                e.preventDefault();
                searchInput.value = '';
            
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                
                if (typingIndicator) {
                    typingIndicator.classList.add('hidden');
                }
            
                searchInput.style.borderColor = '';
                searchInput.style.boxShadow = '';
    
                this.globalSearch();
            }
        });
    },

    setupEventListeners() {
        const searchInput = document.getElementById('searchInput');
        const typingIndicator = document.getElementById('searchTypingIndicator');
        
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
     
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }
       
                    if (typingIndicator) {
                        typingIndicator.classList.add('hidden');
                    }
   
                    searchInput.style.borderColor = '';
                    searchInput.style.boxShadow = '';
                    
                    this.globalSearch();
                }
            });
        }
    },

    setupPaginationLinks() {
        const paginationContainer = document.querySelector('.mt-6.flex.items-center.justify-between nav');
        
        if (!paginationContainer) {
            return;
        }

        const paginationLinks = paginationContainer.querySelectorAll('a[href]');
        
        paginationLinks.forEach(link => {
            if (link.dataset.paginationListener === 'true') {
                return;
            }
            
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

    setupAllNavigationLinks() {
        const detailLinks = document.querySelectorAll('a[href*="penghuni"][href*="show"], a.activity-title, td a[href*="/penghuni/"]');
        
        detailLinks.forEach(link => {
            if (link.dataset.hasListener) return;
            
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:') || link.target === '_blank') {
                return;
            }
            
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                if (typeof navigateWithFullPageLoading === 'function') {
                    navigateWithFullPageLoading(href, 'Membuka detail penghuni...');
                } else {
                    window.location.href = href;
                }
            });
            
            link.dataset.hasListener = 'true';
        });

        const importLink = document.querySelector('a[href*="import"]');
        if (importLink && !importLink.dataset.hasListener) {
            const href = importLink.getAttribute('href');
            if (href && href !== '#') {
                importLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    if (typeof navigateWithFullPageLoading === 'function') {
                        navigateWithFullPageLoading(href, 'Membuka halaman import...');
                    } else {
                        window.location.href = href;
                    }
                });
                importLink.dataset.hasListener = 'true';
            }
        }

        const tambahLink = document.querySelector('a[href*="tambah-penghuni"]');
        if (tambahLink && !tambahLink.dataset.hasListener) {
            const href = tambahLink.getAttribute('href');
            if (href && href !== '#') {
                tambahLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    if (typeof navigateWithFullPageLoading === 'function') {
                        navigateWithFullPageLoading(href, 'Membuka form tambah data penghuni...');
                    } else {
                        window.location.href = href;
                    }
                });
                tambahLink.dataset.hasListener = 'true';
            }
        }

        const baLinks = document.querySelectorAll('a[href*="editBaKeluar"]');
        
        baLinks.forEach(link => {
            if (link.dataset.hasListener) return;
            
            const href = link.getAttribute('href');
            if (href && href !== '#') {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    if (typeof navigateWithFullPageLoading === 'function') {
                        navigateWithFullPageLoading(href, 'Membuka form BA Keluar...');
                    } else {
                        window.location.href = href;
                    }
                });
                link.dataset.hasListener = 'true';
            }
        });
    },

    filterKontrakBerakhir(days) {
        if (typeof showLoading === 'function') {
            showLoading(`Memuat kontrak yang berakhir dalam ${days} hari...`);
        }
        
        const url = new URL(window.location.href);
        url.searchParams.set('kontrak_berakhir', days);
        url.searchParams.delete('bulan_berakhir');
        url.searchParams.delete('tahun_berakhir');
        url.searchParams.delete('page');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    filterByBulan(bulan) {
        if (typeof showLoading === 'function') {
            if (bulan) {
                const namaBulan = new Date(2025, bulan - 1).toLocaleString('id-ID', { month: 'long' });
                showLoading(`Memuat kontrak berakhir bulan ${namaBulan}...`);
            } else {
                showLoading('Menampilkan semua data...');
            }
        }
        
        const url = new URL(window.location.href);
        
        if (bulan) {
            url.searchParams.set('bulan_berakhir', bulan);
            url.searchParams.delete('kontrak_berakhir');
            url.searchParams.delete('tahun_berakhir');
        } else {
            url.searchParams.delete('bulan_berakhir');
        }
        
        url.searchParams.delete('page');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    filterByTahun(tahun) {
        if (typeof showLoading === 'function') {
            if (tahun) {
                showLoading(`Memuat kontrak berakhir tahun ${tahun}...`);
            } else {
                showLoading('Menampilkan semua data...');
            }
        }
        
        const url = new URL(window.location.href);
        
        if (tahun) {
            url.searchParams.set('tahun_berakhir', tahun);
            url.searchParams.delete('kontrak_berakhir');
            url.searchParams.delete('bulan_berakhir');
        } else {
            url.searchParams.delete('tahun_berakhir');
        }
        
        url.searchParams.delete('page');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    resetKontrakBerakhirFilter() {
        if (typeof showLoading === 'function') {
            showLoading('Menampilkan semua data...');
        }
        
        const url = new URL(window.location.href);
        url.searchParams.delete('kontrak_berakhir');
        url.searchParams.delete('bulan_berakhir');
        url.searchParams.delete('tahun_berakhir');
        url.searchParams.delete('page');
        url.searchParams.delete('highlight');
        url.searchParams.delete('sort_by');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
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

    filterByStatus() {
        const status = document.getElementById('statusFilter').value;
        const statusDisplay = status ? status.replace(/_/g, ' ') : '';
        
        if (typeof showLoading === 'function') {
            showLoading(status ? `Memuat data penghuni yang ${statusDisplay}...` : 'Menampilkan semua status...');
        }
        
        const url = new URL(window.location.href);
        
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        url.searchParams.delete('page');
        url.searchParams.delete('highlight');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    filterByBlok(blok) {
        if (typeof showLoading === 'function') {
            showLoading(blok ? `Memuat data Blok ${blok}...` : 'Menampilkan semua blok...');
        }
        
        const url = new URL(window.location.href);
        
        if (blok) {
            url.searchParams.set('blok', blok);
        } else {
            url.searchParams.delete('blok');
        }
        
        url.searchParams.delete('page');
        url.searchParams.delete('highlight');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    closeSuccessModal(shouldReload = false) {
        const modal = document.getElementById('successModal');
        if (!modal) return;
        
        modal.style.opacity = '0';
        modal.style.backdropFilter = 'blur(0px)';
        
        const modalContent = modal.querySelector('.animate-slideUp');
        if (modalContent) {
            modalContent.style.transform = 'translateY(20px) scale(0.95)';
        }
        
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
            
            if (shouldReload) {
                window.location.reload();
            }
        }, 1000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    PenghuniIndex.init();
 
    if (typeof setupModalBackdropClose === 'function') {
        setupModalBackdropClose('successModal');
    }

    const successModal = document.getElementById('successModal');
    if (successModal) {
        setTimeout(() => {
            PenghuniIndex.closeSuccessModal(false);
        }, 1000);
    }
});

window.PenghuniIndex = PenghuniIndex;