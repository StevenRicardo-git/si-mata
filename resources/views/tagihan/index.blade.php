@extends('layouts.app')

@section('title', 'Manajemen Tagihan')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Tagihan</h1>
            <p class="text-gray-500 mt-1">Kelola, cari, dan catat semua tagihan penghuni.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-2">
            <button class="flex items-center bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Excel
            </button>
            <button class="flex items-center bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Tagihan
            </button>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <div class="mb-4">
            <input type="text" placeholder="Cari berdasarkan nama, NIK, atau unit..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="p-4">Nama Penghuni</th>
                        <th class="p-4">Unit</th>
                        <th class="p-4">Periode</th>
                        <th class="p-4">Total Tagihan</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">Budi Santoso</td>
                        <td class="p-4">A-101</td>
                        <td class="p-4">Okt 2025</td>
                        <td class="p-4 font-semibold">Rp 750.000</td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Lunas</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-gray-500 hover:text-primary">Detail</button>
                        </td>
                    </tr>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">Citra Lestari</td>
                        <td class="p-4">B-203</td>
                        <td class="p-4">Okt 2025</td>
                        <td class="p-4 font-semibold">Rp 800.000</td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Belum Lunas</span>
                        </td>
                        <td class="p-4 text-center space-x-2">
                             <button class="text-primary hover:underline">Bayar</button>
                             <button class="text-gray-500 hover:text-primary">Detail</button>
                        </td>
                    </tr>
                     <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">Agus Wijaya</td>
                        <td class="p-4">C-05</td>
                        <td class="p-4">Sep 2025</td>
                        <td class="p-4 font-semibold">Rp 500.000</td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Cicilan</span>
                        </td>
                        <td class="p-4 text-center space-x-2">
                             <button class="text-primary hover:underline">Bayar</button>
                             <button class="text-gray-500 hover:text-primary">Detail</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-end">
            <nav class="flex items-center space-x-2">
                <a href="#" class="px-3 py-1 border rounded-lg hover:bg-gray-100">Sebelumnya</a>
                <a href="#" class="px-3 py-1 bg-primary text-white border border-primary rounded-lg">1</a>
                <a href="#" class="px-3 py-1 border rounded-lg hover:bg-gray-100">2</a>
                <a href="#" class="px-3 py-1 border rounded-lg hover:bg-gray-100">3</a>
                <a href="#" class="px-3 py-1 border rounded-lg hover:bg-gray-100">Selanjutnya</a>
            </nav>
        </div>

    </div>
@endsection