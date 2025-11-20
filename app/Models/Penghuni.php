<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Penghuni extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;
    
    protected $table = 'penghuni';
    
    protected $fillable = [
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'pekerjaan',
        'jenis_kelamin',
        'alamat_ktp',
        'no_hp',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function kontrakAktif()
    {
        return $this->hasOne(Kontrak::class, 'penghuni_id')
                    ->where('status', 'aktif')
                    ->with('unit'); 
    }

    public function kontrak()
    {
        return $this->hasOne(Kontrak::class, 'penghuni_id')
                    ->with('unit')
                    ->latest('tanggal_masuk');
    }

    public function semuaKontrak()
    {
        return $this->hasMany(Kontrak::class, 'penghuni_id')
                    ->with('unit')
                    ->orderBy('tanggal_masuk', 'desc');
    }
    
    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'penghuni_id');
    }
}