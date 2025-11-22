const Dashboard = {
    charts: {},
    
    init() {
        this.animateNumbers();
        this.animateProgressBars();
        this.setupRefresh();
        this.setupTooltips();
        this.setupDashboardLinks();
        this.setupFilters();
        this.initCharts();
        KelompokUmurFilter.init();
    },
    
    initCharts() {
        this.initHunianChart();
        this.initRetribusiChart();
        this.initJenisKelaminChart();
        this.initKelompokUmurChart();
    },
    
    initHunianChart() {
        const ctx = document.getElementById('hunianChart');
        if (!ctx) return;
        
        const unitStatistik = window.unitStatistik || {};
        
        const labels = ['Blok A', 'Blok B', 'Blok C', 'Blok D'];
        const terisi = [
            unitStatistik.A?.terisi || 0,
            unitStatistik.B?.terisi || 0,
            unitStatistik.C?.terisi || 0,
            unitStatistik.D?.terisi || 0
        ];
        const kosong = [
            unitStatistik.A?.kosong || 0,
            unitStatistik.B?.kosong || 0,
            unitStatistik.C?.kosong || 0,
            unitStatistik.D?.kosong || 0
        ];
        
        const hunianLabelPlugin = {
            id: 'hunianLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const terisiMeta = chart.getDatasetMeta(0);
                
                terisiMeta.data.forEach((bar, index) => {
                    const terisiValue = terisi[index];
                    const totalValue = terisi[index] + kosong[index];
                    
                    ctx.font = 'bold 14px sans-serif';
                    ctx.fillStyle = '#fff';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(terisiValue + '/' + totalValue, bar.x, bar.y + (bar.height / 2));
                });
            }
        };
        
        this.charts.hunian = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Terisi',
                        data: terisi,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2
                    },
                    {
                        label: 'Kosong',
                        data: kosong,
                        backgroundColor: 'rgba(229, 231, 235, 0.8)',
                        borderColor: 'rgb(209, 213, 219)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 12, weight: 'bold' },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = terisi[context.dataIndex] + kosong[context.dataIndex];
                                const persen = ((context.parsed.y / total) * 100).toFixed(1);
                                return context.dataset.label + ': ' + context.parsed.y + ' unit (' + persen + '%)';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { weight: 'bold' } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        max: 99,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + ' unit';
                            }
                        }
                    }
                }
            },
            plugins: [hunianLabelPlugin]
        });
    },
    
    initRetribusiChart() {
        const ctx = document.getElementById('retribusiChart');
        if (!ctx) return;
        
        const retribusiPerBlok = window.retribusiPerBlok || {};
        
        const data = [
            retribusiPerBlok.A || 0,
            retribusiPerBlok.B || 0,
            retribusiPerBlok.C || 0,
            retribusiPerBlok.D || 0
        ];
        
        const total = data.reduce((a, b) => a + b, 0);
        const retribusiLabelPlugin = {
            id: 'retribusiLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.getDatasetMeta(0).data.forEach((datapoint, index) => {
                    const { x, y } = datapoint.tooltipPosition();
                    
                    const value = data[index];
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    const valueInJt = (value / 1000000).toFixed(1);
                    
                    ctx.font = 'bold 13px sans-serif';
                    ctx.fillStyle = '#fff';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('Rp ' + valueInJt + 'Jt', x, y - 8);
                    ctx.fillText('(' + percentage + '%)', x, y + 8);
                });
            }
        };
        
        this.charts.retribusi = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Blok A', 'Blok B', 'Blok C', 'Blok D'],
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: { size: 12, weight: 'bold' },
                            padding: 8,
                            boxWidth: 15,
                            boxHeight: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const persen = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': Rp ' + (value / 1000000).toFixed(2) + 'Jt (' + persen + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [retribusiLabelPlugin]
        });
    },

    initJenisKelaminChart() {
        const ctx = document.getElementById('jenisKelaminChart');
        if (!ctx) return;
        
        const jenisKelaminData = window.jenisKelaminData || { laki_laki: 0, perempuan: 0 };
        const data = [jenisKelaminData.perempuan, jenisKelaminData.laki_laki];
        const total = data.reduce((a, b) => a + b, 0);
        
        const percentagePlugin = {
            id: 'percentageLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.getDatasetMeta(0).data.forEach((datapoint, index) => {
                    const { x, y } = datapoint.tooltipPosition();
                    const value = data[index];
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    ctx.shadowColor = 'rgba(255, 255, 255, 1)';
                    ctx.shadowBlur = 4;
                    ctx.shadowOffsetX = 1;
                    ctx.shadowOffsetY = 1;
                    ctx.fillStyle = '#312b2bd3';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = 'bold 18px sans-serif';
                    ctx.fillText(value + ' orang', x, y - 12);
                    ctx.font = 'bold 16px sans-serif';
                    ctx.fillText('(' + percentage + '%)', x, y + 12);
                    ctx.shadowColor = 'transparent';
                    ctx.shadowBlur = 0;
                });
            }
        };
        
        this.charts.jenisKelamin = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Perempuan', 'Laki-laki'], 
                datasets: [{
                    data: data, 
                    backgroundColor: [
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(236, 72, 153)',
                        'rgb(59, 130, 246)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                rotation: 0, 
                circumference: 360,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        reverse: true, 
                        labels: {
                            font: { size: 14, weight: 'bold' },
                            padding: 15,
                            boxWidth: 20,
                            boxHeight: 20,
                            usePointStyle: false
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const persen = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + value + ' orang (' + persen + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [percentagePlugin]
        });
    },

    initKelompokUmurChart() {
        const ctx = document.getElementById('kelompokUmurChart');
        if (!ctx) return;
        
        const kelompokUmurData = window.kelompokUmurData || {
            '0-5': 0,
            '5-12': 0,
            '12-18': 0,
            '18-60': 0,
            '60+': 0
        };
        
        const labels = [['0-5', 'tahun'],
                        ['5-12', 'tahun'], 
                        ['12-18', 'tahun'], 
                        ['18-60', 'tahun'], 
                        ['60+', 'tahun']];
        const data = [
            kelompokUmurData['0-5'],
            kelompokUmurData['5-12'],
            kelompokUmurData['12-18'],
            kelompokUmurData['18-60'],
            kelompokUmurData['60+']
        ];

        const total = data.reduce((a, b) => a + b, 0);
        const barValuePlugin = {
            id: 'barValueLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const maxValue = Math.max(...data);
                
                chart.getDatasetMeta(0).data.forEach((bar, index) => {
                    const value = data[index];
                    const isHighValue = value > maxValue * 0.6;
                    
                    ctx.font = 'bold 14px sans-serif';
                    ctx.textAlign = 'center';
                    
                    if (isHighValue) {
                        ctx.fillStyle = '#fff';
                        ctx.textBaseline = 'top';
                        ctx.fillText(value + ' orang', bar.x, bar.y + 10);
                    } else {
                        ctx.fillStyle = '#1F2937';
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(value + ' orang', bar.x, bar.y - 5);
                    }
                });
            }
        };
        
        this.charts.kelompokUmur = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Orang',
                    data: data,
                    backgroundColor: [
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(168, 85, 247)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                const persen = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return 'Jumlah: ' + value + ' orang (' + persen + '%)';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { weight: 'bold', size: 11 },
                            maxRotation: 0,
                            minRotation: 0,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: Math.ceil(Math.max(...data) / 5),
                            callback: function(value) {
                                return value + ' orang';
                            }
                        }
                    }
                }
            },
            plugins: [barValuePlugin]
        });
    },
    
    setupFilters() {
        const filterButton = document.getElementById('filterButton');
        
        if (filterButton) {
            filterButton.addEventListener('click', () => {
                this.filterPenghuniAktif();
            });
        }
    },

    filterPenghuniAktif() {
        const bulanEl = document.getElementById('filterBulan');
        const tahunEl = document.getElementById('filterTahun');

        if (!bulanEl || !tahunEl) return;

        const bulan = bulanEl.value;
        const tahun = tahunEl.value;
        
        if (typeof showLoading === 'function') {
            const namaBulan = new Date(tahun, bulan - 1).toLocaleString('id-ID', { month: 'long' });
            showLoading(`Memuat penghuni aktif ${namaBulan} ${tahun}...`);
        }
        
        const url = new URL(window.location.href);
        url.searchParams.set('filter_bulan', bulan);
        url.searchParams.set('filter_tahun', tahun);
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 1000);
    },

    animateNumbers() {
        const statValues = document.querySelectorAll('.stat-value');
        
        statValues.forEach(element => {
            const text = element.textContent;
            
            if (!text.includes('Rp') && !isNaN(text.replace(/,/g, ''))) {
                const target = parseInt(text.replace(/,/g, ''));
                this.countUp(element, 0, target, 1000);
            }
        });
    },
    
    countUp(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            
            element.textContent = Math.floor(current).toLocaleString('id-ID');
        }, 16);
    },
    
    animateProgressBars() {
        const progressBars = document.querySelectorAll('.blok-progress-bar, .blok-progress-bar-enhanced');
        
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.width = width;
            }, 300);
        });
    },
    
    setupRefresh() {
        const refreshButton = document.querySelector('#refreshDashboard');
        
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.refreshData();
            });
        }
    },
    
    async refreshData() {
        const refreshBtn = document.querySelector('#refreshDashboard');
        
        if (refreshBtn) {
            refreshBtn.classList.add('loading');
            refreshBtn.disabled = true;
        }
        
        try {
            if (typeof showLoading === 'function') {
                showLoading('Memuat ulang data...');
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } catch (error) {
            if (typeof showToast === 'function') {
                showToast('Gagal memuat data terbaru', 'error');
            }
        } finally {
            if (refreshBtn) {
                refreshBtn.classList.remove('loading');
                refreshBtn.disabled = false;
            }
        }
    },
    
    setupDashboardLinks() {
        const statCards = document.querySelectorAll('.stat-card[href], .blok-card-enhanced[href], .activity-item a[href], .activity-link[href]');
        
        statCards.forEach(card => {
            card.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if (!href || href === '#' || href.startsWith('javascript:') || this.target === '_blank') {
                    return;
                }
                
                e.preventDefault();
                
                if (typeof showPageLoading === 'function') {
                    showPageLoading();
                }
            
                setTimeout(() => {
                    window.location.href = href;
                }, 1200);
            });
        });
    },
    
    setupTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    },
    
    showTooltip(target, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.id = 'active-tooltip';
        
        document.body.appendChild(tooltip);
        
        const rect = target.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
        tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
        tooltip.style.background = '#1F2937';
        tooltip.style.color = 'white';
        tooltip.style.padding = '0.5rem 0.75rem';
        tooltip.style.borderRadius = '0.375rem';
        tooltip.style.fontSize = '0.875rem';
        tooltip.style.zIndex = '9999';
        tooltip.style.pointerEvents = 'none';
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 0.2s ease';
        
        setTimeout(() => {
            tooltip.style.opacity = '1';
        }, 10);
    },
    
    hideTooltip() {
        const tooltip = document.getElementById('active-tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
            setTimeout(() => tooltip.remove(), 200);
        }
    },
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    },

    formatPercentage(value) {
        return `${value > 0 ? '+' : ''}${value.toFixed(1)}%`;
    }
};

    const KelompokUmurFilter = {
    originalKelompokUmurData: null,
    debounceTimer: null,
    countdownInterval: null,
    remainingSeconds: 0,
    
    init() {
        this.setupEventListeners();
        this.storeOriginalData();
    },
    
    setupEventListeners() {
        const umurAwal = document.getElementById('umurAwal');
        const umurAkhir = document.getElementById('umurAkhir');
        
        if (umurAwal) {
            umurAwal.addEventListener('input', () => this.debouncedAutoUpdate());
        }
        if (umurAkhir) {
            umurAkhir.addEventListener('input', () => this.debouncedAutoUpdate());
        }
    },
    
    debouncedAutoUpdate() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
        
        this.remainingSeconds = 5;
        this.showCountdown();
        
        this.countdownInterval = setInterval(() => {
            this.remainingSeconds--;
            this.showCountdown();
            
            if (this.remainingSeconds <= 0) {
                clearInterval(this.countdownInterval);
                this.hideCountdown();
            }
        }, 1000);
        
        this.debounceTimer = setTimeout(() => {
            this.autoUpdatePreview();
            this.hideCountdown();
        }, 5000);
    },
    
    showCountdown() {
        const previewContent = document.getElementById('previewContent');
        
        if (this.remainingSeconds > 0) {
            previewContent.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 font-bold text-2xl mb-3">
                        ${this.remainingSeconds}
                    </div>
                    <p class="text-gray-600 text-sm">Filter akan dijalankan dalam <strong>${this.remainingSeconds}</strong> detik...</p>
                    <div class="w-64 mx-auto mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-1000" style="width: ${(this.remainingSeconds / 5) * 100}%"></div>
                    </div>
                </div>
            `;
        }
    },
    
    hideCountdown() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    },
    
    storeOriginalData() {
        this.originalKelompokUmurData = window.kelompokUmurData ? { ...window.kelompokUmurData } : null;
    },
    
    openModal() {
        const modal = document.getElementById('kelompokUmurFilterModal');
        if (modal) {
            modal.classList.remove('hidden');
            
            document.getElementById('umurAwal').value = '';
            document.getElementById('umurAkhir').value = '';
            document.getElementById('previewContent').innerHTML = '<p class="text-gray-500 text-sm text-center py-8">Masukkan usia untuk melihat preview (otomatis setelah 5 detik)</p>';
        }
    },
    
    closeModal() {
        const modal = document.getElementById('kelompokUmurFilterModal');
        if (modal) {
            modal.classList.add('hidden');
            
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = null;
            }
            this.hideCountdown();
            
            if (Dashboard.charts.kelompokUmur && this.originalKelompokUmurData) {
                Dashboard.charts.kelompokUmur.data.datasets[0].data = Object.values(this.originalKelompokUmurData);
                Dashboard.charts.kelompokUmur.update('none');
            }
        }
    },
    
    async autoUpdatePreview() {
        const umurAwal = parseInt(document.getElementById('umurAwal').value);
        let umurAkhir = parseInt(document.getElementById('umurAkhir').value);
        const previewContent = document.getElementById('previewContent');
        
        if ((umurAwal || umurAwal === 0) && (!umurAkhir && umurAkhir !== 0)) {
            umurAkhir = umurAwal;
        }
        
        if (!umurAwal && umurAwal !== 0) {
            previewContent.innerHTML = '<p class="text-gray-500 text-sm text-center py-8">Masukkan usia untuk melihat preview (otomatis setelah 5 detik)</p>';
            return;
        }
        
        if (umurAkhir && umurAwal > umurAkhir) {
            previewContent.innerHTML = '<p class="text-red-500 text-sm font-semibold text-center py-8">⚠️ Usia awal harus lebih kecil atau sama dengan usia akhir</p>';
            return;
        }

        previewContent.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 text-sm mt-2">Memuat data...</p>
            </div>
        `;
        
        try {
            const requestBody = {
                umur_awal: umurAwal
            };
            
            const umurAkhirInput = document.getElementById('umurAkhir').value;
            if (umurAkhirInput && umurAkhirInput.trim() !== '') {
                requestBody.umur_akhir = umurAkhir;
            }
            
            const response = await fetch('/api/dashboard/filter-kelompok-umur', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(requestBody)
            });
            
            const result = await response.json();
            
            if (!result.success) {
                previewContent.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-500 font-semibold">❌ ${result.message || 'Terjadi kesalahan'}</p>
                    </div>
                `;
                return;
            }
            
            const { kelompok_umur, total_orang, breakdown, rentang } = result.data;
            
            if (total_orang === 0) {
                previewContent.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-yellow-600 font-semibold mb-2">⚠️ Tidak ada data dalam rentang ini</p>
                        <p class="text-gray-600 text-sm">Rentang: <strong>${rentang.text}</strong></p>
                    </div>
                `;
                
                if (Dashboard.charts.kelompokUmur && this.originalKelompokUmurData) {
                    Dashboard.charts.kelompokUmur.data.datasets[0].data = Object.values(this.originalKelompokUmurData);
                    Dashboard.charts.kelompokUmur.update('none');
                }
                return;
            }
            
            let html = `
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border-2 border-blue-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-gray-900">📊 Preview Data Real</h4>
                        <span class="px-3 py-1 bg-blue-600 text-white text-sm font-bold rounded-full">${total_orang} orang</span>
                    </div>
                    <div class="bg-white rounded-lg p-3 mb-3">
                        <p class="text-sm text-gray-600">Rentang usia yang dipilih:</p>
                        <p class="text-2xl font-bold text-blue-600">${rentang.text}</p>
                    </div>
                    <div class="space-y-2">
            `;
            
            const colorMap = {
                '0-5': 'purple',
                '5-12': 'blue',
                '12-18': 'green',
                '18-60': 'yellow',
                '60+': 'red'
            };
            
            breakdown.forEach(item => {
                const color = colorMap[item.kategori] || 'gray';
                html += `
                    <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-${color}-500"></div>
                            <span class="text-sm font-medium text-gray-700">${item.label}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-500">${item.persentase}%</span>
                            <span class="font-bold text-gray-900">${item.jumlah} orang</span>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
            
            previewContent.innerHTML = html;
            
            this.currentFilteredData = kelompok_umur;
            
            if (Dashboard.charts.kelompokUmur) {
                Dashboard.charts.kelompokUmur.data.datasets[0].data = Object.values(kelompok_umur);
                Dashboard.charts.kelompokUmur.update('none');
            }
            
        } catch (error) {
            console.error('Error fetching filter data:', error);
            previewContent.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-500 font-semibold">❌ Gagal memuat data</p>
                    <p class="text-gray-600 text-sm mt-2">${error.message}</p>
                </div>
            `;
        }
    },
    
    filterKelompokUmurByRange(awal, akhir) {
        const original = this.originalKelompokUmurData || window.kelompokUmurData;
        const filtered = { '0-5': 0, '5-12': 0, '12-18': 0, '18-60': 0, '60+': 0 };
        
        if (awal <= 5 && akhir >= 0) filtered['0-5'] = original['0-5'];
        if (awal <= 12 && akhir >= 5) filtered['5-12'] = original['5-12'];
        if (awal <= 18 && akhir >= 12) filtered['12-18'] = original['12-18'];
        if (awal <= 60 && akhir >= 18) filtered['18-60'] = original['18-60'];
        if (awal <= 150 && akhir >= 60) filtered['60+'] = original['60+'];
        
        return filtered;
    },
    
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10B981' : '#EF4444'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },
    
    setQuickRange(awal, akhir) {
        document.getElementById('umurAwal').value = awal;
        document.getElementById('umurAkhir').value = akhir;
        this.debouncedAutoUpdate();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    KelompokUmurFilter.init();
    Dashboard.init();
});

window.Dashboard = Dashboard;
window.KelompokUmurFilter = KelompokUmurFilter;