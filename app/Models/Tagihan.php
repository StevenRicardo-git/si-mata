<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Tagihan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    
    protected $auditEvents = [
        'updated',
        'deleted',
    ];
    
    protected $table = 'tagihan';

    protected $fillable = [
        'kontrak_id',
        'periode_bulan',
        'periode_tahun',
        'total_tagihan',
        'sisa_tagihan',
        'status',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_tagihan' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
    ];

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'kontrak_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }
}