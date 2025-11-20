@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penghuni/penghuni-index.css') }}">
@endpush

@section('title', 'Manajemen Penghuni')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Penghuni</h1>
            <p class="text-gray-500 mt-1">
                @if(request('search'))
                    <span class="font-semibold text-primary">Hasil pencarian: "{{ request('search') }}"</span>
                    @if($semuaPenghuni->total() > 0)
                        <span class="text-gray-400">—</span>
                        <span class="text-green-600 font-semibold">{{ $semuaPenghuni->total() }} data ditemukan</span>
                    @else
                        <span class="text-gray-400">—</span>
                        <span class="text-red-600 font-semibold">Tidak ada hasil</span>
                    @endif
                @elseif(request('kontrak_berakhir'))
                    Menampilkan kontrak yang akan berakhir dalam {{ request('kontrak_berakhir') }} hari
                @elseif(request('bulan_berakhir'))
                    Menampilkan kontrak berakhir bulan {{ \Carbon\Carbon::create(null, request('bulan_berakhir'))->locale('id')->translatedFormat('F Y') }}
                @elseif(request('tahun_berakhir'))
                    Menampilkan kontrak berakhir tahun {{ request('tahun_berakhir') }}
                @elseif(request('sort_by') == 'tanggal_masuk_desc')
                    Menampilkan penghuni terbaru (diurutkan dari yang baru masuk)
                @else
                    Kelola data penghuni rusunawa.
                @endif
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('penghuni.exportExcel', request()->all()) }}" target="_blank" class="flex items-center bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export Excel
            </a>

            <a id="btn-import-excel" href="{{ route('penghuni.import') }}" class="flex items-center bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Import Excel
            </a>
            <a id="btn-tambah-penghuni" href="{{ route('tambah.penghuni') }}" class="flex items-center bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Penghuni
            </a>
        </div>
    </div>

    @if(request('search') && $semuaPenghuni->total() > 0)
    <div class="mb-6 bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-bold text-blue-900 mb-2">🔍 Panduan Warna Hasil Pencarian</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-gray-700"><span class="font-semibold text-green-700">Hijau</span> = Data ditemukan & Status Aktif</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-gray-700"><span class="font-semibold text-red-700">Merah</span> = Data ditemukan & Status Keluar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-gray-700"><span class="font-semibold text-blue-700">Biru</span> = Data lainnya (hasil filter)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(request('highlight') && request('highlight') != 'none')
    <div class="mb-6 {{ request('highlight') == 'new' ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }} border-l-4 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-6 h-6 {{ request('highlight') == 'new' ? 'text-green-600' : 'text-red-600' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if(request('highlight') == 'new')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                @endif
            </svg>
            <div>
                <p class="font-bold {{ request('highlight') == 'new' ? 'text-green-800' : 'text-red-800' }}">
                    @if(request('highlight') == 'new')
                        📈 Penghuni Baru Ditampilkan
                    @else
                        📉 Kontrak yang Berakhir Ditampilkan
                    @endif
                </p>
                <p class="text-sm {{ request('highlight') == 'new' ? 'text-green-700' : 'text-red-700' }}">
                    @if(request('highlight') == 'new')
                        Data diurutkan berdasarkan tanggal masuk terbaru (yang baru masuk muncul di atas)
                    @else
                        Data diurutkan berdasarkan tanggal keluar terdekat (yang akan berakhir muncul di atas)
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="filter-berakhir-wrapper mb-6">
        <div class="filter-berakhir-container-compact {{ request('kontrak_berakhir') || request('bulan_berakhir') || request('tahun_berakhir') ? 'has-active-filter' : '' }}">
            <div class="filter-berakhir-toggle-compact">
                <div class="filter-icon-text">
                    <div class="filter-icon-compact">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="filter-text-compact">
                        <span class="filter-title">Filter Kontrak Berakhir</span>
                        <span class="filter-status">
                            @if(request('kontrak_berakhir'))
                                {{ request('kontrak_berakhir') }} Hari ({{ $semuaPenghuni->total() }} data)
                            @elseif(request('bulan_berakhir'))
                                {{ \Carbon\Carbon::create(null, request('bulan_berakhir'))->locale('id')->translatedFormat('F Y') }} ({{ $semuaPenghuni->total() }} data)
                            @elseif(request('tahun_berakhir'))
                                Tahun {{ request('tahun_berakhir') }} ({{ $semuaPenghuni->total() }} data)
                            @else
                                Arahkan kursor untuk menampilkan filter 
                            @endif
                        </span>
                    </div>
                </div>
                
                <div class="filter-content-right">
                    <div class="filter-chevron-compact">
                        <svg id="filterChevron" 
                            class="chevron-icon {{ request('kontrak_berakhir') || request('bulan_berakhir') || request('tahun_berakhir') ? 'expanded' : '' }}" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    
                    <div id="filterButtonsContainer" 
                        class="filter-options-compact {{ request('kontrak_berakhir') || request('bulan_berakhir') || request('tahun_berakhir') ? 'expanded' : '' }}">
                        <div class="filter-row-compact">
                            <div class="filter-section-compact">
                                <label class="filter-label-compact">Per Hari:</label>
                                <div class="filter-buttons-group">
                                    <button onclick="event.stopPropagation(); PenghuniIndex.filterKontrakBerakhir(15)" 
                                            class="filter-btn-compact {{ request('kontrak_berakhir') == 15 ? 'active' : '' }}">
                                        15 Hari
                                    </button>
                                    <button onclick="event.stopPropagation(); PenghuniIndex.filterKontrakBerakhir(30)" 
                                            class="filter-btn-compact {{ request('kontrak_berakhir') == 30 ? 'active' : '' }}">
                                        30 Hari
                                    </button>
                                    <button onclick="event.stopPropagation(); PenghuniIndex.filterKontrakBerakhir(60)" 
                                            class="filter-btn-compact {{ request('kontrak_berakhir') == 60 ? 'active' : '' }}">
                                        60 Hari
                                    </button>
                                    <button onclick="event.stopPropagation(); PenghuniIndex.filterKontrakBerakhir(90)" 
                                            class="filter-btn-compact {{ request('kontrak_berakhir') == 90 ? 'active' : '' }}">
                                        90 Hari
                                    </button>
                                </div>
                            </div>
                   
                            <div class="filter-section-compact">
                                <label class="filter-label-compact">Per Bulan:</label>
                                <select onchange="PenghuniIndex.filterByBulan(this.value)" class="filter-select-compact">
                                    <option value="">Pilih Bulan</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('bulan_berakhir') == $i ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $i)->locale('id')->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                           
                            <div class="filter-section-compact">
                                <label class="filter-label-compact">Per Tahun:</label>
                                <select onchange="PenghuniIndex.filterByTahun(this.value)" class="filter-select-compact">
                                    <option value="">Pilih Tahun</option>
                                    @for($y = now()->year - 1; $y <= now()->year + 3; $y++)
                                        <option value="{{ $y }}" {{ request('tahun_berakhir') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            
                            @if(request('kontrak_berakhir') || request('bulan_berakhir') || request('tahun_berakhir'))
                            <button onclick="event.stopPropagation(); PenghuniIndex.resetKontrakBerakhirFilter()" 
                                    class="filter-reset-compact">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="w-full sm:w-1/2 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Tampilkan</span>
                        <select id="perPageSelect" onchange="PenghuniIndex.changePerPage()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Data</span>
                    <div class="flex-1 flex gap-2 relative">
                        <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau NIK..." value="{{ request('search', '') }}" class="flex-1 px-4 py-2 pr-32 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm transition-all">
                        
                        <div id="searchTypingIndicator" class="hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Mencari...</span>
                        </div>
                        
                        <button onclick="PenghuniIndex.globalSearch()" class="bg-primary text-white font-semibold px-4 py-2 rounded-lg hover:bg-opacity-90 transition-all text-sm whitespace-nowrap">
                            Cari
                        </button>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Status:</span>
                    <select id="statusFilter" onchange="PenghuniIndex.filterByStatus()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm {{ request('status') == 'aktif' ? 'status-aktif' : (request('status') == 'tidak_aktif' ? 'status-tidak-aktif' : '') }}">
                        <option value="">Semua</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter Blok:</span>
                <div class="flex gap-2">
                    <button onclick="PenghuniIndex.filterByBlok('')" class="blok-filter-btn {{ request('blok') == '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Semua
                    </button>
                    <button onclick="PenghuniIndex.filterByBlok('A')" class="blok-filter-btn {{ request('blok') == 'A' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok A
                    </button>
                    <button onclick="PenghuniIndex.filterByBlok('B')" class="blok-filter-btn {{ request('blok') == 'B' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok B
                    </button>
                    <button onclick="PenghuniIndex.filterByBlok('C')" class="blok-filter-btn {{ request('blok') == 'C' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok C
                    </button>
                    <button onclick="PenghuniIndex.filterByBlok('D')" class="blok-filter-btn {{ request('blok') == 'D' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok D
                    </button>
                </div>
            </div>
        </div>

        @if(request('kontrak_berakhir') || request('bulan_berakhir') || request('tahun_berakhir'))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($semuaPenghuni as $penghuni)
            @php
                $kontrak = $penghuni->kontrak;
                $kodeUnit = $kontrak && $kontrak->unit ? $kontrak->unit->kode_unit : '-';
                
                $sisaHari = '-';
                $isUrgent = false;
                if ($kontrak && $kontrak->tanggal_keluar) {
                    $today = \Carbon\Carbon::today();
                    $tanggalKeluar = \Carbon\Carbon::parse($kontrak->tanggal_keluar);
                    $diff = $today->diffInDays($tanggalKeluar, false);
                    $sisaHari = $diff;
                    $isUrgent = $diff <= 3;
                }

                $cardClass = 'bg-white border-2';
                $btnClass = 'bg-primary hover:bg-opacity-90';
                $showTelaKeluar = false;
                
                if (request('search') != '') {
                    if (in_array($penghuni->id, $searchedIds)) {
                        $isAktif = $kontrak && $kontrak->status == 'aktif';
                        $cardClass = $isAktif ? 'card-search-active' : 'card-search-inactive';
                        $btnClass = $isAktif ? 'btn-detail-active' : 'btn-detail-inactive';
                        $showTelaKeluar = !$isAktif;
                    } else {
                        $cardClass = 'card-search-other';
                        $btnClass = 'bg-primary hover:bg-opacity-90';
                    }
                } else {
                    $cardClass .= $isUrgent ? ' border-red-300 shadow-red-100' : ' border-yellow-300 shadow-yellow-100';
                    $btnClass = 'bg-primary hover:bg-opacity-90';
                }
            @endphp
            <div class="{{ $cardClass }} rounded-xl p-5 shadow-lg hover:shadow-xl transition-all">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <a href="{{ route('penghuni.show', $penghuni->id) }}" class="text-lg font-bold text-gray-800 hover:text-primary transition-colors">
                            {{ $penghuni->nama }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">NIK: {{ $penghuni->nik }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            JK: {{ $penghuni->jenis_kelamin == 'laki-laki' ? 'Laki-laki' : ($penghuni->jenis_kelamin == 'perempuan' ? 'Perempuan' : '-') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="font-semibold text-primary">{{ $kodeUnit }}</span>
                    </div>
                    
                    @if($kontrak && $kontrak->tanggal_keluar)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Berakhir: {{ \Carbon\Carbon::parse($kontrak->tanggal_keluar)->translatedFormat('d F Y') }}</span>
                    </div>
                    @endif
                </div>

                @if($showTelaKeluar)
                <div class="bg-red-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-red-800 font-bold text-lg">Telah Keluar</span>
                    </div>
                </div>
                @else
                <div class="bg-{{ $isUrgent ? 'red' : 'yellow' }}-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-{{ $isUrgent ? 'red' : 'yellow' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-{{ $isUrgent ? 'red' : 'yellow' }}-800 font-bold text-lg">{{ $sisaHari }} Hari Lagi</span>
                    </div>
                </div>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('penghuni.show', $penghuni->id) }}" class="flex-1 text-white text-center py-2 px-4 rounded-lg transition-all font-semibold {{ $btnClass }}">
                        Lihat Detail
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full p-8 text-center text-gray-500">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-semibold">Tidak ada kontrak yang akan berakhir</p>
            </div>
            @endforelse
        </div>
        @else
        
        @php
            $hasAnyKeluarStatus = $semuaPenghuni->contains(function($p) {
                return $p->kontrak && $p->kontrak->status == 'keluar';
            });
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">Kode Unit</th>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">Lantai</th>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">Nama</th>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">JK</th>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">NIK</th>
                        <th class="p-3 text-center border-r table-header-primary" colspan="2">Tanggal Kontrak</th>
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">Keringanan</th>
                        <th class="p-3 text-center border-r table-header-primary" colspan="3">Nilai Retribusi</th>
                        @if($hasAnyKeluarStatus)
                        <th class="p-3 text-center border-r table-header-primary" rowspan="2">Tanggal Keluar</th>
                        @endif
                        <th class="p-3 text-center table-header-primary" rowspan="2">Aksi</th>
                    </tr>
                    <tr>
                        <th class="p-3 text-center border-r table-header-green">Awal</th>
                        <th class="p-3 text-center border-r table-header-red">Akhir</th>
                        <th class="p-3 text-center border-r table-header-primary">Sewa</th>
                        <th class="p-3 text-center border-r table-header-primary">Air</th>
                        <th class="p-3 text-center border-r table-header-primary">Jumlah</th>
                    </tr>
                </thead>
                <tbody id="penghuniTableBody">
                @forelse($semuaPenghuni as $penghuni)
                @php
                    $kontrak = $penghuni->kontrak;
                    $kodeUnit = $kontrak && $kontrak->unit ? $kontrak->unit->kode_unit : '-';
                    $lantai = '-';
                    
                    if ($kodeUnit != '-' && strlen($kodeUnit) >= 4) {
                        $unitNum = (int)substr($kodeUnit, 1);
                        $lantai = (int)floor($unitNum / 100);
                    }
                    
                    $nominalSewa = $kontrak ? ($kontrak->calculated_sewa ?? 0) : 0;
                    $tarifAir = $kontrak ? ($kontrak->calculated_air ?? 0) : 0;
                    $totalRetribusi = $nominalSewa + $tarifAir;
                    
                    $isKontrakAktif = ($kontrak && $kontrak->status == 'aktif');
                    $isKontrakSelesai = ($kontrak && $kontrak->status == 'keluar');
                    $showSip = $isKontrakAktif; 
                    $showBaKeluar = $isKontrakSelesai;
                    $tanggalKeluarDisplay = null;
                    if ($kontrak && $isKontrakSelesai) {
                        $tanggalKeluarDisplay = $kontrak->tanggal_keluar_aktual ?? $kontrak->tanggal_keluar;
                    }
                    
                    \Carbon\Carbon::setLocale('id');
                    
                    $rowHighlightClass = '';
                    
                    if (request('search') != '') {
                        if (in_array($penghuni->id, $searchedIds)) {
                            $isAktif = $kontrak && $kontrak->status == 'aktif';
                            $rowHighlightClass = $isAktif 
                                ? 'search-result-active' 
                                : 'search-result-inactive';
                        } else {
                            $rowHighlightClass = 'search-result-other';
                        }
                    } elseif ($highlightType == 'new') {
                        $rowHighlightClass = 'highlight-new';
                    } elseif ($highlightType == 'ending') {
                        $rowHighlightClass = 'highlight-ending';
                    }
                @endphp
                
                @php
                    $inlineStyle = '';
                    
                    if ($rowHighlightClass == '') {
                        $inlineStyle = 'background: rgba(239, 246, 255, 0.3);';
                    }
                @endphp
                
                <tr class="border-b table-body-row {{ $rowHighlightClass }}" 
                    @if($inlineStyle) style="{{ $inlineStyle }}" @endif
                    onmouseover="
                        if (!this.classList.contains('search-result-active') && 
                            !this.classList.contains('search-result-inactive') && 
                            !this.classList.contains('search-result-other') && 
                            !this.classList.contains('highlight-new') && 
                            !this.classList.contains('highlight-ending')) {
                            this.style.background = 'rgba(239, 246, 255, 0.5)';
                        }
                    "
                    onmouseout="
                        if (!this.classList.contains('search-result-active') && 
                            !this.classList.contains('search-result-inactive') && 
                            !this.classList.contains('search-result-other') && 
                            !this.classList.contains('highlight-new') && 
                            !this.classList.contains('highlight-ending')) {
                            this.style.background = 'rgba(239, 246, 255, 0.3)';
                        }
                    ">
                    <td class="p-3 text-center font-semibold text-primary border-r">{{ $kodeUnit }}</td>
                    <td class="p-3 text-center border-r">{{ $lantai }}</td>
                    <td class="p-3 text-center font-medium border-r">
                        {{ $penghuni->nama }}
                        @if(!request('search'))
                            @if($highlightType == 'new' && $kontrak && $kontrak->tanggal_masuk)
                                @php
                                    $daysSinceEntry = \Carbon\Carbon::now()->diffInDays($kontrak->tanggal_masuk);
                                @endphp
                                @if($daysSinceEntry <= 30)
                                    <span class="ml-2 badge-new-entry">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Baru
                                    </span>
                                @endif
                            @endif
                            @if($highlightType == 'ending' && $kontrak && $kontrak->tanggal_keluar)
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $tanggalKeluar = \Carbon\Carbon::parse($kontrak->tanggal_keluar);
                                    $daysUntilEnd = $today->diffInDays($tanggalKeluar, false);
                                @endphp
                                @if($daysUntilEnd <= 30 && $daysUntilEnd >= 0)
                                    <span class="ml-2 badge-ending-soon">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Akan Berakhir
                                    </span>
                                @endif
                            @endif
                        @endif
                    </td>
                    <td class="p-3 text-center border-r">
                        @if($penghuni->jenis_kelamin == 'laki-laki')
                            <span class="text-blue-600 font-semibold">L</span>
                        @elseif($penghuni->jenis_kelamin == 'perempuan')
                            <span class="text-pink-600 font-semibold">P</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3 text-center border-r">{{ $penghuni->nik }}</td>
                    <td class="p-3 text-center border-r">
                        @if($kontrak && $kontrak->tanggal_masuk)
                            {{ \Carbon\Carbon::parse($kontrak->tanggal_masuk)->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3 text-center border-r">
                        @if($kontrak && $kontrak->tanggal_keluar)
                            {{ \Carbon\Carbon::parse($kontrak->tanggal_keluar)->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3 text-center border-r">
                        @if($kontrak && $kontrak->keringanan)
                            @if($kontrak->keringanan == 'dapat') 
                                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Dapat</span>
                            @elseif($kontrak->keringanan == 'tidak') 
                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>
                            @else 
                                <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">Normal</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3 text-center border-r">{{ $nominalSewa > 0 ? 'Rp ' . number_format($nominalSewa, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center border-r">{{ $tarifAir > 0 ? 'Rp ' . number_format($tarifAir, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center font-semibold border-r">{{ $totalRetribusi > 0 ? 'Rp ' . number_format($totalRetribusi, 0, ',', '.') : '-' }}</td>
                    @if($hasAnyKeluarStatus)
                    <td class="p-3 text-center border-r {{ $isKontrakSelesai ? 'font-semibold text-red-600' : '' }}">
                        @if($tanggalKeluarDisplay)
                            {{ \Carbon\Carbon::parse($tanggalKeluarDisplay)->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    <td class="p-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('penghuni.show', $penghuni->id) }}" class="inline-block bg-primary text-white text-xs font-bold py-1.5 px-3 rounded-lg hover:bg-opacity-90 transition-all" title="Detail">
                                Detail
                            </a>
                            @if($showSip)
                            <a href="{{ route('kontrak.cetakSip', $kontrak->id) }}" 
                            target="_blank" 
                            class="inline-block bg-green-600 text-white text-xs font-bold py-1.5 px-3 rounded-lg hover:bg-green-700 transition-all" 
                            title="Cetak SIP">
                                SIP
                            </a>
                            @endif
                            @if($showBaKeluar)
                            <a href="{{ route('kontrak.editBaKeluar', $kontrak->id) }}" 
                            class="inline-block bg-red-600 text-white text-xs font-bold py-1.5 px-3 rounded-lg hover:bg-red-700 transition-all" 
                            title="BA Keluar">
                                BA Keluar
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="{{ $hasAnyKeluarStatus ? '13' : '12' }}" class="p-8 text-center text-gray-500">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="font-semibold">Belum ada data penghuni</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan
                <span class="font-bold">{{ $semuaPenghuni->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-bold">{{ $semuaPenghuni->lastItem() ?? 0 }}</span>
                dari
                <span class="font-bold">{{ $semuaPenghuni->total() }}</span>
                data
            </p>    

            <nav class="flex items-center space-x-1">
                <a href="{{ $semuaPenghuni->previousPageUrl() }}" class="{{ $semuaPenghuni->onFirstPage() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Sebelumnya</a>

                @php
                    $currentPage = $semuaPenghuni->currentPage();
                    $lastPage = $semuaPenghuni->lastPage();
                    $window = 2;
                    $showPages = [];

                    if ($lastPage <= 7) {
                        for ($i = 1; $i <= $lastPage; $i++) $showPages[] = $i;
                    } else {
                        $showPages[] = 1;
                        if ($currentPage > $window + 2) {
                            $showPages[] = ['type' => 'gap', 'page' => floor(($currentPage - $window + 1) / 2)];
                        }
                        for ($i = max(2, $currentPage - $window); $i <= min($lastPage - 1, $currentPage + $window); $i++) {
                            $showPages[] = $i;
                        }
                        if ($currentPage < $lastPage - $window - 1) {
                            $showPages[] = ['type' => 'gap', 'page' => floor(($currentPage + $window + $lastPage) / 2)];
                        }
                        if ($lastPage > 1) $showPages[] = $lastPage;
                    }
                @endphp

                @foreach (array_unique($showPages, SORT_REGULAR) as $page)
                    @if (is_array($page))
                        <a href="{{ $semuaPenghuni->url($page['page']) }}" class="relative px-3 py-1 text-center text-gray-400 opacity-75 hover:text-primary transition-colors" title="Lompat ke halaman {{ $page['page'] }}">
                            <span class="text-xs">{{ $page['page'] }}</span>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-xs">...</span>
                        </a>
                    @elseif ($page == $currentPage)
                        <span class="px-3 py-1 bg-primary text-white border border-primary rounded-lg z-10">{{ $page }}</span>
                    @else
                        <a href="{{ $semuaPenghuni->url($page) }}" class="px-3 py-1 border rounded-lg hover:bg-gray-100">{{ $page }}</a>
                    @endif
                @endforeach

                <a href="{{ $semuaPenghuni->nextPageUrl() }}" class="{{ !$semuaPenghuni->hasMorePages() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Selanjutnya</a>
            </nav>
        </div>
    </div>

    @if(session('success'))
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
        style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); animation: fadeInBackdrop 0.3s ease-out;" 
        data-modal-timestamp="{{ time() }}">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 animate-slideUp">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Berhasil!</h3>
                <p class="text-gray-600 mb-6">{{ session('success') }}</p>
                
                <button onclick="event.stopPropagation(); PenghuniIndex.closeSuccessModal(false);" class="bg-green-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-green-700 transition-all transform hover:scale-105">
                    OK, Mengerti
                </button>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script src="{{ asset('js/penghuni/penghuni-index.js') }}"></script>
@endpush