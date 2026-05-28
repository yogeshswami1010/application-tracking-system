@if ($paginator->hasPages())
    <ul class="flex items-center justify-center space-x-1" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span class="px-3 py-2 text-gray-400 cursor-not-allowed" aria-hidden="true">&lsaquo;</span>
            </li>
        @else
            <li>
                <a class="px-3 py-2 text-gray-700 hover:bg-gray-100 rounded" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled" aria-disabled="true"><span class="px-3 py-2 text-gray-400 cursor-not-allowed">{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active" aria-current="page"><span class="px-3 py-2 bg-blue-600 text-white rounded">{{ $page }}</span></li>
                    @else
                        <li><a class="px-3 py-2 text-gray-700 hover:bg-gray-100 rounded" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li>
                <a class="px-3 py-2 text-gray-700 hover:bg-gray-100 rounded" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
            </li>
        @else
            <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span class="px-3 py-2 text-gray-400 cursor-not-allowed" aria-hidden="true">&rsaquo;</span>
            </li>
        @endif
    </ul>
@endif
