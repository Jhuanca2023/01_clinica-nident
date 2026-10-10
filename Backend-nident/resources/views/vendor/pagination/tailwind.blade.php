@if ($paginator->hasPages())
<style>
.lv-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 24px 0 8px;
    font-family: 'Montserrat', sans-serif;
    list-style: none;
    margin: 0;
}
.lv-pagination a,
.lv-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #444;
    background: #fff;
    text-decoration: none;
    letter-spacing: .5px;
    transition: none;
    transform: none;
    animation: none;
    cursor: pointer;
    line-height: 1;
    scroll-margin: 0;
}
.lv-pagination a:hover { background: #f5f5f5; border-color: #8C6B2E; color: #8C6B2E; }
.lv-pagination .lv-page-active span {
    background: #8C6B2E;
    border-color: #8C6B2E;
    color: #fff;
    cursor: default;
}
.lv-pagination .lv-page-disabled span {
    color: #ccc;
    border-color: #eee;
    cursor: default;
    background: #fafafa;
}
.lv-pagination .lv-page-dots span {
    border: none;
    background: transparent;
    color: #999;
    min-width: 20px;
    padding: 0 4px;
}
</style>
<nav role="navigation" aria-label="Paginación">
    <ul class="lv-pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="lv-page-disabled"><span>&lsaquo;</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">&lsaquo;</a></li>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="lv-page-dots"><span>{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="lv-page-active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}" aria-label="Página {{ $page }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">&rsaquo;</a></li>
        @else
            <li class="lv-page-disabled"><span>&rsaquo;</span></li>
        @endif

    </ul>
</nav>
@endif
