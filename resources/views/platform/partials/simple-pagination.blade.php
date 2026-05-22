@if($paginator->hasPages())
    <nav class="pagination-simple">
        @if($paginator->onFirstPage())
            <span class="page-btn disabled">上一页</span>
        @else
            <a class="page-btn" href="{{ $paginator->previousPageUrl() }}">上一页</a>
        @endif

        <span class="small muted">第 {{ $paginator->currentPage() }} 页 / 共 {{ $paginator->lastPage() }} 页</span>

        @if($paginator->hasMorePages())
            <a class="page-btn" href="{{ $paginator->nextPageUrl() }}">下一页</a>
        @else
            <span class="page-btn disabled">下一页</span>
        @endif
    </nav>
@endif
