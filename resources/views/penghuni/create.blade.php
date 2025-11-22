@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penghuni/penghuni-create.css') }}">
@endpush

@section('title', 'Tambah Penghuni Baru')

@section('content')
    @php
        $preSelectedUnit = request()->get('unit');
    @endphp
    
    @if($preSelectedUnit)
    <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold text-blue-900">Unit Terpilih: <span class="text-primary">{{ $preSelectedUnit }}</span></p>
                <p class="text-sm text-blue-700">Rusunawa, Blok, dan Unit sudah dipilih otomatis. Silakan lengkapi data lainnya.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Penghuni Baru</h1>
        <p class="text-gray-500 mt-1">Isi formulir data penghuni dan kontrak hunian di bawah ini.</p>
    </div>

    <form id="createPenghuniForm" action="{{ route('penghuni.storeWithKontrak') }}" method="POST">
        @csrf

        <div class="bg-white p-6 rounded-xl shadow-md mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">Data Penghuni</h2>
            
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
                    <div id="nikStatus" class="mt-2 hidden"></div>
                </div>
    
                <div>
                    <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Nama Lengkap..." required>
                    @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
 
                <div>
                    <label for="tempat_lahir" class="block mb-2 text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Tempat Lahir...">
                    @error('tempat_lahir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
           
                <div>
                    <label for="tanggal_lahir" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    @error('tanggal_lahir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pekerjaan" class="block mb-2 text-sm font-medium text-gray-700">Pekerjaan</label>
                    <input type="text" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan pekerjaan....">
                </div>
                
                <div>
                    <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan No HP...">
                </div>

                <div>
                    <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="laki-laki" {{ old('jenis_kelamin') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
        
                <div class="md:col-span-2">
                    <label for="alamat_ktp" class="block mb-2 text-sm font-medium text-gray-700">Alamat (sesuai KTP)</label>
                    <textarea id="alamat_ktp" name="alamat_ktp" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Masukkan Detail Alamat...">{{ old('alamat_ktp') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">
                Data Anggota Keluarga 
                <span class="text-gray-500 text-sm font-normal">(Opsional)</span>
            </h2>
            
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Data Pasangan (Suami/Istri)</h3>
                    <label class="flex items-center cursor-pointer select-none group">
                        <input type="checkbox" id="adaPasangan" name="ada_pasangan" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" {{ old('ada_pasangan') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700 group-hover:text-primary transition-colors">Ada Pasangan</span>
                    </label>
                </div>
                
                <div id="pasanganForm" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pasangan_nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="pasangan_nama" name="pasangan_nama" value="{{ old('pasangan_nama') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Nama pasangan">
                            @error('pasangan_nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="pasangan_nik" class="block mb-2 text-sm font-medium text-gray-700">NIK</label>
                            <input type="text" id="pasangan_nik" name="pasangan_nik" value="{{ old('pasangan_nik') }}" maxlength="16" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="16 digit NIK">
                            @error('pasangan_nik') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="pasangan_umur" class="block mb-2 text-sm font-medium text-gray-700">Umur</label>
                            <input type="number" id="pasangan_umur" name="pasangan_umur" value="{{ old('pasangan_umur') }}" min="0" max="150" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Umur">
                            @error('pasangan_umur') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="pasangan_jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select id="pasangan_jenis_kelamin" name="pasangan_jenis_kelamin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                <option value="laki-laki" {{ old('pasangan_jenis_kelamin') == 'laki-laki' ? 'selected' : '' }}>Laki-laki (Suami)</option>
                                <option value="perempuan" {{ old('pasangan_jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan (Istri)</option>
                            </select>
                            @error('pasangan_jenis_kelamin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Data Keluarga</h3>
                <div class="flex items-end gap-3 mb-4">
                    <div class="flex-1 max-w-xs">
                        <label for="jumlah_anak" class="block mb-2 text-sm font-medium text-gray-700">Jumlah Keluarga</label>
                        <input type="number" id="jumlah_anak" name="jumlah_anak" value="{{ old('jumlah_anak', 0) }}" min="0" max="10" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="0">
                    </div>
                    <button type="button" id="btnGenerateAnak" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 hover:shadow-md transition-all flex items-center gap-2 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Terapkan
                    </button>
                </div>
                
                <div id="anakFormsContainer" class="space-y-3"></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-3">Data Kontrak Hunian</h2>
            
            <input type="hidden" name="unit_kode" id="unitKodeInput">
       
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Rusun <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" onclick="PenghuniCreate.selectRusun('kraton')" id="rusunKraton" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                        <div class="font-bold text-lg mb-1">Kraton</div>
                        <div class="text-xs text-gray-500">Blok A, B, C</div>
                    </button>
                    <button type="button" onclick="PenghuniCreate.selectRusun('mbr_tegalsari')" id="rusunMBR" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                        <div class="font-bold text-lg mb-1">MBR Tegalsari</div>
                        <div class="text-xs text-gray-500">Blok D</div>
                    </button>
                    <button type="button" onclick="PenghuniCreate.selectRusun('prototipe_tegalsari')" id="rusunPrototipe" class="rusun-box border-2 border-gray-300 rounded-lg p-4 text-center hover:border-primary hover:bg-blue-50 transition-all">
                        <div class="font-bold text-lg mb-1">Prototipe Tegalsari</div>
                        <div class="text-xs text-gray-500">Blok P</div>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Blok <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    <button type="button" onclick="PenghuniCreate.selectBlok('A')" id="blokA" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>A</button>
                    <button type="button" onclick="PenghuniCreate.selectBlok('B')" id="blokB" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>B</button>
                    <button type="button" onclick="PenghuniCreate.selectBlok('C')" id="blokC" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>C</button>
                    <button type="button" onclick="PenghuniCreate.selectBlok('D')" id="blokD" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>D</button>
                    <button type="button" onclick="PenghuniCreate.selectBlok('P')" id="blokP" class="blok-box border-2 border-gray-300 rounded-lg px-6 py-4 font-bold text-lg hover:border-primary hover:bg-blue-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed" disabled>P</button>
                </div>
            </div>

            <div class="mb-6">
                <label for="lantaiUnitSelect" class="block text-sm font-medium text-gray-700 mb-3">Lantai & Unit <span class="text-red-500">*</span></label>
                <select id="lantaiUnitSelect" required disabled class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary disabled:bg-gray-100 disabled:opacity-50">
                    <option value="">Pilih Blok terlebih dahulu</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tanggalMasuk" class="block text-sm font-medium text-gray-700 mb-2">Masa Sewa Masuk <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_masuk" id="tanggalMasuk" value="{{ old('tanggal_masuk') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="tanggalKeluar" class="block text-sm font-medium text-gray-700 mb-2">Masa Sewa Keluar <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_keluar" id="tanggalKeluar" value="{{ old('tanggal_keluar') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="no_sip" class="block text-sm font-medium text-gray-700 mb-2">No. SIP</label>
                    <input type="text" id="no_sip" name="no_sip" value="{{ old('no_sip') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan No. SIP">
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
                    <label for="no_sps" class="block text-sm font-medium text-gray-700 mb-2">No. SPS</label>
                    <input type="text" id="no_sps" name="no_sps" value="{{ old('no_sps') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan No. SPS">
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
                        <input type="text" id="tarifAir" name="tarif_air" value="{{ old('tarif_air') }}" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly required>
                    </div>
                </div>

                <div>
                    <label for="nilaiJaminan" class="block text-sm font-medium text-gray-700 mb-2">
                        Nilai Jaminan <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 font-normal">(3x Tarif Sewa Normal)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3.5 text-gray-500">Rp</span>
                        <input type="text" name="nilai_jaminan" id="nilaiJaminan" readonly
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label for="jumlahRetribusi" class="block text-sm font-medium text-gray-700 mb-2">
                        Tarif Sewa Bulanan
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3.5 text-gray-500">Rp</span>
                        <input type="text" id="jumlahRetribusi"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-100 font-bold text-primary"
                            readonly>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="if(typeof navigateWithFullPageLoading === 'function') { navigateWithFullPageLoading('{{ route('penghuni.index') }}', 'Kembali ke data penghuni...'); } else { window.location.href = '{{ route('penghuni.index') }}'; }" class="bg-gray-200 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="submit" class="bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition-all">
                    Simpan Data
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('js/penghuni/tarif-sewa.js') }}"></script>
<script src="{{ asset('js/penghuni/penghuni-create.js') }}"></script>
@endpush