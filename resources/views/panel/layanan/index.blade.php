@extends('panel', ['judul' => 'Layanan & harga', 'sub' => 'Tampil di halaman depan, form booking, dan kasir'])
@section('aksi')<a class="btn" href="{{ route('panel.layanan.create') }}">{!! ikon('plus') !!} Layanan baru</a>@endsection
@section('isi')
@foreach ($data as $kat => $isi)
  <div class="kartu" style="margin-bottom:18px">
    <h2 style="margin-bottom:10px">{{ \App\Models\Layanan::KATEGORI[$kat] ?? $kat }}</h2>
    <div class="tabel-w"><table class="t">
      <tr><th>Layanan</th><th>Untuk</th><th>Durasi</th><th class="kanan">Harga</th><th>Status</th><th class="kanan">Booking</th><th></th></tr>
      @foreach ($isi as $l)
        <tr class="{{ $l->aktif ? '' : 'mati' }}"><td><b>{{ $l->nama }}</b><br><span class="muted kecil">{{ $l->deskripsi }}</span></td><td>{{ $l->untuk }}</td><td class="nowrap">{{ $l->penitipan() ? 'per malam' : $l->durasi . ' menit' }}</td>
          <td class="kanan tebal nowrap">{{ rp($l->harga) }} <span class="muted kecil">/ {{ $l->satuan }}</span></td>
          <td class="nowrap">{!! $l->aktif ? lencana('Aktif', 'hijau') : lencana('Nonaktif', 'abu') !!} {!! $l->publik ? lencana('Publik', 'biru') : '' !!}</td><td class="kanan">{{ $l->booking_count }}</td>
          <td class="kanan"><a class="btn putih kecil" href="{{ route('panel.layanan.edit', $l) }}">{!! ikon('edit') !!}</a></td></tr>
      @endforeach
    </table></div>
  </div>
@endforeach
@endsection
