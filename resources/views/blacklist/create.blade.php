@extends('layouts.app')

@section('title', 'Tambah Data Blacklist Manual')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Data Blacklist Manual</h1>
        <p class="text-gray-500 mt-1">Masukkan data NIK yang ingin Anda blokir secara manual.</p>
    </div>

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
        <form id="createBlacklistForm" action="{{ route('blacklist.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nik" class="block mb-2 text-sm font-medium text-gray-700">NIK <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="nik" 
                           name="nik" 
                           value="{{ old('nik') }}" 
                           maxlength="16" 
                           inputmode="numeric"
                           pattern="[0-9]*"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" 
                           placeholder="Masukkan 16 Digit NIK..." 
                           required 
                           autocomplete="off">
                    @error('nik') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p id="nikStatus" class="mt-1 text-sm hidden"></p>
                </div>

                <div>
                    <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           value="{{ old('nama') }}" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" 
                           placeholder="Masukkan Nama Lengkap..." 
                           required
                           autocomplete="off">
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="alasan_blacklist" class="block mb-2 text-sm font-medium text-gray-700">Alasan Blacklist <span class="text-red-500">*</span></label>
                    <textarea id="alasan_blacklist" 
                              name="alasan_blacklist" 
                              rows="4" 
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" 
                              placeholder="Jelaskan alasan lengkap mengapa NIK ini diblokir..." 
                              required>{{ old('alasan_blacklist') }}</textarea>
                    @error('alasan_blacklist') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" 
                        onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('blacklist.index') }}', 'Kembali ke daftar hitam...'); } else { window.location.href = '{{ route('blacklist.index') }}'; }" 
                        class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition-all">
                    Simpan ke Blacklist
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/blacklist/blacklist-create.js') }}"></script>
@endpush