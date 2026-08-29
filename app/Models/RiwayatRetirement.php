<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatRetirement extends Model
{
    use HasFactory;

    protected $table = 'riwayat_retirement';

    /**
     * Kustomisasi kolom timestamp: tabel ini menggunakan kolom 'created_at' tanpa updated_at.
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_at',
        'petugas',
        'kategori_db',
        'nomor_asset',
        'deskripsi_asset',
        'qty_disposal',
        'nbv_disposal',
        'dokumen_sap',
        'catatan',
    ];

    /**
     * Type casting atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'qty_disposal' => 'integer',
        'nbv_disposal' => 'float',
    ];
}
