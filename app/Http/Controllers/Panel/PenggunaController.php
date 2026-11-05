<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index()
    {
        return view('panel.pengguna', ['data' => User::orderBy('peran')->orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:150|unique:users,email', 'peran' => 'required|in:' . implode(',', array_keys(User::PERAN)), 'telepon' => 'nullable|string|max:20']);
        $sandi = Str::password(10, symbols: false);
        User::create($d + ['password' => $sandi, 'aktif' => true]);
        return back()->with('ok', 'Akun staf dibuat.')->with('sandi_baru', [$d['email'], $sandi]);
    }

    public function update(Request $r, User $user)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'peran' => 'required|in:' . implode(',', array_keys(User::PERAN)), 'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)], 'telepon' => 'nullable|string|max:20']);
        if ($user->id === $r->user()->id && ($d['peran'] !== 'admin' || ! $r->boolean('aktif'))) {
            return back()->with('error', 'Anda tidak bisa menurunkan peran atau menonaktifkan akun sendiri.');
        }
        $user->update($d + ['aktif' => $r->boolean('aktif')]);
        return back()->with('ok', 'Akun ' . $user->name . ' diperbarui.');
    }

    /** Reset kata sandi: sandi baru ditampilkan sekali untuk diberikan ke pemilik akun. */
    public function reset(User $user)
    {
        $sandi = Str::password(10, symbols: false);
        $user->update(['password' => $sandi]);
        return back()->with('ok', 'Kata sandi direset.')->with('sandi_baru', [$user->email, $sandi]);
    }
}
