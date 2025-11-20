@extends('layouts.app')

@section('title', 'Kelola Data Disperkim')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
<style>
    .sortable-ghost {
        opacity: 0.4;
        background: #f0f0f0;
    }
    .sortable-drag {
        opacity: 0.8;
    }
    .drag-handle {
        cursor: move;
        cursor: grab;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Data Disperkim</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <svg class="inline w-6 h-6 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Kepala Dinas
            </h2>
            <button onclick="openAddModal('kepala_dinas')" class="bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Kepala Dinas
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pangkat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kepalaDinas as $kd)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $kd->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $kd->jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $kd->nip ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $kd->pangkat ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $kd->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $kd->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick='openEditModal(@json($kd))' class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="toggleStatus({{ $kd->id }}, '{{ $kd->aktif ? 'nonaktifkan' : 'aktifkan' }}')" class="text-yellow-600 hover:text-yellow-800 mr-3" title="{{ $kd->aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete({{ $kd->id }}, '{{ $kd->nama }}')" class="text-red-600 hover:text-red-800" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data Kepala Dinas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <svg class="inline w-6 h-6 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Staff
            </h2>
            <button onclick="openAddModal('staff')" class="bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Staff
            </button>
        </div>

        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <strong>Tips:</strong> Anda dapat mengubah urutan staff dengan drag & drop. Geser icon ☰ untuk memindahkan posisi.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-12">Urutan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                @php
                    $aktifStaff = $staff->where('aktif', true);
                    $nonAktifStaff = $staff->where('aktif', false);
                @endphp

                <tbody id="staffTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($aktifStaff as $s)
                    <tr class="hover:bg-gray-50" data-id="{{ $s->id }}">
                        <td class="px-4 py-3 text-center">
                            <span class="drag-handle text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $s->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $s->jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $s->nip ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Aktif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick='openEditModal(@json($s))' class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="toggleStatus({{ $s->id }}, 'nonaktifkan')" class="text-yellow-600 hover:text-yellow-800 mr-3" title="Nonaktifkan">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete({{ $s->id }}, '{{ $s->nama }}')" class="text-red-600 hover:text-red-800" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>

                <tbody class="bg-gray-50 divide-y divide-gray-200">
                    @foreach($nonAktifStaff as $s)
                    <tr class="bg-gray-50" data-id="{{ $s->id }}">
                        <td class="px-4 py-3 text-center">
                            <span class="text-gray-300">
                                <svg class="w-6 h-6 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-500 italic">{{ $s->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 italic">{{ $s->jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 italic">{{ $s->nip ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Nonaktif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick='openEditModal(@json($s))' class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="toggleStatus({{ $s->id }}, 'aktifkan')" class="text-green-600 hover:text-green-800 mr-3" title="Aktifkan">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete({{ $s->id }}, '{{ $s->nama }}')" class="text-red-600 hover:text-red-800" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                @if($staff->isEmpty())
                <tbody class="bg-white">
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data Staff
                        </td>
                    </tr>
                </tbody>
                @endif
            </table>
        </div>
    </div>
</div>

<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="backdrop-filter: blur(8px);">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full mx-4 animate-slideUp">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="addModalTitle">Tambah Data</h2>
        <form action="{{ route('disperkim.store') }}" method="POST" id="addForm">
            @csrf
            <input type="hidden" name="tipe" id="addTipe">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                <input type="text" name="nama" id="addNama" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Nama">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan</label>
                <input type="text" name="jabatan" id="addJabatan" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Jabatan">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">NIP</label>
                <input type="text" name="nip" id="addNip" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan NIP">
            </div>

            <div id="addPangkatField" class="hidden">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pangkat</label>
                    <input type="text" name="pangkat" id="addPangkat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Pangkat">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="backdrop-filter: blur(8px);">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full mx-4 animate-slideUp">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Data</h2>
        <form action="" method="POST" id="editForm">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                <input type="text" name="nama" id="editNama" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Nama">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan</label>
                <input type="text" name="jabatan" id="editJabatan" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan Jabatan">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">NIP</label>
                <input type="text" name="nip" id="editNip" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan NIP">
            </div>

            <div id="editPangkatField" class="hidden">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pangkat</label>
                    <input type="text" name="pangkat" id="editPangkat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"placeholder="Masukkan Pangkat">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="backdrop-filter: blur(8px);">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 animate-slideUp">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus <strong id="deleteNama"></strong>?</p>
            
            <form action="" method="POST" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="toggleStatusForm" action="" method="POST" style="display: none;">
    @csrf
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>

    window.updateUrutanUrl = '{{ route("disperkim.updateUrutan") }}';
</script>
<script src="{{ asset('js/disperkim/disperkim-index.js') }}"></script>
@endpush