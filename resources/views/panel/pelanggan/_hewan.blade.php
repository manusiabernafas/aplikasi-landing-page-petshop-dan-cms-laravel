<div class="grid g2" style="gap:0 10px">
  <div class="f"><label>Nama</label><input name="nama" value="{{ $h->nama }}" required maxlength="50"></div>
  <div class="f"><label>Jenis</label><select name="jenis">@foreach (\App\Models\Hewan::JENIS as $j)<option @selected($h->jenis === $j)>{{ $j }}</option>@endforeach</select></div>
  <div class="f"><label>Ras</label><input name="ras" value="{{ $h->ras }}" maxlength="50"></div>
  <div class="f"><label>Kelamin</label><select name="kelamin"><option value="">-</option><option @selected($h->kelamin === 'Jantan')>Jantan</option><option @selected($h->kelamin === 'Betina')>Betina</option></select></div>
  <div class="f"><label>Tgl lahir</label><input type="date" name="tanggal_lahir" value="{{ $h->tanggal_lahir?->toDateString() }}" max="{{ now()->toDateString() }}"></div>
  <div class="f"><label>Berat (kg)</label><input type="number" step="0.1" min="0" name="berat" value="{{ $h->berat }}"></div>
  <div class="f"><label>Warna</label><input name="warna" value="{{ $h->warna }}" maxlength="40"></div>
  <div class="f"><label>Catatan</label><input name="catatan" value="{{ $h->catatan }}" maxlength="300" placeholder="Alergi, sifat"></div>
</div>
