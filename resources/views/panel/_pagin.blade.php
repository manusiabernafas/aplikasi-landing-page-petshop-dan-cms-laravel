@if ($paginator->hasPages())
<nav class="pagin" aria-label="Halaman">
  @if ($paginator->onFirstPage())<span class="off">{!! ikon('kiri') !!}</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">{!! ikon('kiri') !!}</a>@endif
  @foreach ($elements as $el)
    @if (is_string($el))<span class="off">{{ $el }}</span>@endif
    @if (is_array($el))@foreach ($el as $p => $url)@if ($p == $paginator->currentPage())<span class="on">{{ $p }}</span>@else<a href="{{ $url }}">{{ $p }}</a>@endif @endforeach @endif
  @endforeach
  @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">{!! ikon('kanan') !!}</a>@else<span class="off">{!! ikon('kanan') !!}</span>@endif
</nav>
@endif
