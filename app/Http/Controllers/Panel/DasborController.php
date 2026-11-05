<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\Vaksin;
use Illuminate\Support\Facades\DB;

class DasborController extends Controller
{
    public function index()
    {
        $hari = now()->toDateString();
        $trx = Transaksi::where('batal', false);
        $omzetHari = (clone $trx)->whereDate('waktu', $hari)->sum('total');
        $trxHari = (clone $trx)->whereDate('waktu', $hari)->count();
        $omzetKemarin = (clone $trx)->whereDate('waktu', now()->subDay()->toDateString())->sum('total');

        $per = (clone $trx)->where('waktu', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('date(waktu) as t, sum(total) as omzet, count(*) as n')->groupBy('t')->pluck('omzet', 't');
        $grafik = collect(range(13, 0))->map(fn ($i) => ['t' => now()->subDays($i)->toDateString(), 'omzet' => (int) ($per[now()->subDays($i)->toDateString()] ?? 0)]);

        $jadwal = Booking::with('layanan', 'pelanggan')->whereDate('tanggal', $hari)->whereNotNull('jam')->where('status', '!=', 'batal')->orderBy('jam')->get();
        $dititip = Booking::with('layanan', 'pelanggan')->whereNotNull('kandang')->whereIn('status', ['dikonfirmasi', 'dikerjakan'])
            ->whereDate('tanggal', '<=', $hari)->whereDate('tanggal_keluar', '>=', $hari)->orderBy('tanggal_keluar')->get();
        $menunggu = Booking::where('status', 'menunggu')->count();
        $menipis = Produk::where('aktif', true)->whereColumn('stok', '<=', 'stok_min')->orderBy('stok')->limit(6)->get();
        $vaksin = Vaksin::with('hewan.pelanggan')->whereBetween('berikutnya', [$hari, now()->addDays(14)->toDateString()])
            ->whereIn('id', Vaksin::selectRaw('max(id)')->groupBy('hewan_id', 'nama'))->orderBy('berikutnya')->limit(6)->get();
        $laris = TransaksiItem::where('tipe', 'produk')->whereHas('transaksi', fn ($q) => $q->where('batal', false)->where('waktu', '>=', now()->startOfMonth()))
            ->select('nama', DB::raw('sum(qty) as qty'), DB::raw('sum(subtotal) as nilai'))->groupBy('nama')->orderByDesc('qty')->limit(5)->get();

        return view('panel.dasbor', compact('omzetHari', 'trxHari', 'omzetKemarin', 'grafik', 'jadwal', 'dititip', 'menunggu', 'menipis', 'vaksin', 'laris'));
    }
}
