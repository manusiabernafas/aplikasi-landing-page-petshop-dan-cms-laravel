@extends('panel', ['judul' => 'Booking baru', 'sub' => 'Untuk pelanggan yang datang langsung atau menelepon'])
@section('aksi')<a class="btn putih" href="{{ route('panel.booking.index') }}">{!! ikon('kiri') !!} Jadwal</a>@endsection
@section('isi')
<form method="post" action="{{ route('panel.booking.store') }}" class="grid g21" id="fB">
  @csrf
  <div class="kartu">
    <h2 style="margin-bottom:14px">Pelanggan & hewan</h2>
    @if ($pel)
      <input type="hidden" name="pelanggan_id" value="{{ $pel->id }}">
      <div class="alert info" style="justify-content:space-between"><span><b>{{ $pel->nama }}</b> · {{ $pel->telepon }}</span><a href="{{ route('panel.booking.create') }}">Ganti</a></div>
      <div class="f"><label>Hewan</label><select name="hewan_id" id="hewan"><option value="">Hewan baru...</option>@foreach ($pel->hewan as $h)<option value="{{ $h->id }}" data-jenis="{{ $h->jenis }}" @selected(old('hewan_id') == $h->id)>{{ $h->nama }} ({{ $h->jenis }}{{ $h->ras ? ', ' . $h->ras : '' }})</option>@endforeach</select></div>
    @else
      <div class="grid g2 f1" style="gap:0 14px">
        <div class="f"><label>Nama pemilik</label><input name="nama" value="{{ old('nama') }}" maxlength="80">@error('nama')<div class="galat">{{ $message }}</div>@enderror</div>
        <div class="f"><label>Nomor WhatsApp</label><input name="telepon" value="{{ old('telepon') }}" inputmode="tel">@error('telepon')<div class="galat">{{ $message }}</div>@enderror<small>Bila nomor sudah terdaftar, booking otomatis masuk ke pelanggan itu.</small></div>
      </div>
    @endif
    <div class="grid g2 f1" style="gap:0 14px" id="hewanBaru">
      <div class="f"><label>Nama hewan</label><input name="nama_hewan" value="{{ old('nama_hewan') }}" maxlength="50">@error('nama_hewan')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f"><label>Jenis</label><select name="jenis_hewan" id="jenis">@foreach ($jenis as $j)<option @selected(old('jenis_hewan') === $j)>{{ $j }}</option>@endforeach</select></div>
    </div>
    <h2 style="margin:10px 0 14px">Layanan & jadwal</h2>
    <div class="f"><label>Layanan</label><select name="layanan_id" id="layanan">@foreach ($layanan as $l)<option value="{{ $l->id }}" data-titip="{{ $l->penitipan() ? 1 : 0 }}" data-harga="{{ $l->harga }}" @selected(old('layanan_id', request('layanan')) == $l->id)>{{ \App\Models\Layanan::KATEGORI[$l->kategori] }} · {{ $l->nama }} ({{ rp($l->harga) }}/{{ $l->satuan }})</option>@endforeach</select>@error('layanan_id')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="grid g2 f1" style="gap:0 14px">
      <div class="f"><label id="lblTgl">Tanggal</label><input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" min="{{ now()->toDateString() }}">@error('tanggal')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f" id="fJam"><label>Jam</label><select name="jam" id="jam"></select>@error('jam')<div class="galat">{{ $message }}</div>@enderror</div>
      <div class="f" id="fKeluar" hidden><label>Tanggal jemput</label><input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', now()->addDays(2)->toDateString()) }}">@error('tanggal_keluar')<div class="galat">{{ $message }}</div>@enderror</div>
    </div>
    <div class="f"><label>Catatan</label><textarea name="catatan" maxlength="300">{{ old('catatan') }}</textarea></div>
  </div>
  <div class="kartu" style="align-content:start;display:grid;gap:12px">
    <h2>Ketersediaan</h2>
    <div id="info" class="muted">Pilih layanan dan tanggal.</div>
    <button class="btn">{!! ikon('cek') !!} Simpan booking</button>
    <p class="muted kecil" style="margin:0">Booking dari toko langsung berstatus Dikonfirmasi.</p>
  </div>
</form>
@endsection
@push('skrip')
<script>
(() => {
  const f = document.getElementById('fB'), SLOT = '{{ route('booking.slot') }}', jamLama = @json(old('jam'));
  const hw = document.getElementById('hewan');
  const cekHewan = () => { if (!hw) return; const o = hw.selectedOptions[0]; document.getElementById('hewanBaru').hidden = !!hw.value; if (hw.value) f.jenis_hewan.value = o.dataset.jenis; };
  if (hw) hw.onchange = cekHewan; cekHewan();
  async function muat() {
    const o = f.layanan_id.selectedOptions[0], titip = o.dataset.titip === '1';
    document.getElementById('fJam').hidden = titip; document.getElementById('fKeluar').hidden = !titip; document.getElementById('lblTgl').textContent = titip ? 'Tanggal titip' : 'Tanggal';
    const info = document.getElementById('info');
    try {
      const r = await (await fetch(SLOT + '?' + new URLSearchParams({ layanan: f.layanan_id.value, tanggal: f.tanggal.value, keluar: f.tanggal_keluar.value }), { headers: { Accept: 'application/json' } })).json();
      if (r.penitipan) { info.innerHTML = r.kandang > 0 ? '<b>' + r.kandang + ' kandang</b> kosong untuk rentang tanggal ini.' : '<span class="galat">Kandang penuh untuk rentang tanggal ini.</span>'; }
      else {
        f.jam.innerHTML = r.slot.map(s => `<option value="${s.jam}" ${s.lewat || s.sisa < 1 ? 'disabled' : ''} ${s.jam === jamLama ? 'selected' : ''}>${s.jam} ${s.lewat ? '(lewat)' : s.sisa < 1 ? '(penuh)' : '(sisa ' + s.sisa + ')'}</option>`).join('');
        const ok = r.slot.filter(s => !s.lewat && s.sisa > 0).length; info.innerHTML = '<b>' + ok + ' dari ' + r.slot.length + ' jam</b> masih tersedia.';
      }
    } catch (e) { info.textContent = 'Layanan ini tidak bisa dibooking online; cek ketersediaan di jadwal.'; }
  }
  f.addEventListener('change', e => { if (['layanan_id', 'tanggal', 'tanggal_keluar'].includes(e.target.name)) muat(); });
  muat();
})();
</script>
@endpush
