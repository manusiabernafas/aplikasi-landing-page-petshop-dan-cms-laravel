<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokMutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $data = Produk::query()
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('nama', 'like', "%$q%")->orWhere('sku', 'like', "%$q%")))
            ->when($r->query('kategori'), fn ($x, $k) => $x->where('kategori', $k))
            ->when($r->query('stok') === 'menipis', fn ($x) => $x->whereColumn('stok', '<=', 'stok_min'))
            ->orderByRaw('aktif desc')->orderBy('nama')->paginate(24)->withQueryString();
        $ringkas = [
            'jumlah' => Produk::where('aktif', true)->count(),
            'menipis' => Produk::where('aktif', true)->whereColumn('stok', '<=', 'stok_min')->count(),
            'nilai' => (int) Produk::where('aktif', true)->where('stok', '>', 0)->sum(DB::raw('stok * harga_beli')),
        ];
        return view('panel.produk.index', compact('data', 'ringkas'));
    }

    public function create()
    {
        return view('panel.produk.form', ['p' => new Produk(['kategori' => 'Makanan', 'untuk' => 'Semua', 'satuan' => 'pcs', 'stok_min' => 5, 'aktif' => true, 'sku' => 'PS-' . strtoupper(Str::random(5))])]);
    }

    public function store(Request $r)
    {
        $d = $this->data($r);
        $stok = (int) $r->validate(['stok' => 'required|integer|min:0|max:100000'])['stok'];
        DB::transaction(function () use ($d, $stok, $r) {
            $p = Produk::create($d + ['stok' => 0]);
            $this->foto($r, $p);
            if ($stok > 0) {
                StokMutasi::catat($p, 'masuk', $stok, 'Stok awal');
            }
        });
        return redirect()->route('panel.produk.index')->with('ok', 'Produk ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('panel.produk.form', ['p' => $produk]);
    }

    public function update(Request $r, Produk $produk)
    {
        $produk->update($this->data($r, $produk));
        $this->foto($r, $produk);
        return redirect()->route('panel.produk.index')->with('ok', 'Produk diperbarui. Stok diubah lewat menu Stok agar tercatat.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->stok !== 0 || \App\Models\TransaksiItem::where('produk_id', $produk->id)->exists()) {
            $produk->update(['aktif' => false]);
            return back()->with('ok', 'Produk punya stok/riwayat penjualan, jadi dinonaktifkan (tidak dihapus).');
        }
        if ($produk->foto) {
            @unlink(public_path('uploads/' . $produk->foto));
        }
        $produk->delete();
        return back()->with('ok', 'Produk dihapus.');
    }

    public function stok(Produk $produk)
    {
        return view('panel.produk.stok', ['p' => $produk, 'mutasi' => StokMutasi::with('user')->where('produk_id', $produk->id)->latest('waktu')->latest('id')->paginate(20)]);
    }

    /** Stok masuk / keluar / penyesuaian (stok opname). Semua perubahan tercatat. */
    public function simpanStok(Request $r, Produk $produk)
    {
        $d = $r->validate(['jenis' => 'required|in:masuk,keluar,penyesuaian', 'qty' => 'required|integer|min:0|max:100000', 'keterangan' => 'nullable|string|max:150']);
        DB::transaction(function () use ($d, $produk) {
            $p = Produk::whereKey($produk->id)->lockForUpdate()->first();
            $qty = match ($d['jenis']) { 'masuk' => $d['qty'], 'keluar' => -$d['qty'], 'penyesuaian' => $d['qty'] - $p->stok };
            if ($d['jenis'] !== 'penyesuaian' && $d['qty'] === 0) {
                throw \Illuminate\Validation\ValidationException::withMessages(['qty' => 'Jumlah minimal 1.']);
            }
            if ($p->stok + $qty < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages(['qty' => 'Stok tidak boleh minus (stok sekarang ' . $p->stok . ').']);
            }
            StokMutasi::catat($p, $d['jenis'], $qty, $d['keterangan'] ?? null);
        });
        return back()->with('ok', 'Stok ' . $produk->nama . ' diperbarui.');
    }

    private function data(Request $r, ?Produk $p = null): array
    {
        $d = $r->validate([
            'sku' => ['required', 'regex:/^[A-Za-z0-9\-]{3,30}$/', Rule::unique('produk', 'sku')->ignore($p?->id)],
            'nama' => 'required|string|max:100', 'kategori' => 'required|in:' . implode(',', array_keys(Produk::KATEGORI)), 'untuk' => 'required|in:' . implode(',', Produk::UNTUK),
            'harga_beli' => 'required|integer|min:0|max:100000000', 'harga_jual' => 'required|integer|min:1|max:100000000', 'stok_min' => 'required|integer|min:0|max:10000',
            'satuan' => 'required|string|max:15', 'deskripsi' => 'nullable|string|max:400', 'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], ['sku.regex' => 'SKU hanya huruf, angka, dan strip.'], ['harga_beli' => 'harga beli', 'harga_jual' => 'harga jual', 'stok_min' => 'stok minimum']);
        unset($d['foto']);
        $d['sku'] = strtoupper($d['sku']);
        return $d + ['unggulan' => $r->boolean('unggulan'), 'aktif' => $r->boolean('aktif')];
    }

    private function foto(Request $r, Produk $p): void
    {
        if ($r->boolean('hapus_foto') && $p->foto) {
            @unlink(public_path('uploads/' . $p->foto));
            $p->update(['foto' => null]);
        }
        if ($r->hasFile('foto')) {
            $nama = 'produk/' . $p->id . '-' . Str::random(8) . '.' . $r->file('foto')->extension();
            $r->file('foto')->move(public_path('uploads/produk'), basename($nama));
            if ($p->foto) {
                @unlink(public_path('uploads/' . $p->foto));
            }
            $p->update(['foto' => $nama]);
        }
    }
}
