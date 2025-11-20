<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableBatch;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Blacklist extends Model implements AuditableContract
{
    use HasFactory;
    use AuditableBatch;

    protected $table = 'blacklist';

    protected $fillable = [
        'nik',
        'nama',
        'hubungan',
        'alasan_blacklist',
        'tanggal_blacklist',
        'alasan_aktivasi',
        'tanggal_aktivasi',
        'status',
    ];

    protected $casts = [
        'tanggal_blacklist' => 'date',
        'tanggal_aktivasi' => 'date',
    ];
}