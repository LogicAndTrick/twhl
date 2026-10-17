@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        
        {{-- First Page Link --}}
        @if (!$paginator->onFirstPage())
            <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
        @endif

        {{-- Second Page Link --}}
        @if ($paginator->currentPage() > 2)
            <li class="page-item"><a class="page-link" href="{{ $paginator->url(2) }}">2</a></li>
        @endif
        
        {{-- Separator OR page 3 if we're on exactly page 5 --}}
        @if ($paginator->currentPage() > 5)
            <li class="page-item disabled"><span class="page-link">...</span></li>
        @elseif ($paginator->currentPage() == 5)
            <li class="page-item"><a class="page-link" href="{{ $paginator->url(3) }}">3</a></li>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->currentPage() > 3)
            <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->currentPage() - 1) }}" rel="prev">{{ $paginator->currentPage() - 1 }}</a></li>
        @endif

        {{-- Current Page Link --}}
        <li class="page-item active"><span class="page-link">{{ $paginator->currentPage() }}</span></li>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages() && $paginator->currentPage() < \App\Http\Middleware\LimitGuestPagination::getMaxPage())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $paginator->currentPage() + 1 }}</a></li>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        @endif
    </ul>
@endif
