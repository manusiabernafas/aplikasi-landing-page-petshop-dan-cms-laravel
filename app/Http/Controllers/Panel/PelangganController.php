<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Hewan;
use App\Models\Pelanggan;
use App\Models\Vaksin;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $data = Pelanggan::with('hewan:id,pelanggan_id,nama,jenis')->withCount('transaksi')->withSum(['transaksi as belanja' => fn ($t) => $t->where('batal', false)], 'total')
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('nama', 'like', "%$q%")->orWhere('telepon', 'like', "%$q%")->orWhereHas('hewan', fn ($h) => $h->where('nama', 'like', "%$q%"))))
            ->orderBy('nama')->paginate(20)->withQueryString();
        return view('panel.pelanggan.index', compact('data', 'q'));
    }

    /** JSON untuk kasir & form booking. */
    public function cari(Request $r)
    {
        $q = trim((string) $r->query('q'));
        if (mb_strlen($q) < 2) {
            return [];
        }
        return Pelanggan::with('hewan:id,pelanggan_id,nama,jenis')->where(fn ($w) => $w->where('nama', 'like', "%$q%")->orWhere('telepon', 'like', "%$q%"))
            ->orderBy('nama')->limit(8)->get(['id', 'nama', 'telepon']);
    }

    public function create()
    {
        return view('panel.pelanggan.form', ['p' => new Pelanggan()]);
    }

    public function store(Request $r)
    {
        $p = Pelanggan::create($this->data($r));
        return redirect()->route('panel.pelanggan.show', $p)->with('ok', 'Pelanggan ditambahkan. Lanjut tambahkan hewannya.');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load(['hewan.vaksin', 'booking' => fn ($q) => $q->with('layanan')->limit(10), 'transaksi' => fn ($q) => $q->withCount('item')->limit(10)]);
        return view('panel.pelanggan.show', ['p' => $pelanggan, 'jenis' => Hewan::JENIS, 'belanja' => $pelanggan->transaksi()->where('batal', false)->sum('total')]);
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('panel.pelanggan.form', ['p' => $pelanggan]);
    }

    public function update(Request $r, Pelanggan $pelanggan)
    {
        $pelanggan->update($this->data($r));
        return redirect()->route('panel.pelanggan.show', $pelanggan)->with('ok', 'Data pelanggan diperbarui.');
    }

    public function destroy(Request $r, Pelanggan $pelanggan)
    {
        if (! $r->user()->bisa('admin')) {
            return back()->with('error', 'Hanya admin yang bisa menghapus pelanggan.');
        }
        if ($pelanggan->transaksi()->exists() || $pelanggan->booking()->exists()) {
            return back()->with('error', 'Pelanggan punya riwayat transaksi/booking, tidak bisa dihapus.');
        }
        $pelanggan->delete();
        return redirect()->route('panel.pelanggan.index')->with('ok', 'Pelanggan dihapus.');
    }

    public function simpanHewan(Request $r, Pelanggan $pelanggan)
    {
        $pelanggan->hewan()->create($this->hewan($r));
        return back()->with('ok', 'Hewan ditambahkan.');
    }

    public function ubahHewan(Request $r, Hewan $hewan)
    {
        $hewan->update($this->hewan($r));
        return back()->with('ok', 'Data ' . $hewan->nama . ' diperbarui.');
    }

    public function hapusHewan(Hewan $hewan)
    {
        if (\App\Models\Booking::where('hewan_id', $hewan->id)->exists()) {
            return back()->with('error', $hewan->nama . ' punya riwayat booking, tidak bisa dihapus.');
        }
        $hewan->delete();
        return back()->with('ok', 'Hewan dihapus.');
    }

    public function simpanVaksin(Request $r, Hewan $hewan)
    {
        $d = $r->validate(['nama' => 'required|string|max:60', 'tanggal' => 'required|date|before_or_equal:today', 'berikutnya' => 'nullable|date|after:tanggal', 'catatan' => 'nullable|string|max:200'],
            [], ['berikutnya' => 'jadwal berikutnya']);
        $hewan->vaksin()->create($d + ['user_id' => auth()->id()]);
        return back()->with('ok', 'Vaksin ' . $d['nama'] . ' dicatat untuk ' . $hewan->nama . '.');
    }

    public function hapusVaksin(Vaksin $vaksin)
    {
        $vaksin->delete();
        return back()->with('ok', 'Catatan vaksin dihapus.');
    }

    private function data(Request $r): array
    {
        return $r->validate(['nama' => 'required|string|min:3|max:80', 'telepon' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'], 'alamat' => 'nullable|string|max:200', 'catatan' => 'nullable|string|max:300'],
            ['telepon.regex' => 'Nomor WhatsApp tidak valid (contoh 081234567890).']);
    }

    private function hewan(Request $r): array
    {
        return $r->validate([
            'nama' => 'required|string|max:50', 'jenis' => 'required|in:' . implode(',', Hewan::JENIS), 'ras' => 'nullable|string|max:50', 'kelamin' => 'nullable|in:Jantan,Betina',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today', 'berat' => 'nullable|numeric|min:0|max:200', 'warna' => 'nullable|string|max:40', 'catatan' => 'nullable|string|max:300',
        ], [], ['tanggal_lahir' => 'tanggal lahir']);
    }
}
