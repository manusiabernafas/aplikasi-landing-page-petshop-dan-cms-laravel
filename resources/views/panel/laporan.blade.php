@extends('panel', ['judul' => 'Laporan', 'sub' => tgl($dari) . ' - ' . tgl($sampai)])
@php $mx = max(1, $harian->max('nilai')); $laba = $produk - $modal + $layanan - $diskon; @endphp
@section('aksi')<a class="btn peach" href="{{ route('panel.laporan.csv', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}">{!! ikon('unduh') !!} Unduh CSV</a>@endsection
@section('isi')
<form class="filter" method="get">
  <label class="tebal kecil">Dari <input class="inp" type="date" name="dari" value="{{ $dari->toDateString() }}"></label>
  <label class="tebal kecil">Sampai <input class="inp" type="date" name="sampai" value="{{ $sampai->toDateString() }}"></label>
  <button class="btn tinta">Tampilkan</button>
  <a class="btn putih kecil" href="{{ route('panel.laporan', ['dari' => now()->startOfMonth()->toDateString(), 'sampai' => now()->toDateString()]) }}">Bulan ini</a>
  <a class="btn putih kecil" href="{{ route('panel.laporan', ['dari' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'sampai' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}">Bulan lalu</a>
</form>
<div class="grid g4" style="margin-bottom:18px">
  <div class="stat biru"><div class="l"><span class="ib">{!! ikon('dompet') !!}</span>Omzet</div><div class="v">{{ rp($omzet) }}</div><div class="s">{{ $jumlah }} transaksi · rata-rata {{ rp($jumlah ? $omzet / $jumlah : 0) }}</div></div>
  <div class="stat mint"><div class="l"><span class="ib">{!! ikon('grafik') !!}</span>Laba kotor</div><div class="v">{{ rp($laba) }}</div><div class="s">margin produk + layanan - diskon</div></div>
  <div class="stat peach"><div class="l"><span class="ib">{!! ikon('kotak') !!}</span>Penjualan produk</div><div class="v">{{ rp($produk) }}</div><div class="s">modal {{ rp($modal) }}</div></div>
  <div class="stat mentega"><div class="l"><span class="ib">{!! ikon('gunting') !!}</span>Pendapatan layanan</div><div class="v">{{ rp($layanan) }}</div><div class="s">diskon diberikan {{ rp($diskon) }}</div></div>
</div>
<div class="grid g21">
  <div class="kartu"><h2 style="margin-bottom:14px">Omzet harian</h2>
    <div class="bars">@forelse ($harian as $h)<div class="b" title="{{ tgl($h->t) }}: {{ rp($h->nilai) }} ({{ $h->n }} trx)"><i style="height:{{ round($h->nilai / $mx * 100) }}%"></i><small>{{ (int) substr($h->t, 8) }}</small></div>@empty<p class="kosong">Tidak ada data.</p>@endforelse</div>
  </div>
  <div class="kartu"><h2 style="margin-bottom:8px">Metode bayar</h2>
    <div class="lst">@foreach ($metode as $m)<div><span>{!! lencana(\App\Models\Transaksi::METODE[$m->metode] ?? $m->metode, ['tunai' => 'hijau', 'qris' => 'biru', 'transfer' => 'ungu'][$m->metode] ?? 'abu') !!} <span class="muted kecil">{{ $m->n }} trx</span></span><b>{{ rp($m->nilai) }}</b></div>@endforeach</div>
    <h2 style="margin:18px 0 8px">Booking</h2>
    <div class="lst">@foreach (\App\Models\Booking::STATUS as $k => $v)<div><span>{!! lencana($v, \App\Models\Booking::WARNA[$k]) !!}</span><b>{{ $booking[$k] ?? 0 }}</b></div>@endforeach</div>
  </div>
</div>
<div class="grid g2 f1" style="margin-top:18px">
  <div class="kartu"><h2 style="margin-bottom:8px">Produk terlaris</h2><div class="tabel-w"><table class="t"><tr><th>Produk</th><th class="kanan">Qty</th><th class="kanan">Nilai</th><th class="kanan">Laba</th></tr>
    @forelse ($topProduk as $t)<tr><td class="tebal">{{ $t->nama }}</td><td class="kanan">{{ $t->qty }}</td><td class="kanan nowrap">{{ rp($t->nilai) }}</td><td class="kanan nowrap">{{ rp($t->laba) }}</td></tr>@empty<tr><td colspan="4" class="kosong">-</td></tr>@endforelse</table></div></div>
  <div class="kartu"><h2 style="margin-bottom:8px">Layanan terlaris</h2><div class="tabel-w"><table class="t"><tr><th>Layanan</th><th class="kanan">Qty</th><th class="kanan">Nilai</th></tr>
    @forelse ($topLayanan as $t)<tr><td class="tebal">{{ $t->nama }} <span class="muted kecil">{{ $t->kategori }}</span></td><td class="kanan">{{ $t->qty }}</td><td class="kanan nowrap">{{ rp($t->nilai) }}</td></tr>@empty<tr><td colspan="3" class="kosong">-</td></tr>@endforelse</table></div></div>
</div>
@endsection
