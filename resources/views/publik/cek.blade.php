@extends('publik.layout', ['judul' => 'Cek booking'])
@php $urut = ['menunggu', 'dikonfirmasi', 'dikerjakan', 'selesai']; @endphp
@section('isi')
<div class="halaman">
  <div class="wadah" style="max-width:980px">
    <div class="kepala-h"><span class="chip"><i>{!! ikon('cari', 'ic') !!}</i> Cek booking</span><h1>{{ $b && session('baru') ? 'Booking terkirim!' : 'Lacak status booking' }}</h1><p>{{ $b && session('baru') ? 'Simpan kode di bawah. Kami akan mengonfirmasi lewat WhatsApp.' : 'Masukkan kode booking dan 4 digit terakhir nomor WhatsApp.' }}</p></div>
    @if ($b)
      @php $pos = array_search($b->status, $urut); @endphp
      <div class="tiket">
        <div class="kiri">
          <span class="tag">{{ \App\Models\Layanan::KATEGORI[$b->layanan->kategori] ?? '' }}</span>
          <h2 style="font-size:34px;margin:12px 0 4px">{{ $b->layanan->nama }}</h2>
          <p class="muted" style="margin:0">untuk {{ $b->nama_hewan }} ({{ $b->jenis_hewan }}) · a.n. {{ explode(' ', $b->pelanggan->nama)[0] }}</p>
          <div class="alur {{ $b->status === 'batal' ? 'batal' : '' }}">
            @if ($b->status === 'batal')<div class="on">Diterima</div><div class="on">Dibatalkan</div>
            @else @foreach ($urut as $i => $s)<div class="{{ $pos !== false && $i <= $pos ? 'on' : '' }}">{{ \App\Models\Booking::STATUS[$s] }}</div>@endforeach @endif
          </div>
          <div class="detail">
            <div><span>{{ $b->kandang ? 'Titip' : 'Tanggal' }}</span><b>{{ tgl($b->tanggal) }}{{ $b->jam ? ', ' . $b->jam : '' }}</b></div>
            <div><span>{{ $b->kandang ? 'Jemput' : 'Durasi' }}</span><b>{{ $b->kandang ? tgl($b->tanggal_keluar) . ' (' . $b->malam() . ' malam)' : $b->layanan->durasi . ' menit' }}</b></div>
            <div><span>Perkiraan biaya</span><b>{{ rp($b->biaya()) }}</b></div>
            <div><span>Pembayaran</span><b>{{ $b->transaksi_id ? 'Lunas' : 'Di toko' }}</b></div>
          </div>
        </div>
        <div class="kanan">
          <span>Kode booking</span><div class="kode">{{ $b->kode }}</div>
          <span class="tag" style="background:#ffd95a;color:#151a3d">{{ \App\Models\Booking::STATUS[$b->status] }}</span>
          @if (\App\Support\Petshop::waLink())<a class="btn putih kecil" style="margin-top:14px" target="_blank" rel="noopener" href="{{ \App\Support\Petshop::waLink('Halo, saya mau tanya booking ' . $b->kode . ' untuk ' . $b->nama_hewan . '.') }}">{!! ikon('wa') !!} Tanya via WA</a>@endif
        </div>
      </div>
    @endif
    <form class="panel" method="get" action="{{ route('cek') }}" style="margin-top:22px;display:grid;grid-template-columns:1fr 200px auto;gap:12px;align-items:end">
      <div class="f" style="margin:0"><label for="kode">Kode booking</label><input id="kode" name="kode" value="{{ $kode }}" placeholder="BK-XXXXX" style="text-transform:uppercase" required></div>
      <div class="f" style="margin:0"><label for="hp">4 digit terakhir WA</label><input id="hp" name="hp" value="{{ $hp }}" inputmode="numeric" maxlength="4" required></div>
      <button class="btn">{!! ikon('cari') !!} Cek</button>
    </form>
    @if ($salah)<div class="alert err" style="margin-top:14px">Kode booking atau 4 digit nomor WhatsApp tidak cocok.</div>@endif
  </div>
</div>
<style>@media (max-width:760px){form.panel{grid-template-columns:1fr!important}}</style>
@endsection
