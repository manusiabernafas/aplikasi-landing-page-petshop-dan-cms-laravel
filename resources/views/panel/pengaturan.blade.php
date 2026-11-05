@extends('panel', ['judul' => 'Pengaturan toko'])
@section('isi')
<form method="post" enctype="multipart/form-data" action="{{ route('panel.pengaturan.simpan') }}" class="grid g2 f1">
  @csrf
  <div class="kartu">
    <h2 style="margin-bottom:12px">Identitas</h2>
    <div class="f"><label>Nama toko</label><input name="nama" value="{{ old('nama', $k['nama']) }}" required maxlength="60"></div>
    <div class="f"><label>Tagline</label><input name="tagline" value="{{ old('tagline', $k['tagline']) }}" maxlength="100"></div>
    <div class="f"><label>Alamat</label><input name="alamat" value="{{ old('alamat', $k['alamat']) }}" required maxlength="200"></div>
    <div class="grid g2" style="gap:0 12px"><div class="f"><label>WhatsApp</label><input name="whatsapp" value="{{ old('whatsapp', $k['whatsapp']) }}" required>@error('whatsapp')<div class="galat">{{ $message }}</div>@enderror</div><div class="f"><label>Telepon</label><input name="telepon" value="{{ old('telepon', $k['telepon']) }}"></div></div>
    <div class="f"><label>Email</label><input type="email" name="email" value="{{ old('email', $k['email']) }}"></div>
    <div class="f"><label>Jam buka</label><textarea name="jam_buka" required maxlength="200">{{ old('jam_buka', $k['jam_buka']) }}</textarea></div>
    <div class="f"><label>Logo (opsional)</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp"><small>Persegi, maks. 1 MB.</small>@error('logo')<div class="galat">{{ $message }}</div>@enderror</div>
  </div>
  <div class="kartu" style="align-self:start">
    <h2 style="margin-bottom:12px">Jadwal & kasir</h2>
    <div class="f"><label>Jam slot grooming & klinik</label><input name="jam_grooming" value="{{ old('jam_grooming', $k['jam_grooming']) }}" required><small>Pisahkan dengan koma, format 24 jam. Contoh 09:00,10:00,13:00</small>@error('jam_grooming')<div class="galat">{{ $message }}</div>@enderror</div>
    <div class="grid g2" style="gap:0 12px">
      <div class="f"><label>Kapasitas per jam</label><input type="number" name="kapasitas_slot" min="1" max="20" value="{{ old('kapasitas_slot', $k['kapasitas_slot']) }}" required><small>Jumlah hewan per jam per kategori (jumlah groomer).</small></div>
      <div class="f"><label>Jumlah kandang</label><input type="number" name="jumlah_kandang" min="1" max="200" value="{{ old('jumlah_kandang', $k['jumlah_kandang']) }}" required></div>
    </div>
    <div class="f"><label>Booking maksimal (hari ke depan)</label><input type="number" name="maks_hari_booking" min="1" max="120" value="{{ old('maks_hari_booking', $k['maks_hari_booking']) }}" required></div>
    <div class="f"><label>Catatan di struk</label><textarea name="catatan_struk" maxlength="200">{{ old('catatan_struk', $k['catatan_struk']) }}</textarea></div>
    <button class="btn">{!! ikon('cek') !!} Simpan pengaturan</button>
  </div>
</form>
@endsection
