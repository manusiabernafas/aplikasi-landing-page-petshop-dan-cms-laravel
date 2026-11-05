<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Produk;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        return view('panel.layanan.index', ['data' => Layanan::withCount('booking')->orderBy('kategori')->orderBy('urut')->get()->groupBy('kategori')]);
    }

    public function create()
    {
        return view('panel.layanan.form', ['l' => new Layanan(['kategori' => 'grooming', 'satuan' => 'sesi', 'durasi' => 60, 'untuk' => 'Semua', 'publik' => true, 'aktif' => true])]);
    }

    public function store(Request $r)
    {
        Layanan::create($this->data($r));
        return redirect()->route('panel.layanan.index')->with('ok', 'Layanan ditambahkan.');
    }

    public function edit(Layanan $layanan)
    {
        return view('panel.layanan.form', ['l' => $layanan]);
    }

    public function update(Request $r, Layanan $layanan)
    {
        $layanan->update($this->data($r));
        return redirect()->route('panel.layanan.index')->with('ok', 'Layanan diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        if ($layanan->booking()->exists()) {
            $layanan->update(['aktif' => false]);
            return back()->with('ok', 'Layanan sudah punya riwayat booking, jadi dinonaktifkan (tidak dihapus).');
        }
        $layanan->delete();
        return back()->with('ok', 'Layanan dihapus.');
    }

    private function data(Request $r): array
    {
        $d = $r->validate([
            'nama' => 'required|string|max:80', 'kategori' => 'required|in:' . implode(',', array_keys(Layanan::KATEGORI)), 'harga' => 'required|integer|min:0|max:100000000',
            'satuan' => 'required|string|max:15', 'durasi' => 'required|integer|min:5|max:1440', 'untuk' => 'required|in:' . implode(',', Produk::UNTUK),
            'deskripsi' => 'nullable|string|max:300', 'urut' => 'nullable|integer|min:0|max:999',
        ]);
        return $d + ['publik' => $r->boolean('publik'), 'aktif' => $r->boolean('aktif'), 'urut' => $d['urut'] ?? 0];
    }
}
