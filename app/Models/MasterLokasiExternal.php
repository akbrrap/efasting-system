<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLokasiExternal extends Model
{
    use HasFactory;

    protected $table = 'master_lokasi_external';

    /**
     * Matikan timestamps bawaan Laravel karena tabel master_lokasi_external tidak memiliki kolom created_at/updated_at.
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code_entity',
        'description',
    ];
}
