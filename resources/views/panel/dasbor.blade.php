@extends('panel', ['judul' => 'Halo, ' . explode(' ', auth()->user()->name)[0], 'sub' => tgl(now()) . ' · ' . toko('nama')])
@php $u = auth()->user(); $mx = max(1, $grafik->max('omzet')); $naik = $omzetKemarin ? round(($omzetHari - $omzetKemarin) / $omzetKemarin * 100) : null; @endphp
@section('aksi')
  @if ($u->bisa('admin', 'kasir'))<a class="btn peach" href="{{ route('panel.kasir') }}">{!! ikon('kasir') !!} Buka kasir</a>@endif
  <a class="btn" href="{{ route('panel.booking.create') }}">{!! ikon('plus') !!} Booking baru</a>
@endsection
@section('isi')
<div class="grid g4" style="margin-bottom:18px">
  <div class="stat biru"><div class="l"><span class="ib">{!! ikon('dompet') !!}</span>Omzet hari ini</div><div class="v">{{ rp($omzetHari) }}</div><div class="s">{{ $trxHari }} transaksi @if ($naik !== null)· {{ $naik >= 0 ? '+' : '' }}{{ $naik }}% dari kemarin @endif</div></div>
  <div class="stat peach"><div class="l"><span class="ib">{!! ikon('gunting') !!}</span>Jadwal hari ini</div><div class="v">{{ $jadwal->count() }}</div><div class="s">{{ $jadwal->where('status', 'selesai')->count() }} selesai · {{ $menunggu }} booking menunggu konfirmasi</div></div>
  <div class="stat mint"><div class="l"><span class="ib">{!! ikon('hotel') !!}</span>Sedang dititip</div><div class="v">{{ $dititip->count() }} <span style="font-size:18px">/ {{ toko('jumlah_kandang') }}</span></div><div class="s">{{ $dititip->filter(fn ($b) => $b->tanggal_keluar->isToday())->count() }} dijemput hari ini</div></div>
  <div class="stat mentega"><div class="l"><span class="ib">{!! ikon('peringatan') !!}</span>Stok menipis</div><div class="v">{{ $menipis->count() }}</div><div class="s">produk di bawah stok minimum</div></div>
</div>
<div class="grid g21">
  <div class="kartu">
    <div class="kp"><div><h2>Omzet 14 hari</h2><span class="muted kecil">Produk + layanan, tanpa transaksi batal</span></div><b class="jf" style="font-size:22px">{{ rp($grafik->sum('omzet')) }}</b></div>
    <div class="bars">@foreach ($grafik as $g)<div class="b" title="{{ tgl($g['t']) }}: {{ rp($g['omzet']) }}"><i style="height:{{ round($g['omzet'] / $mx * 100) }}%"></i><small>{{ (int) substr($g['t'], 8) }}</small></div>@endforeach</div>
  </div>
  <div class="kartu">
    <div class="kp"><h2>Jadwal hari ini</h2><a class="kecil tebal" href="{{ route('panel.booking.index') }}">Lihat semua</a></div>
    <div class="lst">
      @forelse ($jadwal->take(6) as $b)
        <div><span style="display:flex;gap:12px;align-items:center;min-width:0"><span class="jamb">{{ $b->jam }}</span><span style="min-width:0"><a class="tebal" style="color:inherit;text-decoration:none" href="{{ route('panel.booking.show', $b) }}">{{ $b->nama_hewan }}</a> <span class="muted kecil">· {{ $b->pelanggan->nama }}</span><br><span class="muted kecil">{{ $b->layanan->nama }}</span></span></span>{!! lencana(\App\Models\Booking::STATUS[$b->status], \App\Models\Booking::WARNA[$b->status]) !!}</div>
      @empty<p class="kosong">Belum ada jadwal hari ini.</p>@endforelse
    </div>
  </div>
</div>
<div class="grid g3" style="margin-top:18px">
  <div class="kartu">
    <div class="kp"><h2>Pet hotel</h2><a class="kecil tebal" href="{{ route('panel.booking.index', ['tab' => 'penitipan']) }}">Denah kandang</a></div>
    <div class="lst">
      @forelse ($dititip->take(5) as $b)
        <div><span><b>{{ $b->nama_hewan }}</b> <span class="muted kecil">kandang {{ $b->kandang }}</span><br><span class="muted kecil">{{ $b->pelanggan->nama }}</span></span><span class="kanan kecil">{!! $b->tanggal_keluar->isToday() ? lencana('Jemput hari ini', 'peach') : 'jemput ' . tgl($b->tanggal_keluar) !!}</span></div>
      @empty<p class="kosong">Kandang kosong.</p>@endforelse
    </div>
  </div>
  <div class="kartu">
    <div class="kp"><h2>Vaksin jatuh tempo</h2><span class="muted kecil">14 hari ke depan</span></div>
    <div class="lst">
      @forelse ($vaksin as $v)
        @php $pl = $v->hewan->pelanggan; @endphp
        <div><span><b>{{ $v->hewan->nama }}</b> <span class="muted kecil">· {{ $v->nama }}</span><br><span class="muted kecil">{{ tgl($v->berikutnya) }} · {{ $pl->nama }}</span></span><a class="btn hijau kecil" target="_blank" rel="noopener" href="{{ $pl->waLink('Halo ' . $pl->nama . ', jadwal vaksin ' . $v->nama . ' untuk ' . $v->hewan->nama . ' jatuh pada ' . tgl($v->berikutnya) . '. Mau sekalian booking? - ' . toko('nama')) }}">{!! ikon('wa') !!} Ingatkan</a></div>
      @empty<p class="kosong">Tidak ada vaksin jatuh tempo.</p>@endforelse
    </div>
  </div>
  <div class="kartu">
    <div class="kp"><h2>Stok menipis</h2>@if ($u->bisa('admin', 'kasir'))<a class="kecil tebal" href="{{ route('panel.produk.index', ['stok' => 'menipis']) }}">Kelola</a>@endif</div>
    <div class="lst">
      @forelse ($menipis as $p)
        <div><span style="display:flex;gap:10px;align-items:center">@include('panel._ubin', ['p' => $p, 'kelas' => 's'])<span><b class="kecil">{{ $p->nama }}</b><br><span class="muted kecil">min. {{ $p->stok_min }} {{ $p->satuan }}</span></span></span>{!! lencana('Sisa ' . $p->stok, $p->stok <= 0 ? 'merah' : 'kuning') !!}</div>
      @empty<p class="kosong">Semua stok aman.</p>@endforelse
    </div>
  </div>
</div>
@if ($laris->count())
<div class="kartu" style="margin-top:18px">
  <div class="kp"><h2>Produk terlaris bulan ini</h2></div>
  <div class="tabel-w"><table class="t"><tr><th>#</th><th>Produk</th><th class="kanan">Terjual</th><th class="kanan">Nilai</th></tr>
    @foreach ($laris as $i => $l)<tr><td>{{ $i + 1 }}</td><td class="tebal">{{ $l->nama }}</td><td class="kanan">{{ $l->qty }}</td><td class="kanan">{{ rp($l->nilai) }}</td></tr>@endforeach
  </table></div>
</div>
@endif
@endsection
