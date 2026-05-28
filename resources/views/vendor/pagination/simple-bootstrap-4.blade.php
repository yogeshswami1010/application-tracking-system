@if ($paginator->hasPages())
    <ul class="flex items-center justify-center space-x-1" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true">
                <span class="px-3 py-2 text-gray-400 cursor-not-allowed">@lang('pagination.previous')</span>
            </li>
        @else
            <li>
                <a class="px-3 py-2 text-gray-700 hover:bg-gray-100 rounded" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
            </li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li>
                <a class="px-3 py-2 text-gray-700 hover:bg-gray-100 rounded" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
            </li>
        @else
            <li class="disabled" aria-disabled="true">
                <span class="px-3 py-2 text-gray-400 cursor-not-allowed">@lang('pagination.next')</span>
            </li>
        @endif
    </ul>
@endif
