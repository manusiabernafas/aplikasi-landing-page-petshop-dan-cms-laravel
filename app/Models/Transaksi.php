<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    public const METODE = ['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer'];

    protected $fillable = ['nomor', 'pelanggan_id', 'user_id', 'subtotal', 'diskon', 'total', 'bayar', 'kembali', 'metode', 'catatan', 'batal', 'waktu'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime', 'batal' => 'boolean'];
    }

    public function item()
    {
        return $this->hasMany(TransaksiItem::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function nomorBaru(): string
    {
        $awal = 'TRX-' . now()->format('ymd') . '-';
        $akhir = self::where('nomor', 'like', $awal . '%')->lockForUpdate()->max('nomor');
        return $awal . str_pad((string) ((int) substr((string) $akhir, -4) + 1), 4, '0', STR_PAD_LEFT);
    }
}
