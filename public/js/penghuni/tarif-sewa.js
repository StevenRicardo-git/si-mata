const TarifService = {
    tarifKeringanan: null,
    tarifAir: null,
    isLoaded: false,

    async init() {
        if (this.isLoaded) {
            return { keringanan: this.tarifKeringanan, air: this.tarifAir };
        }

        try {
            const response = await fetch('/api/tarif-keringanan', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Gagal memuat tarif: ${response.status}`);
            }

            const data = await response.json();
            
            this.tarifKeringanan = data.tarif;
            this.tarifAir = data.tarif_air;
            this.isLoaded = true;

            return { keringanan: this.tarifKeringanan, air: this.tarifAir };
        } catch (error) {
            if (typeof showToast === 'function') {
                showToast('Menggunakan tarif default', 'warning');
            }
            
            return this.getDefaultTarif();
        }
    },

    getDefaultTarif() {
        this.tarifKeringanan = {
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
        };
        
        this.tarifAir = {
            kraton: 60000,
            mbr_tegalsari: 70000,
            prototipe_tegalsari: 0
        };
        
        this.isLoaded = true;
        return { keringanan: this.tarifKeringanan, air: this.tarifAir };
    }
};

window.TarifService = TarifService;