@php $k = \App\Support\Petshop::semua(); $logo = $k['logo'] ? asset('uploads/' . $k['logo']) : null; @endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ isset($judul) ? $judul . ' - ' : '' }}{{ $k['nama'] }} · {{ $k['tagline'] }}</title>
<meta name="description" content="{{ $k['nama'] }}: {{ $k['tagline'] }}. Booking grooming, penitipan, dan vaksin online.">
<link rel="icon" href="{{ $logo ?? asset('img/ikon.svg') }}">
<link rel="preload" href="{{ asset('fonts/PaytoneOne.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/situs.css') }}">
</head>
<body>
<header class="nav">
  <div class="wadah">
    <a class="merek" href="{{ route('beranda') }}"><span class="logo">@if ($logo)<img src="{{ $logo }}" alt="">@else{!! ikon('jejak', 'ic') !!}@endif</span><span><b>{{ $k['nama'] }}</b><small>{{ $k['tagline'] }}</small></span></a>
    <nav class="menu" id="menu">
      <a href="{{ route('beranda') }}#layanan">Layanan</a><a href="{{ route('beranda') }}#produk">Produk</a><a href="{{ route('beranda') }}#lokasi">Lokasi</a><a href="{{ route('cek') }}">Cek booking</a>
    </nav>
    <a class="btn kecil" href="{{ route('booking') }}">{!! ikon('kalender') !!} Booking</a>
    <button class="burger" type="button" aria-label="Menu" onclick="document.getElementById('menu').classList.toggle('buka')">{!! ikon('menu') !!}</button>
  </div>
</header>
@yield('isi')
<footer>
  <div class="wadah">
    <span class="muted kecil">&copy; {{ date('Y') }} {{ $k['nama'] }} · {{ $k['alamat'] }}</span>
    <span><a href="{{ route('booking') }}">Booking</a><a href="{{ route('cek') }}">Cek booking</a><a href="{{ route('masuk') }}">Masuk staf</a></span>
  </div>
</footer>
@stack('skrip')
</body>
</html>
