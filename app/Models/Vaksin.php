<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaksin extends Model
{
    protected $table = 'vaksin';

    protected $fillable = ['hewan_id', 'nama', 'tanggal', 'berikutnya', 'catatan', 'user_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'berikutnya' => 'date'];
    }

    public function hewan()
    {
        return $this->belongsTo(Hewan::class);
    }
}
