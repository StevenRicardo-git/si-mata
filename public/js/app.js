let sidebarOpen = false;
let isNavigating = false;
let menuOpen = false;
let navigationInProgress = false;
let accordionTimeout = null;
let sidebarAnimating = false;

const successAlert = document.getElementById('success-alert');
if (successAlert) {
    successAlert.classList.add('transition-all', 'duration-1000', 'ease-in-out');
    successAlert.style.overflow = 'hidden';
    setTimeout(() => {
        successAlert.style.opacity = '0';
        successAlert.style.maxHeight = '0';
        successAlert.style.paddingTop = '0';
        successAlert.style.paddingBottom = '0';
        successAlert.style.marginTop = '0';
        successAlert.style.marginBottom = '0';
        setTimeout(() => { successAlert.remove(); }, 1000);
    }, 4000);
}

const errorAlert = document.getElementById('error-alert');
if (errorAlert) {
    errorAlert.classList.add('transition-all', 'duration-1000', 'ease-in-out');
    errorAlert.style.overflow = 'hidden';
    setTimeout(() => {
        errorAlert.style.opacity = '0';
        errorAlert.style.maxHeight = '0';
        errorAlert.style.paddingTop = '0';
        errorAlert.style.paddingBottom = '0';
        errorAlert.style.marginTop = '0';
        errorAlert.style.marginBottom = '0';
        setTimeout(() => { errorAlert.remove(); }, 1000);
    }, 6000);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            modal.style.opacity = '1';
            const modalContent = modal.querySelector('.modal-content, .animate-slideUp');
            if (modalContent) {
                modalContent.style.transform = 'translateY(0) scale(1)';
                modalContent.style.opacity = '1';
            }
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.transition = 'opacity 0.3s ease-out, backdrop-filter 0.3s ease-out';
        modal.style.opacity = '0';
        modal.style.backdropFilter = 'blur(0px)';
        const modalContent = modal.querySelector('.modal-content, .animate-slideUp');
        if (modalContent) {
            modalContent.style.transition = 'transform 0.3s ease-out, opacity 0.3s ease-out';
            modalContent.style.transform = 'translateY(20px) scale(0.95)';
            modalContent.style.opacity = '0';
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            modal.style.opacity = '';
            modal.style.backdropFilter = '';
            if (modalContent) {
                modalContent.style.transform = '';
                modalContent.style.opacity = '';
            }
        }, 300);
    }
}

function setupModalBackdropClose(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) { closeModal(modalId); }
        });
    }
}

function showLoading(message = 'Memuat data...') {
    hideLoading();
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = `position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); opacity: 0;`;
    overlay.innerHTML = `<div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; min-width: 300px;"><svg style="width: 64px; height: 64px; margin: 0 auto 20px; color: #2b398b;" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p style="color: #2b398b; font-weight: 700; font-size: 16px; margin-bottom: 12px;">${message}</p><div style="display: flex; justify-content: center; gap: 6px; margin-top: 16px;"><div style="width: 10px; height: 10px; background: #2b398b; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both;"></div><div style="width: 10px; height: 10px; background: #2b398b; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: 0.16s;"></div><div style="width: 10px; height: 10px; background: #2b398b; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: 0.32s;"></div></div></div>`;
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        overlay.style.opacity = '1';
        overlay.style.transition = 'opacity 0.3s ease-out';
    }, 10);
    setTimeout(() => { hideLoading(); }, 30000);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 200);
        document.body.style.overflow = '';
    }
}

