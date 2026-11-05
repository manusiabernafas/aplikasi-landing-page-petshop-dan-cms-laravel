<span class="ubin {{ $kelas ?? '' }}" style="background:{{ $p->warna() }}">@if ($p->fotoUrl())<img src="{{ $p->fotoUrl() }}" alt="" loading="lazy">@else{!! ikon($p->ikon()) !!}@endif</span>
