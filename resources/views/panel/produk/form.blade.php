@extends('panel', ['judul' => $p->exists ? 'Ubah produk' : 'Produk baru'])
@section('aksi')<a class="btn putih" href="{{ route('panel.produk.index') }}">{!! ikon('kiri') !!} Kembali</a>@endsection
@section('isi')
<form method="post" enctype="multipart/form-data" action="{{ $p->exists ? route('panel.produk.update', $p) : route('panel.produk.store') }}" class="grid g21">
  @csrf @if ($p->exists) @method('PUT') @endif
  <div class="kartu">
    <div class="grid g2 f1" style="gap:0 14px">
      <div class="f"><label>SKU / kode</label><input name="sku" value="{{ old('sku', $p->sku) }}" required>@error('sku')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label>Nama produk</label><input name="nama" value="{{ old('nama', $p->nama) }}" required maxlength="100">@error('nama')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label>Kategori</label><select name="kategori">@foreach (array_keys(\App\Models\Produk::KATEGORI) as $k)<option @selected(old('kategori', $p->kategori) === $k)>{{ $k }}</option>@endforeach</select></div>
      <div class="f"><label>Untuk hewan</label><select name="untuk">@foreach (\App\Models\Produk::UNTUK as $k)<option @selected(old('untuk', $p->untuk) === $k)>{{ $k }}</option>@endforeach</select></div>
      <div class="f"><label>Harga beli (modal)</label><input type="number" name="harga_beli" min="0" value="{{ old('harga_beli', $p->harga_beli ?? 0) }}" required>@error('harga_beli')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label>Harga jual</label><input type="number" name="harga_jual" min="1" value="{{ old('harga_jual', $p->harga_jual) }}" required>@error('harga_jual')<div class="galat">{{ $message }}</div>@enderror</div>
      @unless ($p->exists)<div class="f"><label>Stok awal</label><input type="number" name="stok" min="0" value="{{ old('stok', 0) }}" required>@error('stok')<div class="galat">{{ $message }}</div>@enderror</div>@endunless
      <div class="f"><label>Stok minimum</label><input type="number" name="stok_min" min="0" value="{{ old('stok_min', $p->stok_min) }}" required><small>Muncul peringatan bila stok di bawah angka ini.</small></div>
      <div class="f"><label>Satuan</label><input name="satuan" value="{{ old('satuan', $p->satuan) }}" required maxlength="15"></div>
    </div>
    <div class="f"><label>Deskripsi (opsional)</label><textarea name="deskripsi" maxlength="400">{{ old('deskripsi', $p->deskripsi) }}</textarea></div>
  </div>
  <div class="kartu" style="display:grid;gap:14px;align-content:start">
    <div style="max-width:220px">@if ($p->exists)@include('panel._ubin', ['p' => $p])@endif</div>
    <div class="f"><label>Foto (opsional)</label><input type="file" name="foto" accept="image/jpeg,image/png,image/webp"><small>JPG/PNG/WEBP maks. 2 MB. Tanpa foto, tampil ikon kategori.</small>@error('foto')<div class="galat">{{ $message }}</div>@enderror</div>
    @if ($p->foto)<label class="cek"><input type="checkbox" name="hapus_foto" value="1"> Hapus foto</label>@endif
    <label class="cek"><input type="checkbox" name="unggulan" value="1" @checked(old('unggulan', $p->unggulan))> Tampilkan di halaman depan</label>
    <label class="cek"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $p->aktif))> Aktif (bisa dijual di kasir)</label>
    <button class="btn">{!! ikon('cek') !!} Simpan produk</button>
    @if ($p->exists)<p class="muted kecil" style="margin:0">Stok sekarang {{ $p->stok }} {{ $p->satuan }}. Ubah stok lewat menu <a href="{{ route('panel.produk.stok', $p) }}">Stok</a> agar tercatat.</p>@endif
  </div>
</form>
@if ($p->exists)
<form method="post" action="{{ route('panel.produk.destroy', $p) }}" data-yakin="Hapus produk ini? Bila sudah pernah terjual, produk hanya dinonaktifkan." style="margin-top:14px">@csrf @method('DELETE')<button class="btn merah">{!! ikon('hapus') !!} Hapus produk</button></form>
@endif
@endsection
