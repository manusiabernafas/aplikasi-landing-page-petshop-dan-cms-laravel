<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = ['nama', 'telepon', 'alamat', 'catatan'];

    public function hewan()
    {
        return $this->hasMany(Hewan::class)->orderBy('nama');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class)->latest('waktu');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class)->latest('tanggal');
    }

    /** 0812... / +62812... / 62812... jadi 62812... */
    public static function normalHp(?string $hp): string
    {
        $d = preg_replace('/\D/', '', (string) $hp);
        return str_starts_with($d, '0') ? '62' . substr($d, 1) : $d;
    }

    public function waLink(string $pesan = ''): string
    {
        return 'https://wa.me/' . self::normalHp($this->telepon) . ($pesan !== '' ? '?text=' . rawurlencode($pesan) : '');
    }
}
