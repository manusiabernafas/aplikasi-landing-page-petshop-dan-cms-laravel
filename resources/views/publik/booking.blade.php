@extends('publik.layout', ['judul' => 'Booking'])
@php $ikonKat = ['grooming' => 'gunting', 'penitipan' => 'hotel', 'klinik' => 'stetoskop']; @endphp
@section('isi')
<div class="halaman">
  <div class="wadah">
    <div class="kepala-h"><span class="chip"><i>{!! ikon('kalender', 'ic') !!}</i> Booking online</span><h1>Pilih jadwal untuk anabulmu</h1><p>Kuota tiap jam terbatas. Setelah booking, kamu dapat kode untuk cek status.</p></div>
    @if ($errors->any())<div class="alert err" role="alert">Ada isian yang perlu diperbaiki. Lihat tulisan merah di bawah.</div>@endif
    <form method="post" action="{{ route('booking.simpan') }}" class="form-g" id="fBook" novalidate>
      @csrf
      <label class="hp-trap">Situs <input name="situs" tabindex="-1" autocomplete="off"></label>
      <div class="panel lgk">
        <div>
          <h3><span class="n">1</span> Pilih layanan</h3>
          <div class="pilih-l">
            @foreach ($layanan as $l)
              <label><input type="radio" name="layanan_id" value="{{ $l->id }}" data-harga="{{ $l->harga }}" data-titip="{{ $l->penitipan() ? 1 : 0 }}" data-untuk="{{ $l->untuk }}" data-nama="{{ $l->nama }}" data-satuan="{{ $l->satuan }}" @checked(old('layanan_id', $pilih) == $l->id)>
                <span><b>{{ $l->nama }}</b><small>{{ $l->untuk === 'Semua' ? 'Kucing & anjing' : $l->untuk }} · <span class="hg">{{ rp($l->harga) }}</span>/{{ $l->satuan }}</small></span></label>
            @endforeach
          </div>
          @error('layanan_id')<div class="galat">{{ $message }}</div>@enderror
        </div>
        <div>
          <h3><span class="n">2</span> Data anabul</h3>
          <div class="f"><span>Jenis hewan</span><div class="jenis">@foreach ($jenis as $j)<label><input type="radio" name="jenis_hewan" value="{{ $j }}" @checked(old('jenis_hewan', 'Kucing') === $j)>{!! ikon('jejak') !!} {{ $j }}</label>@endforeach</div>@error('jenis_hewan')<div class="galat">{{ $message }}</div>@enderror</div>
          <div class="dua">
            <div class="f"><label for="nama_hewan">Nama hewan</label><input id="nama_hewan" name="nama_hewan" value="{{ old('nama_hewan') }}" maxlength="50" placeholder="Mochi">@error('nama_hewan')<div class="galat">{{ $message }}</div>@enderror</div>
            <div class="f"><label for="nama">Nama pemilik</label><input id="nama" name="nama" value="{{ old('nama') }}" maxlength="80" autocomplete="name">@error('nama')<div class="galat">{{ $message }}</div>@enderror</div>
          </div>
          <div class="f"><label for="telepon">Nomor WhatsApp</label><input id="telepon" name="telepon" value="{{ old('telepon') }}" inputmode="tel" placeholder="08xxxxxxxxxx" autocomplete="tel">@error('telepon')<div class="galat">{{ $message }}</div>@enderror</div>
        </div>
        <div>
          <h3><span class="n">3</span> Jadwal</h3>
          <div class="dua">
            <div class="f"><label for="tanggal" id="lblTgl">Tanggal</label><input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" min="{{ now()->toDateString() }}" max="{{ now()->addDays((int) toko('maks_hari_booking'))->toDateString() }}">@error('tanggal')<div class="galat">{{ $message }}</div>@enderror</div>
            <div class="f" id="fKeluar" hidden><label for="tanggal_keluar">Tanggal jemput</label><input type="date" id="tanggal_keluar" name="tanggal_keluar" value="{{ old('tanggal_keluar', now()->addDays(2)->toDateString()) }}">@error('tanggal_keluar')<div class="galat">{{ $message }}</div>@enderror</div>
          </div>
          <div class="f" id="fJam"><span>Jam</span><div class="slot" id="slot"><span class="muted kecil">Pilih layanan dulu.</span></div>@error('jam')<div class="galat">{{ $message }}</div>@enderror</div>
          <div id="infoKandang" class="alert ok" hidden></div>
          <div class="f"><label for="catatan">Catatan untuk groomer (opsional)</label><textarea id="catatan" name="catatan" maxlength="300" placeholder="Alergi, sifat, permintaan khusus">{{ old('catatan') }}</textarea></div>
        </div>
      </div>
      <aside class="ringkas">
        <h3>Ringkasan</h3>
        <div class="br"><span>Layanan</span><b id="rLay">-</b></div>
        <div class="br"><span>Anabul</span><b id="rHew">-</b></div>
        <div class="br"><span>Jadwal</span><b id="rJad">-</b></div>
        <div class="tot"><span>Perkiraan biaya</span><b id="rTot">Rp0</b></div>
        <button class="btn peach">Kirim booking <span class="bulat">{!! ikon('panah') !!}</span></button>
        <p class="kecil" style="color:#a9aed0;margin:14px 0 0">Pembayaran di toko saat layanan selesai. Biaya bisa berubah bila ada tambahan layanan.</p>
      </aside>
    </form>
  </div>
