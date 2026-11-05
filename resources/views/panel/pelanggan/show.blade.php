@extends('panel', ['judul' => $p->nama, 'sub' => $p->telepon . ($p->alamat ? ' · ' . $p->alamat : '')])
@php $u = auth()->user(); @endphp
@section('aksi')
  <a class="btn hijau" target="_blank" rel="noopener" href="{{ $p->waLink() }}">{!! ikon('wa') !!} WhatsApp</a>
  <a class="btn" href="{{ route('panel.booking.create', ['pelanggan' => $p->id]) }}">{!! ikon('kalender') !!} Booking</a>
  <a class="btn putih" href="{{ route('panel.pelanggan.edit', $p) }}">{!! ikon('edit') !!} Ubah</a>
@endsection
@section('isi')
<div class="grid g4" style="margin-bottom:18px">
  <div class="stat biru"><div class="l"><span class="ib">{!! ikon('dompet') !!}</span>Total belanja</div><div class="v">{{ rp($belanja) }}</div></div>
  <div class="stat"><div class="l"><span class="ib">{!! ikon('struk') !!}</span>Transaksi</div><div class="v">{{ $p->transaksi()->where('batal', false)->count() }}</div></div>
  <div class="stat peach"><div class="l"><span class="ib">{!! ikon('gunting') !!}</span>Booking</div><div class="v">{{ $p->booking()->count() }}</div></div>
  <div class="stat mint"><div class="l"><span class="ib">{!! ikon('jejak') !!}</span>Hewan</div><div class="v">{{ $p->hewan->count() }}</div></div>
</div>
@if ($p->catatan)<div class="alert info">{!! ikon('info') !!} {{ $p->catatan }}</div>@endif
<div class="kp" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h2 style="font-size:24px">Kartu hewan</h2></div>
<div class="hewan" style="margin-bottom:18px">
  @foreach ($p->hewan as $h)
    <div class="hw">
      <div class="hd"><span class="ava {{ $h->jenis === 'Anjing' ? 'anjing' : ($h->jenis === 'Kucing' ? '' : 'lain') }}">{!! ikon('jejak') !!}</span><div style="flex:1"><h3>{{ $h->nama }}</h3><span class="muted kecil">{{ $h->jenis }}{{ $h->ras ? ' · ' . $h->ras : '' }}{{ $h->kelamin ? ' · ' . $h->kelamin : '' }}</span></div></div>
      <div class="mini"><div>Umur<b>{{ $h->umur() }}</b></div><div>Berat<b>{{ $h->berat ? str_replace('.', ',', $h->berat) . ' kg' : '-' }}</b></div><div>Warna<b>{{ $h->warna ?: '-' }}</b></div></div>
      @if ($h->catatan)<div class="alert info" style="margin:0">{{ $h->catatan }}</div>@endif
      <b style="font-size:14px">Vaksin</b>
      <div class="lst">
        @forelse ($h->vaksin as $v)
          <div><span>{{ $v->nama }}<br><span class="muted kecil">{{ tgl($v->tanggal) }}</span></span><span class="kanan kecil">{!! $v->berikutnya ? ($v->berikutnya->isPast() ? lencana('Lewat ' . tgl($v->berikutnya), 'merah') : 'berikutnya<br>' . tgl($v->berikutnya)) : '' !!}</span>
            @if ($u->bisa('admin'))<form method="post" action="{{ route('panel.vaksin.destroy', $v) }}" data-yakin="Hapus catatan vaksin ini?">@csrf @method('DELETE')<button class="btn merah kecil" aria-label="Hapus">{!! ikon('x') !!}</button></form>@endif</div>
        @empty<p class="muted kecil" style="margin:0">Belum ada catatan.</p>@endforelse
      </div>
      <details class="lipat"><summary>+ Catat vaksin</summary>
        <form method="post" action="{{ route('panel.vaksin.store', $h) }}">@csrf
          <div class="f"><input name="nama" placeholder="Nama vaksin, mis. Tricat (F3)" required maxlength="60"></div>
          <div class="grid g2" style="gap:0 10px"><div class="f"><label>Tanggal</label><input type="date" name="tanggal" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required></div><div class="f"><label>Berikutnya</label><input type="date" name="berikutnya" value="{{ now()->addYear()->toDateString() }}"></div></div>
          <button class="btn kecil">Simpan vaksin</button></form>
      </details>
      <details class="lipat"><summary>Ubah data {{ $h->nama }}</summary>
        <form method="post" action="{{ route('panel.hewan.update', $h) }}">@csrf @method('PUT') @include('panel.pelanggan._hewan', ['h' => $h])<button class="btn kecil">Simpan</button></form>
        @if ($u->bisa('admin'))<form method="post" action="{{ route('panel.hewan.destroy', $h) }}" data-yakin="Hapus {{ $h->nama }}?" style="margin-top:8px">@csrf @method('DELETE')<button class="btn merah kecil">Hapus hewan</button></form>@endif
      </details>
    </div>
  @endforeach
  <div class="hw" style="background:transparent;border:2px dashed var(--garis)">
    <h3>Tambah hewan</h3>
    <form method="post" action="{{ route('panel.hewan.store', $p) }}">@csrf @include('panel.pelanggan._hewan', ['h' => new \App\Models\Hewan()])<button class="btn kecil">{!! ikon('plus') !!} Tambah</button></form>
  </div>
</div>
<div class="grid g2 f1">
  <div class="kartu"><h2 style="margin-bottom:8px">Booking terakhir</h2>
    <div class="lst">@forelse ($p->booking as $b)<div><span><a class="tebal" href="{{ route('panel.booking.show', $b) }}">{{ $b->layanan->nama }}</a><br><span class="muted kecil">{{ $b->nama_hewan }} · {{ tgl($b->tanggal) }}</span></span>{!! lencana(\App\Models\Booking::STATUS[$b->status], \App\Models\Booking::WARNA[$b->status]) !!}</div>@empty<p class="muted">Belum ada booking.</p>@endforelse</div>
  </div>
  <div class="kartu"><h2 style="margin-bottom:8px">Transaksi terakhir</h2>
    <div class="lst">@forelse ($p->transaksi as $t)<div><span>@if ($u->bisa('admin', 'kasir'))<a class="tebal" href="{{ route('panel.transaksi.show', $t) }}">{{ $t->nomor }}</a>@else<b>{{ $t->nomor }}</b>@endif<br><span class="muted kecil">{{ tgl($t->waktu, true) }} · {{ $t->item_count }} item</span></span><b class="{{ $t->batal ? 'muted' : '' }}">{{ rp($t->total) }}</b></div>@empty<p class="muted">Belum ada transaksi.</p>@endforelse</div>
  </div>
</div>
@if ($u->bisa('admin'))
<form method="post" action="{{ route('panel.pelanggan.destroy', $p) }}" data-yakin="Hapus pelanggan ini beserta data hewannya?" style="margin-top:18px">@csrf @method('DELETE')<button class="btn merah">{!! ikon('hapus') !!} Hapus pelanggan</button></form>
@endif
@endsection
