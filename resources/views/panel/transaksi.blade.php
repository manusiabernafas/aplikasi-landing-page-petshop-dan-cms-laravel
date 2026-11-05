@extends('panel', ['judul' => 'Transaksi', 'sub' => 'Riwayat penjualan produk & layanan'])
@section('aksi')<a class="btn peach" href="{{ route('panel.kasir') }}">{!! ikon('kasir') !!} Kasir</a>@endsection
@section('isi')
<form class="filter" method="get">
  <input class="inp" name="q" value="{{ request('q') }}" placeholder="Nomor / nama pelanggan">
  <input class="inp" type="date" name="tanggal" value="{{ request('tanggal') }}">
  <select class="inp" name="metode"><option value="">Semua metode</option>@foreach (\App\Models\Transaksi::METODE as $k => $v)<option value="{{ $k }}" @selected(request('metode') === $k)>{{ $v }}</option>@endforeach</select>
  <button class="btn tinta">{!! ikon('cari') !!} Saring</button>
</form>
<div class="kartu">
  <div class="tabel-w"><table class="t">
    <tr><th>Nomor</th><th>Waktu</th><th>Pelanggan</th><th>Item</th><th>Metode</th><th>Kasir</th><th class="kanan">Total</th><th></th></tr>
    @forelse ($data as $t)
      <tr class="{{ $t->batal ? 'mati' : '' }}"><td class="tebal nowrap">{{ $t->nomor }}</td><td class="nowrap">{{ tgl($t->waktu, true) }}</td><td>{{ $t->pelanggan?->nama ?? 'Umum' }}</td><td>{{ $t->item_count }}</td>
        <td>{!! lencana(\App\Models\Transaksi::METODE[$t->metode], ['tunai' => 'hijau', 'qris' => 'biru', 'transfer' => 'ungu'][$t->metode]) !!} @if ($t->batal){!! lencana('Batal', 'merah') !!}@endif</td><td>{{ $t->user?->name }}</td>
        <td class="kanan tebal nowrap">{{ rp($t->total) }}</td><td class="kanan"><a class="btn putih kecil" href="{{ route('panel.transaksi.show', $t) }}">{!! ikon('struk') !!} Struk</a></td></tr>
    @empty<tr><td colspan="8" class="kosong">Belum ada transaksi.</td></tr>@endforelse
  </table></div>
  {{ $data->links('panel._pagin') }}
</div>
@endsection
