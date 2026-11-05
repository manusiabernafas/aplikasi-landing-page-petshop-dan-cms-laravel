<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    /** kategori => [warna ubin, ikon] */
    public const KATEGORI = [
        'Makanan' => ['#ff9f6e', 'mangkuk'], 'Snack' => ['#ffd45c', 'tulang'], 'Pasir & Kebersihan' => ['#8fd3c1', 'sapu'],
        'Obat & Vitamin' => ['#ff8fb1', 'kapsul'], 'Aksesori' => ['#a9b8ff', 'kalung'], 'Mainan' => ['#c9a7ff', 'bola'], 'Kandang & Tas' => ['#9ccf7d', 'kandang'],
    ];

    public const UNTUK = ['Semua', 'Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Burung'];

    protected $fillable = ['sku', 'nama', 'kategori', 'untuk', 'harga_beli', 'harga_jual', 'stok', 'stok_min', 'satuan', 'deskripsi', 'foto', 'unggulan', 'aktif'];

    protected function casts(): array
    {
        return ['unggulan' => 'boolean', 'aktif' => 'boolean'];
    }

    public function menipis(): bool
    {
        return $this->stok <= $this->stok_min;
    }

    public function warna(): string
    {
        return self::KATEGORI[$this->kategori][0] ?? '#a9b8ff';
    }

    public function ikon(): string
    {
        return self::KATEGORI[$this->kategori][1] ?? 'tas';
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('uploads/' . $this->foto) : null;
    }
}
