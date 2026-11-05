@extends('panel', ['judul' => 'Kasir', 'sub' => 'Klik produk atau layanan untuk memasukkan ke keranjang'])
@php
  $dataP = $produk->map(fn ($p) => ['t' => 'produk', 'id' => $p->id, 'nama' => $p->nama, 'kat' => $p->kategori, 'harga' => $p->harga_jual, 'stok' => $p->stok, 'satuan' => $p->satuan, 'sku' => $p->sku, 'warna' => $p->warna(), 'ikon' => ikon($p->ikon()), 'foto' => $p->fotoUrl()]);
  $ikL = ['grooming' => 'gunting', 'penitipan' => 'hotel', 'klinik' => 'stetoskop']; $wL = ['grooming' => '#ffb38a', 'penitipan' => '#9ee0c8', 'klinik' => '#ff8fb1'];
  $dataL = $layanan->map(fn ($l) => ['t' => 'layanan', 'id' => $l->id, 'nama' => $l->nama, 'kat' => \App\Models\Layanan::KATEGORI[$l->kategori], 'harga' => $l->harga, 'stok' => null, 'satuan' => $l->satuan, 'sku' => '', 'warna' => $wL[$l->kategori], 'ikon' => ikon($ikL[$l->kategori]), 'foto' => null]);
  $awal = $booking ? [['t' => 'layanan', 'id' => $booking->layanan_id, 'qty' => $booking->layanan->penitipan() ? $booking->malam() : 1, 'booking' => $booking->id, 'label' => $booking->nama_hewan . ' (' . $booking->kode . ')']] : [];
@endphp
@section('isi')
@if ($tunggu->count())
  <div class="tunggu">@foreach ($tunggu as $b)<a href="{{ route('panel.kasir', ['booking' => $b->id]) }}"><b>{{ $b->nama_hewan }} · {{ $b->kode }}</b>{{ $b->layanan->nama }} · {{ rp($b->biaya()) }}</a>@endforeach</div>
@endif
<div class="pos">
  <div class="katalog">
    <div class="filter" style="margin-bottom:10px"><input class="inp" id="cari" placeholder="Cari nama / SKU produk..." style="flex:1" autofocus></div>
    <div class="tab" id="tab"></div>
    <div class="kat-grid" id="grid"></div>
  </div>
  <div class="keranjang">
    <div style="display:flex;justify-content:space-between;align-items:center"><h2>Keranjang</h2><button class="btn putih kecil" type="button" id="kosongkan">Kosongkan</button></div>
    <div style="position:relative"><input class="inp" id="pel" placeholder="Pelanggan (opsional): cari nama / HP" autocomplete="off"><div class="saran" id="saran" hidden></div></div>
    <div id="pelTerpilih" hidden style="display:flex;justify-content:space-between;align-items:center;background:#232a5c;border-radius:12px;padding:8px 12px"></div>
    <div id="isi"></div>
    <div class="k-tot" style="font-size:14px"><span>Subtotal</span><span id="sub">Rp0</span></div>
    <div class="k-tot" style="font-size:14px;align-items:center"><span>Diskon (Rp)</span><input class="inp" id="diskon" type="number" min="0" value="0" style="width:130px;min-height:36px;text-align:right"></div>
    <div class="k-tot"><span>Total</span><b id="tot">Rp0</b></div>
    <div class="metode">@foreach (\App\Models\Transaksi::METODE as $k => $v)<label><input type="radio" name="metode" value="{{ $k }}" @checked($k === 'tunai')>{{ $v }}</label>@endforeach</div>
    <div id="tunai"><input class="inp" id="bayar" type="number" min="0" placeholder="Uang diterima"><div class="uang" id="uang" style="margin-top:6px"></div><div class="k-tot" style="font-size:14px;margin-top:8px"><span>Kembalian</span><b id="kembali" style="font-size:20px">Rp0</b></div></div>
    <input class="inp" id="catatan" placeholder="Catatan (opsional)" maxlength="200">
    <button class="btn peach" id="simpan" style="min-height:52px;font-size:16px">{!! ikon('cek') !!} Bayar & cetak struk</button>
    <div id="pesan"></div>
  </div>
