@extends('panel', ['judul' => 'Struk ' . $t->nomor])
@section('aksi')
  <a class="btn putih" href="{{ route('panel.transaksi.index') }}">{!! ikon('kiri') !!} Transaksi</a>
  <button class="btn" type="button" onclick="print()">{!! ikon('cetak') !!} Cetak</button>
  @if (auth()->user()->bisa('admin') && ! $t->batal)
    <form method="post" action="{{ route('panel.transaksi.batal', $t) }}" data-yakin="Batalkan transaksi {{ $t->nomor }}? Stok produk akan dikembalikan.">@csrf<button class="btn merah">{!! ikon('x') !!} Batalkan</button></form>
  @endif
@endsection
@section('isi')
@if ($t->batal)<div class="alert err no-print">Transaksi ini sudah dibatalkan. Stok sudah dikembalikan.</div>@endif
<div class="struk">
  <div class="tengah"><b style="font-size:16px">{{ toko('nama') }}</b><br>{{ toko('alamat') }}<br>WA {{ toko('whatsapp') }}</div>
  <hr>
  <div class="br"><span>{{ $t->nomor }}</span><span>{{ $t->waktu->format('d/m/Y H:i') }}</span></div>
  <div class="br"><span>Kasir: {{ $t->user?->name }}</span><span>{{ $t->pelanggan?->nama ?? 'Umum' }}</span></div>
  <hr>
  @foreach ($t->item as $i)
    <div>{{ $i->nama }}</div>
    <div class="br"><span>&nbsp; {{ $i->qty }} x {{ number_format($i->harga, 0, ',', '.') }}</span><span>{{ number_format($i->subtotal, 0, ',', '.') }}</span></div>
  @endforeach
  <hr>
  <div class="br"><span>Subtotal</span><span>{{ number_format($t->subtotal, 0, ',', '.') }}</span></div>
  @if ($t->diskon)<div class="br"><span>Diskon</span><span>-{{ number_format($t->diskon, 0, ',', '.') }}</span></div>@endif
  <div class="br"><b>TOTAL</b><b>{{ number_format($t->total, 0, ',', '.') }}</b></div>
  <div class="br"><span>{{ \App\Models\Transaksi::METODE[$t->metode] }}</span><span>{{ number_format($t->bayar, 0, ',', '.') }}</span></div>
  @if ($t->kembali)<div class="br"><span>Kembali</span><span>{{ number_format($t->kembali, 0, ',', '.') }}</span></div>@endif
  @if ($t->catatan)<hr><div>Catatan: {{ $t->catatan }}</div>@endif
  <hr>
  <div class="tengah">{{ toko('catatan_struk') }}</div>
  @if ($t->batal)<div class="tengah" style="margin-top:8px"><b>*** DIBATALKAN ***</b></div>@endif
</div>
@endsection
