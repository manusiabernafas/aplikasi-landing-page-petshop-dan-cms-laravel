<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Hewan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Aturan jadwal: slot jam grooming/klinik dan kandang penitipan. */
class Jadwal
{
    public const AKTIF = ['menunggu', 'dikonfirmasi', 'dikerjakan'];

    /** Sisa kuota tiap slot jam pada tanggal itu untuk kategori layanan (grooming/klinik). */
    public static function slot(string $tanggal, string $kategori): array
    {
        $kap = max(1, (int) Petshop::get('kapasitas_slot'));
        $pakai = Booking::query()->join('layanan', 'layanan.id', '=', 'booking.layanan_id')
            ->where('layanan.kategori', $kategori)->whereDate('booking.tanggal', $tanggal)->whereIn('booking.status', self::AKTIF)
            ->groupBy('booking.jam')->selectRaw('booking.jam, count(*) as n')->pluck('n', 'jam');
        $kini = now();
        return collect(Petshop::slot())->map(fn ($j) => [
            'jam' => $j,
            'sisa' => max(0, $kap - (int) ($pakai[$j] ?? 0)),
            'lewat' => Carbon::parse($tanggal . ' ' . $j)->lte($kini),
        ])->all();
    }

    /** Nomor kandang yang kosong untuk rentang [masuk, keluar). */
    public static function kandangKosong(string $masuk, string $keluar, ?int $kecuali = null): array
    {
        $n = max(1, (int) Petshop::get('jumlah_kandang'));
        $isi = Booking::whereNotNull('kandang')->whereIn('status', self::AKTIF)->when($kecuali, fn ($q) => $q->where('id', '!=', $kecuali))
            ->whereDate('tanggal', '<', $keluar)->whereDate('tanggal_keluar', '>', $masuk)->pluck('kandang')->all();
        return array_values(array_diff(range(1, $n), $isi));
    }

    /** Kode acak tanpa huruf/angka yang mirip (0/O, 1/I/L). */
    public static function acak(int $n): string
    {
        $h = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= $h[random_int(0, strlen($h) - 1)];
        }
        return $s;
    }

    /**
     * Buat booking baru (dari halaman publik atau meja kasir).
     * $d: nama, telepon, nama_hewan, jenis_hewan, layanan_id, tanggal, jam?, tanggal_keluar?, catatan?, hewan_id?, pelanggan_id?
     */
    public static function buat(array $d, string $sumber): Booking
    {
        $layanan = Layanan::where('aktif', true)->when($sumber === 'online', fn ($q) => $q->where('publik', true))->find($d['layanan_id'] ?? 0);
        if (! $layanan) {
            throw ValidationException::withMessages(['layanan_id' => 'Layanan tidak tersedia.']);
        }
        $hari = Carbon::parse($d['tanggal'])->startOfDay();
        $maks = now()->startOfDay()->addDays(max(1, (int) Petshop::get('maks_hari_booking')));
        if ($hari->lt(now()->startOfDay()) || $hari->gt($maks)) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal booking harus antara hari ini dan ' . tgl($maks) . '.']);
        }
        if ($layanan->untuk !== 'Semua' && $layanan->untuk !== $d['jenis_hewan']) {
            throw ValidationException::withMessages(['layanan_id' => 'Layanan ' . $layanan->nama . ' khusus untuk ' . strtolower($layanan->untuk) . '.']);
        }

        return DB::transaction(function () use ($d, $sumber, $layanan, $hari) {
            // Kunci baris layanan agar dua booking di slot/kandang terakhir tidak lolos bersamaan
            Layanan::where('kategori', $layanan->kategori)->lockForUpdate()->get();
            $jam = null;
            $keluar = null;
            $kandang = null;
            if ($layanan->penitipan()) {
                $keluar = Carbon::parse($d['tanggal_keluar'] ?? '')->startOfDay();
                if (empty($d['tanggal_keluar']) || $keluar->lte($hari) || $hari->diffInDays($keluar) > 30) {
                    throw ValidationException::withMessages(['tanggal_keluar' => 'Tanggal jemput harus setelah tanggal titip (maks. 30 malam).']);
                }
                $kosong = self::kandangKosong($hari->toDateString(), $keluar->toDateString());
                if (! $kosong) {
                    throw ValidationException::withMessages(['tanggal' => 'Kandang penuh di tanggal itu. Coba tanggal lain atau hubungi kami.']);
                }
                $kandang = $kosong[0];
            } else {
                $jam = (string) ($d['jam'] ?? '');
                $s = collect(self::slot($hari->toDateString(), $layanan->kategori))->firstWhere('jam', $jam);
                if (! $s) {
                    throw ValidationException::withMessages(['jam' => 'Pilih jam yang tersedia.']);
                }
                if ($s['lewat']) {
                    throw ValidationException::withMessages(['jam' => 'Jam ' . $jam . ' sudah lewat. Pilih jam lain.']);
                }
                if ($s['sisa'] < 1) {
                    throw ValidationException::withMessages(['jam' => 'Jam ' . $jam . ' sudah penuh. Pilih jam lain.']);
                }
            }

            $pel = ! empty($d['pelanggan_id']) ? Pelanggan::findOrFail($d['pelanggan_id']) : null;
            if (! $pel) {
                $hp = Pelanggan::normalHp($d['telepon']);
                $pel = Pelanggan::whereIn('telepon', [$hp, '0' . substr($hp, 2), '+' . $hp])->first()
                    ?? Pelanggan::create(['nama' => $d['nama'], 'telepon' => $d['telepon']]);
            }
            $hewan = ! empty($d['hewan_id']) ? $pel->hewan()->find($d['hewan_id']) : null;
            $hewan ??= $pel->hewan()->whereRaw('lower(nama) = ?', [Str::lower($d['nama_hewan'])])->where('jenis', $d['jenis_hewan'])->first()
                ?? Hewan::create(['pelanggan_id' => $pel->id, 'nama' => $d['nama_hewan'], 'jenis' => $d['jenis_hewan']]);

            do {
                $kode = 'BK-' . self::acak(5);
            } while (Booking::where('kode', $kode)->exists());

            return Booking::create([
                'kode' => $kode, 'pelanggan_id' => $pel->id, 'hewan_id' => $hewan->id, 'layanan_id' => $layanan->id,
                'nama_hewan' => $hewan->nama, 'jenis_hewan' => $hewan->jenis, 'tanggal' => $hari->toDateString(), 'jam' => $jam,
                'tanggal_keluar' => $keluar?->toDateString(), 'kandang' => $kandang, 'catatan' => $d['catatan'] ?? null,
                'status' => $sumber === 'toko' ? 'dikonfirmasi' : 'menunggu', 'sumber' => $sumber,
            ]);
        });
    }
}
