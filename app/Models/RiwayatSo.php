<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatSo extends Model
{
    use HasFactory;

    protected $table = 'riwayat_so';

    /**
     * Kustomisasi kolom timestamp: tabel ini menggunakan kolom 'timestamp' sebagai created_at, tanpa updated_at.
     */
    const CREATED_AT = 'timestamp';
    const UPDATED_AT = null;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'timestamp',
        'user',
        'nomor_asset',
        'deskripsi_asset',
        'serial_number',
        'qty_buku',
        'qty_fisik',
        'selisih',
        'tagging',
        'status_penggunaan',
        'kondisi',
        'lokasi',
        'link_foto_fisik',
        'link_tagging_asset',
    ];

    /**
     * Type casting atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
        'qty_buku' => 'integer',
        'qty_fisik' => 'integer',
        'selisih' => 'integer',
    ];

    /**
     * Relasi ke data master asset internal.
     */
    public function masterAsset(): BelongsTo
    {
        return $this->belongsTo(MasterAsset::class, 'nomor_asset', 'nomor_asset');
    }
}
