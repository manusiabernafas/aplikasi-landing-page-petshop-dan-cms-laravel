@extends('publik.layout')
@php
  $k = \App\Support\Petshop::semua();
  $ikonKat = ['grooming' => 'gunting', 'penitipan' => 'hotel', 'klinik' => 'stetoskop'];
  $namaKat = ['grooming' => 'Grooming', 'penitipan' => 'Pet hotel', 'klinik' => 'Klinik & vaksin'];
@endphp
@section('isi')
<section class="hero">
  <div class="wadah">
    <div>
      <span class="chip"><i>{!! ikon('hati', 'ic') !!}</i> Dipercaya {{ number_format($angka['pelanggan'], 0, ',', '.') }}+ pemilik anabul</span>
      <h1>Anabul <span class="stab">bersih,</span> wangi, dan <span class="b">bahagia.</span></h1>
      <p class="lead">Grooming, pet hotel, vaksin, dan perlengkapan hewan dalam satu tempat. Pilih jadwal online, datang tepat waktu, tanpa antre.</p>
      <div class="aksi">
        <a class="btn" href="{{ route('booking') }}">Booking jadwal <span class="bulat">{!! ikon('panah') !!}</span></a>
        @if (\App\Support\Petshop::waLink())<a class="btn garis" href="{{ \App\Support\Petshop::waLink('Halo ' . $k['nama'] . ', saya mau tanya.') }}" target="_blank" rel="noopener">{!! ikon('wa') !!} Chat WhatsApp</a>@endif
      </div>
      <div class="bukti">
        <div class="av"><span style="background:#ffb38a">MO</span><span style="background:#9ee0c8">LU</span><span style="background:#ffd95a">BR</span><span style="background:#c9b6ff">+</span></div>
        <div><b>{{ number_format($angka['grooming'], 0, ',', '.') }} layanan</b><span class="muted kecil">selesai dikerjakan groomer kami</span></div>
      </div>
    </div>
    <div class="seni">
      <div class="bg"></div>
      <img src="{{ asset('img/hero.svg') }}" alt="Ilustrasi kucing dan anjing selesai grooming">
      <div class="lbl l1"><span class="ib">{!! ikon('gunting') !!}</span><span>Grooming 60-120 menit<small>sampo sesuai jenis bulu</small></span></div>
      <div class="lbl l2"><span class="ib">{!! ikon('hotel') !!}</span><span>{{ $k['jumlah_kandang'] }} kandang pet hotel<small>laporan foto tiap hari</small></span></div>
    </div>
  </div>
</section>

<div class="pita"><div class="jalan">@for ($i = 0; $i < 2; $i++)<span>Mandi sehat</span><span>Potong kuku</span><span>Lion cut</span><span>Pet hotel</span><span>Vaksin</span><span>Makanan premium</span><span>Pasir & vitamin</span>@endfor</div></div>

<section class="blok" id="layanan">
  <div class="wadah">
    <div class="kepala">
      <div><span class="kicker">{!! ikon('jejak') !!} Layanan kami</span><h2>Perawatan lengkap dari ujung kuku sampai ujung ekor</h2></div>
      <div class="tab-l" role="tablist">
        @foreach ($layanan as $kat => $isi)<button type="button" data-kat="{{ $kat }}" class="{{ $loop->first ? 'on' : '' }}">{!! ikon($ikonKat[$kat] ?? 'jejak') !!} {{ $namaKat[$kat] ?? $kat }}</button>@endforeach
      </div>
    </div>
    @foreach ($layanan as $kat => $isi)
      <div class="lay" data-panel="{{ $kat }}" @unless ($loop->first) hidden @endunless>
        @foreach ($isi as $l)
          <div class="kartu-l {{ $kat }}">
            <span class="ikon">{!! ikon($ikonKat[$kat] ?? 'jejak') !!}</span>
            <h3>{{ $l->nama }}</h3>
            <p>{{ $l->deskripsi }}</p>
            <div class="meta"><span class="tag">{!! ikon('jejak') !!} {{ $l->untuk === 'Semua' ? 'Kucing & anjing' : $l->untuk }}</span>@if (! $l->penitipan())<span class="tag">{!! ikon('jam') !!} {{ $l->durasi }} menit</span>@endif</div>
            <div class="harga"><b>{{ rp($l->harga) }}</b><span class="muted">/ {{ $l->satuan }}</span></div>
            <a class="btn kecil peach" href="{{ route('booking', ['layanan' => $l->id]) }}">Pilih jadwal</a>
          </div>
        @endforeach
      </div>
    @endforeach
    <div class="angka">
      <div><b>{{ number_format($angka['pelanggan'], 0, ',', '.') }}</b><span>pelanggan terdaftar</span></div>
      <div><b>{{ number_format($angka['hewan'], 0, ',', '.') }}</b><span>anabul dirawat</span></div>
      <div><b>{{ number_format($angka['grooming'], 0, ',', '.') }}</b><span>layanan selesai</span></div>
      <div><b>{{ number_format($angka['produk'], 0, ',', '.') }}</b><span>produk tersedia</span></div>
    </div>
  </div>
