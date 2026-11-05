<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function index(Request $r)
    {
        return view('panel.profil', ['u' => $r->user()]);
    }

    public function sandi(Request $r)
    {
        $r->validate(['sandi_lama' => 'required|current_password', 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]], [], ['sandi_lama' => 'kata sandi lama', 'password' => 'kata sandi baru']);
        $r->user()->update(['password' => Hash::make($r->input('password'))]);
        return back()->with('ok', 'Kata sandi diganti.');
    }
}
