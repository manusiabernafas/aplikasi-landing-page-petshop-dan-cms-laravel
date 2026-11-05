@extends('panel', ['judul' => $l->exists ? 'Ubah layanan' : 'Layanan baru'])
@section('aksi')<a class="btn putih" href="{{ route('panel.layanan.index') }}">{!! ikon('kiri') !!} Kembali</a>@endsection
@section('isi')
<form class="kartu" style="max-width:760px" method="post" action="{{ $l->exists ? route('panel.layanan.update', $l) : route('panel.layanan.store') }}">
  @csrf @if ($l->exists) @method('PUT') @endif
  <div class="grid g2 f1" style="gap:0 14px">
    <div class="f"><label>Nama layanan</label><input name="nama" value="{{ old('nama', $l->nama) }}" required maxlength="80">@error('nama')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="f"><label>Kategori</label><select name="kategori">@foreach (\App\Models\Layanan::KATEGORI as $k => $v)<option value="{{ $k }}" @selected(old('kategori', $l->kategori) === $k)>{{ $v }}</option>@endforeach</select><small>Penitipan memakai tanggal titip-jemput & kandang; lainnya memakai slot jam.</small></div>
    <div class="f"><label>Harga</label><input type="number" name="harga" min="0" value="{{ old('harga', $l->harga) }}" required>@error('harga')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="f"><label>Satuan</label><input name="satuan" value="{{ old('satuan', $l->satuan) }}" required maxlength="15" placeholder="sesi / malam / dosis"></div>
    <div class="f"><label>Durasi (menit)</label><input type="number" name="durasi" min="5" value="{{ old('durasi', $l->durasi) }}" required></div>
    <div class="f"><label>Untuk hewan</label><select name="untuk">@foreach (\App\Models\Produk::UNTUK as $k)<option @selected(old('untuk', $l->untuk) === $k)>{{ $k }}</option>@endforeach</select></div>
    <div class="f"><label>Urutan tampil</label><input type="number" name="urut" min="0" value="{{ old('urut', $l->urut) }}"></div>
  </div>
  <div class="f"><label>Deskripsi</label><textarea name="deskripsi" maxlength="300">{{ old('deskripsi', $l->deskripsi) }}</textarea></div>
  <div style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:16px"><label class="cek"><input type="checkbox" name="publik" value="1" @checked(old('publik', $l->publik))> Bisa dibooking online</label><label class="cek"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $l->aktif))> Aktif</label></div>
  <button class="btn">{!! ikon('cek') !!} Simpan</button>
</form>
@if ($l->exists)
<form method="post" action="{{ route('panel.layanan.destroy', $l) }}" data-yakin="Hapus layanan ini?" style="margin-top:14px">@csrf @method('DELETE')<button class="btn merah">{!! ikon('hapus') !!} Hapus layanan</button></form>
@endif
@endsection
