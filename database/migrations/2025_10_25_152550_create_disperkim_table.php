<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disperkim', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['kepala_dinas', 'staff'])->comment('Tipe data: kepala_dinas atau staff');
            $table->string('nama');
            $table->string('jabatan');
            $table->string('nip')->nullable()->comment('NIP untuk kepala dinas');
            $table->string('pangkat')->nullable()->comment('Pangkat untuk kepala dinas');
            $table->integer('urutan')->default(0)->comment('Urutan tampilan');
            $table->boolean('aktif')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamps();
            
        
            $table->index(['tipe', 'aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disperkim');
    }
};