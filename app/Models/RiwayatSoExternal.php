<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatSoExternal extends Model
{
    use HasFactory;

    protected $table = 'riwayat_so_external';

    /**
     * Kustomisasi kolom timestamp: tabel ini menggunakan kolom 'time_stamps' sebagai created_at, tanpa updated_at.
     */
    const CREATED_AT = 'time_stamps';
    const UPDATED_AT = null;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'time_stamps',
        'user',
        'nomor_asset',
        'deskripsi_asset',
        'serial_number',
        'aktual_loc',
        'book_qty',
        'physic_qty',
        'variance',
        'kelengkapan_tagging',
        'status',
        'kondisi',
        'keterangan',
        'foto_fisik',
        'foto_tagging',
    ];

    /**
     * Type casting atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time_stamps' => 'datetime',
        'book_qty' => 'integer',
        'physic_qty' => 'integer',
        'variance' => 'integer',
    ];

    /**
     * Relasi ke data master asset external.
     */
    public function masterAsset(): BelongsTo
    {
        return $this->belongsTo(MasterAssetExternal::class, 'nomor_asset', 'nomor_asset');
    }
}
