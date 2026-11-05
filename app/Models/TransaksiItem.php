<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiItem extends Model
{
    protected $table = 'transaksi_item';

    public $timestamps = false;

    protected $fillable = ['transaksi_id', 'tipe', 'produk_id', 'layanan_id', 'booking_id', 'nama', 'harga', 'harga_beli', 'qty', 'subtotal'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
