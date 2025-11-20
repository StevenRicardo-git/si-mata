document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file');
    const fileName = document.getElementById('fileName');
    const importForm = document.getElementById('importForm');
    
    if (!dropZone || !fileInput || !fileName || !importForm) {
        return;
    }

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-primary', 'bg-blue-50');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-primary', 'bg-blue-50');
        }, false);
    });

    dropZone.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFiles(files);
        }
    }, false);

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) {
            return;
        }
        
        const file = files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

        const validExtensions = ['.xlsx', '.xls'];
        if (!validExtensions.includes(fileExtension)) {
            showError('File harus berformat .xlsx atau .xls');
            fileInput.value = '';
            return;
        }

        if (parseFloat(fileSize) > 5) {
            showError('Ukuran file maksimal 5MB');
            fileInput.value = '';
            return;
        }

        showSuccess(file.name, fileSize, fileExtension);
    }
    
    function showError(message) {
        fileName.innerHTML = '';

        const errorDiv = document.createElement('div');
        errorDiv.className = 'mt-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-r';
        errorDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-red-700 font-medium text-sm">${message}</span>
            </div>
        `;
        
        fileName.appendChild(errorDiv);
        resetDropZone();
    }
    
    function showSuccess(name, size, extension) {
        fileName.innerHTML = '';

        const previewDiv = document.createElement('div');
        previewDiv.className = 'mt-4 p-4 bg-green-50 border-2 border-green-500 rounded-lg animate-slideUp';
        previewDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-12 h-12 flex-shrink-0" viewBox="0 0 48 48" fill="none">
                    <path d="M38 44H10C8.9 44 8 43.1 8 42V6C8 4.9 8.9 4 10 4H28L40 16V42C40 43.1 39.1 44 38 44Z" fill="#21A366"/>
                    <path d="M28 4L40 16H30C28.9 16 28 15.1 28 14V4Z" fill="#107C41"/>
                    <path d="M16 24L20 28L16 32H22L24 30L26 32H32L28 28L32 24H26L24 26L22 24H16Z" fill="white"/>
                </svg>
                
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate mb-1" title="${name}">
                        ${name}
                    </p>
                    <p class="text-xs text-gray-600 mb-2">
                        ${size} MB • ${extension.toUpperCase()}
                    </p>
                    
                    <div class="w-full bg-green-200 rounded-full h-2 mb-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-500" style="width: 100%"></div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-xs text-green-700 font-medium">File siap untuk diimport</span>
                    </div>
                </div>
            </div>
        `;
        
        fileName.appendChild(previewDiv);

        dropZone.classList.remove('bg-gray-50', 'hover:bg-blue-50', 'border-gray-300');
        dropZone.classList.add('border-green-500', 'bg-green-50');
    }
    
    function resetDropZone() {
        dropZone.classList.remove('border-green-500', 'bg-green-50', 'border-primary', 'bg-blue-50');
        dropZone.classList.add('bg-gray-50', 'hover:bg-blue-50', 'border-gray-300');
    }

    importForm.addEventListener('submit', function(e) {
        if (!fileInput.files || fileInput.files.length === 0) {
            e.preventDefault();
            alert('Silakan pilih file Excel terlebih dahulu!');
            return false;
        }

        showPersistentLoading();

        return true;
    });
    
    function showPersistentLoading() {
        const existingOverlay = document.getElementById('importLoadingOverlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        const overlay = document.createElement('div');
        overlay.id = 'importLoadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        `;
        
        overlay.innerHTML = `
            <div style="background: white; padding: 40px 50px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; max-width: 400px;">
                <div style="margin-bottom: 24px;">
                    <svg style="width: 80px; height: 80px; margin: 0 auto; color: #21A366;" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <h3 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 12px;">
                    Mengimport Data Excel
                </h3>
                
                <p style="color: #6b7280; font-size: 15px; margin-bottom: 24px; line-height: 1.6;">
                    Mohon tunggu, sistem sedang memproses file Excel Anda...<br>
                    <strong>Jangan tutup halaman ini!</strong>
                </p>
                
                <div style="background: #f3f4f6; border-radius: 12px; padding: 16px; margin-top: 20px;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                        <svg style="width: 24px; height: 24px; color: #21A366;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span style="color: #374151; font-weight: 600; font-size: 14px;">Proses Import:</span>
                    </div>
                    
                    <div style="text-align: left; color: #6b7280; font-size: 13px; line-height: 1.8;">
                        <div style="margin-bottom: 6px;">✓ Membaca file Excel...</div>
                        <div style="margin-bottom: 6px;">✓ Validasi data penghuni...</div>
                        <div style="margin-bottom: 6px;">✓ Menyimpan ke database...</div>
                        <div style="color: #21A366; font-weight: 600;">⏳ Sedang diproses...</div>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: center; gap: 6px; margin-top: 24px;">
                    <div style="width: 10px; height: 10px; background: #21A366; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both;"></div>
                    <div style="width: 10px; height: 10px; background: #21A366; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: 0.16s;"></div>
                    <div style="width: 10px; height: 10px; background: #21A366; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: 0.32s;"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }
});