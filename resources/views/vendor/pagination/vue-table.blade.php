@if ($paginator->total() > 0)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $maxVisiblePages = 5;
        $halfWindow = intdiv($maxVisiblePages, 2);

        $startPage = max(1, $currentPage - $halfWindow);
        $endPage = min($lastPage, $startPage + $maxVisiblePages - 1);

        if (($endPage - $startPage + 1) < $maxVisiblePages) {
            $startPage = max(1, $endPage - $maxVisiblePages + 1);
        }
    @endphp

    <div class="dataTables_wrapper container-fluid dt-bootstrap4">
        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" role="status" aria-live="polite">
                    Affichage de {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} sur {{ $paginator->total() }} entrees
                </div>
            </div>

            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination">
                        <li class="paginate_button page-item previous {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <a href="{{ $currentPage <= 1 ? '#' : $paginator->previousPageUrl() }}" class="page-link">Precedent</a>
                        </li>

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            <li class="paginate_button page-item {{ $page === $currentPage ? 'active' : '' }}">
                                <a href="{{ $paginator->url($page) }}" class="page-link">{{ $page }}</a>
                            </li>
                        @endfor

                        <li class="paginate_button page-item next {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                            <a href="{{ $currentPage >= $lastPage ? '#' : $paginator->nextPageUrl() }}" class="page-link">Suivant</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
