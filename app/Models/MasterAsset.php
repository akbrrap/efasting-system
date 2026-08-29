<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterAsset extends Model
{
    use HasFactory;

    protected $table = 'master_asset';

    /**
     * Matikan timestamps bawaan Laravel karena tabel master_asset tidak memiliki kolom created_at/updated_at.
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nomor_asset',
        'deskripsi_asset',
        'serial_number',
        'cost_center',
        'qty_buku',
        'cap_date',
        'nilai_perolehan',
        'akumulasi_depresiasi',
        'nbv',
        'allocation',
    ];

    /**
     * Type casting atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty_buku' => 'integer',
        'cap_date' => 'date',
        'nilai_perolehan' => 'float',
        'akumulasi_depresiasi' => 'float',
        'nbv' => 'float',
    ];

    /**
     * Relasi ke riwayat stock opname internal.
     */
    public function riwayatSo(): HasMany
    {
        return $this->hasMany(RiwayatSo::class, 'nomor_asset', 'nomor_asset');
    }
}
