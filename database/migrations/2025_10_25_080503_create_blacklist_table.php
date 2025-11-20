<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('hubungan')->nullable()->comment('Penghuni Utama, Istri, Anak, dll.');
            $table->text('alasan_blacklist');
            $table->date('tanggal_blacklist');
            $table->text('alasan_aktivasi')->nullable();
            $table->date('tanggal_aktivasi')->nullable();
            $table->string('status')->default('blacklist')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist');
    }
};