<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';

    public const KATEGORI = ['grooming' => 'Grooming', 'penitipan' => 'Penitipan (pet hotel)', 'klinik' => 'Klinik & vaksin'];

    protected $fillable = ['nama', 'kategori', 'harga', 'satuan', 'durasi', 'untuk', 'deskripsi', 'publik', 'aktif', 'urut'];

    protected function casts(): array
    {
        return ['publik' => 'boolean', 'aktif' => 'boolean'];
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function penitipan(): bool
    {
        return $this->kategori === 'penitipan';
    }
}
