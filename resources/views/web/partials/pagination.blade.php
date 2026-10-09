@if ($paginator->hasPages())
    <style>
    .custom-web-pagination {
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      margin-top: 28px !important;
      margin-bottom: 20px !important;
      width: 100% !important;
    }
    .custom-web-pagination .pagination-list {
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      list-style: none !important;
      padding: 0 !important;
      margin: 0 !important;
      flex-wrap: wrap !important;
    }
    .custom-web-pagination li a,
    .custom-web-pagination li span {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      min-width: 38px !important;
      height: 38px !important;
      padding: 0 12px !important;
      font-size: 13px !important;
      font-weight: 600 !important;
      color: #071a41 !important;
      background-color: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 10px !important;
      text-decoration: none !important;
      transition: all 0.2s ease !important;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
    }
    .custom-web-pagination li a:hover {
      background-color: #071a41 !important;
      color: #ffffff !important;
      border-color: #071a41 !important;
    }
    .custom-web-pagination li.active span {
      background-color: #16a34a !important;
      color: #ffffff !important;
      border-color: #16a34a !important;
      box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3) !important;
    }
    .custom-web-pagination li.disabled span {
      color: #cbd5e1 !important;
      background-color: #f8fafc !important;
      border-color: #f1f5f9 !important;
      cursor: not-allowed !important;
    }
    </style>
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-web-pagination">
        <ul class="pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true">&lsaquo; Prev</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo; Prev</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">Next &rsaquo;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">Next &rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
