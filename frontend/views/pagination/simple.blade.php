@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span>&laquo; Anterior</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">&laquo; Anterior</a>
    @endif

    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
        @if ($page == $paginator->currentPage())
            <span class="active">{{ $page }}</span>
        @else
            <a href="{{ $url }}">{{ $page }}</a>
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">Siguiente &raquo;</a>
    @else
        <span>Siguiente &raquo;</span>
    @endif
@endif
