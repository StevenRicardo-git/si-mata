@extends('layouts.app')

@section('title', 'Import Data Penghuni')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Import Data Penghuni dari Excel</h1>
            <p class="text-gray-500 mt-1">Upload file Excel untuk menambahkan banyak data sekaligus.</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-bold text-blue-900 mb-3">Format Excel yang diperlukan:</p>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-blue-800 mb-2">📋 Kolom Utama:</p>
                                <div class="bg-white rounded p-2 text-xs text-gray-700 space-y-1">
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">NO BLOK</code> - Kode blok (A/B/C/D)</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Lt</code> - Lantai (1/2/3)</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Nama 1</code> - Nama penghuni utama</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">NIK</code> - 16 digit</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">TTL</code> - Tempat, DD-MM-YYYY</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">PEKERJAAN</code></div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">ALAMAT KTP</code></div>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-blue-800 mb-2">📄 Kolom Kontrak:</p>
                                <div class="bg-white rounded p-2 text-xs text-gray-700 space-y-1">
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">MASA SEWA MASUK</code></div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">MASA SEWA KELUAR</code></div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">KERINGANAN</code> - dapat/tidak</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">NO. SPS</code></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-blue-800 mb-2">👨‍👩‍👧‍👦 Data Keluarga:</p>
                                <div class="bg-white rounded p-2 text-xs text-gray-700 space-y-1">
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Nama 2</code> - Pasangan (suami/istri)</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Umur</code> - Umur pasangan</div>
                                    <div><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Hubungan</code> - istri/suami</div>
                                    <div class="pt-2"><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Nama 3, 4, 5...</code> - Anak-anak</div>
                                    <div class="text-gray-600 italic">Format sama: Nama, Umur, Hubungan</div>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-blue-800 mb-2">⚠️ Catatan:</p>
                                <div class="bg-white rounded p-2 text-xs text-gray-700 space-y-1">
                                    <div>• <strong>Nama 1</strong> = Kepala keluarga</div>
                                    <div>• <strong>Nama 2+</strong> = Tidak perlu NIK</div>
                                    <div>• Format: <strong>.xlsx atau .xls</strong></div>
                                    <div>• Maksimal: <strong>5MB</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <form id="importForm" action="{{ route('penghuni.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-primary transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50">
                    <label for="file" class="cursor-pointer flex flex-col items-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    <div class="mt-4">
                            <span class="mt-2 block text-base font-semibold text-gray-900">
                                Klik untuk pilih file Excel
                            </span>
                            <span class="mt-1 block text-sm text-gray-500">atau seret dan lepas file di sini</span>
                            <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls" required>
                    </div>
                    <p id="fileName" class="mt-3 text-sm text-gray-600"></p>
                    <p class="mt-2 text-xs text-gray-400">Format: .xlsx, .xls • Maksimal 5MB</p>
                    </label>
                </div>

                @error('file')
                    <div class="mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded-r">
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    </div>
                @enderror

                @if(session('error'))
                    <div class="mt-3 bg-red-50 border-l-4 border-red-500 p-3 rounded-r">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                <div class="mt-6 flex flex-col sm:flex-row justify-end items-center gap-4">
                    <div class="flex gap-3">
                        <button type="button" onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('penghuni.index') }}', 'Kembali ke data penghuni...'); } else { window.location.href = '{{ route('penghuni.index') }}'; }" class="bg-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="bg-primary text-white font-bold py-2.5 px-6 rounded-lg hover:bg-opacity-90 transition-all flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Import Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/penghuni/penghuni-import.js') }}"></script>
@endpush