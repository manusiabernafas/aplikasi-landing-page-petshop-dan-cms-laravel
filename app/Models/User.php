<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const PERAN = ['admin' => 'Admin', 'kasir' => 'Kasir', 'groomer' => 'Groomer'];

    protected $fillable = ['name', 'email', 'peran', 'telepon', 'aktif', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean'];
    }

    public function bisa(string ...$peran): bool
    {
        return in_array($this->peran, $peran, true);
    }
}