</div>
@endsection
@push('skrip')
<script>
(() => {
  const f = document.getElementById('fBook'), slotEl = document.getElementById('slot'), jamLama = @json(old('jam'));
  const rp = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
  const hari = t => t ? new Date(t + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) : '-';
  const lay = () => f.querySelector('input[name=layanan_id]:checked');
  async function muat() {
    const l = lay(); ringkas(); if (!l) return;
    const titip = l.dataset.titip === '1';
    document.getElementById('fKeluar').hidden = !titip; document.getElementById('fJam').hidden = titip; document.getElementById('infoKandang').hidden = !titip;
    document.getElementById('lblTgl').textContent = titip ? 'Tanggal titip' : 'Tanggal';
    const q = new URLSearchParams({ layanan: l.value, tanggal: f.tanggal.value, keluar: f.tanggal_keluar.value });
    slotEl.innerHTML = '<span class="muted kecil">Memuat jadwal...</span>';
    try {
      const r = await (await fetch('{{ route('booking.slot') }}?' + q, { headers: { Accept: 'application/json' } })).json();
      if (r.penitipan) { const ik = document.getElementById('infoKandang'); ik.textContent = r.kandang > 0 ? r.kandang + ' kandang masih kosong untuk tanggal itu.' : 'Kandang penuh untuk tanggal itu. Coba tanggal lain.'; ik.className = 'alert ' + (r.kandang > 0 ? 'ok' : 'err'); }
      else slotEl.innerHTML = r.slot.map(s => { const off = s.lewat || s.sisa < 1; return `<label class="${off ? 'penuh' : ''}"><input type="radio" name="jam" value="${s.jam}" ${off ? 'disabled' : ''} ${s.jam === jamLama && !off ? 'checked' : ''}>${s.jam}<small>${s.lewat ? 'lewat' : s.sisa < 1 ? 'penuh' : 'sisa ' + s.sisa}</small></label>`; }).join('') || '<span class="muted kecil">Tidak ada jam tersedia.</span>';
    } catch (e) { slotEl.innerHTML = '<span class="galat">Gagal memuat jadwal. Muat ulang halaman.</span>'; }
    ringkas();
  }
  function ringkas() {
    const l = lay(), titip = l && l.dataset.titip === '1', jam = f.querySelector('input[name=jam]:checked');
    const malam = titip ? Math.max(1, Math.round((new Date(f.tanggal_keluar.value) - new Date(f.tanggal.value)) / 864e5)) : 1;
    document.getElementById('rLay').textContent = l ? l.dataset.nama : '-';
    const jh = f.querySelector('input[name=jenis_hewan]:checked');
    document.getElementById('rHew').textContent = (f.nama_hewan.value || '-') + (jh ? ' (' + jh.value + ')' : '');
    document.getElementById('rJad').textContent = titip ? hari(f.tanggal.value) + ' - ' + hari(f.tanggal_keluar.value) + ' (' + malam + ' malam)' : hari(f.tanggal.value) + (jam ? ', ' + jam.value : '');
    document.getElementById('rTot').textContent = l ? rp(l.dataset.harga * (malam > 0 ? malam : 1)) : 'Rp0';
  }
  f.addEventListener('change', e => { if (['layanan_id', 'tanggal', 'tanggal_keluar'].includes(e.target.name)) muat(); else ringkas(); });
  f.addEventListener('input', ringkas);
  muat();
})();
</script>
@endpush
