@extends('panel', ['judul' => 'Ganti kata sandi', 'sub' => $u->name . ' · ' . $u->email])
@section('isi')
<form class="kartu" style="max-width:520px" method="post" action="{{ route('panel.profil.sandi') }}">
  @csrf
  <div class="f"><label>Kata sandi lama</label><input type="password" name="sandi_lama" required autocomplete="current-password">@error('sandi_lama')<div class="galat">{{ $message }}</div>@enderror</div>
  <div class="f"><label>Kata sandi baru</label><input type="password" name="password" required autocomplete="new-password"><small>Minimal 8 karakter, ada huruf dan angka.</small>@error('password')<div class="galat">{{ $message }}</div>@enderror</div>
  <div class="f"><label>Ulangi kata sandi baru</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
  <button class="btn">{!! ikon('kunci') !!} Simpan</button>
</form>
@endsection
