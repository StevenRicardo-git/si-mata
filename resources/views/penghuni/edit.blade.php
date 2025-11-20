@extends('layouts.app')

@section('title', 'Edit Data Penghuni')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Data Penghuni</h1>
        <p class="text-gray-500 mt-1">Anda sedang mengubah data untuk: <span class="font-bold">{{ $penghuni->nama }}</span></p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <form id="editPenghuniForm" action="{{ route('penghuni.update', $penghuni->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
                <div>
                    <label for="nik" class="block mb-2 text-sm font-medium text-gray-700">NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik', $penghuni->nik) }}" maxlength="16" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" required placeholder="Masukkan NIK...">
                    @error('nik') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
       
                <div>
                    <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $penghuni->nama) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" required placeholder="Masukkan Nama Lengkap...">
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
       
                <div>
                    <label for="tempat_lahir" class="block mb-2 text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $penghuni->tempat_lahir) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Tempat Lahir...">
                </div>

                <div>
                    <label for="tanggal_lahir" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                        value="{{ old('tanggal_lahir', $penghuni->tanggal_lahir ? $penghuni->tanggal_lahir->format('Y-m-d') : '') }}" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="laki-laki" {{ old('jenis_kelamin', $penghuni->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('jenis_kelamin', $penghuni->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
      
                <div>
                    <label for="pekerjaan" class="block mb-2 text-sm font-medium text-gray-700">Pekerjaan</label>
                    <input type="text" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan', $penghuni->pekerjaan) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Pekerjaan...">
                </div>
      
                <div>
                    <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $penghuni->no_hp) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Nomor HP...">
                </div>
     
                <div class="md:col-span-2">
                    <label for="alamat_ktp" class="block mb-2 text-sm font-medium text-gray-700">Alamat (sesuai KTP)</label>
                    <textarea id="alamat_ktp" name="alamat_ktp" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Detail Alamat...">{{ old('alamat_ktp', $penghuni->alamat_ktp) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('penghuni.show', $penghuni->id) }}', 'Kembali ke detail penghuni...'); } else { window.location.href = '{{ route('penghuni.show', $penghuni->id) }}'; }" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="submit" class="bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/penghuni/penghuni-edit.js') }}"></script>
@endpush