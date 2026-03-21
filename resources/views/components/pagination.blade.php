@if ($paginator->hasPages())
<div class="pagination">
       <div class="pagination__info">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
    <div class="pagination__controls">
        @if ($paginator->onFirstPage())
            <span class="pagination__link disabled">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination__link" rel="prev">Previous</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination__link disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination__link pagination__link--active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination__link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination__link" rel="next">Next</a>
        @else
            <span class="pagination__link disabled">Next</span>
        @endif
    </div>

 
</div>
@endif