function showToast(message, type = 'success', duration = 3000) {
    const colors = { success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500' };
    const icons = {
        success: `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`,
        error: `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`,
        warning: `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
        info: `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    };
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 z-50 transform transition-all duration-300 translate-x-full`;
    toast.innerHTML = `${icons[type]}<span class="font-medium">${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function validateNIK(nik) { return /^\d{16}$/.test(nik); }
function getTodayDate() { return new Date().toISOString().slice(0, 10); }

function toggleSidebar() {
    if (sidebarAnimating) {
        return;
    }
    
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('modernToggle');
    const mainContent = document.getElementById('mainContent');
    const mainNav = document.getElementById('mainNav');
    
    if (!sidebar || !overlay || !toggleBtn || !mainNav) {
        return;
    }
    
    sidebarAnimating = true;
    toggleBtn.classList.add('animating');
    sidebarOpen = !sidebarOpen;
    
    if (sidebarOpen) {
        sidebar.classList.add('open');
        overlay.classList.remove('hidden');
        void overlay.offsetHeight;
        overlay.classList.add('show');
        toggleBtn.classList.add('open');
        mainNav.style.display = 'block';
        mainNav.style.opacity = '0';
        mainNav.classList.remove('show');
        
        void mainNav.offsetHeight;
        
        setTimeout(() => {
            mainNav.classList.add('show');
            
            requestAnimationFrame(() => {
                mainNav.style.opacity = '1';
            });
            
            setTimeout(() => {
                const activeLink = mainNav.querySelector('a.active');
                if (activeLink) {
                    const navRect = mainNav.getBoundingClientRect();
                    const linkRect = activeLink.getBoundingClientRect();
                    const scrollOffset = linkRect.top - navRect.top - (navRect.height / 2) + (linkRect.height / 2);
                    
                    mainNav.scrollTo({
                        top: mainNav.scrollTop + scrollOffset,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        }, 150);
        
        menuOpen = true;
        
        setTimeout(() => {
            sidebarAnimating = false;
            toggleBtn.classList.remove('animating');
        }, 650);
        
    } else {
        mainNav.classList.remove('show');
        mainNav.style.opacity = '0';
        
        setTimeout(() => {
            mainNav.style.display = 'none';
        }, 350);
        
        menuOpen = false;
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        toggleBtn.classList.remove('open');
        
        setTimeout(() => {
            overlay.classList.add('hidden');
            if (mainContent) {
                mainContent.classList.remove('ml-64', 'push-content');
                mainContent.style.marginLeft = '';
            }
            document.body.style.overflow = '';
        }, 500);
        
        setTimeout(() => {
            sidebarAnimating = false;
            toggleBtn.classList.remove('animating');
        }, 650);
    }
}

function toggleAccordion(id) {
    if (accordionTimeout) return;
    
    const content = document.getElementById(id + '-content');
    const arrow = document.getElementById(id + '-arrow');
    
    if (!content || !arrow) return;
    
    const isActive = content.classList.contains('active');
    
    document.querySelectorAll('.accordion-content').forEach(item => {
        if (item.id !== id + '-content') {
            item.classList.remove('active');
            const otherArrow = document.getElementById(item.id.replace('-content', '-arrow'));
            if (otherArrow) otherArrow.classList.remove('rotated');
        }
    });

    if (isActive) {
        content.classList.remove('active');
        arrow.classList.remove('rotated');
    } else {
        content.classList.add('active');
        arrow.classList.add('rotated');
    }
    
    accordionTimeout = setTimeout(() => {
        accordionTimeout = null;
    }, 400);
}

function showPageLoading(message = 'Memuat halaman...') {
    let loader = document.getElementById('pageLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'pageLoader';
        loader.innerHTML = `<div style="text-align: center;"><div class="spinner" style="margin: 0 auto 24px;"></div><p style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 20px;">${message}</p><div style="display: flex; justify-content: center; gap: 8px;"><div style="width: 12px; height: 12px; background: white; border-radius: 50%;" class="animate-bounce-dots"></div><div style="width: 12px; height: 12px; background: white; border-radius: 50%;" class="animate-bounce-dots" style="animation-delay: 0.2s;"></div><div style="width: 12px; height: 12px; background: white; border-radius: 50%;" class="animate-bounce-dots" style="animation-delay: 0.4s;"></div></div></div>`;
        document.body.appendChild(loader);
    } else {
        const messageEl = loader.querySelector('p');
        if (messageEl) messageEl.textContent = message;
    }
    isNavigating = true;
    document.body.style.overflow = 'hidden';
    loader.style.display = 'flex';
    loader.offsetHeight;
    requestAnimationFrame(() => { loader.classList.add('show'); });
}

function hidePageLoading() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.remove('show');
        loader.classList.add('hide');
        setTimeout(() => {
            loader.remove();
            isNavigating = false;
        }, 400);
    } else {
        isNavigating = false;
    }
    document.body.style.overflow = '';
}

function navigateWithFullPageLoading(url, message = 'Memuat halaman...') {
    if (!url || url === '#' || navigationInProgress) return;
    
    const targetPath = new URL(url, window.location.origin).pathname;
    const currentPath = window.location.pathname;
    
    if (targetPath === currentPath) return;
    
    isNavigating = true;
    navigationInProgress = true;
    showPageLoading(message);
    setTimeout(() => { window.location.href = url; }, 800);
}

function showLoginAnimation(callback) {
    const animationDiv = document.getElementById('loginAnimation');
    if (animationDiv) animationDiv.style.display = 'flex';
    setTimeout(callback, 1500); 
}

function hideLoginAnimation() {
    const animation = document.getElementById('loginAnimation');
    if (animation) {
        animation.classList.remove('show');
        animation.classList.add('hide');
        setTimeout(() => {
            animation.style.display = 'none';
            animation.classList.remove('hide');
        }, 500);
    }
}

function handleLogout() {
    const modal = document.getElementById('logoutModal');
    const modalContent = document.getElementById('logoutModalContent');
    if (!modal || !modalContent) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.style.transition = 'opacity 0.3s ease-out';
        modal.style.opacity = '1';
        modalContent.style.transition = 'transform 0.3s ease-out';
        modalContent.style.transform = 'scale(1)';
    }, 10);
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    const modalContent = document.getElementById('logoutModalContent');
    if (!modal || !modalContent) return;
    modal.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function confirmLogout() {
    closeLogoutModal();
    setTimeout(() => {
        const animation = document.getElementById('logoutAnimation');
        if (animation) {
            animation.style.display = 'flex';
            animation.offsetHeight;
            setTimeout(() => { animation.classList.add('show'); }, 10);
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) {
                setTimeout(() => { logoutForm.submit(); }, 2000);
            } else {
                setTimeout(() => { window.location.href = '/login'; }, 2000);
            }
        } else {
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) logoutForm.submit();
            else window.location.href = '/login';
        }
    }, 200);
}

window.addEventListener('load', function() {
    if (isNavigating) {
        return;
    }
    setTimeout(() => {
        hideLoading();
        hidePageLoading();
        hideLoginAnimation();
    }, 500);
});

window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        isNavigating = true;
        showPageLoading('Memuat halaman...');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } else {
        isNavigating = false;
        navigationInProgress = false;
        setTimeout(() => {
            hideLoading();
            hidePageLoading();
            hideLoginAnimation();
        }, 300);
    }
});

