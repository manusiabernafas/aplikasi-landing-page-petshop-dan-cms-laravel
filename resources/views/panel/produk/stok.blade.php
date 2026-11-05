@extends('panel', ['judul' => 'Stok: ' . $p->nama, 'sub' => $p->sku . ' · ' . $p->kategori])
@section('aksi')<a class="btn putih" href="{{ route('panel.produk.index') }}">{!! ikon('kiri') !!} Produk</a>@endsection
@section('isi')
@php $wj = ['masuk' => 'hijau', 'keluar' => 'merah', 'penyesuaian' => 'biru', 'penjualan' => 'peach', 'batal' => 'ungu']; @endphp
<div class="grid g12">
  <div style="display:grid;gap:18px;align-content:start">
    <div class="stat {{ $p->menipis() ? 'mentega' : 'mint' }}"><div class="l"><span class="ib">{!! ikon('kotak') !!}</span>Stok sekarang</div><div class="v">{{ $p->stok }} {{ $p->satuan }}</div><div class="s">minimum {{ $p->stok_min }} · nilai {{ rp(max(0, $p->stok) * $p->harga_beli) }}</div></div>
    <form class="kartu" method="post" action="{{ route('panel.produk.stok.simpan', $p) }}">
      @csrf
      <h2 style="margin-bottom:12px">Catat perubahan stok</h2>
      <div class="f"><label>Jenis</label><select name="jenis" id="jenis"><option value="masuk">Stok masuk (kulakan)</option><option value="keluar">Stok keluar (rusak, kedaluwarsa, pakai sendiri)</option><option value="penyesuaian">Stok opname (set jumlah sebenarnya)</option></select></div>
      <div class="f"><label id="lblQty">Jumlah</label><input type="number" name="qty" min="0" required value="{{ old('qty') }}">@error('qty')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label>Keterangan</label><input name="keterangan" maxlength="150" placeholder="Nama distributor / alasan" value="{{ old('keterangan') }}"></div>
      <button class="btn">{!! ikon('cek') !!} Simpan</button>
    </form>
  </div>
  <div class="kartu">
    <h2 style="margin-bottom:10px">Riwayat stok</h2>
    <div class="tabel-w"><table class="t">
      <tr><th>Waktu</th><th>Jenis</th><th class="kanan">Jumlah</th><th class="kanan">Stok akhir</th><th>Keterangan</th><th>Oleh</th></tr>
      @forelse ($mutasi as $m)
        <tr><td class="nowrap">{{ tgl($m->waktu, true) }}</td><td>{!! lencana(ucfirst($m->jenis), $wj[$m->jenis] ?? 'abu') !!}</td><td class="kanan tebal">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td><td class="kanan">{{ $m->stok_akhir }}</td><td>{{ $m->keterangan }}</td><td class="kecil">{{ $m->user?->name }}</td></tr>
      @empty<tr><td colspan="6" class="kosong">Belum ada riwayat.</td></tr>@endforelse
    </table></div>
    {{ $mutasi->links('panel._pagin') }}
  </div>
</div>
@endsection
@push('skrip')<script>document.getElementById('jenis').onchange = e => document.getElementById('lblQty').textContent = e.target.value === 'penyesuaian' ? 'Jumlah stok sebenarnya' : 'Jumlah';</script>@endpush
