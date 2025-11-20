@extends('layouts.app')

@section('title', 'Pusat Laporan')

@section('content')

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Pusat Laporan</h1>
        <p class="text-gray-500 mt-1">Buat dan ekspor laporan pendapatan, tunggakan, dan lainnya.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md mb-8">
        <h2 class="text-xl font-bold mb-4">Buat Laporan Baru</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="jenis_laporan" class="block mb-2 text-sm font-medium text-gray-700">Jenis Laporan</label>
                <select id="jenis_laporan" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    <option>Laporan Pendapatan</option>
                    <option>Laporan Tunggakan</option>
                    <option>Rekapitulasi Tagihan per Unit</option>
                </select>
            </div>

            <div>
                <label for="bulan" class="block mb-2 text-sm font-medium text-gray-700">Bulan</label>
                <select id="bulan" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>

            <div>
                <label for="tahun" class="block mb-2 text-sm font-medium text-gray-700">Tahun</label>
                <input type="number" id="tahun" value="2025" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button class="flex items-center bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Tampilkan Laporan
            </button>
        </div>
    </div>
    

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold">Laporan Pendapatan</h3>
                <p class="text-gray-500">Periode: Oktober 2025</p>
            </div>
            <button class="flex items-center bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Excel
            </button>
        </div>
        
        <div class="border-b my-4"></div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-4">Tgl. Bayar</th>
                        <th class="p-4">Nama Penghuni</th>
                        <th class="p-4">Unit</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-4">15 Okt 2025</td>
                        <td class="p-4">Budi Santoso</td>
                        <td class="p-4">A-101</td>
                        <td class="p-4">Tagihan Sewa Okt 2025</td>
                        <td class="p-4 text-right font-semibold">Rp 750.000</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-4">15 Okt 2025</td>
                        <td class="p-4">Citra Lestari</td>
                        <td class="p-4">B-203</td>
                        <td class="p-4">Tagihan Sewa Okt 2025</td>
                        <td class="p-4 text-right font-semibold">Rp 800.000</td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-4">14 Okt 2025</td>
                        <td class="p-4">Agus Wijaya</td>
                        <td class="p-4">C-05</td>
                        <td class="p-4">Cicilan Tunggakan Sep 2025</td>
                        <td class="p-4 text-right font-semibold">Rp 500.000</td>
                    </tr>
                </tbody>
                <tfoot class="font-bold bg-gray-50">
                    <tr>
                        <td colspan="4" class="p-4 text-right">Total Pendapatan</td>
                        <td class="p-4 text-right text-lg text-green-600">Rp 2.050.000</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection