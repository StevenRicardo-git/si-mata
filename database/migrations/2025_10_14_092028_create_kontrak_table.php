<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghuni')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->date('tanggal_keluar_aktual')->nullable();
            $table->string('status')->default('aktif');
            $table->enum('keringanan', ['dapat', 'tidak', 'normal'])->default('normal');
            $table->decimal('nominal_keringanan', 15, 2)->nullable();
            $table->decimal('tarif_air', 10, 2)->nullable();
            $table->string('no_sps')->nullable();
            $table->date('tanggal_sps')->nullable();
            $table->string('no_sip')->nullable();
            $table->date('tanggal_sip')->nullable();
            $table->decimal('nilai_jaminan', 15, 2)->nullable();
            $table->decimal('tunggakan', 15, 2)->nullable();
            $table->text('alasan_keluar')->nullable();              
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};