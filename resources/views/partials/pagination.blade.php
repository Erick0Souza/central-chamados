@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginação">
        <span>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
        <div>
            @if ($paginator->onFirstPage())
            <span class="button secondary disabled">Anterior</span>@else<a class="button secondary"
                    href="{{ $paginator->previousPageUrl() }}">Anterior</a>
                @endif @if ($paginator->hasMorePages())
                <a class="button secondary" href="{{ $paginator->nextPageUrl() }}">Próxima</a>@else<span
                        class="button secondary disabled">Próxima</span>
                @endif
        </div>
    </nav>
@endif
