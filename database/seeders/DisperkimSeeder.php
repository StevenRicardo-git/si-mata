<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisperkimSeeder extends Seeder
{
    public function run(): void
    {
    
        DB::table('disperkim')->insert([
            'tipe' => 'kepala_dinas',
            'nama' => 'Ir. EKO SETYAWAN, MUM.',
            'jabatan' => 'Kepala Dinas Perumahan Dan Kawasan Permukiman Kota Tegal',
            'nip' => '196503020 196503 1 001',
            'pangkat' => 'Pembina Utama Muda',
            'urutan' => 1,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staff = [
            [
                'nama' => 'YUNITAVIA SRI ANAWATI, ST',
                'jabatan' => 'Penata Kelola Perumahan Muda',
                'urutan' => 1,
            ],
            [
                'nama' => 'EKO HADI WARSITO',
                'jabatan' => 'Pengadministrasi Umum UPTD Pengelolaan Rusunawa',
                'urutan' => 2,
            ],
            [
                'nama' => 'WARTOJO, SM',
                'jabatan' => 'Bendahara Penerima Pembantu',
                'urutan' => 3,
            ],
        ];

        foreach ($staff as $s) {
            DB::table('disperkim')->insert([
                'tipe' => 'staff',
                'nama' => $s['nama'],
                'jabatan' => $s['jabatan'],
                'nip' => null,
                'pangkat' => null,
                'urutan' => $s['urutan'],
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}