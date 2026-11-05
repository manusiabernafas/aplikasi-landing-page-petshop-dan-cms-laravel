@php
  $k = \App\Support\Petshop::semua();
  $logo = $k['logo'] ? asset('uploads/' . $k['logo']) : null;
  $u = auth()->user();
  $tunggu = \App\Models\Booking::where('status', 'menunggu')->count();
  $tipis = \App\Models\Produk::where('aktif', true)->whereColumn('stok', '<=', 'stok_min')->count();
  $menu = ['Harian' => [['panel.dasbor', 'Dasbor', 'grid', null, 'panel.dasbor']]];
  if ($u->bisa('admin', 'kasir')) {
    $menu['Harian'][] = ['panel.kasir', 'Kasir', 'kasir', null, 'panel.kasir'];
    $menu['Harian'][] = ['panel.transaksi.index', 'Transaksi', 'struk', null, 'panel.transaksi.*'];
  }
  $menu['Harian'][] = ['panel.booking.index', 'Jadwal & booking', 'kalender', $tunggu, 'panel.booking.*'];
  $menu['Data'] = [['panel.pelanggan.index', 'Pelanggan & hewan', 'jejak', null, 'panel.pelanggan.*']];
  if ($u->bisa('admin', 'kasir')) $menu['Data'][] = ['panel.produk.index', 'Produk & stok', 'kotak', $tipis, 'panel.produk.*'];
  if ($u->bisa('admin')) {
    $menu['Data'][] = ['panel.layanan.index', 'Layanan & harga', 'gunting', null, 'panel.layanan.*'];
    $menu['Toko'] = [['panel.laporan', 'Laporan', 'grafik', null, 'panel.laporan*'], ['panel.pengguna.index', 'Staf', 'pengguna', null, 'panel.pengguna.*'], ['panel.pengaturan', 'Pengaturan', 'atur', null, 'panel.pengaturan*']];
  }
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $judul ?? 'Dasbor' }} - {{ $k['nama'] }}</title>
<link rel="icon" href="{{ $logo ?? asset('img/ikon.svg') }}">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body>
<div class="app">
  <aside class="side" id="side">
    <a class="merek" href="{{ route('panel.dasbor') }}"><span class="lg">@if ($logo)<img src="{{ $logo }}" alt="">@else{!! ikon('jejak') !!}@endif</span><span><b>{{ $k['nama'] }}</b><small>Panel toko</small></span></a>
    @foreach ($menu as $grup => $isi)
      <div class="grup">{{ $grup }}</div>
      @foreach ($isi as [$r, $l, $ic, $jml, $pola])
        <a class="nav {{ request()->routeIs($pola) ? 'on' : '' }}" href="{{ route($r) }}">{!! ikon($ic) !!} {{ $l }} @if ($jml)<span class="jml">{{ $jml }}</span>@endif</a>
      @endforeach
    @endforeach
    <div class="bawah">
      <div class="siapa"><span class="av">{{ mb_substr($u->name, 0, 1) }}</span><span><b>{{ $u->name }}</b>{{ \App\Models\User::PERAN[$u->peran] ?? $u->peran }}</span></div>
      <a class="nav {{ request()->routeIs('panel.profil') ? 'on' : '' }}" href="{{ route('panel.profil') }}">{!! ikon('kunci') !!} Ganti kata sandi</a>
      <a class="nav" href="{{ route('beranda') }}" target="_blank" rel="noopener">{!! ikon('rumah') !!} Lihat situs</a>
      <form method="post" action="{{ route('keluar') }}">@csrf<button class="nav" type="submit">{!! ikon('keluar') !!} Keluar</button></form>
    </div>
  </aside>
  <main class="utama">
    <div class="atas">
      <button class="tombol-menu" type="button" aria-label="Buka menu" onclick="document.getElementById('side').classList.toggle('buka')">{!! ikon('menu') !!}</button>
      <h1>{{ $judul ?? 'Dasbor' }}@isset($sub)<span class="sub">{{ $sub }}</span>@endisset</h1>
      @yield('aksi')
    </div>
    @if (session('ok'))<div class="alert ok" role="status">{!! ikon('cek') !!} {{ session('ok') }} @if (session('wa'))<a class="btn hijau kecil" href="{{ session('wa') }}" target="_blank" rel="noopener">{!! ikon('wa') !!} Kabari pelanggan</a>@endif</div>@endif
    @if (session('error'))<div class="alert err" role="alert">{!! ikon('peringatan') !!} {{ session('error') }}</div>@endif
    @if (session('sandi_baru'))<div class="alert info">Kata sandi untuk <b>{{ session('sandi_baru')[0] }}</b>: <code>{{ session('sandi_baru')[1] }}</code> Berikan ke pemilik akun sekarang, sandi ini hanya ditampilkan sekali.</div>@endif
    @if ($errors->any())<div class="alert err" role="alert">{!! ikon('peringatan') !!} {{ $errors->count() === 1 ? $errors->first() : 'Ada isian yang perlu diperbaiki. Lihat tulisan merah di bawah.' }}</div>@endif
    @yield('isi')
  </main>
</div>
<script>
document.addEventListener('click', e => { const s = document.getElementById('side'); if (s.classList.contains('buka') && !s.contains(e.target) && !e.target.closest('.tombol-menu')) s.classList.remove('buka'); });
document.querySelectorAll('form[data-yakin]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.yakin)) e.preventDefault(); }));
</script>
@stack('skrip')
</body>
</html>
