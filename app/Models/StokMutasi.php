<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';

    public $timestamps = false;

    protected $fillable = ['produk_id', 'user_id', 'jenis', 'qty', 'stok_akhir', 'keterangan', 'waktu'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime'];
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Ubah stok produk + catat mutasinya. Panggil di dalam transaksi DB dengan baris produk terkunci. */
    public static function catat(Produk $p, string $jenis, int $qty, ?string $ket = null, ?int $user = null): self
    {
        $p->stok += $qty;
        $p->save();
        return self::create(['produk_id' => $p->id, 'user_id' => $user ?? auth()->id(), 'jenis' => $jenis, 'qty' => $qty, 'stok_akhir' => $p->stok, 'keterangan' => $ket, 'waktu' => now()]);
    }
}
