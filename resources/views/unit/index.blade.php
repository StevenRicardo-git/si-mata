@extends('layouts.app')

@section('title', 'Manajemen Unit')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/unit/unit-index.css') }}">
@endpush

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Unit Rusunawa</h1>
            <p class="text-gray-500 mt-1">Lihat status semua unit rusunawa.</p>
            <p class="text-gray-500 mt-1">Klik blok untuk melihat riwayat penghuni unit.</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="w-full sm:w-1/2 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Tampilkan</span>
                        <select id="perPageSelect" onchange="UnitIndex.changePerPage()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page', 25) == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Data</span>
                    <div class="flex-1 flex gap-2">
                        <input type="text" id="searchInput" placeholder="Cari berdasarkan nama/NIK/kode unit..." value="{{ request('search', '') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                        <button onclick="UnitIndex.search()" class="bg-primary text-white font-semibold px-4 py-2 rounded-lg hover:bg-opacity-90 transition-all text-sm whitespace-nowrap">
                            Cari
                        </button>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Status:</span>
                    <select id="statusFilter" onchange="UnitIndex.filterByStatus()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                        <option value="">Semua</option>
                        <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="terisi" {{ request('status') == 'terisi' ? 'selected' : '' }}>Terisi</option>
                        <option value="perbaikan" {{ request('status') == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter Blok:</span>
                <div class="flex gap-2">
                    <button onclick="UnitIndex.filterByBlok('')" class="blok-filter-btn {{ request('blok') == '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Semua
                    </button>
                    <button onclick="UnitIndex.filterByBlok('A')" class="blok-filter-btn {{ request('blok') == 'A' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok A
                    </button>
                    <button onclick="UnitIndex.filterByBlok('B')" class="blok-filter-btn {{ request('blok') == 'B' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok B
                    </button>
                    <button onclick="UnitIndex.filterByBlok('C')" class="blok-filter-btn {{ request('blok') == 'C' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok C
                    </button>
                    <button onclick="UnitIndex.filterByBlok('D')" class="blok-filter-btn {{ request('blok') == 'D' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} px-4 py-2 rounded-lg font-semibold text-sm transition-all">
                        Blok D
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3 text-center" style="width: 8%;">Blok</th>
                        <th class="p-3 text-center" style="width: 5%;">Lt</th>
                        <th class="p-3 text-center" style="width: 22%;">Nama Penghuni</th>
                        <th class="p-3 text-center" style="width: 18%;">NIK</th>
                        <th class="p-3 text-center" style="width: 47%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($units as $unit)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-center font-semibold text-primary">
                        <a href="{{ route('unit.show', ['kode_unit' => $unit['kode_unit']]) }}" 
                        class="hover:underline" 
                        title="Lihat Riwayat Unit {{ $unit['kode_unit'] }}">
                            {{ $unit['kode_unit'] }}
                        </a>
                    </td>
                    <td class="p-3 text-center">{{ $unit['lantai'] }}</td>
                    <td class="p-3 text-center font-medium">
                        @if($unit['penghuni'])
                            <a href="{{ route('penghuni.show', $unit['penghuni']->id) }}" class="text-primary hover:underline">
                                {{ $unit['penghuni']->nama }}
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        @if($unit['penghuni'])
                            {{ $unit['penghuni']->nik }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="unit-status-container">
                            @if($unit['status'] === 'terisi' && $unit['penghuni'])
                                <a href="{{ route('penghuni.show', $unit['penghuni']->id) }}" class="unit-action-btn btn-detail" title="Detail Penghuni">
                                    Detail Penghuni
                                </a>
                                <div class="status-badge-wrapper">
                                    <span class="unit-status-badge status-terisi">Terisi</span>
                                </div>
                           @elseif($unit['status'] === 'tersedia')
                                <div class="status-badge-wrapper">
                                    <span class="unit-status-badge status-tersedia">Tersedia</span>
                                </div>
                                <a href="{{ route('tambah.penghuni', ['unit' => $unit['kode_unit']]) }}" class="unit-action-btn btn-add" title="Tambah Penghuni ke {{ $unit['kode_unit'] }}">
                                    + Tambah Penghuni
                                </a>
                            @else
                                <div class="status-badge-wrapper">
                                    <span class="unit-status-badge status-tersedia">Tersedia</span>
                                </div>
                                <a href="{{ route('tambah.penghuni', ['unit' => $unit['kode_unit']]) }}" class="unit-action-btn btn-add" title="Tambah Penghuni ke {{ $unit['kode_unit'] }}">
                                    + Tambah Penghuni
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <p class="font-semibold">Data tidak ditemukan</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan
                <span class="font-bold">{{ $units->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-bold">{{ $units->lastItem() ?? 0 }}</span>
                dari
                <span class="font-bold">{{ $units->total() }}</span>
                data
            </p>

            <nav class="flex items-center space-x-1">
                <a href="{{ $units->previousPageUrl() }}" class="{{ $units->onFirstPage() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Sebelumnya</a>

                @php
                    $currentPage = $units->currentPage();
                    $lastPage = $units->lastPage();
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
                        <a href="{{ $units->url($page['page']) }}" class="relative px-3 py-1 text-center text-gray-400 opacity-75 hover:text-primary transition-colors" title="Lompat ke halaman {{ $page['page'] }}">
                            <span class="text-xs">{{ $page['page'] }}</span>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-xs">...</span>
                        </a>
                    @elseif ($page == $currentPage)
                        <span class="px-3 py-1 bg-primary text-white border border-primary rounded-lg z-10">{{ $page }}</span>
                    @else
                        <a href="{{ $units->url($page) }}" class="px-3 py-1 border rounded-lg hover:bg-gray-100">{{ $page }}</a>
                    @endif
                @endforeach

                <a href="{{ $units->nextPageUrl() }}" class="{{ !$units->hasMorePages() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Selanjutnya</a>
            </nav>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/unit/unit-index.js') }}"></script>
@endpush