window.addEventListener('popstate', function(event) {
    event.preventDefault();
    isNavigating = true;
    showPageLoading('Memuat halaman...');
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});

window.addEventListener('beforeunload', function() { hideLoading(); });

document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/penghuni') || currentPath.includes('/unit') || 
        currentPath.includes('/tagihan') || currentPath.includes('/blacklist')) {
        const manajemenAccordion = document.getElementById('manajemen-content');
        const manajemenArrow = document.getElementById('manajemen-arrow');
        if (manajemenAccordion && manajemenArrow) {
            manajemenAccordion.classList.add('active');
            manajemenArrow.classList.add('rotated');
        }
    }
    
    if (currentPath.includes('/laporan') || currentPath.includes('/disperkim') || 
        currentPath.includes('/audit')) {
        const laporanAccordion = document.getElementById('laporan-content');
        const laporanArrow = document.getElementById('laporan-arrow');
        if (laporanAccordion && laporanArrow) {
            laporanAccordion.classList.add('active');
            laporanArrow.classList.add('rotated');
        }
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebarOpen) {
            toggleSidebar();
        }
    });
    
    const overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
    
    const sidebarLinks = document.querySelectorAll('#sidebar a[href]:not(.accordion-header)');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (navigationInProgress) return;
            
            const url = this.getAttribute('href');
            
            if (url && url !== '#' && !url.startsWith('javascript:') && !url.startsWith('http')) {
                e.preventDefault();
                e.stopPropagation();
                
                const targetPath = new URL(url, window.location.origin).pathname;
                const currentPath = window.location.pathname;
                
                if (targetPath === currentPath) {
                    if (sidebarOpen) {
                        toggleSidebar();
                    }
                    return;
                }
                
                navigateWithFullPageLoading(url, 'Memuat halaman...');
            }
        });
    });
});

