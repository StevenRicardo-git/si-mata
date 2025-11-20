<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Disperkim extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;
    
    protected $table = 'disperkim';
    
    protected $fillable = [
        'tipe',
        'nama',
        'jabatan',
        'nip',
        'pangkat',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeKepalaDinas($query)
    {
        return $query->where('tipe', 'kepala_dinas');
    }

    public function scopeStaff($query)
    {
        return $query->where('tipe', 'staff');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public static function getKepalaDinasAktif()
    {
        return self::kepalaDinas()->aktif()->ordered()->first();
    }

    public static function getStaffAktif()
    {
        return self::staff()->aktif()->ordered()->get();
    }
}