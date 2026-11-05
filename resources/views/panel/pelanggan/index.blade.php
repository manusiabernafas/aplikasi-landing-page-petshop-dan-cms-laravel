@extends('panel', ['judul' => 'Pelanggan & hewan'])
@section('aksi')<a class="btn" href="{{ route('panel.pelanggan.create') }}">{!! ikon('plus') !!} Pelanggan baru</a>@endsection
@section('isi')
<form class="filter" method="get"><input class="inp" name="q" value="{{ $q }}" placeholder="Nama, nomor HP, atau nama hewan" style="flex:1;max-width:420px"><button class="btn tinta">{!! ikon('cari') !!} Cari</button></form>
<div class="kartu"><div class="tabel-w"><table class="t">
  <tr><th>Pelanggan</th><th>Hewan</th><th class="kanan">Transaksi</th><th class="kanan">Total belanja</th><th></th></tr>
  @forelse ($data as $p)
    <tr><td><a class="tebal" href="{{ route('panel.pelanggan.show', $p) }}">{{ $p->nama }}</a><br><span class="muted kecil">{{ $p->telepon }}</span></td>
      <td>@foreach ($p->hewan as $h){!! lencana($h->nama . ' · ' . $h->jenis, $h->jenis === 'Anjing' ? 'hijau' : 'peach') !!} @endforeach</td>
      <td class="kanan">{{ $p->transaksi_count }}</td><td class="kanan tebal nowrap">{{ rp($p->belanja ?? 0) }}</td>
      <td class="kanan nowrap"><a class="btn hijau kecil" target="_blank" rel="noopener" href="{{ $p->waLink() }}" aria-label="WhatsApp">{!! ikon('wa') !!}</a> <a class="btn putih kecil" href="{{ route('panel.pelanggan.show', $p) }}">Detail</a></td></tr>
  @empty<tr><td colspan="5" class="kosong">Tidak ada pelanggan.</td></tr>@endforelse
</table></div>{{ $data->links('panel._pagin') }}</div>
@endsection
