<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hewan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Support\Jadwal;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function beranda()
    {
        $layanan = Layanan::where('aktif', true)->where('publik', true)->orderBy('urut')->orderBy('harga')->get()->groupBy('kategori');
        $produk = Produk::where('aktif', true)->where('unggulan', true)->where('stok', '>', 0)->orderBy('nama')->limit(8)->get();
        $angka = [
            'pelanggan' => Pelanggan::count(),
            'hewan' => Hewan::count(),
            'grooming' => Booking::where('status', 'selesai')->count(),
            'produk' => Produk::where('aktif', true)->count(),
        ];
        return view('publik.beranda', compact('layanan', 'produk', 'angka'));
    }

    public function booking(Request $r)
    {
        $layanan = Layanan::where('aktif', true)->where('publik', true)->orderBy('urut')->orderBy('harga')->get();
        return view('publik.booking', ['layanan' => $layanan, 'pilih' => (int) $r->query('layanan'), 'jenis' => Hewan::JENIS]);
    }

    /** Sisa slot jam (grooming/klinik) atau kandang (penitipan) untuk tanggal & layanan. */
    public function slot(Request $r)
    {
        $d = $r->validate(['layanan' => 'required|integer', 'tanggal' => 'required|date_format:Y-m-d', 'keluar' => 'nullable|date_format:Y-m-d']);
        $l = Layanan::where('aktif', true)->where('publik', true)->findOrFail($d['layanan']);
        if ($l->penitipan()) {
            $keluar = $d['keluar'] ?? date('Y-m-d', strtotime($d['tanggal'] . ' +1 day'));
            return ['penitipan' => true, 'kandang' => $keluar > $d['tanggal'] ? count(Jadwal::kandangKosong($d['tanggal'], $keluar)) : 0];
        }
        return ['penitipan' => false, 'slot' => Jadwal::slot($d['tanggal'], $l->kategori)];
    }

    public function simpanBooking(Request $r)
    {
        if ($r->filled('situs')) { // honeypot
            return redirect()->route('booking')->with('ok', 'Terima kasih.');
        }
        $d = $r->validate([
            'nama' => 'required|string|min:3|max:80',
            'telepon' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'nama_hewan' => 'required|string|max:50',
            'jenis_hewan' => 'required|in:' . implode(',', Hewan::JENIS),
            'layanan_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'jam' => 'nullable|date_format:H:i',
            'tanggal_keluar' => 'nullable|date_format:Y-m-d',
            'catatan' => 'nullable|string|max:300',
        ], ['telepon.regex' => 'Nomor WhatsApp tidak valid (contoh 081234567890).'], ['nama_hewan' => 'nama hewan', 'jenis_hewan' => 'jenis hewan', 'layanan_id' => 'layanan', 'tanggal_keluar' => 'tanggal jemput']);
        $b = Jadwal::buat($d, 'online');
        return redirect()->route('cek', ['kode' => $b->kode, 'hp' => substr(preg_replace('/\D/', '', $d['telepon']), -4)])->with('baru', true);
    }

    /** Cek status booking: kode + 4 digit terakhir nomor WhatsApp. */
    public function cek(Request $r)
    {
        $kode = strtoupper(trim((string) $r->query('kode')));
        $hp = preg_replace('/\D/', '', (string) $r->query('hp'));
        $b = null;
        $salah = false;
        if ($kode !== '') {
            $b = Booking::with('layanan', 'pelanggan')->where('kode', $kode)->first();
            if (! $b || strlen($hp) !== 4 || substr(preg_replace('/\D/', '', $b->pelanggan->telepon), -4) !== $hp) {
                $b = null;
                $salah = true;
            }
        }
        return view('publik.cek', compact('b', 'kode', 'hp', 'salah'));
    }
}
