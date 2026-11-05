<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Hewan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\StokMutasi;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vaksin;
use App\Support\Jadwal;
use App\Support\Petshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Satu transaksi DB: ribuan baris contoh tersimpan dalam hitungan detik
        DB::transaction(fn () => $this->isi());
    }

    private function isi(): void
    {
        mt_srand(28);
        Petshop::simpan(Petshop::DEFAULT);
        $admin = User::create(['name' => 'Rani Puspita', 'email' => 'admin@petshop.test', 'peran' => 'admin', 'telepon' => '081234567890', 'password' => 'admin123', 'aktif' => true]);
        $kasir = User::create(['name' => 'Dimas Saputra', 'email' => 'kasir@petshop.test', 'peran' => 'kasir', 'telepon' => '081298760011', 'password' => 'kasir123', 'aktif' => true]);
        User::create(['name' => 'Ayu Lestari', 'email' => 'groomer@petshop.test', 'peran' => 'groomer', 'telepon' => '081298760022', 'password' => 'groomer123', 'aktif' => true]);

        // Layanan
        $L = [
            ['Mandi Sehat Kucing', 'grooming', 45000, 'sesi', 60, 'Kucing', 'Mandi sampo anti kutu, potong kuku, bersih telinga, sisir & keringkan.'],
            ['Grooming Lengkap Kucing', 'grooming', 85000, 'sesi', 90, 'Kucing', 'Mandi, cukur area, potong kuku, bersih telinga & mata, parfum.'],
            ['Mandi Jamur & Kutu', 'grooming', 75000, 'sesi', 90, 'Semua', 'Sampo medicated untuk jamur, kutu, dan kulit bermasalah.'],
            ['Grooming Anjing Kecil', 'grooming', 95000, 'sesi', 90, 'Anjing', 'Untuk ras kecil di bawah 10 kg: mandi, trimming, potong kuku.'],
            ['Grooming Anjing Besar', 'grooming', 150000, 'sesi', 120, 'Anjing', 'Ras sedang & besar, termasuk blow dry dan deshedding.'],
            ['Cukur Lion Cut', 'grooming', 110000, 'sesi', 120, 'Kucing', 'Potongan singa untuk kucing bulu panjang yang gimbal.'],
            ['Hotel Kucing Standar', 'penitipan', 40000, 'malam', 1440, 'Kucing', 'Kandang 60 cm, makan 2x, pasir diganti tiap hari, laporan foto via WA.'],
            ['Hotel Kucing Suite', 'penitipan', 65000, 'malam', 1440, 'Kucing', 'Kandang tingkat 90 cm, waktu main di ruang bebas, makanan premium.'],
            ['Hotel Anjing', 'penitipan', 75000, 'malam', 1440, 'Anjing', 'Kandang besar, jalan pagi & sore, makan sesuai kebiasaan.'],
            ['Vaksin Tricat (F3)', 'klinik', 150000, 'dosis', 30, 'Kucing', 'Vaksin dasar kucing, termasuk cek fisik singkat.'],
            ['Vaksin Rabies', 'klinik', 120000, 'dosis', 30, 'Semua', 'Vaksin rabies tahunan untuk kucing dan anjing.'],
            ['Konsultasi & Cek Kesehatan', 'klinik', 60000, 'sesi', 30, 'Semua', 'Pemeriksaan umum, timbang berat, saran pakan dan perawatan.'],
        ];
        foreach ($L as $i => $x) {
            Layanan::create(['nama' => $x[0], 'kategori' => $x[1], 'harga' => $x[2], 'satuan' => $x[3], 'durasi' => $x[4], 'untuk' => $x[5], 'deskripsi' => $x[6], 'publik' => true, 'aktif' => true, 'urut' => $i]);
        }
        $layanan = Layanan::all();

        // Produk
        $P = [
            ['Royal Canin Kitten 2 kg', 'Makanan', 'Kucing', 265000, 312000, 'pak', 1], ['Whiskas Tuna Dewasa 1,2 kg', 'Makanan', 'Kucing', 58000, 69000, 'pak', 1], ['Me-O Persian 1,1 kg', 'Makanan', 'Kucing', 52000, 63000, 'pak', 0],
            ['Bolt Ikan Repack 1 kg', 'Makanan', 'Kucing', 21000, 28000, 'pak', 1], ['Pro Plan Adult Chicken 1,5 kg', 'Makanan', 'Kucing', 205000, 245000, 'pak', 0], ['Felibite Salmon 500 g', 'Makanan', 'Kucing', 17000, 23000, 'pak', 0],
            ['Pedigree Beef Dewasa 1,5 kg', 'Makanan', 'Anjing', 72000, 86000, 'pak', 1], ['Royal Canin Mini Adult 2 kg', 'Makanan', 'Anjing', 248000, 295000, 'pak', 0], ['Wet Food Sheba Tuna 70 g', 'Makanan', 'Kucing', 9500, 13000, 'sachet', 1],
            ['Pelet Kelinci Alfalfa 1 kg', 'Makanan', 'Kelinci', 26000, 35000, 'pak', 0], ['Ciao Churu Tuna isi 4', 'Snack', 'Kucing', 19000, 26000, 'pak', 1], ['Dentastix Small isi 7', 'Snack', 'Anjing', 28000, 38000, 'pak', 0],
            ['Snack Stik Ayam Anjing', 'Snack', 'Anjing', 15000, 22000, 'pak', 0], ['Catnip Kering 20 g', 'Snack', 'Kucing', 8000, 15000, 'pak', 0],
            ['Pasir Gumpal Lavender 10 L', 'Pasir & Kebersihan', 'Kucing', 52000, 68000, 'karung', 1], ['Pasir Tofu Original 6 L', 'Pasir & Kebersihan', 'Kucing', 58000, 75000, 'pak', 1], ['Pasir Zeolit 25 kg', 'Pasir & Kebersihan', 'Kucing', 38000, 52000, 'karung', 0],
            ['Sampo Anti Kutu 250 ml', 'Pasir & Kebersihan', 'Semua', 32000, 45000, 'botol', 0], ['Tisu Basah Hewan isi 80', 'Pasir & Kebersihan', 'Semua', 18000, 27000, 'pak', 0],
            ['Obat Cacing Drontal Cat', 'Obat & Vitamin', 'Kucing', 22000, 32000, 'tablet', 0], ['Obat Tetes Kutu Revolution', 'Obat & Vitamin', 'Kucing', 78000, 98000, 'pipet', 1], ['Vitamin Bulu Nutri-Plus Gel', 'Obat & Vitamin', 'Semua', 95000, 125000, 'tube', 0],
            ['Salep Jamur Scabicid', 'Obat & Vitamin', 'Semua', 25000, 38000, 'tube', 0],
            ['Kalung Lonceng Kucing', 'Aksesori', 'Kucing', 7000, 15000, 'pcs', 0], ['Harness Anjing Ukuran M', 'Aksesori', 'Anjing', 48000, 75000, 'pcs', 1], ['Mangkuk Keramik Kucing', 'Aksesori', 'Kucing', 22000, 35000, 'pcs', 0],
            ['Sisir Kutu Stainless', 'Aksesori', 'Semua', 14000, 25000, 'pcs', 0], ['Litter Box Tertutup Jumbo', 'Kandang & Tas', 'Kucing', 115000, 155000, 'pcs', 0], ['Tas Ransel Kapsul Kucing', 'Kandang & Tas', 'Kucing', 145000, 199000, 'pcs', 1],
            ['Kandang Lipat 60 cm', 'Kandang & Tas', 'Semua', 210000, 275000, 'pcs', 0], ['Pet Cargo Kecil', 'Kandang & Tas', 'Semua', 85000, 115000, 'pcs', 0],
            ['Mainan Bulu Tongkat', 'Mainan', 'Kucing', 9000, 18000, 'pcs', 0], ['Bola Lonceng isi 3', 'Mainan', 'Kucing', 7000, 14000, 'pak', 0], ['Garukan Kardus Kucing', 'Mainan', 'Kucing', 28000, 45000, 'pcs', 1],
            ['Mainan Tali Gigit Anjing', 'Mainan', 'Anjing', 16000, 28000, 'pcs', 0], ['Terowongan Main Kucing', 'Mainan', 'Kucing', 55000, 85000, 'pcs', 0],
        ];
        $mulai = now()->subDays(62);
        foreach ($P as $i => $x) {
            $p = Produk::create(['sku' => 'PS-' . str_pad((string) (101 + $i), 4, '0', STR_PAD_LEFT), 'nama' => $x[0], 'kategori' => $x[1], 'untuk' => $x[2], 'harga_beli' => $x[3], 'harga_jual' => $x[4],
                'stok' => 0, 'stok_min' => $x[1] === 'Makanan' ? 6 : 4, 'satuan' => $x[5], 'unggulan' => (bool) $x[6], 'aktif' => true]);
            $q = ($x[1] === 'Makanan' || $x[1] === 'Pasir & Kebersihan' ? 60 : 25) + mt_rand(0, 10);
            StokMutasi::create(['produk_id' => $p->id, 'user_id' => $admin->id, 'jenis' => 'masuk', 'qty' => $q, 'stok_akhir' => $q, 'keterangan' => 'Stok awal', 'waktu' => $mulai]);
            $p->update(['stok' => $q]);
        }
        $produk = Produk::all();

        // Pelanggan & hewan
        $nama = ['Andini Putri', 'Bayu Pratama', 'Citra Maharani', 'Dodi Kurniawan', 'Elisa Rahmawati', 'Fajar Hidayat', 'Galih Prasetyo', 'Hana Safitri', 'Irfan Maulana', 'Jessica Tanoto',
            'Kevin Wijaya', 'Laras Sekar', 'Mega Purnama', 'Nadia Azzahra', 'Oki Setiawan', 'Putri Ayuningtyas', 'Raka Aditya', 'Sari Wulandari', 'Tommy Halim', 'Umi Nur Aini',
            'Vina Oktaviani', 'Wahyu Nugroho', 'Yoga Permana', 'Zahra Khairunnisa', 'Agnes Kristiani', 'Bima Sakti', 'Clara Natalia', 'Dewi Anggraini', 'Eko Prabowo', 'Fitri Handayani',
            'Gita Savitri', 'Hendra Gunawan', 'Intan Permatasari', 'Joko Susilo', 'Kirana Larasati', 'Lukas Hartono', 'Maya Septiani', 'Nico Saputra'];
        $kucing = ['Mochi', 'Oyen', 'Milo', 'Luna', 'Kitty', 'Snowy', 'Coco', 'Simba', 'Mimi', 'Leo', 'Nala', 'Bubu', 'Kiko', 'Tofu', 'Momo', 'Belang', 'Ciko', 'Pipi'];
        $anjing = ['Bruno', 'Max', 'Rocky', 'Choco', 'Bella', 'Hachi', 'Lucky', 'Doggy'];
        $rasK = ['Persia', 'Anggora', 'Domestik', 'British Shorthair', 'Maine Coon', 'Scottish Fold', 'Ragdoll', 'Domestik'];
        $rasA = ['Pomeranian', 'Shih Tzu', 'Golden Retriever', 'Poodle', 'Beagle', 'Kintamani'];
        $jalan = ['Jl. Kaliurang Km 7', 'Jl. Gejayan', 'Jl. Monjali', 'Perum Candi Gebang', 'Jl. Palagan Km 9', 'Jl. Affandi', 'Jl. Seturan Raya'];
        foreach ($nama as $i => $n) {
            $pel = Pelanggan::create(['nama' => $n, 'telepon' => '0812' . (27000000 + $i * 7919), 'alamat' => $jalan[$i % 7] . ' No. ' . (3 + $i * 2) . ', Sleman',
                'catatan' => $i % 9 === 0 ? 'Pelanggan lama, suka diingatkan jadwal vaksin.' : null]);
            $pel->created_at = now()->subDays(mt_rand(20, 200));
            $pel->save();
            $jml = $i % 4 === 0 ? 2 : 1;
            for ($k = 0; $k < $jml; $k++) {
                $anj = ($i + $k) % 5 === 0;
                $h = Hewan::create(['pelanggan_id' => $pel->id, 'nama' => $anj ? $anjing[($i + $k) % count($anjing)] : $kucing[($i * 2 + $k) % count($kucing)], 'jenis' => $anj ? 'Anjing' : 'Kucing',
                    'ras' => $anj ? $rasA[$i % count($rasA)] : $rasK[($i + $k) % count($rasK)], 'kelamin' => ($i + $k) % 2 ? 'Betina' : 'Jantan',
                    'tanggal_lahir' => now()->subMonths(mt_rand(5, 70))->toDateString(), 'berat' => $anj ? mt_rand(30, 280) / 10 : mt_rand(25, 62) / 10,
                    'warna' => ['Oranye', 'Putih', 'Hitam putih', 'Abu-abu', 'Calico', 'Cokelat'][($i + $k) % 6]]);
                $tv = now()->subDays(mt_rand(40, 340));
                Vaksin::create(['hewan_id' => $h->id, 'nama' => $anj ? 'Rabies' : 'Tricat (F3)', 'tanggal' => $tv->toDateString(), 'berikutnya' => $tv->copy()->addYear()->toDateString(), 'user_id' => $admin->id]);
                if ($i % 6 === 1) {
                    Vaksin::create(['hewan_id' => $h->id, 'nama' => 'Booster F4', 'tanggal' => now()->subDays(mt_rand(20, 40))->toDateString(), 'berikutnya' => now()->addDays(mt_rand(1, 12))->toDateString(), 'user_id' => $admin->id]);
                }
            }
        }
        $semuaHewan = Hewan::all();

        // Transaksi produk 60 hari terakhir
        for ($d = 60; $d >= 0; $d--) {
            $hari = now()->subDays($d);
            $n = ($hari->isWeekend() ? 9 : 6) + mt_rand(0, 4) - ($d === 0 ? 2 : 0);
            for ($j = 0; $j < $n; $j++) {
                $waktu = $d === 0 ? now()->subMinutes(mt_rand(5, 600)) : $hari->copy()->setTime(mt_rand(8, 19), mt_rand(0, 59));
                if ($waktu->gt(now()) || ! $waktu->isSameDay($hari)) {
                    continue;
                }
                $pel = mt_rand(0, 3) ? mt_rand(1, count($nama)) : null;
                $isi = [];
                for ($m = 0, $k = mt_rand(1, 3); $m < $k; $m++) {
                    $p = $produk[mt_rand(0, $produk->count() - 1)];
                    if (isset($isi[$p->id]) || $p->stok < 3) {
                        continue;
                    }
                    $isi[$p->id] = [$p, $p->satuan === 'sachet' ? mt_rand(3, 12) : mt_rand(1, 2)];
                }
                if (! $isi) {
                    continue;
                }
                $sub = array_sum(array_map(fn ($x) => $x[0]->harga_jual * $x[1], $isi));
                $diskon = $sub > 300000 && mt_rand(0, 2) === 0 ? 10000 : 0;
                $t = $this->transaksi($waktu, $pel, mt_rand(0, 2) ? $kasir->id : $admin->id, $sub, $diskon, ['tunai', 'tunai', 'qris', 'qris', 'transfer'][mt_rand(0, 4)]);
                foreach ($isi as [$p, $q]) {
                    $t->item()->create(['tipe' => 'produk', 'produk_id' => $p->id, 'nama' => $p->nama, 'harga' => $p->harga_jual, 'harga_beli' => $p->harga_beli, 'qty' => $q, 'subtotal' => $p->harga_jual * $q]);
                    $p->stok -= $q;
                    $p->save();
                    StokMutasi::create(['produk_id' => $p->id, 'user_id' => $t->user_id, 'jenis' => 'penjualan', 'qty' => -$q, 'stok_akhir' => $p->stok, 'keterangan' => $t->nomor, 'waktu' => $waktu]);
                }
            }
            if ($d % 7 === 3) { // kulakan mingguan
                foreach ($produk as $p) {
                    if ($p->stok < 20 && $p->id % 5 !== 0) {
                        $p->stok += 24;
                        $p->save();
                        StokMutasi::create(['produk_id' => $p->id, 'user_id' => $admin->id, 'jenis' => 'masuk', 'qty' => 24, 'stok_akhir' => $p->stok, 'keterangan' => 'Kulakan distributor', 'waktu' => $hari->copy()->setTime(8, 15)]);
                    }
                }
            }
        }

        // Booking: 30 hari lalu sampai 7 hari ke depan
        $slot = Petshop::slot();
        $kap = (int) Petshop::get('kapasitas_slot');
        $nKandang = (int) Petshop::get('jumlah_kandang');
        $catatan = [null, null, 'Agak galak saat potong kuku, mohon pelan.', 'Bulu rontok banyak, minta deshedding.', null, 'Alergi sampo wangi, pakai yang hipoalergenik.', null];
        $kandangPakai = [];
        $kini = now()->format('H:i');
        for ($d = -30; $d <= 7; $d++) {
            $hari = now()->addDays($d)->startOfDay();
            $n = $d > 0 ? max(2, 6 - $d + mt_rand(0, 2)) : 5 + mt_rand(0, 3);
            $dipakai = [];
            for ($j = 0; $j < $n; $j++) {
                $h = $semuaHewan[mt_rand(0, $semuaHewan->count() - 1)];
                $cocok = $layanan->filter(fn ($l) => $l->untuk === 'Semua' || $l->untuk === $h->jenis)->values();
                $l = $cocok[mt_rand(0, $cocok->count() - 1)];
                $jam = $keluar = $kandang = null;
                if ($l->penitipan()) {
                    $keluar = $hari->copy()->addDays(mt_rand(1, 5));
                    for ($c = 1; $c <= $nKandang && ! $kandang; $c++) {
                        if (! collect($kandangPakai[$c] ?? [])->contains(fn ($r) => $r[0]->lt($keluar) && $r[1]->gt($hari))) {
                            $kandang = $c;
                        }
                    }
                    if (! $kandang) {
                        continue;
                    }
                } else {
                    $bebas = array_values(array_filter($slot, fn ($s) => ($dipakai[$l->kategori . $s] ?? 0) < $kap));
                    if (! $bebas) {
                        continue;
                    }
                    $jam = $bebas[mt_rand(0, count($bebas) - 1)];
                    $dipakai[$l->kategori . $jam] = ($dipakai[$l->kategori . $jam] ?? 0) + 1;
                }
                $akhir = $keluar ?? $hari;
                if ($akhir->lt(now()->startOfDay())) {
                    $status = mt_rand(0, 11) ? 'selesai' : 'batal';
                } elseif ($d <= 0) {
                    $status = $kandang ? 'dikerjakan' : ($jam < $kini ? (mt_rand(0, 1) ? 'selesai' : 'dikerjakan') : 'dikonfirmasi');
                } else {
                    $status = mt_rand(0, 2) ? 'dikonfirmasi' : 'menunggu';
                }
                if ($kandang && $status !== 'batal') {
                    $kandangPakai[$kandang][] = [$hari->copy(), $keluar->copy()];
                }
                $b = Booking::create(['kode' => 'BK-' . Jadwal::acak(5), 'pelanggan_id' => $h->pelanggan_id, 'hewan_id' => $h->id, 'layanan_id' => $l->id, 'nama_hewan' => $h->nama, 'jenis_hewan' => $h->jenis,
                    'tanggal' => $hari->toDateString(), 'jam' => $jam, 'tanggal_keluar' => $keluar?->toDateString(), 'kandang' => $kandang, 'catatan' => $catatan[mt_rand(0, count($catatan) - 1)],
                    'status' => $status, 'sumber' => mt_rand(0, 2) ? 'online' : 'toko']);
                if ($status === 'selesai' && $akhir->lt(now()->startOfDay())) { // sudah dibayar di kasir
                    $biaya = $b->biaya();
                    $t = $this->transaksi($akhir->copy()->setTime(mt_rand(10, 18), mt_rand(0, 59)), $h->pelanggan_id, $kasir->id, $biaya, 0, ['tunai', 'qris', 'qris', 'transfer'][mt_rand(0, 3)]);
                    $t->item()->create(['tipe' => 'layanan', 'layanan_id' => $l->id, 'booking_id' => $b->id, 'nama' => $l->nama . ' · ' . $h->nama . ' (' . $b->kode . ')', 'harga' => $l->harga,
                        'qty' => $l->penitipan() ? $b->malam() : 1, 'subtotal' => $biaya]);
                    $b->update(['transaksi_id' => $t->id]);
                }
            }
        }

        // Nomor transaksi berurutan per hari sesuai waktu
        Transaksi::orderBy('waktu')->get()->groupBy(fn ($t) => $t->waktu->format('ymd'))->each(function ($g, $key) {
            $g->each(fn ($t) => $t->update(['nomor' => 'TMP-' . $t->id]));
            $g->values()->each(fn ($t, $i) => $t->update(['nomor' => 'TRX-' . $key . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)]));
        });
        // Beberapa stok menipis supaya peringatan terlihat
        foreach ([3 => 3, 17 => 2, 26 => 1] as $id => $sisa) {
            $p = Produk::find($id);
            StokMutasi::create(['produk_id' => $p->id, 'user_id' => $admin->id, 'jenis' => 'penyesuaian', 'qty' => $sisa - $p->stok, 'stok_akhir' => $sisa, 'keterangan' => 'Stok opname', 'waktu' => now()->subDay()->setTime(19, 30)]);
            $p->update(['stok' => $sisa]);
        }
    }

    private function transaksi($waktu, ?int $pel, int $user, int $sub, int $diskon, string $metode): Transaksi
    {
        $total = $sub - $diskon;
        $bayar = $metode === 'tunai' ? (int) (ceil($total / 50000) * 50000) : $total;
        $t = new Transaksi(['nomor' => 'TMP-' . uniqid(), 'pelanggan_id' => $pel, 'user_id' => $user, 'subtotal' => $sub, 'diskon' => $diskon, 'total' => $total, 'bayar' => $bayar,
            'kembali' => $bayar - $total, 'metode' => $metode, 'waktu' => $waktu]);
        $t->created_at = $t->updated_at = $waktu;
        $t->save();
        return $t;
    }
}