</div>
@endsection
@push('skrip')
<script>
(() => {
  const DATA = @json($dataP->concat($dataL)->values()), CARI = '{{ route('panel.pelanggan.cari') }}', SIMPAN = '{{ route('panel.kasir.simpan') }}', TOKEN = document.querySelector('meta[name=csrf-token]').content;
  const $ = s => document.querySelector(s), rp = n => 'Rp' + Math.round(n || 0).toLocaleString('id-ID'), esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
  let K = @json($awal), PEL = @json($booking ? ['id' => $booking->pelanggan_id, 'nama' => $booking->pelanggan->nama, 'telepon' => $booking->pelanggan->telepon] : null), TAB = 'Semua';
  const cari = (t, id) => DATA.find(d => d.t === t && d.id === id);
  const tabs = ['Semua', ...new Set(DATA.map(d => d.kat))];
  function renderTab() { $('#tab').innerHTML = tabs.map(t => `<button type="button" class="${t === TAB ? 'on' : ''}">${esc(t)}</button>`).join(''); }
  $('#tab').onclick = e => { const b = e.target.closest('button'); if (!b) return; TAB = b.textContent; renderTab(); renderGrid(); };
  function renderGrid() {
    const q = $('#cari').value.toLowerCase();
    const L = DATA.filter(d => (TAB === 'Semua' || d.kat === TAB) && (!q || (d.nama + ' ' + d.sku).toLowerCase().includes(q)));
    $('#grid').innerHTML = L.map(d => { const di = K.filter(k => k.t === d.t && k.id === d.id).reduce((a, k) => a + k.qty, 0), sisa = d.stok === null ? null : d.stok - di;
      return `<button type="button" class="item ${sisa !== null && sisa <= 0 ? 'habis' : ''}" data-t="${d.t}" data-id="${d.id}"><span class="ubin" style="background:${d.warna}">${d.foto ? `<img src="${esc(d.foto)}" alt="">` : d.ikon}</span><span class="nm">${esc(d.nama)}</span><span class="hg">${rp(d.harga)}</span><span class="stk">${d.stok === null ? 'per ' + esc(d.satuan) : 'Stok ' + sisa + ' ' + esc(d.satuan)}</span></button>`; }).join('') || '<p class="kosong" style="grid-column:1/-1">Tidak ditemukan.</p>';
  }
  $('#cari').oninput = renderGrid;
  $('#cari').onkeydown = e => { if (e.key === 'Enter') { const b = $('#grid .item:not(.habis)'); if (b) { b.click(); e.target.select(); } } };
  $('#grid').onclick = e => { const b = e.target.closest('.item'); if (!b) return; tambah(b.dataset.t, +b.dataset.id, 1); };
  function tambah(t, id, n, booking) {
    const d = cari(t, id), k = K.find(x => x.t === t && x.id === id && !x.booking);
    const total = K.filter(x => x.t === t && x.id === id).reduce((a, x) => a + x.qty, 0);
    if (d.stok !== null && total + n > d.stok) return info('Stok ' + d.nama + ' tinggal ' + d.stok + '.', 'err');
    if (k) { k.qty += n; if (k.qty <= 0) K.splice(K.indexOf(k), 1); } else if (n > 0) K.push({ t, id, qty: n });
    render();
  }
  function render() {
    $('#isi').innerHTML = K.length ? K.map((k, i) => { const d = cari(k.t, k.id); return `<div class="k-item"><span><b>${esc(d.nama)}</b>${k.label ? `<br><small>${esc(k.label)}</small>` : ''}<br><small>${rp(d.harga)} / ${esc(d.satuan)}</small></span><b>${rp(d.harga * k.qty)}</b><span class="qty"><button type="button" data-i="${i}" data-d="-1">&minus;</button><span>${k.qty}</span><button type="button" data-i="${i}" data-d="1">+</button></span></div>`; }).join('') : '<p style="color:#a9aed0;text-align:center;margin:14px 0">Belum ada item.</p>';
    const sub = K.reduce((a, k) => a + cari(k.t, k.id).harga * k.qty, 0), dis = Math.min(sub, +$('#diskon').value || 0), tot = sub - dis;
    $('#sub').textContent = rp(sub); $('#tot').textContent = rp(tot);
    const tunai = document.querySelector('input[name=metode]:checked').value === 'tunai';
    $('#tunai').hidden = !tunai;
    const pas = [tot, Math.ceil(tot / 10000) * 10000, Math.ceil(tot / 50000) * 50000, Math.ceil(tot / 100000) * 100000].filter((v, i, a) => v > 0 && a.indexOf(v) === i).slice(0, 4);
    $('#uang').innerHTML = pas.map(v => `<button type="button" data-v="${v}">${v === tot ? 'Uang pas' : rp(v).replace('Rp', '')}</button>`).join('');
    $('#kembali').textContent = rp(Math.max(0, (+$('#bayar').value || 0) - tot));
    renderGrid();
  }
  $('#isi').onclick = e => { const b = e.target.closest('[data-i]'); if (!b) return; const k = K[+b.dataset.i], d = cari(k.t, k.id);
    if (+b.dataset.d > 0 && d.stok !== null && K.filter(x => x.t === k.t && x.id === k.id).reduce((a, x) => a + x.qty, 0) >= d.stok) return info('Stok habis.', 'err');
    k.qty += +b.dataset.d; if (k.qty <= 0) K.splice(+b.dataset.i, 1); render(); };
  $('#uang').onclick = e => { const b = e.target.closest('[data-v]'); if (b) { $('#bayar').value = b.dataset.v; render(); } };
  ['#diskon', '#bayar'].forEach(s => $(s).oninput = render);
  document.querySelectorAll('input[name=metode]').forEach(r => r.onchange = render);
  $('#kosongkan').onclick = () => { K = []; PEL = null; pel(); render(); };
  // pelanggan
  let tm;
  $('#pel').oninput = e => { clearTimeout(tm); const q = e.target.value.trim(); if (q.length < 2) { $('#saran').hidden = true; return; }
    tm = setTimeout(async () => { const r = await (await fetch(CARI + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } })).json();
      $('#saran').innerHTML = r.map(p => `<button type="button" data-p='${esc(JSON.stringify({ id: p.id, nama: p.nama, telepon: p.telepon }))}'><b>${esc(p.nama)}</b> <span class="muted kecil">${esc(p.telepon)} · ${p.hewan.map(h => esc(h.nama)).join(', ')}</span></button>`).join('') || '<button type="button" disabled>Tidak ditemukan</button>';
      $('#saran').hidden = false; }, 220); };
  $('#saran').onclick = e => { const b = e.target.closest('[data-p]'); if (!b) return; PEL = JSON.parse(b.dataset.p); $('#saran').hidden = true; $('#pel').value = ''; pel(); };
  function pel() { const el = $('#pelTerpilih'); el.hidden = !PEL; $('#pel').hidden = !!PEL; if (PEL) el.innerHTML = `<span><b>${esc(PEL.nama)}</b><br><small style="color:#a9aed0">${esc(PEL.telepon)}</small></span><button type="button" class="btn putih kecil" id="hapusPel">Ganti</button>`;
    const h = $('#hapusPel'); if (h) h.onclick = () => { PEL = null; pel(); }; }
  function info(t, j) { $('#pesan').innerHTML = `<div class="alert ${j}" style="margin:0">${t}</div>`; }
  $('#simpan').onclick = async () => {
    if (!K.length) return info('Keranjang masih kosong.', 'err');
    const b = $('#simpan'); b.disabled = true;
    const body = { item: K.map(k => ({ tipe: k.t, id: k.id, qty: k.qty, booking: k.booking || null })), pelanggan_id: PEL ? PEL.id : null, diskon: +$('#diskon').value || 0,
      metode: document.querySelector('input[name=metode]:checked').value, bayar: +$('#bayar').value || 0, catatan: $('#catatan').value };
    try {
      const r = await fetch(SIMPAN, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': TOKEN }, body: JSON.stringify(body) });
      const j = await r.json();
      if (!r.ok) throw new Error(j.errors ? Object.values(j.errors)[0][0] : (j.message || 'Gagal menyimpan.'));
      K.forEach(k => { const d = cari(k.t, k.id); if (d.stok !== null) d.stok -= k.qty; });
      K = []; PEL = null; pel(); $('#bayar').value = ''; $('#diskon').value = 0; $('#catatan').value = ''; render();
      info(`Tersimpan <b>${esc(j.nomor)}</b> · total ${rp(j.total)} · kembali ${rp(j.kembali)} <a class="btn putih kecil" href="${esc(j.struk)}" target="_blank">Cetak struk</a>`, 'ok');
      if (location.search.includes('booking=')) history.replaceState(null, '', location.pathname);
    } catch (e) { info(esc(e.message), 'err'); }
    b.disabled = false;
  };
  renderTab(); pel(); render();
})();
</script>
@endpush
