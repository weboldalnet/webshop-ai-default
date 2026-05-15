@if($products->hasPages())
    <nav aria-label="Page navigation" class="ws-pagination d-flex justify-content-center">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if($products->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item"><a class="page-link js-pagination-link" href="{{ $products->previousPageUrl() }}" data-page="{{ $products->currentPage() - 1 }}" rel="prev">&laquo;</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                <li class="page-item {{ $page == $products->currentPage() ? 'active' : '' }}">
                    <a class="page-link js-pagination-link" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
                </li>
            @endforeach

            {{-- Next Page Link --}}
            @if($products->hasMorePages())
                <li class="page-item"><a class="page-link js-pagination-link" href="{{ $products->nextPageUrl() }}" data-page="{{ $products->currentPage() + 1 }}" rel="next">&raquo;</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
