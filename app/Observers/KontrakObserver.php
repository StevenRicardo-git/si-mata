<?php

namespace App\Observers;

use App\Models\Kontrak;
use App\Models\Unit;

class KontrakObserver
{
    public function created(Kontrak $kontrak): void
    {
        $unit = Unit::find($kontrak->unit_id);       
        if ($unit) {
            Unit::disableAuditing();
            $unit->status = 'terisi';
            $unit->save();
            Unit::enableAuditing();
        }
    }

    private function handleContractEnd(Kontrak $kontrak): void
    {
        $kontrakAktifLain = Kontrak::where('unit_id', $kontrak->unit_id)
                                    ->where('status', 'aktif')
                                    ->exists();
        if (!$kontrakAktifLain) {
            $unit = Unit::find($kontrak->unit_id);
            if ($unit) {
                Unit::disableAuditing();
                $unit->status = 'tersedia';
                $unit->save();
                Unit::enableAuditing();
            }
        }
    }
    
    public function updated(Kontrak $kontrak): void
    {
        if ($kontrak->wasChanged('status') && $kontrak->status == 'keluar') {
            $this->handleContractEnd($kontrak);
        }
    }

    public function deleted(Kontrak $kontrak): void
    {
        $this->handleContractEnd($kontrak);
    }
}