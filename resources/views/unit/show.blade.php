@extends('layouts.app')

@section('title', 'Riwayat Unit ' . $kode_unit)

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Riwayat Unit: {{ $kode_unit }}</h1>
            <p class="text-gray-500 mt-1">
                Blok {{ substr($kode_unit, 0, 1) }}, Lantai {{ $lantai }}
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('unit.index') }}', 'Kembali ke manajemen unit...'); } else { window.location.href = '{{ route('unit.index') }}'; }" class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </button>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">Daftar Riwayat Penghuni</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Nama Penghuni</th>
                        <th class="p-3 text-left">NIK</th>
                        <th class="p-3 text-left">Masa Sewa Masuk</th>
                        <th class="p-3 text-left">Masa Sewa Keluar</th>
                        <th class="p-3 text-left">Tanggal Keluar</th>
                        <th class="p-3 text-center">Status Kontrak</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($riwayatKontrak as $kontrak)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">
            
                            {{ $kontrak->penghuni->nama ?? 'N/A' }}
                        </td>
                        <td class="p-3">
                            {{ $kontrak->penghuni->nik ?? 'N/A' }}
                        </td>
                        <td class="p-3">
                            {{ $kontrak->tanggal_masuk ? \Carbon\Carbon::parse($kontrak->tanggal_masuk)->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="p-3">
                            {{ $kontrak->tanggal_keluar ? \Carbon\Carbon::parse($kontrak->tanggal_keluar)->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="p-3">
                
                            {{ $kontrak->tanggal_keluar_aktual ? \Carbon\Carbon::parse($kontrak->tanggal_keluar_aktual)->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="p-3 text-center">
                            @if ($kontrak->status == 'aktif') 
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                            @else 
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Keluar</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
             
                            @if($kontrak->penghuni)
                            <a href="{{ route('penghuni.show', $kontrak->penghuni->id) }}" class="text-primary hover:underline font-medium text-xs">
                                Lihat Detail Penghuni
                            </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            <p class="font-semibold">Belum ada riwayat penghuni untuk unit ini.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection