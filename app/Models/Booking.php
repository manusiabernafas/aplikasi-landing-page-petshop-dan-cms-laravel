<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';

    public const STATUS = ['menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai', 'batal' => 'Batal'];

    public const WARNA = ['menunggu' => 'kuning', 'dikonfirmasi' => 'biru', 'dikerjakan' => 'ungu', 'selesai' => 'hijau', 'batal' => 'abu'];

    /** Status berikutnya yang diizinkan. */
    public const ALUR = ['menunggu' => ['dikonfirmasi', 'batal'], 'dikonfirmasi' => ['dikerjakan', 'batal'], 'dikerjakan' => ['selesai'], 'selesai' => [], 'batal' => []];

    protected $fillable = ['kode', 'pelanggan_id', 'hewan_id', 'layanan_id', 'nama_hewan', 'jenis_hewan', 'tanggal', 'jam', 'tanggal_keluar', 'kandang', 'catatan', 'status', 'sumber', 'transaksi_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'tanggal_keluar' => 'date'];
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function hewan()
    {
        return $this->belongsTo(Hewan::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function malam(): int
    {
        return $this->tanggal_keluar ? max(1, (int) $this->tanggal->diffInDays($this->tanggal_keluar)) : 1;
    }

    /** Perkiraan biaya: harga layanan x malam (penitipan) atau x 1. */
    public function biaya(): int
    {
        return $this->layanan->harga * ($this->layanan->penitipan() ? $this->malam() : 1);
    }
}
