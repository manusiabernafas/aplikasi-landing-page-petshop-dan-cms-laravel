<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hewan extends Model
{
    protected $table = 'hewan';

    public const JENIS = ['Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Burung', 'Lainnya'];

    protected $fillable = ['pelanggan_id', 'nama', 'jenis', 'ras', 'kelamin', 'tanggal_lahir', 'berat', 'warna', 'catatan'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'berat' => 'float'];
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function vaksin()
    {
        return $this->hasMany(Vaksin::class)->latest('tanggal');
    }

    public function umur(): string
    {
        if (! $this->tanggal_lahir) {
            return '-';
        }
        $d = $this->tanggal_lahir->diff(now());
        return $d->y ? $d->y . ' th ' . ($d->m ? $d->m . ' bln' : '') : max(1, $d->m) . ' bln';
    }
}
