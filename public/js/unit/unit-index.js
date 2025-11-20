const UnitIndex = {
    searchTimeout: null,
    
    init() {
        this.setupAutoSearch();
        this.setupTambahPenghuniLinks();
    },

    setupTambahPenghuniLinks() {
        const tambahLinks = document.querySelectorAll('.btn-add');
        
        tambahLinks.forEach(link => {
            if (link.dataset.hasListener) return;
            
            const href = link.getAttribute('href');
            if (href && href.includes('tambah-penghuni')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    const url = new URL(href, window.location.origin);
                    const unitCode = url.searchParams.get('unit');
                    
                    if (typeof navigateWithFullPageLoading === 'function') {
                        const message = unitCode ? 
                            `Membuka form untuk unit ${unitCode}...` : 
                            'Membuka form tambah penghuni...';
                        navigateWithFullPageLoading(href, message);
                    } else {
                        if (typeof showLoading === 'function') {
                            showLoading('Membuka form...');
                        }
                        setTimeout(() => {
                            window.location.href = href;
                        }, 1000);
                    }
                });
                link.dataset.hasListener = 'true';
            }
        });
    },
    
    setupAutoSearch() {
        const searchInput = document.getElementById('searchInput');
        const typingIndicator = document.getElementById('searchTypingIndicator');
        
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            const value = e.target.value.trim();
            
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
                
                this.search();
            }, 800);
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
                
                this.search();
            }
        });
        
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
                
                this.search();
            }
        });
    },
    
    changePerPage() {
        const perPage = document.getElementById('perPageSelect').value;
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page');
        
        showLoading('Memuat data unit...');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1200);
    },
    
    filterByStatus() {
        const status = document.getElementById('statusFilter').value;
        const perPage = document.getElementById('perPageSelect').value;
        const url = new URL(window.location.href);
        
        url.searchParams.delete('status');
        url.searchParams.delete('page');
        url.searchParams.set('per_page', perPage);
        
        if (status !== '') {
            url.searchParams.set('status', status);
        }
        
        showLoading('Memfilter berdasarkan status...');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1200);
    },
    
    filterByBlok(blok) {
        const status = document.getElementById('statusFilter').value;
        const perPage = document.getElementById('perPageSelect').value;
        const url = new URL(window.location.href);
        
        url.searchParams.delete('blok');
        url.searchParams.delete('page');
        url.searchParams.set('per_page', perPage);
        
        if (status !== '') {
            url.searchParams.set('status', status);
        }
        
        if (blok !== '') {
            url.searchParams.set('blok', blok);
        }
        
        showLoading('Memfilter berdasarkan blok...');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1200);
    },
    
    search() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;
        const perPage = document.getElementById('perPageSelect').value;
        const url = new URL(window.location.href);
        
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        url.searchParams.set('per_page', perPage);
        
        if (status !== '') {
            url.searchParams.set('status', status);
        }
        
        if (searchTerm !== '') {
            url.searchParams.set('search', searchTerm);
        }
        
        showLoading('Mencari data...');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1500);
    },
    
    resetFilters() {
        const perPage = document.getElementById('perPageSelect').value;
        const url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('per_page', perPage);
        
        showLoading('Reset filter...');
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1200);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    UnitIndex.init();
    
    const currentBlok = new URLSearchParams(window.location.search).get('blok');
    if (currentBlok) {
        const blokButtons = document.querySelectorAll('.blok-filter-btn');
        blokButtons.forEach(btn => {
            const btnText = btn.textContent.trim();
            if (btnText.includes(currentBlok)) {
                btn.classList.add('bg-primary', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
            }
        });
    }
    
    const searchInput = document.getElementById('searchInput');
    const searchParam = new URLSearchParams(window.location.search).get('search');
    if (searchParam && searchInput) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
});

window.UnitIndex = UnitIndex;