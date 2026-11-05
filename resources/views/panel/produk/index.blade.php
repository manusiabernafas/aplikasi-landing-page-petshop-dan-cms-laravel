@extends('panel', ['judul' => 'Produk & stok'])
@section('aksi')<a class="btn" href="{{ route('panel.produk.create') }}">{!! ikon('plus') !!} Produk baru</a>@endsection
@section('isi')
<div class="grid g3" style="margin-bottom:18px">
  <div class="stat"><div class="l"><span class="ib">{!! ikon('kotak') !!}</span>Produk aktif</div><div class="v">{{ $ringkas['jumlah'] }}</div></div>
  <div class="stat mentega"><div class="l"><span class="ib">{!! ikon('peringatan') !!}</span>Stok menipis</div><div class="v">{{ $ringkas['menipis'] }}</div><div class="s"><a href="{{ route('panel.produk.index', ['stok' => 'menipis']) }}" style="color:inherit">Tampilkan</a></div></div>
  <div class="stat mint"><div class="l"><span class="ib">{!! ikon('dompet') !!}</span>Nilai stok (harga beli)</div><div class="v">{{ rp($ringkas['nilai']) }}</div></div>
</div>
<form class="filter" method="get">
  <input class="inp" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU">
  <select class="inp" name="kategori"><option value="">Semua kategori</option>@foreach (array_keys(\App\Models\Produk::KATEGORI) as $k)<option @selected(request('kategori') === $k)>{{ $k }}</option>@endforeach</select>
  <select class="inp" name="stok"><option value="">Semua stok</option><option value="menipis" @selected(request('stok') === 'menipis')>Menipis</option></select>
  <button class="btn tinta">{!! ikon('cari') !!} Saring</button>
</form>
<div class="kartu">
  <div class="tabel-w"><table class="t">
    <tr><th>Produk</th><th>Kategori</th><th class="kanan">Harga beli</th><th class="kanan">Harga jual</th><th class="kanan">Margin</th><th class="kanan">Stok</th><th></th></tr>
    @forelse ($data as $p)
      <tr class="{{ $p->aktif ? '' : 'mati' }}">
        <td><span style="display:flex;gap:12px;align-items:center">@include('panel._ubin', ['p' => $p, 'kelas' => 's'])<span><b>{{ $p->nama }}</b>@if ($p->unggulan) {!! lencana('Unggulan', 'peach') !!}@endif<br><span class="muted kecil">{{ $p->sku }} · untuk {{ strtolower($p->untuk) }}</span></span></span></td>
        <td>{{ $p->kategori }}</td><td class="kanan nowrap">{{ rp($p->harga_beli) }}</td><td class="kanan nowrap tebal">{{ rp($p->harga_jual) }}</td>
        <td class="kanan">{{ $p->harga_beli ? round(($p->harga_jual - $p->harga_beli) / $p->harga_jual * 100) . '%' : '-' }}</td>
        <td class="kanan nowrap">{!! $p->menipis() ? lencana($p->stok . ' ' . $p->satuan, $p->stok <= 0 ? 'merah' : 'kuning') : $p->stok . ' ' . e($p->satuan) !!}</td>
        <td class="kanan nowrap"><a class="btn peach kecil" href="{{ route('panel.produk.stok', $p) }}">{!! ikon('kotak') !!} Stok</a> <a class="btn putih kecil" href="{{ route('panel.produk.edit', $p) }}">{!! ikon('edit') !!}</a></td>
      </tr>
    @empty<tr><td colspan="7" class="kosong">Tidak ada produk.</td></tr>@endforelse
  </table></div>
  {{ $data->links('panel._pagin') }}
</div>
@endsection