function customAlert(message, type = 'info') {
    return new Promise((resolve) => {
        const existingAlert = document.getElementById('customAlertModal');
        if (existingAlert) existingAlert.remove();
        const alertTypes = {
            success: { icon: `<svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, color: 'green', title: 'Berhasil!' },
            error: { icon: `<svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, color: 'red', title: 'Error!' },
            warning: { icon: `<svg class="w-16 h-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`, color: 'yellow', title: 'Perhatian!' },
            info: { icon: `<svg class="w-16 h-16 text-blue-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, color: 'blue', title: 'Informasi' }
        };
        const alertConfig = alertTypes[type] || alertTypes.info;
        const modal = document.createElement('div');
        modal.id = 'customAlertModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';
        modal.style.backdropFilter = 'blur(8px)';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease-out';
        modal.innerHTML = `<div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform scale-95 transition-transform duration-300" id="customAlertContent"><div class="text-center">${alertConfig.icon}<h3 class="text-2xl font-bold text-gray-900 mt-4 mb-3">${alertConfig.title}</h3><p class="text-gray-600 mb-6 text-base leading-relaxed">${message}</p><button onclick="closeCustomAlert()" class="w-full bg-${alertConfig.color}-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-${alertConfig.color}-700 transition-all focus:outline-none focus:ring-2 focus:ring-${alertConfig.color}-500 focus:ring-offset-2">OK</button></div></div>`;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            const content = document.getElementById('customAlertContent');
            if (content) content.style.transform = 'scale(1)';
        });
        window.closeCustomAlert = function() {
            modal.style.opacity = '0';
            const content = document.getElementById('customAlertContent');
            if (content) content.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
                delete window.closeCustomAlert;
                resolve(true);
            }, 300);
        };
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                window.closeCustomAlert();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) window.closeCustomAlert();
        });
    });
}

function customConfirm(message, options = {}) {
    return new Promise((resolve) => {
        const existingConfirm = document.getElementById('customConfirmModal');
        if (existingConfirm) existingConfirm.remove();
        const { title = 'Konfirmasi', confirmText = 'Ya', cancelText = 'Batal', type = 'warning' } = options;
        const icons = {
            warning: `<svg class="w-16 h-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
            danger: `<svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
            info: `<svg class="w-16 h-16 text-blue-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            success: `<svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
        };
        const buttonColors = { warning: 'yellow', danger: 'red', info: 'blue', success: 'green' };
        const icon = icons[type] || icons.warning;
        const color = buttonColors[type] || 'yellow';
        const modal = document.createElement('div');
        modal.id = 'customConfirmModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';
        modal.style.backdropFilter = 'blur(8px)';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease-out';
        modal.innerHTML = `<div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform scale-95 transition-transform duration-300" id="customConfirmContent"><div class="text-center">${icon}<h3 class="text-2xl font-bold text-gray-900 mt-4 mb-3">${title}</h3><p class="text-gray-600 mb-6 text-base leading-relaxed">${message}</p><div class="flex gap-3">${cancelText ? `<button onclick="closeCustomConfirm(false)" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">${cancelText}</button>` : ''}<button onclick="closeCustomConfirm(true)" class="${cancelText ? 'flex-1' : 'w-full'} bg-${color}-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-${color}-700 transition-all focus:outline-none focus:ring-2 focus:ring-${color}-500 focus:ring-offset-2">${confirmText}</button></div></div></div>`;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            const content = document.getElementById('customConfirmContent');
            if (content) content.style.transform = 'scale(1)';
        });
        window.closeCustomConfirm = function(result) {
            modal.style.opacity = '0';
            const content = document.getElementById('customConfirmContent');
            if (content) content.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
                delete window.closeCustomConfirm;
                resolve(result);
            }, 300);
        };
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                window.closeCustomConfirm(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) window.closeCustomConfirm(false);
        });
    });
}

window.openModal = openModal;
window.closeModal = closeModal;
window.setupModalBackdropClose = setupModalBackdropClose;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;
window.validateNIK = validateNIK;
window.getTodayDate = getTodayDate;
window.toggleSidebar = toggleSidebar;
window.toggleAccordion = toggleAccordion;
window.showPageLoading = showPageLoading;
window.hidePageLoading = hidePageLoading;
window.navigateWithFullPageLoading = navigateWithFullPageLoading;
window.showLoginAnimation = showLoginAnimation;
window.hideLoginAnimation = hideLoginAnimation;
window.handleLogout = handleLogout;
window.closeLogoutModal = closeLogoutModal;
window.confirmLogout = confirmLogout;
window.customAlert = customAlert;
window.customConfirm = customConfirm;