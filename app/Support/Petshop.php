<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Pengaturan toko (tabel pengaturan, kunci-nilai). */
class Petshop
{
    public const DEFAULT = [
        'nama' => 'Kandang Ceria',
        'tagline' => 'Petshop, grooming & pet hotel',
        'alamat' => 'Jl. Kaliurang Km 6,5 No. 21, Sleman, Yogyakarta',
        'whatsapp' => '081234567890',
        'telepon' => '(0274) 880-123',
        'email' => 'halo@kandangceria.test',
        'jam_buka' => "Senin - Sabtu: 08.00 - 20.00\nMinggu: 09.00 - 17.00",
        'jam_grooming' => '09:00,10:00,11:00,13:00,14:00,15:00,16:00',
        'kapasitas_slot' => '2',
        'jumlah_kandang' => '12',
        'maks_hari_booking' => '30',
        'catatan_struk' => 'Terima kasih sudah mampir. Barang yang sudah dibeli dapat ditukar 3 hari dengan struk.',
        'logo' => '',
    ];

    private static ?array $cache = null;

    public static function semua(): array
    {
        return self::$cache ??= array_merge(self::DEFAULT, DB::table('pengaturan')->pluck('nilai', 'kunci')->map(fn ($v) => (string) $v)->all());
    }

    public static function get(string $k): string
    {
        return self::semua()[$k] ?? '';
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $k => $v) {
            DB::table('pengaturan')->updateOrInsert(['kunci' => $k], ['nilai' => (string) $v]);
        }
        self::$cache = null;
    }

    /** Slot jam grooming, mis. ['09:00', '10:00', ...] */
    public static function slot(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', self::get('jam_grooming'))), fn ($j) => preg_match('/^\d{2}:\d{2}$/', $j)));
    }

    public static function waLink(string $pesan = ''): string
    {
        $no = \App\Models\Pelanggan::normalHp(self::get('whatsapp'));
        return $no === '' ? '' : 'https://wa.me/' . $no . ($pesan !== '' ? '?text=' . rawurlencode($pesan) : '');
    }
}
