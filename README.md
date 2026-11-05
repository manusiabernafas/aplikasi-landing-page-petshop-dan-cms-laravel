# Kandang Ceria: Aplikasi Manajemen Petshop, Grooming & Pet Hotel (Laravel 12 + MySQL)

Website publik + panel toko untuk petshop, salon grooming, dan pet hotel: booking online dengan slot jam dan kandang, kasir POS, stok produk, kartu hewan dan vaksin, serta laporan.

## Kebutuhan
- PHP 8.2 atau lebih baru (XAMPP / Laragon / hosting), ekstensi pdo_mysql, mbstring, fileinfo, gd tidak wajib
- MySQL 5.7+ / MariaDB 10.3+
- Composer (https://getcomposer.org)

## Instalasi cepat (XAMPP)
1. Ekstrak ke `C:\xampp\htdocs\petshop`.
2. Nyalakan Apache & MySQL.
3. Jalankan `setup.bat` (Windows) atau `bash setup.sh` (Linux/macOS). Database `petshop` dan data contoh dibuat otomatis.
   Jika MySQL memakai password, isi `DB_PASSWORD` di `.env` (salin dari `.env.example`) lebih dulu.
4. Buka http://localhost/petshop dan panel di http://localhost/petshop/masuk

| Peran | Email | Sandi | Akses |
|---|---|---|---|
| Admin | admin@petshop.test | admin123 | semua menu |
| Kasir | kasir@petshop.test | kasir123 | kasir, transaksi, produk & stok, booking, pelanggan |
| Groomer | groomer@petshop.test | groomer123 | jadwal & booking, pelanggan & hewan |

Segera ganti semua kata sandi setelah instalasi.

## Fitur utama
- **Website publik**: landing page, layanan & harga, produk unggulan, booking online (slot jam / tanggal titip-jemput), cek status booking dengan kode + 4 digit WA.
- **Kasir**: produk + layanan dalam satu layar, diskon, tunai/QRIS/transfer, kembalian, struk 58 mm, tagih booking.
- **Stok**: stok masuk, keluar, opname; riwayat mutasi; peringatan stok minimum.
- **Jadwal**: tampilan per jam untuk grooming & klinik, denah kandang pet hotel per tanggal, alur status + tombol kabari WhatsApp.
- **Pelanggan & hewan**: kartu hewan (ras, umur, berat, catatan), riwayat vaksin, pengingat vaksin jatuh tempo.
- **Laporan**: omzet, laba kotor, metode bayar, produk & layanan terlaris, ekspor CSV.

## Pengaturan penting (menu Pengaturan)
- `Jam slot grooming & klinik`: contoh `09:00,10:00,11:00,13:00`.
- `Kapasitas per jam`: jumlah hewan yang bisa ditangani per jam per kategori (biasanya = jumlah groomer).
- `Jumlah kandang`: kapasitas pet hotel.
- Nama toko, logo, WhatsApp, alamat, jam buka, catatan di struk.

## Struktur
- `app/Support/Jadwal.php`: aturan slot jam, kandang kosong, dan pembuatan booking.
- `app/Http/Controllers/Panel/KasirController.php`: transaksi kasir dengan penguncian stok.
- `app/Models/StokMutasi.php`: setiap perubahan stok lewat `StokMutasi::catat()`.
- `resources/views/publik`: website publik; `resources/views/panel`: panel toko.
- `public/css/situs.css`, `public/css/panel.css`: gaya tampilan (tanpa framework CSS, font disertakan).

## Hosting
Arahkan document root ke folder `public/`. Di `.env` set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domainmu`. Jalankan `php artisan migrate --force` (tanpa `--seed` agar tidak memakai data contoh), lalu buat akun admin lewat `php artisan tinker`.

## Keamanan
CSRF di semua form, password bcrypt, batas percobaan masuk, rate limit booking & cek status, honeypot form publik, validasi gambar unggahan (folder `public/uploads` tidak bisa menjalankan skrip), header keamanan, dan CSV aman dari formula injection.
