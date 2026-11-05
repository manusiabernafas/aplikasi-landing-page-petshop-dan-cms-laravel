@extends('publik.layout', ['judul' => 'Masuk staf'])
@section('isi')
<div class="halaman">
  <div class="wadah" style="max-width:520px">
    <div class="kepala-h"><span class="chip"><i>{!! ikon('kunci', 'ic') !!}</i> Khusus staf toko</span><h1>Masuk panel</h1><p>Admin, kasir, dan groomer masuk memakai email.</p></div>
    <form class="panel" method="post" action="{{ route('masuk.proses') }}" novalidate>
      @csrf
      @if (session('ok'))<div class="alert ok">{{ session('ok') }}</div>@endif
      @if (session('error'))<div class="alert err" role="alert">{{ session('error') }}</div>@endif
      <div class="f"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>@error('email')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label for="password">Kata sandi</label><input id="password" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label style="display:flex;gap:8px;align-items:center;font-weight:600"><input type="checkbox" name="ingat" value="1" style="width:auto;min-height:0"> Ingat saya di perangkat ini</label></div>
      <button class="btn" style="width:100%">Masuk {!! ikon('panah') !!}</button>
    </form>
  </div>
</div>
@endsection
