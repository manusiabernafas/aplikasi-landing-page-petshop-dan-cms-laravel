@extends('panel', ['judul' => 'Jadwal & booking'])
@php $hari = \Carbon\Carbon::parse($tanggal); @endphp
@section('aksi')<a class="btn" href="{{ route('panel.booking.create') }}">{!! ikon('plus') !!} Booking baru</a>@endsection
@section('isi')
<div class="filter" style="justify-content:space-between">
  <div class="pil">
    <a class="{{ $tab === 'jadwal' ? 'on' : '' }}" href="{{ route('panel.booking.index', ['tab' => 'jadwal', 'tanggal' => $tanggal]) }}">Jadwal grooming & klinik</a>
    <a class="{{ $tab === 'penitipan' ? 'on' : '' }}" href="{{ route('panel.booking.index', ['tab' => 'penitipan', 'tanggal' => $tanggal]) }}">Denah pet hotel</a>
    <a class="{{ $tab === 'semua' ? 'on' : '' }}" href="{{ route('panel.booking.index', ['tab' => 'semua']) }}">Semua booking</a>
  </div>
  @if ($tab !== 'semua')
    <form method="get" style="display:flex;gap:8px;align-items:center">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <a class="btn putih kecil" href="{{ route('panel.booking.index', ['tab' => $tab, 'tanggal' => $hari->copy()->subDay()->toDateString()]) }}" aria-label="Sebelumnya">{!! ikon('kiri') !!}</a>
      <input class="inp" type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()" style="min-width:0">
      <a class="btn putih kecil" href="{{ route('panel.booking.index', ['tab' => $tab, 'tanggal' => $hari->copy()->addDay()->toDateString()]) }}" aria-label="Berikutnya">{!! ikon('kanan') !!}</a>
      @unless ($hari->isToday())<a class="btn tinta kecil" href="{{ route('panel.booking.index', ['tab' => $tab]) }}">Hari ini</a>@endunless
    </form>
  @endif
</div>
@if ($menunggu->count() && $tab !== 'semua')
  <div class="kartu" style="margin-bottom:18px;background:#fff1b8">
    <div class="kp"><h2>{{ $menunggu->count() }} booking online menunggu konfirmasi</h2></div>
    <div class="jadwal" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));display:grid">
      @foreach ($menunggu as $b)<a class="bk menunggu" href="{{ route('panel.booking.show', $b) }}"><b>{{ $b->nama_hewan }} · {{ $b->layanan->nama }}</b><small>{{ tgl($b->tanggal) }}{{ $b->jam ? ', ' . $b->jam : ' s.d. ' . tgl($b->tanggal_keluar) }} · {{ $b->pelanggan->nama }}</small></a>@endforeach
    </div>
  </div>
@endif

@if ($tab === 'jadwal')
  <div class="kartu">
    <div class="kp"><h2>{{ $hari->translatedFormat('l') }}, {{ tgl($hari) }}</h2><span class="muted kecil">{{ $data->where('status', '!=', 'batal')->count() }} booking · kapasitas {{ toko('kapasitas_slot') }} per jam per kategori</span></div>
    <div class="jadwal">
      @foreach ($slot as $j)
        <div class="slotbar"><div class="jm">{{ $j }}</div><div class="isi">
          @foreach ($data->where('jam', $j) as $b)
            <a class="bk {{ $b->status }}" href="{{ route('panel.booking.show', $b) }}"><span style="display:flex;justify-content:space-between;gap:8px"><b>{{ $b->nama_hewan }} <span class="muted kecil">({{ $b->jenis_hewan }})</span></b>{!! lencana(\App\Models\Booking::STATUS[$b->status], \App\Models\Booking::WARNA[$b->status]) !!}</span><small>{{ $b->layanan->nama }} · {{ $b->layanan->durasi }} mnt</small><small>{{ $b->pelanggan->nama }} · {{ $b->kode }}</small></a>
          @endforeach
        </div></div>
      @endforeach
      @foreach ($data->whereNotIn('jam', $slot) as $b)<a class="bk {{ $b->status }}" href="{{ route('panel.booking.show', $b) }}"><b>{{ $b->jam }} · {{ $b->nama_hewan }}</b><small>{{ $b->layanan->nama }}</small></a>@endforeach
    </div>
  </div>
@elseif ($tab === 'penitipan')
  <div class="kartu">
    <div class="kp"><h2>Pet hotel, {{ tgl($hari) }}</h2><span class="muted kecil">{{ $data->count() }} dari {{ count($slot) }} kandang terisi · kuning = jemput hari itu</span></div>
    <div class="kandang">
      @foreach ($slot as $n)
        @php $b = $data->firstWhere('kandang', $n); @endphp
        @if ($b)
          <a class="kd isi {{ $b->tanggal_keluar->toDateString() === $tanggal ? 'pulang' : '' }}" href="{{ route('panel.booking.show', $b) }}"><span class="no">KANDANG {{ $n }}</span><b class="jf" style="font-size:20px">{{ $b->nama_hewan }}</b><span class="kecil">{{ $b->layanan->nama }}</span><span class="kecil">{{ $b->pelanggan->nama }}</span><span class="kecil tebal">{{ tgl($b->tanggal) }} - {{ tgl($b->tanggal_keluar) }}</span></a>
        @else
          <div class="kd"><span class="no">KANDANG {{ $n }}</span><span class="muted">Kosong</span></div>
        @endif
      @endforeach
    </div>
  </div>
@else
  <form class="filter" method="get"><input type="hidden" name="tab" value="semua"><input class="inp" name="q" value="{{ $q }}" placeholder="Kode / nama hewan / pelanggan">
    <select class="inp" name="status"><option value="">Semua status</option>@foreach (\App\Models\Booking::STATUS as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select><button class="btn tinta">{!! ikon('cari') !!} Saring</button></form>
  <div class="kartu"><div class="tabel-w"><table class="t">
    <tr><th>Kode</th><th>Jadwal</th><th>Hewan</th><th>Pelanggan</th><th>Layanan</th><th>Status</th><th>Bayar</th></tr>
    @forelse ($data as $b)
      <tr><td><a class="tebal" href="{{ route('panel.booking.show', $b) }}">{{ $b->kode }}</a></td><td class="nowrap">{{ tgl($b->tanggal) }}{{ $b->jam ? ', ' . $b->jam : ' - ' . tgl($b->tanggal_keluar) }}</td><td>{{ $b->nama_hewan }}</td><td>{{ $b->pelanggan->nama }}</td><td>{{ $b->layanan->nama }}</td>
        <td>{!! lencana(\App\Models\Booking::STATUS[$b->status], \App\Models\Booking::WARNA[$b->status]) !!}</td><td>{!! $b->transaksi_id ? lencana('Lunas', 'hijau') : '<span class="muted kecil">-</span>' !!}</td></tr>
    @empty<tr><td colspan="7" class="kosong">Tidak ada booking.</td></tr>@endforelse
  </table></div>{{ $data->links('panel._pagin') }}</div>
@endif
@endsection
