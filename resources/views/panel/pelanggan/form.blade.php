@extends('panel', ['judul' => $p->exists ? 'Ubah pelanggan' : 'Pelanggan baru'])
@section('aksi')<a class="btn putih" href="{{ $p->exists ? route('panel.pelanggan.show', $p) : route('panel.pelanggan.index') }}">{!! ikon('kiri') !!} Kembali</a>@endsection
@section('isi')
<form class="kartu" style="max-width:680px" method="post" action="{{ $p->exists ? route('panel.pelanggan.update', $p) : route('panel.pelanggan.store') }}">
  @csrf @if ($p->exists) @method('PUT') @endif
  <div class="grid g2 f1" style="gap:0 14px">
    <div class="f"><label>Nama</label><input name="nama" value="{{ old('nama', $p->nama) }}" required maxlength="80">@error('nama')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="f"><label>Nomor WhatsApp</label><input name="telepon" value="{{ old('telepon', $p->telepon) }}" required inputmode="tel">@error('telepon')<div class="galat">{{ $message }}</div>@enderror</div>
  </div>
  <div class="f"><label>Alamat</label><input name="alamat" value="{{ old('alamat', $p->alamat) }}" maxlength="200"></div>
  <div class="f"><label>Catatan</label><textarea name="catatan" maxlength="300">{{ old('catatan', $p->catatan) }}</textarea></div>
  <button class="btn">{!! ikon('cek') !!} Simpan</button>
</form>
@endsection
