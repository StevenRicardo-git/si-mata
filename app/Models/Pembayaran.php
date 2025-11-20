<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Pembayaran extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    
    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
    ];

    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'user_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_pembayaran',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}