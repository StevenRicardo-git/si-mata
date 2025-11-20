<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Unit extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;

    protected $table = 'units';

    protected $fillable = [
        'kode_unit',
        'tipe',
        'status',
        'harga_sewa',
    ];
    
    public function kontrak()
    {
        return $this->hasMany(Kontrak::class, 'unit_id');
    }
}