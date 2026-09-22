@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2" role="navigation" aria-label="Navigasi Halaman">
        {{-- Tampilan Mobile (Layar Kecil) --}}
        <div class="d-flex justify-content-between align-items-center w-100 d-sm-none">
            <span class="small text-muted">
                Hal. <strong>{{ $paginator->currentPage() }}</strong> dari {{ $paginator->lastPage() }}
            </span>
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link py-1 px-2">&laquo; Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link py-1 px-2" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link py-1 px-2" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link py-1 px-2">Next &raquo;</span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Tampilan Desktop / Tablet --}}
        <div class="d-none d-sm-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
            <div>
                <p class="small text-muted mb-0">
                    Menampilkan <span class="fw-semibold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> sampai <span class="fw-semibold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> dari total <span class="fw-semibold text-dark">{{ $paginator->total() }}</span> data
                </p>
            </div>

            <div>
                <ul class="pagination pagination-sm mb-0 shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link fw-semibold">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
