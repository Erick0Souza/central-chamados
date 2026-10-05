<div class="ticket-list">
    <div class="table-heading">
        <span>Chamado</span>
        <span>Status</span>
        <span>Responsável</span>
        <span>Atualizado</span>
    </div>

    @forelse($items as $ticket)
        <a href="{{ route('tickets.show', $ticket) }}" class="ticket-row">
            <div class="ticket-subject">
                <span class="ticket-code">{{ $ticket->code() }} · {{ $ticket->category->name }}</span>
                <strong>{{ $ticket->title }}</strong>
            </div>

            <div class="ticket-state">
                <span class="badge status-{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
                <span class="priority priority-{{ $ticket->priority }}">{{ $ticket->priorityLabel() }}</span>
            </div>

            <div class="ticket-owner">
                <span>{{ $ticket->assignee?->name ?? 'Não atribuído' }}</span>
            </div>

            <time datetime="{{ $ticket->updated_at->toIso8601String() }}" class="ticket-time">
                {{ $ticket->updated_at->diffForHumans() }}
            </time>
        </a>
    @empty
        <div class="empty">
            <h3>Nenhum chamado encontrado.</h3>
            <a href="{{ route('tickets.create') }}" class="button secondary">Novo chamado</a>
        </div>
    @endforelse
</div>
