const PenghuniEdit = {
    validateNIKInput() {
        const nikInput = document.getElementById('nik');
        if (!nikInput) return;
        
        nikInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });
    },

    validatePhoneInput() {
        const phoneInput = document.getElementById('no_hp');
        if (!phoneInput) return;
        
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    },
    
    setupFormValidation() {
        const form = document.getElementById('editPenghuniForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const nik = document.getElementById('nik').value;
            
            if (nik.length !== 16) {
                e.preventDefault();
                
                if (typeof showToast === 'function') {
                    showToast('NIK harus terdiri dari 16 digit angka!', 'error');
                } else {
                    alert('NIK harus terdiri dari 16 digit angka!');
                }
                
                document.getElementById('nik').focus();
                return false;
            }

            e.preventDefault();

            if (typeof showLoading === 'function') {
                showLoading('Memperbarui data penghuni...');
            }

            setTimeout(() => {
                form.submit();
            }, 1500);
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    PenghuniEdit.validateNIKInput();
    PenghuniEdit.validatePhoneInput();
    PenghuniEdit.setupFormValidation();
});

window.PenghuniEdit = PenghuniEdit;