</section>

<section class="blok">
  <div class="wadah fitur">
    <div class="besar">
      <div><span class="tag" style="background:#ffd95a">Kenapa {{ $k['nama'] }}?</span><h2 style="margin-top:18px">Dirawat seperti anabul sendiri.</h2></div>
      <p>Setiap hewan punya kartu catatan: alergi, sifat, berat badan, dan jadwal vaksin. Groomer membaca catatan itu sebelum mulai, jadi kamu tidak perlu menjelaskan ulang tiap datang.</p>
      <svg class="jejak" viewBox="0 0 24 24" fill="currentColor"><circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.05Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/></svg>
    </div>
    <div class="kecil-k">
      <div class="kk">{!! ikon('kalender') !!}<h3>Jadwal pasti</h3><p>Pilih jam yang masih kosong. Kuota per jam dibatasi supaya tidak terburu-buru.</p></div>
      <div class="kk">{!! ikon('suntik') !!}<h3>Pengingat vaksin</h3><p>Riwayat vaksin tercatat. Kami kabari lewat WhatsApp saat jadwal berikutnya tiba.</p></div>
      <div class="kk">{!! ikon('perisai') !!}<h3>Produk original</h3><p>Makanan, pasir, dan obat dari distributor resmi dengan stok terpantau.</p></div>
      <div class="kk">{!! ikon('wa') !!}<h3>Kabar via WA</h3><p>Status dikonfirmasi, dikerjakan, sampai siap jemput langsung ke WhatsApp kamu.</p></div>
    </div>
  </div>
</section>

@if ($produk->count())
<section class="blok" id="produk">
  <div class="wadah">
    <div class="kepala"><div><span class="kicker">{!! ikon('tas') !!} Belanja di toko</span><h2>Favorit pelanggan bulan ini</h2></div><p>Tersedia di toko kami. Tanyakan stok lewat WhatsApp atau titip beli sekalian saat grooming.</p></div>
    <div class="produk">
      @foreach ($produk as $p)
        <div class="kp">
          <div class="ubin" style="background:{{ $p->warna() }}">@if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="{{ $p->nama }}" loading="lazy">@else<span class="bul"></span>{!! ikon($p->ikon()) !!}@endif<span class="tag untuk">{{ $p->untuk }}</span></div>
          <div class="nm">{{ $p->nama }}</div>
          <div class="hg">{{ rp($p->harga_jual) }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="blok">
  <div class="wadah">
    <div class="kepala"><div><span class="kicker">{!! ikon('kilat') !!} Cara booking</span><h2>Tiga langkah, tanpa daftar akun</h2></div></div>
    <div class="langkah">
      <div><span class="ib">{!! ikon('jejak') !!}</span><h3>Pilih layanan</h3><p>Grooming, pet hotel, atau vaksin. Harga jelas sejak awal.</p></div>
      <div><span class="ib">{!! ikon('kalender') !!}</span><h3>Pilih jadwal</h3><p>Lihat jam yang masih kosong, atau tanggal titip dan jemput untuk pet hotel.</p></div>
      <div><span class="ib">{!! ikon('cek') !!}</span><h3>Simpan kode booking</h3><p>Pantau statusnya di halaman Cek booking dengan kode dan 4 digit nomor WA.</p></div>
    </div>
  </div>
</section>

<div class="wadah" id="lokasi">
  <div class="cta">
    <div>
      <h2>Yuk, manjakan <span>anabulmu</span> minggu ini.</h2>
      <p>Datang langsung atau booking dulu supaya tidak menunggu.</p>
      <a class="btn peach" href="{{ route('booking') }}">Booking sekarang <span class="bulat">{!! ikon('panah') !!}</span></a>
    </div>
    <div class="info">
      <div>{!! ikon('pin') !!}<span><b style="color:#fff">Alamat</b>{{ $k['alamat'] }}</span></div>
      <div>{!! ikon('jam') !!}<span><b style="color:#fff">Jam buka</b>{{ $k['jam_buka'] }}</span></div>
      <div>{!! ikon('telepon') !!}<span><b style="color:#fff">Kontak</b>WA {{ $k['whatsapp'] }} · {{ $k['telepon'] }}</span></div>
    </div>
  </div>
</div>
@endsection
@push('skrip')
<script>
document.querySelectorAll('[data-kat]').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('[data-kat]').forEach(x => x.classList.toggle('on', x === b));
  document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== b.dataset.kat);
}));
</script>
@endpush
