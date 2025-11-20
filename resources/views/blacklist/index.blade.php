@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penghuni/penghuni-index.css') }}">
@endpush

@section('title', 'Daftar Blacklist')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Blacklist</h1>
            <p class="text-gray-500 mt-1">Kelola data penghuni yang masuk daftar hitam.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('blacklist.create') }}" class="flex items-center bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah ke Blacklist
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-300 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-bold text-green-800">{!! session('success') !!}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-300 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-bold text-red-800">{!! session('error') !!}</p>
        </div>
    </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="w-full sm:w-1/2 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Tampilkan</span>
                        <select id="perPageSelect" onchange="BlacklistIndex.changePerPage()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                            <option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Data</span>
                    <div class="flex-1 flex gap-2">
                        <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau NIK..." value="{{ request('search', '') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm">
                        <button onclick="BlacklistIndex.globalSearch()" class="bg-primary text-white font-semibold px-4 py-2 rounded-lg hover:bg-opacity-90 transition-all text-sm whitespace-nowrap">
                            Cari
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3 text-center">Nama</th>
                        <th class="p-3 text-center">NIK</th>
                        <th class="p-3 text-center">Alasan Blacklist</th>
                        <th class="p-3 text-center">Tanggal Blacklist</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Alasan Aktivasi</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blacklist as $item)
                    <tr class="border-b hover:bg-gray-50 data-row">
                        <td class="p-3 text-center font-medium">
                            @php
                                $penghuniExists = \App\Models\Penghuni::where('nik', $item->nik)->exists();
                            @endphp
                            
                            @if($penghuniExists)
                                <a href="{{ route('penghuni.show', $item->nik) }}" 
                                class="text-primary hover:underline hover:text-opacity-80 transition-colors font-semibold">
                                    {{ strtoupper($item->nama) }}
                                </a>
                            @else
                                <span class="text-gray-600 font-semibold cursor-help" title="Data penghuni masih minimal (ditambahkan dari blacklist manual)">
                                    {{ strtoupper($item->nama) }}
                                    <svg class="w-4 h-4 inline ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">{{ $item->nik }}</td>
                        <td class="p-3 text-center max-w-xs truncate" title="{{ $item->alasan_blacklist }}">
                            {{ Str::limit($item->alasan_blacklist, 50) }}
                            @if(strlen($item->alasan_blacklist) > 50)
                                <button onclick="BlacklistIndex.showDetailAlasan('{{ addslashes($item->nama) }}', '{{ addslashes($item->alasan_blacklist) }}', 'Alasan Blacklist')" 
                                        class="text-primary hover:underline text-xs ml-1">
                                    Lihat
                                </button>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            {{ $item->tanggal_blacklist ? $item->tanggal_blacklist->format('d M Y') : '-' }}
                        </td>
                        <td class="p-3 text-center">
                            @if($item->status == 'blacklist')
                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Blacklist</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif Kembali</span>
                            @endif
                        </td>
                        <td class="p-3 text-center max-w-xs truncate" title="{{ $item->alasan_aktivasi ?? '-' }}">
                            @if($item->alasan_aktivasi)
                                {{ Str::limit($item->alasan_aktivasi, 50) }}
                                @if(strlen($item->alasan_aktivasi) > 50)
                                    <button onclick="BlacklistIndex.showDetailAlasan('{{ addslashes($item->nama) }}', '{{ addslashes($item->alasan_aktivasi) }}', 'Alasan Aktivasi Kembali')" 
                                            class="text-primary hover:underline text-xs ml-1">
                                        Lihat
                                    </button>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($item->status == 'blacklist')
                                <button onclick="BlacklistIndex.openReactivateModal('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ $item->nik }}')" 
                                        class="text-green-600 hover:underline font-medium text-xs">
                                    Aktifkan
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-semibold">Belum ada data di blacklist</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan
                <span class="font-bold">{{ $blacklist->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-bold">{{ $blacklist->lastItem() ?? 0 }}</span>
                dari
                <span class="font-bold">{{ $blacklist->total() }}</span>
                data
            </p>    

            <nav class="flex items-center space-x-1">
                <a href="{{ $blacklist->previousPageUrl() }}" class="{{ $blacklist->onFirstPage() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Sebelumnya</a>

                @php
                    $currentPage = $blacklist->currentPage();
                    $lastPage = $blacklist->lastPage();
                @endphp

                @for ($i = 1; $i <= $lastPage; $i++)
                    @if ($i == $currentPage)
                        <span class="px-3 py-1 bg-primary text-white border border-primary rounded-lg z-10">{{ $i }}</span>
                    @else
                        <a href="{{ $blacklist->url($i) }}" class="px-3 py-1 border rounded-lg hover:bg-gray-100">{{ $i }}</a>
                    @endif
                @endfor

                <a href="{{ $blacklist->nextPageUrl() }}" class="{{ !$blacklist->hasMorePages() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Selanjutnya</a>
            </nav>
        </div>
    </div>

    <div id="reactivateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Aktifkan Kembali</h3>
            <p class="text-gray-600 mb-4">Anda akan mengaktifkan: <strong id="reactivateNama" class="text-gray-800"></strong> (<span id="reactivateNik"></span>)</p>
            
            <form id="reactivateForm" method="POST" action="">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <label for="alasan_aktivasi" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Pengaktifan Kembali <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="alasan_aktivasi" 
                        name="alasan_aktivasi" 
                        rows="4" 
                        required 
                        minlength="10"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        placeholder="Jelaskan mengapa penghuni ini diaktifkan kembali (minimal 10 karakter)..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('reactivateModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition-all">
                        Ya, Aktifkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="detailAlasanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 id="detailAlasanTitle" class="text-2xl font-bold text-gray-900 mb-2">Detail Alasan</h3>
            <p class="text-gray-600 mb-4">Nama: <strong id="detailAlasanNama" class="text-gray-800"></strong></p>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p id="detailAlasanContent" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <button type="button" onclick="closeModal('detailAlasanModal')" class="w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">Tutup</button>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/blacklist/blacklist-index.js') }}"></script>
@endpush