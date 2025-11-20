<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontrak_id')->constrained('kontrak')->onDelete('cascade');
            $table->integer('periode_bulan');
            $table->integer('periode_tahun');

            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('sisa_tagihan', 15, 2);
            $table->string('status')->default('belum_lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
