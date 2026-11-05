@extends('panel', ['judul' => 'Staf', 'sub' => 'Admin: semua menu · Kasir: kasir, produk, booking, pelanggan · Groomer: jadwal & pelanggan'])
@section('isi')
<div class="grid g21">
  <div class="kartu"><div class="tabel-w"><table class="t">
    <tr><th>Nama & email</th><th>Peran</th><th>Status</th><th></th></tr>
    @foreach ($data as $s)
      <tr class="{{ $s->aktif ? '' : 'mati' }}"><td colspan="4" style="padding:0">
        <form method="post" action="{{ route('panel.pengguna.update', $s) }}" style="display:grid;grid-template-columns:1.3fr 1.4fr .9fr auto auto;gap:8px;align-items:center;padding:10px">
          @csrf @method('PUT')
          <input class="inp" name="name" value="{{ $s->name }}" required aria-label="Nama"><input class="inp" name="email" value="{{ $s->email }}" required aria-label="Email">
          <select class="inp" name="peran" aria-label="Peran">@foreach (\App\Models\User::PERAN as $k => $v)<option value="{{ $k }}" @selected($s->peran === $k)>{{ $v }}</option>@endforeach</select>
          <label class="cek kecil"><input type="checkbox" name="aktif" value="1" @checked($s->aktif)> Aktif</label>
          <button class="btn kecil">Simpan</button>
        </form>
        <form method="post" action="{{ route('panel.pengguna.reset', $s) }}" data-yakin="Reset kata sandi {{ $s->name }}?" style="padding:0 10px 10px">@csrf<button class="btn putih kecil">{!! ikon('kunci') !!} Reset sandi</button></form>
      </td></tr>
    @endforeach
  </table></div></div>
  <form class="kartu" method="post" action="{{ route('panel.pengguna.store') }}" style="align-self:start">
    @csrf <h2 style="margin-bottom:12px">Tambah staf</h2>
    <div class="f"><label>Nama</label><input name="name" value="{{ old('name') }}" required></div>
    <div class="f"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="f"><label>No HP</label><input name="telepon" value="{{ old('telepon') }}"></div>
    <div class="f"><label>Peran</label><select name="peran">@foreach (\App\Models\User::PERAN as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
    <button class="btn">{!! ikon('plus') !!} Buat akun</button><p class="muted kecil">Kata sandi acak ditampilkan sekali setelah akun dibuat.</p>
  </form>
</div>
<style>@media (max-width:900px){.t form[style*="grid-template-columns"]{grid-template-columns:1fr 1fr!important}}</style>
@endsection
