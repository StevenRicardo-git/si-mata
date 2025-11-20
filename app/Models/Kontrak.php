<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Carbon\Carbon;

class Kontrak extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;

    protected $table = 'kontrak';

    protected $fillable = [
        'penghuni_id',
        'unit_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'tanggal_keluar_aktual',
        'status',
        'keringanan',
        'nominal_keringanan',
        'tarif_air',
        'no_sip',
        'tanggal_sip',
        'no_sps',
        'tanggal_sps',
        'nilai_jaminan',
        'alasan_keluar',
        'tunggakan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_keluar_aktual' => 'date',
        'tanggal_sps' => 'date',
        'tanggal_sip' => 'date',
        'nominal_keringanan' => 'decimal:2',
        'nilai_jaminan' => 'decimal:2',
        'tarif_air' => 'decimal:2',
        'tunggakan' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kontrak) {
            if ($kontrak->tanggal_masuk && !$kontrak->tanggal_keluar) {
                $tanggalMasuk = Carbon::parse($kontrak->tanggal_masuk);
                $kontrak->tanggal_keluar = $tanggalMasuk->copy()->addYears(3)->subDay();
            }
        });

        static::updating(function ($kontrak) { 
            if ($kontrak->isDirty('tanggal_masuk') && !$kontrak->isDirty('tanggal_keluar')) {
                $tanggalMasuk = Carbon::parse($kontrak->tanggal_masuk);
                $kontrak->tanggal_keluar = $tanggalMasuk->copy()->addYears(3)->subDay();
            }
        });
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class, 'penghuni_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}