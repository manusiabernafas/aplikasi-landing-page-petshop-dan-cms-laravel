<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Petshop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('panel.pengaturan', ['k' => Petshop::semua()]);
    }

    public function simpan(Request $r)
    {
        $d = $r->validate([
            'nama' => 'required|string|max:60', 'tagline' => 'nullable|string|max:100', 'alamat' => 'required|string|max:200',
            'whatsapp' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'], 'telepon' => 'nullable|string|max:30', 'email' => 'nullable|email|max:120',
            'jam_buka' => 'required|string|max:200', 'jam_grooming' => ['required', 'regex:/^\s*\d{2}:\d{2}(\s*,\s*\d{2}:\d{2})*\s*$/'],
            'kapasitas_slot' => 'required|integer|min:1|max:20', 'jumlah_kandang' => 'required|integer|min:1|max:200', 'maks_hari_booking' => 'required|integer|min:1|max:120',
            'catatan_struk' => 'nullable|string|max:200', 'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
        ], ['whatsapp.regex' => 'Nomor WhatsApp tidak valid.', 'jam_grooming.regex' => 'Tulis jam dipisah koma, contoh 09:00,10:00,13:00.']);
        unset($d['logo']);
        $d['jam_grooming'] = implode(',', array_map('trim', explode(',', $d['jam_grooming'])));
        if ($r->hasFile('logo')) {
            $nama = 'logo-' . Str::random(8) . '.' . $r->file('logo')->extension();
            $r->file('logo')->move(public_path('uploads'), $nama);
            if (Petshop::get('logo')) {
                @unlink(public_path('uploads/' . Petshop::get('logo')));
            }
            $d['logo'] = $nama;
        }
        Petshop::simpan(array_map(fn ($v) => $v ?? '', $d));
        return back()->with('ok', 'Pengaturan disimpan.');
    }
}
