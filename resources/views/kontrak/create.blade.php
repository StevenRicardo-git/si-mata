@extends('layouts.app')

@section('title', 'Tambah Kontrak Baru')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Kontrak Baru</h1>
        <p class="text-gray-500 mt-1">Membuat kontrak baru untuk penghuni: <span class="font-bold">{{ $penghuni->nama }}</span></p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <form action="{{ route('kontrak.store') }}" method="POST">
            @csrf
    
            <input type="hidden" name="penghuni_id" value="{{ $penghuni->id }}">

            <div class="space-y-4">

                <div>
                    <label for="unit_id" class="block mb-2 text-sm font-medium text-gray-700">Pilih Unit (Hanya menampilkan unit kosong)</label>
                    <select id="unit_id" name="unit_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" required>
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($unitKosong as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->kode_unit }} - (Tarif: Rp {{ number_format($unit->tarif_sewa) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal_masuk" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Masuk</label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" required>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('penghuni.show', $penghuni->id) }}" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Batal</a>
                <button type="submit" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-all">Simpan Kontrak</button>
            </div>
        </form>
    </div>
@endsection