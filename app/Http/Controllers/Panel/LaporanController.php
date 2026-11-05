<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function rentang(Request $r): array
    {
        $dari = $r->query('dari') && strtotime($r->query('dari')) ? Carbon::parse($r->query('dari'))->startOfDay() : now()->startOfMonth();
        $sampai = $r->query('sampai') && strtotime($r->query('sampai')) ? Carbon::parse($r->query('sampai'))->endOfDay() : now()->endOfDay();
        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
        }
        return [$dari, $sampai];
    }

    public function index(Request $r)
    {
        [$dari, $sampai] = $this->rentang($r);
        $trx = Transaksi::where('batal', false)->whereBetween('waktu', [$dari, $sampai]);
        $item = TransaksiItem::whereHas('transaksi', fn ($q) => $q->where('batal', false)->whereBetween('waktu', [$dari, $sampai]));
        $omzet = (int) (clone $trx)->sum('total');
        $diskon = (int) (clone $trx)->sum('diskon');
        $jumlah = (clone $trx)->count();
        $modal = (int) (clone $item)->where('tipe', 'produk')->sum(DB::raw('harga_beli * qty'));
        $produk = (int) (clone $item)->where('tipe', 'produk')->sum('subtotal');
        $layanan = (int) (clone $item)->where('tipe', 'layanan')->sum('subtotal');
        $metode = (clone $trx)->selectRaw('metode, count(*) as n, sum(total) as nilai')->groupBy('metode')->get();
        $harian = (clone $trx)->selectRaw('date(waktu) as t, count(*) as n, sum(total) as nilai')->groupBy('t')->orderBy('t')->get();
        $topProduk = (clone $item)->where('tipe', 'produk')->select('nama', DB::raw('sum(qty) as qty'), DB::raw('sum(subtotal) as nilai'), DB::raw('sum((harga - harga_beli) * qty) as laba'))->groupBy('nama')->orderByDesc('nilai')->limit(10)->get();
        $topLayanan = (clone $item)->where('tipe', 'layanan')->join('layanan', 'layanan.id', '=', 'transaksi_item.layanan_id')
            ->select('layanan.nama', 'layanan.kategori', DB::raw('sum(transaksi_item.qty) as qty'), DB::raw('sum(transaksi_item.subtotal) as nilai'))->groupBy('layanan.nama', 'layanan.kategori')->orderByDesc('nilai')->limit(10)->get();
        $booking = Booking::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        return view('panel.laporan', compact('dari', 'sampai', 'omzet', 'diskon', 'jumlah', 'modal', 'produk', 'layanan', 'metode', 'harian', 'topProduk', 'topLayanan', 'booking'));
    }

    /** CSV per item transaksi; sel diawali = + - @ diberi tanda kutip agar aman dibuka di Excel. */
    public function csv(Request $r)
    {
        [$dari, $sampai] = $this->rentang($r);
        $sel = fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : $v;
        return response()->streamDownload(function () use ($dari, $sampai, $sel) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF");
            fputcsv($o, ['Nomor', 'Waktu', 'Kasir', 'Pelanggan', 'Metode', 'Tipe', 'Item', 'Harga', 'Qty', 'Subtotal', 'Harga Beli', 'Diskon Transaksi', 'Total Transaksi'], ';', '"', '');
            Transaksi::with('item', 'pelanggan', 'user')->where('batal', false)->whereBetween('waktu', [$dari, $sampai])->orderBy('waktu')->chunk(200, function ($ts) use ($o, $sel) {
                foreach ($ts as $t) {
                    foreach ($t->item as $i) {
                        fputcsv($o, array_map($sel, [$t->nomor, $t->waktu->format('Y-m-d H:i'), $t->user?->name, $t->pelanggan?->nama ?? 'Umum', $t->metode, $i->tipe, $i->nama, $i->harga, $i->qty, $i->subtotal, $i->harga_beli, $t->diskon, $t->total]), ';', '"', '');
                    }
                }
            });
            fclose($o);
        }, 'laporan-penjualan-' . $dari->format('Ymd') . '-' . $sampai->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
