<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Keluarga extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;

    protected $table = 'keluargas';

    protected $fillable = [
        'penghuni_id',
        'nama',
        'nik',
        'umur',
        'jenis_kelamin',
        'hubungan',
        'catatan',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class, 'penghuni_id');
    }
}