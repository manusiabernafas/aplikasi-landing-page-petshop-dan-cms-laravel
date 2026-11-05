@extends('panel', ['judul' => 'Booking ' . $b->kode, 'sub' => $b->layanan->nama . ' · ' . ($b->sumber === 'online' ? 'booking online' : 'dari toko')])
@php $label = ['dikonfirmasi' => ['Konfirmasi', 'btn'], 'dikerjakan' => [$b->layanan->penitipan() ? 'Check-in' : 'Mulai kerjakan', 'btn tinta'], 'selesai' => [$b->layanan->penitipan() ? 'Check-out' : 'Selesai', 'btn hijau'], 'batal' => ['Batalkan', 'btn merah']]; @endphp
@section('aksi')<a class="btn putih" href="{{ route('panel.booking.index', ['tanggal' => $b->tanggal->toDateString(), 'tab' => $b->kandang ? 'penitipan' : 'jadwal']) }}">{!! ikon('kiri') !!} Jadwal</a>@endsection
@section('isi')
<div class="grid g21">
  <div style="display:grid;gap:18px;align-content:start">
    <div class="kartu">
      <div class="kp"><h2>{{ $b->nama_hewan }} <span class="muted" style="font-size:16px">({{ $b->jenis_hewan }})</span></h2>{!! lencana(\App\Models\Booking::STATUS[$b->status], \App\Models\Booking::WARNA[$b->status]) !!}</div>
      <dl class="dl">
        <dt>Layanan</dt><dd>{{ $b->layanan->nama }} · {{ rp($b->layanan->harga) }}/{{ $b->layanan->satuan }}</dd>
        @if ($b->kandang)
          <dt>Titip - jemput</dt><dd>{{ tgl($b->tanggal) }} - {{ tgl($b->tanggal_keluar) }} ({{ $b->malam() }} malam)</dd><dt>Kandang</dt><dd>Nomor {{ $b->kandang }}</dd>
        @else
          <dt>Jadwal</dt><dd>{{ tgl($b->tanggal) }}, jam {{ $b->jam }} ({{ $b->layanan->durasi }} menit)</dd>
        @endif
        <dt>Perkiraan biaya</dt><dd>{{ rp($b->biaya()) }}</dd>
        <dt>Pemilik</dt><dd><a href="{{ route('panel.pelanggan.show', $b->pelanggan) }}">{{ $b->pelanggan->nama }}</a> · {{ $b->pelanggan->telepon }}</dd>
        <dt>Catatan</dt><dd>{{ $b->catatan ?: '-' }}</dd>
        <dt>Pembayaran</dt><dd>@if ($b->transaksi)<a href="{{ route('panel.transaksi.show', $b->transaksi) }}">{{ $b->transaksi->nomor }}</a> {!! lencana('Lunas', 'hijau') !!}@else Belum dibayar @endif</dd>
      </dl>
      @if ($next = \App\Models\Booking::ALUR[$b->status] ?? [])
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px">
          @foreach ($next as $s)
            <form method="post" action="{{ route('panel.booking.status', $b) }}" @if ($s === 'batal') data-yakin="Batalkan booking ini?" @endif>@csrf<input type="hidden" name="status" value="{{ $s }}"><button class="{{ $label[$s][1] }}">{{ $label[$s][0] }}</button></form>
          @endforeach
        </div>
      @endif
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
        @if (! $b->transaksi_id && $b->status !== 'batal' && auth()->user()->bisa('admin', 'kasir'))<a class="btn peach" href="{{ route('panel.kasir', ['booking' => $b->id]) }}">{!! ikon('kasir') !!} Tagih di kasir</a>@endif
        <a class="btn hijau" target="_blank" rel="noopener" href="{{ $b->pelanggan->waLink('Halo ' . $b->pelanggan->nama . ', ini dari ' . toko('nama') . ' tentang booking ' . $b->kode . ' (' . $b->nama_hewan . ').') }}">{!! ikon('wa') !!} Chat pemilik</a>
      </div>
    </div>
    @if ($riwayat->count())
      <div class="kartu"><h2 style="margin-bottom:8px">Kunjungan sebelumnya</h2>
        <div class="lst">@foreach ($riwayat as $r)<div><span><a class="tebal" href="{{ route('panel.booking.show', $r) }}">{{ $r->layanan->nama }}</a><br><span class="muted kecil">{{ tgl($r->tanggal) }} · {{ $r->kode }}</span></span>{!! lencana(\App\Models\Booking::STATUS[$r->status], \App\Models\Booking::WARNA[$r->status]) !!}</div>@endforeach</div>
      </div>
    @endif
  </div>
  @if ($h = $b->hewan)
    <div class="hw" style="align-content:start">
      <div class="hd"><span class="ava {{ $h->jenis === 'Anjing' ? 'anjing' : ($h->jenis === 'Kucing' ? '' : 'lain') }}">{!! ikon('jejak') !!}</span><div><h3>{{ $h->nama }}</h3><span class="muted kecil">{{ $h->jenis }}{{ $h->ras ? ' · ' . $h->ras : '' }}{{ $h->kelamin ? ' · ' . $h->kelamin : '' }}</span></div></div>
      <div class="mini"><div>Umur<b>{{ $h->umur() }}</b></div><div>Berat<b>{{ $h->berat ? str_replace('.', ',', $h->berat) . ' kg' : '-' }}</b></div><div>Warna<b>{{ $h->warna ?: '-' }}</b></div></div>
      @if ($h->catatan)<div class="alert info" style="margin:0">{{ $h->catatan }}</div>@endif
      <b>Riwayat vaksin</b>
      <div class="lst">@forelse ($h->vaksin->take(4) as $v)<div><span>{{ $v->nama }}<br><span class="muted kecil">{{ tgl($v->tanggal) }}</span></span><span class="kecil">{{ $v->berikutnya ? 'berikutnya ' . tgl($v->berikutnya) : '' }}</span></div>@empty<p class="muted kecil">Belum ada catatan vaksin.</p>@endforelse</div>
      <a class="btn putih kecil" href="{{ route('panel.pelanggan.show', $b->pelanggan) }}">Buka kartu hewan</a>
    </div>
  @endif
</div>
@endsection
