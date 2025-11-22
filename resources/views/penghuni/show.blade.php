@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penghuni/penghuni-show.css') }}">
@endpush

@section('title', 'Detail Penghuni')

@section('content')
    @php
        $blacklistEntry = \App\Models\Blacklist::where('nik', $penghuni->nik)->first();
    @endphp
    @if($blacklistEntry && $blacklistEntry->status == 'aktif')
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6" role="alert">
        <div class="flex">
            <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zM9 11v-2h2v2H9zm0 4v-2h2v2H9z"/></svg></div>
            <div>
                <p class="font-bold">Penghuni Ini Pernah Di-blacklist</p>
                <p class="text-sm">
                    Penghuni ini diaktifkan kembali pada 

                    <strong>{{ $blacklistEntry->tanggal_aktivasi ? $blacklistEntry->tanggal_aktivasi->format('d M Y') : 'N/A' }}</strong>.
                </p>
                <p class="text-sm mt-1"><strong>Alasan Aktivasi:</strong> {{ $blacklistEntry->alasan_aktivasi }}</p>
            </div>
        </div>
    </div>
    @endif
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Penghuni</h1>
            <p class="text-gray-500 mt-1">Informasi lengkap untuk <span class="font-bold">{{ $penghuni->nama }}</span></p>
        </div>
        <div class="flex gap-3">

            @if($blacklistEntry && $blacklistEntry->status == 'blacklist')
                <button onclick="PenghuniDetail.openReactivateModal('{{ $blacklistEntry->id }}', '{{ addslashes($penghuni->nama) }}', '{{ $penghuni->nik }}')" class="bg-yellow-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-yellow-600 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Aktifkan
                </button>
            
            @else
                <button onclick="PenghuniDetail.openBlacklistModal('{{ $penghuni->id }}', '{{ addslashes($penghuni->nama) }}', 'penghuni')" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    Blacklist
                </button>
            @endif

            <button onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('penghuni.edit', $penghuni->id) }}', 'Membuka form edit...'); } else { window.location.href = '{{ route('penghuni.edit', $penghuni->id) }}'; }" class="bg-gray-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-gray-600 transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Data
            </button>
            
            <button onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('penghuni.index') }}', 'Kembali ke data penghuni...'); } else { window.location.href = '{{ route('penghuni.index') }}'; }" class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Data Penghuni
            </button>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
            <div class="flex items-center mb-6 pb-4 border-b">
                <div class="bg-primary rounded-full w-16 h-16 flex items-center justify-center mr-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $penghuni->nama }}</h2>
                    <p class="text-gray-600 text-sm">NIK: {{ $penghuni->nik }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Tempat, Tanggal Lahir (TTL)</p>
            <p class="text-gray-800 font-medium text-sm">
                @php
                    $ttl = '';
                    if ($penghuni->tempat_lahir) {
                        $ttl .= $penghuni->tempat_lahir;
                    }
                    if ($penghuni->tanggal_lahir) {
                        if ($ttl) $ttl .= ', ';
                        $ttl .= \Carbon\Carbon::parse($penghuni->tanggal_lahir)
                            ->locale('id')
                            ->translatedFormat('d F Y');
                    }
                @endphp
                {{ $ttl ?: '-' }}
            </p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Pekerjaan</p>
            <p class="text-gray-800 font-medium text-sm">{{ $penghuni->pekerjaan ?? '-' }}</p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">No. HP</p>
            <p class="text-gray-800 font-medium text-sm">{{ $penghuni->no_hp ?? '-' }}</p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Jenis Kelamin</p>
            <p class="text-gray-800 font-medium text-sm">
                {{ $penghuni->jenis_kelamin ? ucfirst($penghuni->jenis_kelamin) : '-' }}
            </p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg md:col-span-2 lg:col-span-3">
            <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Alamat (Sesuai KTP)</p>
            <p class="text-gray-800 font-medium text-sm">{{ $penghuni->alamat_ktp ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md mb-6">
    <div class="flex justify-between items-center mb-4 relative z-20">
        <h2 class="text-xl font-bold text-gray-800">Riwayat Kontrak & Unit</h2>
        
        @php
            $hasActiveContract = $penghuni->semuaKontrak->where('status', 'aktif')->isNotEmpty();
            $activeContract = $hasActiveContract ? $penghuni->semuaKontrak->where('status', 'aktif')->first() : null;
            $isBlacklisted = $blacklistEntry && $blacklistEntry->status == 'blacklist';
        @endphp
        
        @if($isBlacklisted)
            <div class="relative group">
                <button 
                    disabled
                    class="bg-red-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed opacity-75 flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    <span>Penghuni Di-Blacklist</span>
                </button>

                <div class="absolute top-full right-0 mt-2 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm w-80 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-10" role="alert">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong class="font-bold">Penghuni masuk daftar hitam!</strong>
                            <p class="mt-1"><strong>Alasan:</strong> {{ $blacklistEntry->alasan_blacklist }}</p>
                            <p class="mt-2">Silakan aktifkan penghuni terlebih dahulu untuk membuat kontrak baru.</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($hasActiveContract)

            <div class="relative group">
                <button 
                    disabled
                    class="bg-red-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed opacity-75 flex items-center text-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Kontrak Aktif Ada</span>
                </button>
                
                <div class="absolute top-full right-0 mt-2 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm w-80 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-10" role="alert">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong class="font-bold">Kontrak aktif sudah ada!</strong>
                            <p class="mt-1">Unit: <strong>{{ $activeContract->unit->kode_unit ?? '-' }}</strong></p>
                            <p class="mt-1">Silakan akhiri kontrak yang ada terlebih dahulu untuk membuat kontrak baru.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <button 
                onclick="PenghuniDetail.openAddKontrakModal()" 
                id="addKontrakBtn"
                class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-all flex items-center text-sm"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Kontrak</span>
            </button>
        @endif
    </div>
    
    <div class="overflow-x-auto relative z-10">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="p-3 text-center" rowspan="2">Unit</th>
                    <th class="p-3 text-center" rowspan="2">Masa Sewa Masuk</th>
                    <th class="p-3 text-center" rowspan="2">Masa Sewa Keluar</th>
                    <th class="p-3 text-center" rowspan="2">No. SIP</th>
                    <th class="p-3 text-center" rowspan="2">Tanggal SIP</th>
                    <th class="p-3 text-center" rowspan="2">No. SPS</th>
                    <th class="p-3 text-center" rowspan="2">Tanggal SPS</th>
                    <th class="p-3 text-center" rowspan="2">Keringanan</th>
                    <th class="p-3 text-center" colspan="3">Nilai Retribusi</th>
                    <th class="p-3 text-center" rowspan="2">Nilai Jaminan (Rp)</th>
                    <th class="p-3 text-center" rowspan="2">Tunggakan</th>
                    <th class="p-3 text-center" rowspan="2">Tanggal Keluar</th>
                    <th class="p-3 text-center" rowspan="2">Alasan</th>
                    <th class="p-3 text-center" rowspan="2">Status</th>
                    <th class="p-3 text-center" rowspan="2">Aksi</th>
                </tr>
                <tr>
                    <th class="p-3 border-l text-center">Sewa (Rp)</th>
                    <th class="p-3 text-center">Air (Rp)</th>
                    <th class="p-3 text-center">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($penghuni->semuaKontrak as $kontrak)
                @php
                    $nominalSewa = $kontrak->nominal_keringanan ?? 0;
                    $tarifAir = $kontrak->tarif_air ?? 0;
                    $jumlahRetribusi = $nominalSewa + $tarifAir;
                    
                    $tanggalKeluarDisplay = null;
                    if ($kontrak->status == 'keluar') {
                        $tanggalKeluarDisplay = $kontrak->tanggal_keluar_aktual ?? $kontrak->tanggal_keluar;
                    }
                @endphp
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-bold text-primary">{{ $kontrak->unit->kode_unit ?? '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->tanggal_masuk ? \Carbon\Carbon::parse($kontrak->tanggal_masuk)->locale('id')->translatedFormat('d M Y') : '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->tanggal_keluar ? \Carbon\Carbon::parse($kontrak->tanggal_keluar)->locale('id')->translatedFormat('d M Y') : '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->no_sip ?? '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->tanggal_sip ? \Carbon\Carbon::parse($kontrak->tanggal_sip)->locale('id')->translatedFormat('d M Y') : '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->no_sps ?? '-' }}</td>
                    <td class="p-3 text-right">{{ $kontrak->tanggal_sps ? \Carbon\Carbon::parse($kontrak->tanggal_sps)->locale('id')->translatedFormat('d M Y') : '-' }}</td>
                    <td class="p-3 text-right">
                        @if($kontrak->keringanan == 'dapat') <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Dapat</span>
                        @elseif($kontrak->keringanan == 'tidak') <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>
                        @else <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">Normal</span>
                        @endif
                    </td>
                    <td class="p-3 border-l text-right">{{ $nominalSewa > 0 ? '' . number_format($nominalSewa, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center">{{ $tarifAir > 0 ? '' . number_format($tarifAir, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center font-semibold text-primary">{{ $jumlahRetribusi > 0 ? '' . number_format($jumlahRetribusi, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center">{{ $kontrak->nilai_jaminan ? '' . number_format($kontrak->nilai_jaminan, 0, ',', '.') : '-' }}</td>
                    <td class="p-3 text-center">{{ $kontrak->tunggakan ? '' . number_format($kontrak->tunggakan, 0, ',', '.') : '' }}</td>
                    <td class="p-3 text-right {{ $kontrak->status == 'keluar' ? 'font-semibold text-red-600' : '' }}">
                        @if($tanggalKeluarDisplay)
                            {{ \Carbon\Carbon::parse($tanggalKeluarDisplay)->locale('id')->translatedFormat('d M Y') }}
                        @else
                            
                        @endif
                    </td>
                    <td class="p-3 max-w-xs truncate text-left" title="{{ $kontrak->alasan_keluar ?? '' }}">{{ $kontrak->alasan_keluar ?? '' }}</td>
                    <td class="p-3">
                        @if ($kontrak->status == 'aktif') <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                        @else <span class="px-2 py-1 text-xs font-sexmibold text-gray-800 bg-gray-100 rounded-full">Keluar</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        @if ($kontrak->status == 'aktif')
                            <button onclick="PenghuniDetail.openTerminateModal(
                                {{ $kontrak->id }}, 
                                '{{ $kontrak->unit->kode_unit }}', 
                                '{{ $kontrak->tanggal_masuk->format('Y-m-d') }}',
                                '{{ $kontrak->tanggal_keluar ? $kontrak->tanggal_keluar->format('Y-m-d') : '' }}'
                            )" class="text-red-500 hover:underline font-medium text-xs">Akhiri</button>
                        @else
                            <a href="{{ route('kontrak.editBaKeluar', $kontrak->id) }}" class="text-red-500 hover:underline font-medium text-xs">
                                BA Keluar
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="17" class="p-4 text-center text-gray-500">Belum ada riwayat kontrak untuk penghuni ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Data Anggota Keluarga</h2>
            <button onclick="PenghuniDetail.openAddKeluargaModal()" class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-all flex items-center text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Anggota
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3">Nama</th>
                        <th class="p-3">NIK</th>
                        <th class="p-3">Umur</th>
                        <th class="p-3">Jenis Kelamin</th>
                        <th class="p-3">Hubungan</th>
                        <th class="p-3">Catatan</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penghuni->keluarga as $anggota)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">{{ $anggota->nama }}</td>
                        <td class="p-3">{{ $anggota->nik ?? '-' }}</td>
                        <td class="p-3">{{ $anggota->umur ? $anggota->umur . ' tahun' : '-' }}</td>
                        <td class="p-3">
                            @if($anggota->jenis_kelamin == 'laki-laki')
                                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Laki-laki</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-pink-800 bg-pink-100 rounded-full">Perempuan</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-semibold @if($anggota->hubungan == 'istri' || $anggota->hubungan == 'suami') text-purple-800 bg-purple-100 @elseif($anggota->hubungan == 'anak') text-green-800 bg-green-100 @else text-orange-800 bg-orange-100 @endif rounded-full">
                                {{ ucfirst($anggota->hubungan) }}
                            </span>
                        </td>
                        <td class="p-3 max-w-xs truncate" title="{{ $anggota->catatan ?? '-' }}">{{ $anggota->catatan ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <button onclick="PenghuniDetail.openEditKeluargaModal({{ $anggota->id }}, '{{ addslashes($anggota->nama) }}', '{{ $anggota->nik }}', {{ $anggota->umur ?? 'null' }}, '{{ $anggota->jenis_kelamin }}', '{{ $anggota->hubungan }}', '{{ addslashes($anggota->catatan ?? '') }}')" class="text-blue-600 hover:underline text-xs">Edit</button>
                                <button onclick="PenghuniDetail.openDeleteKeluargaModal({{ $anggota->id }}, '{{ addslashes($anggota->nama) }}')" class="text-red-600 hover:underline text-xs">Hapus</button>
                                @if($anggota->nik)
                                <button onclick="PenghuniDetail.openBlacklistModal('{{ $anggota->id }}', '{{ addslashes($anggota->nama) }}', 'keluarga')" class="text-red-600 hover:underline text-xs">Blacklist</button>
                                @else
                                <span class="text-gray-400 text-xs" title="Tidak bisa di-blacklist tanpa NIK">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-4 text-center text-gray-500">Belum ada data anggota keluarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="addKontrakModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Tambah Kontrak Baru</h3>
            
            <form id="addKontrakForm" method="POST" action="{{ route('kontrak.store') }}">
                @csrf
                <input type="hidden" name="penghuni_id" value="{{ $penghuni->id }}">
                <input type="hidden" name="unit_kode" id="unitKodeInput">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Rusun <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" onclick="PenghuniDetail.selectRusun('kraton')" id="rusunKraton" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                                <div class="font-bold text-lg mb-1">Kraton</div>
                                <div class="text-xs text-gray-500">Blok A, B, C</div>
                            </button>
                            <button type="button" onclick="PenghuniDetail.selectRusun('mbr_tegalsari')" id="rusunMBR" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                                <div class="font-bold text-lg mb-1">MBR Tegalsari</div>
                                <div class="text-xs text-gray-500">Blok D</div>
                            </button>
                            <button type="button" onclick="PenghuniDetail.selectRusun('prototipe_tegalsari')" id="rusunPrototipe" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                                <div class="font-bold text-lg mb-1">Prototipe Tegalsari</div>
                                <div class="text-xs text-gray-500">Blok P</div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Blok <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <button type="button" onclick="PenghuniDetail.selectBlok('A')" id="blokA" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>A</button>
                            <button type="button" onclick="PenghuniDetail.selectBlok('B')" id="blokB" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>B</button>
                            <button type="button" onclick="PenghuniDetail.selectBlok('C')" id="blokC" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>C</button>
                            <button type="button" onclick="PenghuniDetail.selectBlok('D')" id="blokD" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>D</button>
                            <button type="button" onclick="PenghuniDetail.selectBlok('P')" id="blokP" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>P</button>
                        </div>
                    </div>

                    <div>
                        <label for="lantaiUnitSelect" class="block text-sm font-medium text-gray-700 mb-3">Lantai & Unit <span class="text-red-500">*</span></label>
                        <select id="lantaiUnitSelect" required disabled class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary disabled:bg-gray-100 disabled:opacity-50">
                            <option value="">Pilih Blok terlebih dahulu</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggalMasuk" class="block text-sm font-medium text-gray-700 mb-2">Masa Sewa Masuk <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_masuk" id="tanggalMasuk" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="tanggalKeluar" class="block text-sm font-medium text-gray-700 mb-2">Masa Sewa Keluar <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_keluar" id="tanggalKeluar" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="no_sip" class="block text-sm font-medium text-gray-700 mb-2">No. SIP <span class="text-red-500">*</span></label>
                            <input type="text" id="no_sip" name="no_sip" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan No. SIP">
                        </div>
                        <div>
                            <label for="tanggal_sip" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal SIP 
                                <span class="text-xs text-gray-500 font-normal">(Otomatis)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="date" 
                                    id="tanggal_sip" 
                                    name="tanggal_sip" 
                                    required 
                                    readonly
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed focus:ring-0 focus:border-gray-300"
                                    title="Tanggal SIP otomatis mengikuti tanggal masuk sewa">
                                <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Isi tanggal masuk sewa terlebih dahulu
                            </p>
                        </div>
                        
                        <div>
                            <label for="no_sps" class="block text-sm font-medium text-gray-700 mb-2">No. SPS <span class="text-red-500">*</span></label>
                            <input type="text" id="no_sps" name="no_sps" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan No. SPS">
                        </div>
                        <div>
                            <label for="tanggal_sps" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal SPS 
                                <span class="text-xs text-gray-500 font-normal">(Otomatis)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="date" 
                                    id="tanggal_sps" 
                                    name="tanggal_sps" 
                                    required 
                                    readonly
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed focus:ring-0 focus:border-gray-300"
                                    title="Tanggal SPS otomatis mengikuti tanggal masuk sewa">
                                <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Isi tanggal masuk sewa terlebih dahulu
                            </p>
                        </div>

                        <div>
                            <label for="keringananSelect" class="block text-sm font-medium text-gray-700 mb-2">Keringanan <span class="text-red-500">*</span></label>
                            <select name="keringanan" id="keringananSelect" required disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary disabled:bg-gray-100">
                                <option value="">Pilih Blok terlebih dahulu</option>
                            </select>
                            <input type="hidden" id="nominalKeringanan" name="nominal_keringanan">
                        </div>
                        
                        <div>
                            <label for="tarifAir" class="block text-sm font-medium text-gray-700 mb-2">Tarif Air <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-3.5 text-gray-500">Rp</span>
                                <input type="text" id="tarifAir" name="tarif_air" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly required>
                            </div>
                        </div>

                        <div>
                            <label for="nilaiJaminan" class="block text-sm font-medium text-gray-700 mb-2">
                                Nilai Jaminan <span class="text-red-500">*</span>
                                <span class="text-xs text-gray-500 font-normal">(3x Tarif Sewa Normal)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3.5 text-gray-500 font-medium z-10 pointer-events-none">Rp</span>
                                <input 
                                    type="text" 
                                    name="nilai_jaminan" 
                                    id="nilaiJaminan" 
                                    readonly
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed text-gray-700"
                                    placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label for="jumlahRetribusi" class="block text-sm font-medium text-gray-700 mb-2">
                                Tarif Sewa Bulanan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3.5 text-gray-500">Rp</span>
                                <input type="text" id="jumlahRetribusi"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100 font-bold text-primary"
                                    readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="closeModal('addKontrakModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">Simpan Kontrak</button>
                </div>
            </form>
        </div>
    </div>

    <div id="terminateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">Akhiri Kontrak</h3>
            <p class="text-gray-600 mb-4 text-center">Unit: <span id="unitName" class="font-bold"></span></p>
            
            <form id="terminateForm" method="POST" action=""> 
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="tanggalKeluarTerminate" class="block text-left text-sm font-medium text-gray-700 mb-2">Tanggal Keluar <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggalKeluarTerminate" name="tanggal_keluar" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label for="tunggakan" class="block text-left text-sm font-medium text-gray-700 mb-2">Tunggakan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-gray-500">Rp</span>
                            <input type="text" id="tunggakan" name="tunggakan" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label for="alasanKeluar" class="block text-left text-sm font-medium text-gray-700 mb-2">Alasan Keluar</label>
                        <textarea id="alasanKeluar" name="alasan_keluar" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Masukkan alasan (opsional)"></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('terminateModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition-all">Ya, Akhiri</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addKeluargaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Tambah Anggota Keluarga</h3>
            <form id="addKeluargaForm" method="POST" action="{{ route('keluarga.store') }}">
                @csrf
                <input type="hidden" name="penghuni_id" value="{{ $penghuni->id }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Masukkan nama">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIK (Opsional)</label>
                        <input type="text" name="nik" maxlength="16" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="16 digit NIK">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Umur</label>
                        <input type="number" name="umur" min="0" max="150" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Masukkan umur">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" id="jenisKelamin" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="" disabled selected>Pilih Jenis Kelamin</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan <span class="text-red-500">*</span></label>
                        <select name="hubungan" id="hubungan" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="" disabled selected>Pilih Hubungan</option>
                            <option value="istri">Istri</option>
                            <option value="suami">Suami</option>
                            <option value="anak">Anak</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div id="catatanContainer" class="catatan-field" style="max-height: 0; opacity: 0; margin-top: 0; overflow: hidden;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan <span class="text-red-500">*</span></label>
                        <textarea name="catatan" id="catatan" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Jelaskan hubungan..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('addKeluargaModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editKeluargaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Edit Anggota Keluarga</h3>
            <form id="editKeluargaForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="editNama" name="nama" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan nama">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIK (Opsional)</label>
                        <input type="text" id="editNik" name="nik" maxlength="16" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="16 digit NIK">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Umur</label>
                        <input type="number" id="editUmur" name="umur" min="0" max="150" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan umur">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select id="editJenisKelamin" name="jenis_kelamin" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan <span class="text-red-500">*</span></label>
                        <select id="editHubungan" name="hubungan" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Hubungan</option>
                            <option value="istri">Istri</option>
                            <option value="suami">Suami</option>
                            <option value="anak">Anak</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div id="editCatatanContainer" class="catatan-field" style="max-height: 0; opacity: 0; margin-top: 0; overflow: hidden;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan <span class="text-red-500">*</span></label>
                        <textarea id="editCatatan" name="catatan" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Jelaskan hubungan..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('editKeluargaModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition-all">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteKeluargaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Hapus Anggota Keluarga</h3>
                <p class="text-gray-600 mb-2">Apakah Anda yakin ingin menghapus:</p>
                <p class="text-lg font-bold text-gray-900 mb-6" id="deleteNamaKeluarga"></p>
                <form id="deleteKeluargaForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal('deleteKeluargaModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition-all">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="blacklistModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Konfirmasi Blacklist</h3>
            <p class="text-gray-600 mb-4">Anda akan memasukkan ke daftar hitam: <strong id="blacklistNama" class="text-gray-800"></strong></p>
            <form id="blacklistForm" method="POST" action="">
                @csrf
                <div class_space-y-4>
                    <label for="alasan_blacklist" class="block text-sm font-medium text-gray-700 mb-2">Alasan Blacklist <span class="text-red-500">*</span></label>
                    <textarea id="alasan_blacklist" name="alasan_blacklist" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Jelaskan mengapa orang ini di-blacklist..."></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('blacklistModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition-all">Ya, Blacklist</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="reactivateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full modal-content">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Aktifkan Kembali</h3>
            <p class="text-gray-600 mb-4">Anda akan mengaktifkan: <strong id="reactivateNama" class="text-gray-800"></strong> (<span id="reactivateNik"></span>)</p>
            <form id="reactivateForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class_space-y-4>
                    <label for="alasan_aktivasi" class="block text-sm font-medium text-gray-700 mb-2">Alasan Pengaktifan Kembali <span class="text-red-500">*</span></label>
                    <textarea id="alasan_aktivasi" name="alasan_aktivasi" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Jelaskan mengapa penghuni ini diaktifkan kembali..."></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('reactivateModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">Batal</button>
                    <button type="submit" class="flex-1 bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition-all">Ya, Aktifkan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/penghuni/tarif-sewa.js') }}"></script>
<script src="{{ asset('js/penghuni/penghuni-show.js') }}"></script>
@endpush