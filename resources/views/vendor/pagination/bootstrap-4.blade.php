@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
                </li>
            @endif

            @php
                $start = max($paginator->currentPage() - 2, 1);
                $end = min($paginator->currentPage() + 2, $paginator->lastPage());
            @endphp

            {{-- First Page --}}
            @if ($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>

                @if ($start > 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif

            {{-- Middle Pages --}}
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $paginator->currentPage())
                    <li class="page-item active">
                        <span class="page-link">{{ $i }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last Page --}}
            @if ($end < $paginator->lastPage())
                @if ($end < $paginator->lastPage() - 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif

                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">
                        {{ $paginator->lastPage() }}
                    </a>
                </li>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">&rsaquo;</span>
                </li>
            @endif

        </ul>
    </nav>
@endif