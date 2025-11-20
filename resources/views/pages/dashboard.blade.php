@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">
@endpush

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 mt-1">Ringkasan manajemen rusunawa dan potensi retribusi bulanan</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <a href="{{ route('penghuni.index', ['status' => 'aktif', 'sort_by' => 'blok']) }}" class="stat-card bg-white hover-lift">
            <div class="stat-content">
                <div>
                    <p class="stat-label">Penghuni Aktif</p>
                    <p class="stat-value">{{ $penghuniAktif }}</p>
                    <p class="stat-sublabel">orang</p>
                </div>
                <div class="stat-icon bg-blue-100">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('unit.index', ['status' => 'terisi']) }}" class="stat-card bg-white hover-lift">
            <div class="stat-content">
                <div>
                    <p class="stat-label">Unit Terisi</p>
                    <p class="stat-value">{{ $unitTerisi }}</p>
                    <p class="stat-sublabel">dari {{ $totalUnitSeharusnya }} unit</p>
                </div>
                <div class="stat-icon bg-purple-100">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('penghuni.index', ['sort_by' => 'tanggal_masuk_desc', 'status' => 'aktif', 'highlight' => $perubahanRetribusi > 0 ? 'new' : ($perubahanRetribusi < 0 ? 'ending' : 'none')]) }}" class="stat-card bg-white hover-lift">
            <div class="stat-content">
                <div>
                    <p class="stat-label">Potensi Bulan Ini</p>
                    <p class="stat-value text-green-600">Rp {{ number_format($potensiRetribusiBulanIni / 1000000, 1) }}Jt</p>
                    <p class="stat-sublabel">
                        @if($perubahanRetribusi > 0)
                            <span class="trend-up"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg> {{ number_format($perubahanRetribusi, 1) }}%</span>
                        @elseif($perubahanRetribusi < 0)
                            <span class="trend-down"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ number_format(abs($perubahanRetribusi), 1) }}%</span>
                        @else
                            <span class="trend-neutral">0%</span>
                        @endif
                        dibandingkan bulan lalu
                    </p>
                </div>
                <div class="stat-icon bg-green-100">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('penghuni.index', ['kontrak_berakhir' => 30, 'status' => 'aktif']) }}" class="stat-card bg-white hover-lift">
            <div class="stat-content">
                <div>
                    <p class="stat-label">Potensi Bulan Depan</p>
                    <p class="stat-value text-blue-600">Rp {{ number_format($potensiRetribusiBulanDepan / 1000000, 1) }}Jt</p>
                    <p class="stat-sublabel">
                        @if($perubahanBulanDepan > 0)
                            <span class="trend-up"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg> {{ number_format($perubahanBulanDepan, 1) }}%</span>
                        @elseif($perubahanBulanDepan < 0)
                            <span class="trend-down"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ number_format(abs($perubahanBulanDepan), 1) }}%</span>
                        @else
                            <span class="trend-neutral">0%</span>
                        @endif
                        dibandingkan bulan ini
                    </p>
                </div>
                <div class="stat-icon bg-blue-100">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md p-6 mb-6 border border-blue-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Aksi Cepat
                </h2>
                <p class="text-sm text-gray-600 mt-1">Akses menu utama dengan cepat</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('penghuni.index') }}" class="quick-action-card group">
                <div class="quick-action-icon bg-blue-100 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Data Penghuni</h3>
                    <p class="text-xs text-gray-600 mt-1">Lihat & kelola penghuni</p>
                </div>
            </a>

            <a href="{{ route('tambah.penghuni') }}" class="quick-action-card group">
                <div class="quick-action-icon bg-green-100 group-hover:bg-green-600 transition-colors">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-gray-800 group-hover:text-green-600 transition-colors">Tambah Penghuni</h3>
                    <p class="text-xs text-gray-600 mt-1">Daftarkan penghuni baru</p>
                </div>
            </a>

            <a href="{{ route('blacklist.index') }}" class="quick-action-card group">
                <div class="quick-action-icon bg-red-100 group-hover:bg-red-600 transition-colors">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-gray-800 group-hover:text-red-600 transition-colors">Daftar Blacklist</h3>
                    <p class="text-xs text-gray-600 mt-1">Kelola daftar hitam</p>
                </div>
            </a>

            <a href="{{ route('audit') }}" class="quick-action-card group">
                <div class="quick-action-icon bg-purple-100 group-hover:bg-purple-600 transition-colors">
                    <svg class="w-6 h-6 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-gray-800 group-hover:text-purple-600 transition-colors">Jejak Audit</h3>
                    <p class="text-xs text-gray-600 mt-1">Lihat riwayat aktivitas</p>
                </div>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Penghuni Aktif Per Periode</h2>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Bulan:</label>
                    <select id="filterBulan" class="filter-select" onchange="Dashboard.filterPenghuniAktif()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $filterBulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Tahun:</label>
                    <select id="filterTahun" class="filter-select" onchange="Dashboard.filterPenghuniAktif()">
                        @foreach($tahunOptions as $tahun)
                            <option value="{{ $tahun }}" {{ $filterTahun == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                    {{ $jumlahPenghuniAktifPeriod }}
                </span>
            </div>
        </div>
        
        @if($penghuniAktifPeriod->count() > 0)
            <div class="penghuni-table-wrapper">
                <table class="penghuni-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">NIK</th>
                            <th class="text-center">Blok</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Tgl Masuk</th>
                            <th class="text-center">Tgl Keluar</th>
                            <th class="text-center">Keringanan</th>
                            <th class="text-center">Retribusi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penghuniAktifPeriod as $index => $penghuni)
                        @php
                            $kontrak = $penghuni->kontrak;
                            $unit = $kontrak ? $kontrak->unit : null;
                            $blok = $unit ? substr($unit->kode_unit, 0, 1) : '-';
                        @endphp
                        <tr>
                            <td class="text-center text-gray-600 font-medium">{{ $index + 1 }}</td>
                            <td class="text-center font-semibold text-gray-900">{{ $penghuni->nama }}</td>
                            <td class="text-center text-gray-600">{{ $penghuni->nik ?? '-' }}</td>
                            <td class="text-center">
                                @if($blok != '-')
                                    <span class="blok-badge blok-{{ $blok }}">Blok {{ $blok }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center font-medium text-gray-700">{{ $unit ? $unit->kode_unit : '-' }}</td>
                            <td class="text-center text-gray-600">{{ $kontrak && $kontrak->tanggal_masuk ? $kontrak->tanggal_masuk->format('d M Y') : '-' }}</td>
                            <td class="text-center text-gray-600">{{ $kontrak && $kontrak->tanggal_keluar ? $kontrak->tanggal_keluar->format('d M Y') : '-' }}</td>
                            <td class="text-center">
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
                            <td class="text-center font-semibold text-green-600">
                                @if($kontrak && $kontrak->calculated_total > 0)
                                    Rp {{ number_format($kontrak->calculated_total, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('penghuni.show', $penghuni->id) }}" class="inline-block bg-primary text-white text-xs font-bold py-1.5 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-500 text-center">Tidak ada penghuni aktif pada periode ini</p>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Potensi Retribusi Per Blok</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(['A', 'B', 'C', 'D'] as $blok)
            <a href="{{ route('penghuni.index', ['blok' => $blok, 'status' => 'aktif']) }}" class="blok-card-enhanced">
                <div class="blok-card-header">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <h3 class="text-lg font-bold text-white">Blok {{ $blok }}</h3>
                    </div>
                    <span class="blok-badge-enhanced">{{ $unitStatistik[$blok]['persentase_hunian'] }}%</span>
                </div>
                <div class="blok-info-terisi">
                    <span class="text-white opacity-90 text-sm">Terisi</span>
                    <span class="font-semibold text-white">{{ $unitStatistik[$blok]['terisi'] }}/{{ $unitStatistik[$blok]['total'] }}</span>
                </div>
                <div class="blok-progress-enhanced mb-4">
                    <div class="blok-progress-bar-enhanced" style="width: {{ $unitStatistik[$blok]['persentase_hunian'] }}%"></div>
                </div>
                <div class="blok-breakdown">
                    @php $totalLantai = in_array($blok, ['A', 'B', 'C']) ? 5 : 3; @endphp
                    @for($lantai = 1; $lantai <= $totalLantai; $lantai++)
                        @php $dataLantai = $unitStatistik[$blok]['potensi_per_lantai'][$lantai] ?? ['potensi' => 0, 'terisi' => 0]; @endphp
                        <div class="lantai-item">
                            <div class="lantai-label">
                                <span class="lantai-number">Lt {{ $lantai }}</span>
                                <span class="lantai-units-count">{{ $dataLantai['terisi'] }} unit</span>
                            </div>
                            <span class="lantai-value">
                                @if($dataLantai['potensi'] > 0)
                                    Rp {{ number_format($dataLantai['potensi'] / 1000, 0) }}k
                                @else - @endif
                            </span>
                        </div>
                    @endfor
                </div>
                <div class="blok-total">
                    <span class="blok-total-label">Potensi Blok {{ $blok }}</span>
                    <span class="blok-total-value">Rp {{ number_format($unitStatistik[$blok]['potensi'] / 1000000, 1) }}Jt</span>
                </div>
                <div class="blok-detail-link-wrapper">
                    <span>Lihat Detail</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Statistik Hunian Per Blok</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="hunianChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Potensi Pendapatan Per Blok</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="retribusiChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Sebaran Kelompok Umur Penghuni Beserta Keluarga</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="kelompokUmurChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Distribusi Jenis Kelamin</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="jenisKelaminChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Kontrak Baru (30 Hari)</h2>
                <span class="badge badge-info">{{ $kontrakDiperbaruiCount }}</span>
            </div>
            @if($kontrakDiperbarui->count() > 0)
                <div class="space-y-3">
                    @foreach($kontrakDiperbarui as $kontrak)
                        @php 
                            $createdAt = \Carbon\Carbon::parse($kontrak->created_at);
                            $tanggalMasukCarbon = \Carbon\Carbon::parse($kontrak->tanggal_masuk);
                            $selisihHari = $createdAt->diffInDays($tanggalMasukCarbon);
                            $isImport = $selisihHari > 60;
                            
                            if ($isImport) {
                                $waktuRelatif = $tanggalMasukCarbon->locale('id')->diffForHumans();
                            } else {
                                $waktuRelatif = $createdAt->locale('id')->diffForHumans();
                            }
                            
                            $tanggalMasukFormatted = $tanggalMasukCarbon->locale('id')->translatedFormat('d M Y');
                        @endphp
                        <div class="activity-item">
                            <div class="activity-icon bg-cyan-100">
                                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="activity-content">
                                <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-title hover:text-primary transition-colors">{{ $kontrak->penghuni->nama }}</a>
                                <p class="activity-subtitle">Unit {{ $kontrak->unit->kode_unit }} • {{ $waktuRelatif }}</p>
                                <p class="text-xs text-gray-400 mt-1">Mulai: {{ $tanggalMasukFormatted }}</p>
                            </div>
                            <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <p class="text-gray-500 text-sm">Tidak ada kontrak baru dalam 30 hari terakhir</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Kontrak Sudah Lewat</h2>
                <span class="badge badge-danger">{{ $kontrakExpired->count() }}</span>
            </div>
            @if($kontrakExpired->count() > 0)
                <div class="space-y-3">
                    @foreach($kontrakExpired as $kontrak)
                        @php 
                            $tanggalKeluar = $kontrak->tanggal_keluar_aktual ?? $kontrak->tanggal_keluar;
                            $lewatHari = abs(\Carbon\Carbon::today()->diffInDays($tanggalKeluar, false));
                        @endphp
                        <div class="activity-item">
                            <div class="activity-icon bg-red-100">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="activity-content">
                                <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-title hover:text-primary transition-colors">{{ $kontrak->penghuni->nama }}</a>
                                <p class="activity-subtitle text-red-600">Unit {{ $kontrak->unit->kode_unit }} • Lewat {{ $lewatHari }} hari</p>
                            </div>
                            <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-gray-500 text-sm">Semua kontrak masih berlaku</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ route('penghuni.index', ['kontrak_berakhir' => 30, 'status' => 'aktif']) }}" class="text-xl font-bold text-gray-800 hover:text-primary transition-colors">Kontrak Akan Berakhir (30 Hari)</a>
                <span class="badge badge-warning">{{ $kontrakAkanBerakhir->count() }}</span>
            </div>
            @if($kontrakAkanBerakhir->count() > 0)
                <div class="space-y-3">
                    @foreach($kontrakAkanBerakhir as $kontrak)
                        @php $sisaHari = \Carbon\Carbon::today()->diffInDays($kontrak->tanggal_keluar, false); @endphp
                        <div class="activity-item">
                            <div class="activity-icon bg-yellow-100">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="activity-content">
                                <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-title hover:text-primary transition-colors">{{ $kontrak->penghuni->nama }}</a>
                                <p class="activity-subtitle">Unit {{ $kontrak->unit->kode_unit }} • {{ $sisaHari }} hari lagi</p>
                            </div>
                            <a href="{{ route('penghuni.show', $kontrak->penghuni_id) }}" class="activity-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-gray-500 text-sm">Tidak ada kontrak yang akan berakhir</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">
                Penghuni Baru Rusunawa
            </h2>
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                {{ $penghuniTerbaru->count() }}
            </span>
        </div>
        
        @if($penghuniTerbaru->count() > 0)
            <div class="overflow-y-auto max-h-80">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50 sticky top-0">
                        <tr>
                            <th class="p-3 text-center">Nama</th>
                            <th class="p-3 text-center">JK</th>
                            <th class="p-3 text-center">NIK</th>
                            <th class="p-3 text-center">Tgl Masuk</th>
                            <th class="p-3 text-center">Tgl Keluar</th>
                            <th class="p-3 text-center">Keringanan</th>
                            <th class="p-3 text-center">Nilai Retribusi (Rp)</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penghuniTerbaru as $penghuni)
                        @php
                            $kontrak = $penghuni->kontrak;
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 text-center font-medium">{{ $penghuni->nama }}</td>
                            <td class="p-3 text-center">
                                @if($penghuni->jenis_kelamin == 'laki-laki')
                                    <span class="text-blue-600 font-semibold" title="Laki-laki">L</span>
                                @elseif($penghuni->jenis_kelamin == 'perempuan')
                                    <span class="text-pink-600 font-semibold" title="Perempuan">P</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3 text-center">{{ $penghuni->nik ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $kontrak && $kontrak->tanggal_masuk ? $kontrak->tanggal_masuk->format('d M Y') : '-' }}</td>
                            <td class="p-3 text-center">{{ $kontrak && $kontrak->tanggal_keluar ? $kontrak->tanggal_keluar->format('d M Y') : '-' }}</td>
                            <td class="p-3 text-center">
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
                            <td class="p-3 text-center font-medium">
                                @if($kontrak && $kontrak->calculated_total > 0)
                                    {{ number_format($kontrak->calculated_total, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('penghuni.show', $penghuni->id) }}" class="inline-block bg-primary text-white text-xs font-bold py-1 px-3 rounded-lg hover:bg-opacity-90 transition-all" title="Detail">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-500 text-sm">Tidak ada penghuni baru.</p>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.unitStatistik = @json($unitStatistik);
    window.retribusiPerBlok = @json($retribusiPerBlok);
    window.jenisKelaminData = @json($jenisKelaminData);
    window.kelompokUmurData = @json($kelompokUmurData);
</script>
<script src="{{ asset('js/pages/dashboard.js') }}"></script>
@endpush