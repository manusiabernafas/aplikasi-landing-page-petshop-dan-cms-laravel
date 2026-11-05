<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\PublikController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::controller(PublikController::class)->group(function () {
    Route::get('/', 'beranda')->name('beranda');
    Route::get('/booking', 'booking')->name('booking');
    Route::post('/booking', 'simpanBooking')->middleware('throttle:booking')->name('booking.simpan');
    Route::get('/booking/slot', 'slot')->middleware('throttle:slot')->name('booking.slot');
    Route::get('/cek-booking', 'cek')->middleware('throttle:cek')->name('cek');
});

// Masuk staf
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'masukForm'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk'])->name('masuk.proses');
});
Route::post('/keluar', [AuthController::class, 'keluar'])->middleware('auth')->name('keluar');
Route::get('/login', fn () => redirect()->route('masuk'))->name('login');

// Panel staf
Route::middleware(['auth', 'peran:admin,kasir,groomer'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [Panel\DasborController::class, 'index'])->name('dasbor');

    // Booking grooming, klinik & penitipan (semua staf)
    Route::controller(Panel\BookingController::class)->prefix('booking')->name('booking.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/baru', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{booking}', 'show')->whereNumber('booking')->name('show');
        Route::post('/{booking}/status', 'status')->whereNumber('booking')->name('status');
    });

    // Pelanggan & hewan (semua staf)
    Route::get('pelanggan/cari', [Panel\PelangganController::class, 'cari'])->name('pelanggan.cari');
    Route::resource('pelanggan', Panel\PelangganController::class)->parameters(['pelanggan' => 'pelanggan']);
    Route::controller(Panel\PelangganController::class)->group(function () {
        Route::post('pelanggan/{pelanggan}/hewan', 'simpanHewan')->name('hewan.store');
        Route::put('hewan/{hewan}', 'ubahHewan')->name('hewan.update');
        Route::delete('hewan/{hewan}', 'hapusHewan')->name('hewan.destroy');
        Route::post('hewan/{hewan}/vaksin', 'simpanVaksin')->name('vaksin.store');
        Route::delete('vaksin/{vaksin}', 'hapusVaksin')->name('vaksin.destroy');
    });

    // Kasir & stok (admin, kasir)
    Route::middleware('peran:admin,kasir')->group(function () {
        Route::get('kasir', [Panel\KasirController::class, 'index'])->name('kasir');
        Route::post('kasir', [Panel\KasirController::class, 'simpan'])->name('kasir.simpan');
        Route::get('transaksi', [Panel\KasirController::class, 'daftar'])->name('transaksi.index');
        Route::get('transaksi/{transaksi}', [Panel\KasirController::class, 'struk'])->name('transaksi.show');
        Route::get('produk/{produk}/stok', [Panel\ProdukController::class, 'stok'])->name('produk.stok');
        Route::post('produk/{produk}/stok', [Panel\ProdukController::class, 'simpanStok'])->name('produk.stok.simpan');
        Route::resource('produk', Panel\ProdukController::class)->except('show')->parameters(['produk' => 'produk']);
    });

    // Khusus admin
    Route::middleware('peran:admin')->group(function () {
        Route::post('transaksi/{transaksi}/batal', [Panel\KasirController::class, 'batal'])->name('transaksi.batal');
        Route::resource('layanan', Panel\LayananController::class)->except('show')->parameters(['layanan' => 'layanan']);
        Route::get('laporan', [Panel\LaporanController::class, 'index'])->name('laporan');
        Route::get('laporan/csv', [Panel\LaporanController::class, 'csv'])->name('laporan.csv');
        Route::get('pengguna', [Panel\PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('pengguna', [Panel\PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('pengguna/{user}', [Panel\PenggunaController::class, 'update'])->name('pengguna.update');
        Route::post('pengguna/{user}/reset', [Panel\PenggunaController::class, 'reset'])->name('pengguna.reset');
        Route::get('pengaturan', [Panel\PengaturanController::class, 'index'])->name('pengaturan');
        Route::post('pengaturan', [Panel\PengaturanController::class, 'simpan'])->name('pengaturan.simpan');
    });

    Route::get('profil', [Panel\ProfilController::class, 'index'])->name('profil');
    Route::post('profil', [Panel\ProfilController::class, 'sandi'])->name('profil.sandi');
});
