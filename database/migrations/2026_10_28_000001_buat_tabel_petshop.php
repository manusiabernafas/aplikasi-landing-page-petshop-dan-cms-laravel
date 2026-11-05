<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('peran', 10)->default('kasir')->after('email'); // admin | kasir | groomer
            $t->string('telepon', 20)->nullable()->after('peran');
            $t->boolean('aktif')->default(true)->after('telepon');
        });

        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 60)->primary();
            $t->text('nilai')->nullable();
        });

        Schema::create('pelanggan', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 80);
            $t->string('telepon', 20)->index();
            $t->string('alamat', 200)->nullable();
            $t->string('catatan', 300)->nullable();
            $t->timestamps();
        });

        Schema::create('hewan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $t->string('nama', 50);
            $t->string('jenis', 20);
            $t->string('ras', 50)->nullable();
            $t->string('kelamin', 10)->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->decimal('berat', 5, 2)->nullable();
            $t->string('warna', 40)->nullable();
            $t->string('catatan', 300)->nullable();
            $t->timestamps();
        });

        Schema::create('vaksin', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hewan_id')->constrained('hewan')->cascadeOnDelete();
            $t->string('nama', 60);
            $t->date('tanggal');
            $t->date('berikutnya')->nullable()->index();
            $t->string('catatan', 200)->nullable();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('produk', function (Blueprint $t) {
            $t->id();
            $t->string('sku', 30)->unique();
            $t->string('nama', 100);
            $t->string('kategori', 30)->index();
            $t->string('untuk', 20)->default('Semua');
            $t->unsignedInteger('harga_beli')->default(0);
            $t->unsignedInteger('harga_jual');
            $t->integer('stok')->default(0);
            $t->unsignedInteger('stok_min')->default(5);
            $t->string('satuan', 15)->default('pcs');
            $t->string('deskripsi', 400)->nullable();
            $t->string('foto')->nullable();
            $t->boolean('unggulan')->default(false);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('stok_mutasi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('jenis', 15); // masuk | keluar | penyesuaian | penjualan | batal
            $t->integer('qty');
            $t->integer('stok_akhir');
            $t->string('keterangan', 150)->nullable();
            $t->dateTime('waktu')->index();
        });

        Schema::create('layanan', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 80);
            $t->string('kategori', 15)->index(); // grooming | penitipan | klinik
            $t->unsignedInteger('harga');
            $t->string('satuan', 15)->default('sesi');
            $t->unsignedSmallInteger('durasi')->default(60);
            $t->string('untuk', 20)->default('Semua');
            $t->string('deskripsi', 300)->nullable();
            $t->boolean('publik')->default(true);
            $t->boolean('aktif')->default(true);
            $t->unsignedSmallInteger('urut')->default(0);
            $t->timestamps();
        });

        Schema::create('transaksi', function (Blueprint $t) {
            $t->id();
            $t->string('nomor', 24)->unique();
            $t->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedInteger('subtotal');
            $t->unsignedInteger('diskon')->default(0);
            $t->unsignedInteger('total');
            $t->unsignedInteger('bayar');
            $t->unsignedInteger('kembali')->default(0);
            $t->string('metode', 10);
            $t->string('catatan', 200)->nullable();
            $t->boolean('batal')->default(false);
            $t->dateTime('waktu')->index();
            $t->timestamps();
        });

        Schema::create('booking', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 12)->unique();
            $t->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $t->foreignId('hewan_id')->nullable()->constrained('hewan')->nullOnDelete();
            $t->foreignId('layanan_id')->constrained('layanan');
            $t->string('nama_hewan', 50);
            $t->string('jenis_hewan', 20);
            $t->date('tanggal')->index();
            $t->string('jam', 5)->nullable();
            $t->date('tanggal_keluar')->nullable();
            $t->unsignedSmallInteger('kandang')->nullable();
            $t->string('catatan', 300)->nullable();
            $t->string('status', 12)->default('menunggu')->index(); // menunggu | dikonfirmasi | dikerjakan | selesai | batal
            $t->string('sumber', 8)->default('online');
            $t->foreignId('transaksi_id')->nullable()->constrained('transaksi')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('transaksi_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $t->string('tipe', 8); // produk | layanan
            $t->foreignId('produk_id')->nullable()->constrained('produk')->nullOnDelete();
            $t->foreignId('layanan_id')->nullable()->constrained('layanan')->nullOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained('booking')->nullOnDelete();
            $t->string('nama', 120);
            $t->unsignedInteger('harga');
            $t->unsignedInteger('harga_beli')->default(0);
            $t->unsignedInteger('qty');
            $t->unsignedInteger('subtotal');
        });
    }

    public function down(): void
    {
        foreach (['transaksi_item', 'booking', 'transaksi', 'layanan', 'stok_mutasi', 'produk', 'vaksin', 'hewan', 'pelanggan', 'pengaturan'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['peran', 'telepon', 'aktif']));
    }
};
