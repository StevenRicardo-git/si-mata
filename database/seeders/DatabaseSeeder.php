<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Yunitavia',
            'username' => 'yunitavia',
            'password' => Hash::make('rusun'),
            'role' => 'Penata Kelola Perumahan Ahli Muda',
        ]);

        User::create([
            'name' => 'Wartojo',
            'username' => 'wartojo',
            'password' => Hash::make('rusun'),
            'role' => 'bendahara',
        ]);
    }
}