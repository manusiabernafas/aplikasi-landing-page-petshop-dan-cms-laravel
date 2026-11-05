<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\StokMutasi;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function index(Request $r)
    {
        $produk = Produk::where('aktif', true)->orderBy('kategori')->orderBy('nama')->get(['id', 'sku', 'nama', 'kategori', 'untuk', 'harga_jual', 'stok', 'satuan', 'foto']);
        $layanan = Layanan::where('aktif', true)->orderBy('kategori')->orderBy('urut')->get(['id', 'nama', 'kategori', 'harga', 'satuan', 'untuk']);
        // Tagih booking: isi keranjang dengan layanannya
        $booking = null;
        if ($r->filled('booking')) {
            $booking = Booking::with('layanan', 'pelanggan')->whereNull('transaksi_id')->where('status', '!=', 'batal')->find($r->query('booking'));
        }
        $tunggu = Booking::with('layanan', 'pelanggan')->whereNull('transaksi_id')->whereIn('status', ['dikerjakan', 'selesai'])->latest('tanggal')->limit(8)->get();
        return view('panel.kasir', compact('produk', 'layanan', 'booking', 'tunggu'));
    }

    /**
     * Simpan transaksi. Browser hanya mengirim id + qty; harga & stok dihitung ulang di server
     * dengan baris produk dikunci (lockForUpdate) agar stok tidak minus saat dua kasir bersamaan.
     */
    public function simpan(Request $r)
    {
        $d = $r->validate([
            'item' => 'required|array|min:1|max:60',
            'item.*.tipe' => 'required|in:produk,layanan',
            'item.*.id' => 'required|integer',
            'item.*.qty' => 'required|integer|min:1|max:999',
            'item.*.booking' => 'nullable|integer',
            'pelanggan_id' => 'nullable|integer|exists:pelanggan,id',
            'diskon' => 'nullable|integer|min:0',
            'metode' => 'required|in:' . implode(',', array_keys(Transaksi::METODE)),
            'bayar' => 'nullable|integer|min:0',
            'catatan' => 'nullable|string|max:200',
        ]);

        $t = DB::transaction(function () use ($d) {
            $baris = [];
            $sub = 0;
            $idProduk = collect($d['item'])->where('tipe', 'produk')->pluck('id')->unique()->all();
            $produk = Produk::whereIn('id', $idProduk)->where('aktif', true)->lockForUpdate()->get()->keyBy('id');
            $butuh = [];
            foreach ($d['item'] as $i) {
                if ($i['tipe'] === 'produk') {
                    $p = $produk[$i['id']] ?? throw ValidationException::withMessages(['item' => 'Ada produk yang sudah tidak dijual. Muat ulang halaman kasir.']);
                    $butuh[$p->id] = ($butuh[$p->id] ?? 0) + $i['qty'];
                    $baris[] = ['tipe' => 'produk', 'produk_id' => $p->id, 'nama' => $p->nama, 'harga' => $p->harga_jual, 'harga_beli' => $p->harga_beli, 'qty' => $i['qty'], 'subtotal' => $p->harga_jual * $i['qty']];
                } else {
                    $l = Layanan::where('aktif', true)->find($i['id']) ?? throw ValidationException::withMessages(['item' => 'Ada layanan yang sudah tidak aktif.']);
                    $b = null;
                    if (! empty($i['booking'])) {
                        $b = Booking::whereKey($i['booking'])->lockForUpdate()->first();
                        if (! $b || $b->transaksi_id || $b->status === 'batal' || (int) $b->layanan_id !== (int) $l->id) {
                            throw ValidationException::withMessages(['item' => 'Booking sudah dibayar atau tidak cocok dengan layanannya.']);
                        }
                    }
                    $nama = $l->nama . ($b ? ' · ' . $b->nama_hewan . ' (' . $b->kode . ')' : '');
                    $baris[] = ['tipe' => 'layanan', 'layanan_id' => $l->id, 'booking_id' => $b?->id, 'nama' => $nama, 'harga' => $l->harga, 'harga_beli' => 0, 'qty' => $i['qty'], 'subtotal' => $l->harga * $i['qty']];
                }
            }
            foreach ($butuh as $id => $q) {
                if ($q > $produk[$id]->stok) {
                    throw ValidationException::withMessages(['item' => 'Stok ' . $produk[$id]->nama . ' tinggal ' . max(0, $produk[$id]->stok) . '.']);
                }
            }
            $sub = array_sum(array_column($baris, 'subtotal'));
            $diskon = (int) ($d['diskon'] ?? 0);
            if ($diskon > $sub) {
                throw ValidationException::withMessages(['diskon' => 'Diskon melebihi subtotal.']);
            }
            $total = $sub - $diskon;
            $bayar = $d['metode'] === 'tunai' ? (int) ($d['bayar'] ?? 0) : $total;
            if ($bayar < $total) {
                throw ValidationException::withMessages(['bayar' => 'Uang yang dibayar kurang ' . rp($total - $bayar) . '.']);
            }
            $t = Transaksi::create([
                'nomor' => Transaksi::nomorBaru(), 'pelanggan_id' => $d['pelanggan_id'] ?? null, 'user_id' => auth()->id(),
                'subtotal' => $sub, 'diskon' => $diskon, 'total' => $total, 'bayar' => $bayar, 'kembali' => $bayar - $total,
                'metode' => $d['metode'], 'catatan' => $d['catatan'] ?? null, 'waktu' => now(),
            ]);
            foreach ($baris as $b) {
                $t->item()->create($b);
                if ($b['tipe'] === 'produk') {
                    StokMutasi::catat($produk[$b['produk_id']], 'penjualan', -$b['qty'], $t->nomor);
                }
                if (! empty($b['booking_id'])) {
                    Booking::whereKey($b['booking_id'])->update(['transaksi_id' => $t->id, 'status' => 'selesai']);
                }
            }
            return $t;
        });

        return response()->json(['ok' => true, 'nomor' => $t->nomor, 'total' => $t->total, 'kembali' => $t->kembali, 'struk' => route('panel.transaksi.show', $t)]);
    }

    public function daftar(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $t = Transaksi::with('pelanggan', 'user')->withCount('item')
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('nomor', 'like', "%$q%")->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%$q%"))))
            ->when($r->query('tanggal'), fn ($x, $tg) => $x->whereDate('waktu', $tg))
            ->when($r->query('metode'), fn ($x, $m) => $x->where('metode', $m))
            ->latest('waktu')->paginate(20)->withQueryString();
        return view('panel.transaksi', ['data' => $t]);
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load('item', 'pelanggan', 'user');
        return view('panel.struk', ['t' => $transaksi]);
    }

    /** Batalkan transaksi: stok produk dikembalikan, booking dibuka lagi untuk ditagih. */
    public function batal(Transaksi $transaksi)
    {
        if ($transaksi->batal) {
            return back()->with('error', 'Transaksi ini sudah dibatalkan.');
        }
        DB::transaction(function () use ($transaksi) {
            $t = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            foreach ($t->item as $i) {
                if ($i->produk_id && ($p = Produk::whereKey($i->produk_id)->lockForUpdate()->first())) {
                    StokMutasi::catat($p, 'batal', $i->qty, 'Batal ' . $t->nomor);
                }
            }
            Booking::where('transaksi_id', $t->id)->update(['transaksi_id' => null]);
            $t->update(['batal' => true]);
        });
        return back()->with('ok', 'Transaksi ' . $transaksi->nomor . ' dibatalkan dan stok dikembalikan.');
    }
}
