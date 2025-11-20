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

document.addEventListener('DOMContentLoaded', () => {
    Dashboard.init();
});

window.Dashboard = Dashboard;