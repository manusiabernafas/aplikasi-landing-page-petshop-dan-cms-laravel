<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hewan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Support\Jadwal;
use App\Support\Petshop;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $r)
    {
        $tanggal = $r->query('tanggal') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r->query('tanggal')) ? $r->query('tanggal') : now()->toDateString();
        $tab = in_array($r->query('tab'), ['jadwal', 'penitipan', 'semua'], true) ? $r->query('tab') : 'jadwal';
        $q = trim((string) $r->query('q'));
        $dasar = Booking::with('layanan', 'pelanggan');
        if ($tab === 'jadwal') {
            $data = (clone $dasar)->whereDate('tanggal', $tanggal)->whereNotNull('jam')->orderBy('jam')->get();
            $slot = Petshop::slot();
        } elseif ($tab === 'penitipan') {
            $data = (clone $dasar)->whereNotNull('kandang')->where('status', '!=', 'batal')->whereDate('tanggal', '<=', $tanggal)->whereDate('tanggal_keluar', '>=', $tanggal)->orderBy('kandang')->get();
            $slot = range(1, max(1, (int) Petshop::get('jumlah_kandang')));
        } else {
            $data = (clone $dasar)->when($q, fn ($x) => $x->where(fn ($w) => $w->where('kode', 'like', "%$q%")->orWhere('nama_hewan', 'like', "%$q%")->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%$q%"))))
                ->when($r->query('status'), fn ($x, $s) => $x->where('status', $s))->latest('tanggal')->latest('id')->paginate(20)->withQueryString();
            $slot = [];
        }
        $menunggu = Booking::with('layanan', 'pelanggan')->where('status', 'menunggu')->orderBy('tanggal')->orderBy('jam')->limit(10)->get();
        return view('panel.booking.index', compact('data', 'tanggal', 'tab', 'slot', 'menunggu', 'q'));
    }

    public function create(Request $r)
    {
        $pel = $r->query('pelanggan') ? Pelanggan::with('hewan')->find($r->query('pelanggan')) : null;
        return view('panel.booking.form', ['layanan' => Layanan::where('aktif', true)->orderBy('kategori')->orderBy('urut')->get(), 'pel' => $pel, 'jenis' => Hewan::JENIS]);
    }

    /** Booking dari meja toko / telepon (langsung berstatus dikonfirmasi). */
    public function store(Request $r)
    {
        $d = $r->validate([
            'pelanggan_id' => 'nullable|integer|exists:pelanggan,id', 'hewan_id' => 'nullable|integer',
            'nama' => 'required_without:pelanggan_id|nullable|string|min:3|max:80', 'telepon' => ['required_without:pelanggan_id', 'nullable', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'nama_hewan' => 'required_without:hewan_id|nullable|string|max:50', 'jenis_hewan' => 'required|in:' . implode(',', Hewan::JENIS),
            'layanan_id' => 'required|integer', 'tanggal' => 'required|date_format:Y-m-d', 'jam' => 'nullable|date_format:H:i', 'tanggal_keluar' => 'nullable|date_format:Y-m-d', 'catatan' => 'nullable|string|max:300',
        ], ['telepon.regex' => 'Nomor WhatsApp tidak valid.'], ['nama_hewan' => 'nama hewan', 'layanan_id' => 'layanan']);
        if (! empty($d['hewan_id'])) {
            $h = Hewan::where('pelanggan_id', $d['pelanggan_id'] ?? 0)->findOrFail($d['hewan_id']);
            $d['nama_hewan'] = $h->nama;
            $d['jenis_hewan'] = $h->jenis;
        }
        $b = Jadwal::buat($d, 'toko');
        return redirect()->route('panel.booking.show', $b)->with('ok', 'Booking ' . $b->kode . ' dibuat.');
    }

    public function show(Booking $booking)
    {
        $booking->load('layanan', 'pelanggan', 'hewan.vaksin', 'transaksi');
        $riwayat = Booking::with('layanan')->where('hewan_id', $booking->hewan_id)->where('id', '!=', $booking->id)->latest('tanggal')->limit(5)->get();
        return view('panel.booking.show', ['b' => $booking, 'riwayat' => $riwayat]);
    }

    public function status(Request $r, Booking $booking)
    {
        $d = $r->validate(['status' => 'required|in:' . implode(',', array_keys(Booking::STATUS))]);
        if (! in_array($d['status'], Booking::ALUR[$booking->status] ?? [], true)) {
            return back()->with('error', 'Booking berstatus ' . Booking::STATUS[$booking->status] . ' tidak bisa diubah menjadi ' . Booking::STATUS[$d['status']] . '.');
        }
        if ($d['status'] === 'batal' && $booking->transaksi_id) {
            return back()->with('error', 'Booking sudah dibayar. Batalkan transaksinya dulu.');
        }
        $booking->update(['status' => $d['status']]);
        $pesan = ['dikonfirmasi' => 'dikonfirmasi', 'dikerjakan' => 'sedang dikerjakan', 'selesai' => 'sudah selesai dan siap dijemput', 'batal' => 'dibatalkan'][$d['status']];
        $wa = $booking->pelanggan->waLink('Halo ' . $booking->pelanggan->nama . ', booking ' . $booking->layanan->nama . ' untuk ' . $booking->nama_hewan . ' (' . $booking->kode . ') ' . $pesan . '. Terima kasih - ' . Petshop::get('nama'));
        return back()->with('ok', 'Status booking diubah menjadi ' . Booking::STATUS[$d['status']] . '.')->with('wa', $wa);
    }
}
