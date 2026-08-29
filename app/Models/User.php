<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Matikan timestamps bawaan Laravel karena tabel users di schema.sql tidak memiliki kolom created_at/updated_at.
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'nama_karyawan',
        'jenis_user',
    ];

    /**
     * Atribut yang disembunyikan dalam serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cek apakah user adalah administrator.
     */
    public function isAdmin(): bool
    {
        return strtoupper($this->jenis_user) === 'ADMINISTRATOR';
    }

    /**
     * Cek apakah user adalah user eksternal.
     */
    public function isExternal(): bool
    {
        return strtoupper($this->jenis_user) === 'EKSTERNAL';
    }
}
