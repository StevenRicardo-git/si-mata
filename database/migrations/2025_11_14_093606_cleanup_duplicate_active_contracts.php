<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('kontrak')
            ->select('penghuni_id', DB::raw('COUNT(*) as jumlah'))
            ->where('status', 'aktif')
            ->groupBy('penghuni_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $kontraks = DB::table('kontrak')
                ->where('penghuni_id', $dup->penghuni_id)
                ->where('status', 'aktif')
                ->orderBy('tanggal_masuk', 'desc')
                ->get();

            $keepFirst = true;
            foreach ($kontraks as $kontrak) {
                if ($keepFirst) {
                    $keepFirst = false;
                    Log::info("Keeping contract ID {$kontrak->id} for penghuni {$dup->penghuni_id}");
                    continue;
                }

                DB::table('kontrak')
                    ->where('id', $kontrak->id)
                    ->update([
                        'status' => 'selesai',
                        'alasan_keluar' => 'Data cleanup - duplikasi kontrak aktif',
                        'tanggal_keluar_aktual' => now(),
                        'updated_at' => now()
                    ]);

                DB::table('units')
                    ->where('id', $kontrak->unit_id)
                    ->update(['status' => 'tersedia', 'updated_at' => now()]);

                Log::info("Deactivated contract ID {$kontrak->id} for penghuni {$dup->penghuni_id}");
            }
        }
    }

    public function down(): void
    {
        Log::warning('Cannot rollback cleanup_duplicate_active_contracts migration');
    }
};