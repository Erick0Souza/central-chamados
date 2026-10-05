@extends('layouts.app')
@section('title', $ticket->code())
@section('content')
    <a class="back-link" href="{{ route('tickets.index') }}">
        <x-icon name="back" />
        Voltar
    </a>

    <div class="page-heading detail-heading">
        <div>
            <div class="title-meta">
                <span class="ticket-code">{{ $ticket->code() }}</span>
                <span class="badge status-{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
            </div>
            <h1>{{ $ticket->title }}</h1>
            <p>{{ $ticket->creator->name }} · {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="detail-grid">
        <div class="detail-main">
            <section class="panel padded">
                <h2>Descrição</h2>
                <p class="prose">{{ $ticket->description }}</p>

                @if ($ticket->attachments->isNotEmpty())
                    <div class="attachments">
                        @foreach ($ticket->attachments as $attachment)
                            <a href="{{ route('attachments.download', $attachment) }}">
                                <x-icon name="clip" />
                                <span>
                                    {{ $attachment->original_name }}
                                    <small>{{ number_format($attachment->size / 1024, 0, ',', '.') }} KB</small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel padded">
                <div class="section-title">
                    <h2>Comentários</h2>
                    <span class="count">{{ $comments->total() }}</span>
                </div>

                <div class="conversation">
                    @forelse($comments as $comment)
                        <article class="comment">
                            <span class="avatar {{ $comment->user->isStaff() ? 'staff' : '' }}">
                                {{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                            </span>
                            <div>
                                <div class="comment-head">
                                    <strong>{{ $comment->user->name }}</strong>
                                    <time>{{ $comment->created_at->format('d/m/Y H:i') }}</time>
                                </div>
                                <p class="prose">{{ $comment->body }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="muted conversation-empty">Nenhum comentário.</p>
                    @endforelse
                </div>

                {{ $comments->links('partials.pagination') }}

                @can('comment', $ticket)
                    <form class="form-stack comment-form" method="post" action="{{ route('tickets.comment', $ticket) }}">
                        @csrf
                        <label for="body">Novo comentário</label>
                        <textarea id="body" name="body" rows="4" minlength="2" maxlength="5000" required
                            placeholder="Escreva um comentário">{{ old('body') }}</textarea>
                        <div class="form-actions">
                            <button class="button">Enviar</button>
                        </div>
                    </form>
                @else
                    <div class="closed-note">Chamado encerrado.</div>
                @endcan
            </section>
        </div>

        <aside class="detail-aside">
            <section class="panel padded">
                <h2>Detalhes</h2>

                <dl class="metadata">
                    <dt>Prioridade</dt>
                    <dd><span class="priority priority-{{ $ticket->priority }}">{{ $ticket->priorityLabel() }}</span></dd>

                    <dt>Categoria</dt>
                    <dd>{{ $ticket->category->name }}</dd>

                    <dt>Responsável</dt>
                    <dd>{{ $ticket->assignee?->name ?? 'Não atribuído' }}</dd>

                    <dt>Atualizado</dt>
                    <dd>{{ $ticket->updated_at->format('d/m/Y H:i') }}</dd>
                </dl>

                @can('assign', $ticket)
                    <form class="form-stack compact" method="post" action="{{ route('tickets.assign', $ticket) }}">
                        @csrf
                        @method('PATCH')

                        <label>
                            Responsável
                            <select name="assigned_to" required>
                                <option value="">Selecione</option>
                                @foreach ($technicians as $technician)
                                    <option value="{{ $technician->id }}" @selected($ticket->assigned_to === $technician->id)>
                                        {{ $technician->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <button class="button secondary full">Salvar</button>
                    </form>
                @endcan

                @php($transitions = array_values(array_filter($ticket->transitionsFor(auth()->user()), fn ($status) => $status !== 'encerrado')))
                @if (count($transitions))
                    <form class="form-stack compact" action="{{ route('tickets.status', $ticket) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <label>
                            Status
                            <select name="status" required>
                                @foreach ($transitions as $status)
                                    <option value="{{ $status }}">{{ \App\Models\Ticket::STATUSES[$status] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <button class="button full">Atualizar</button>
                    </form>
                @endif

                @can('close', $ticket)
                    <form class="form-stack compact" action="{{ route('tickets.close', $ticket) }}" method="post"
                        onsubmit="return confirm('Encerrar este chamado?');">
                        @csrf
                        @method('PATCH')
                        <button class="button danger full" type="submit">Encerrar chamado</button>
                    </form>
                @endcan
            </section>

            <details class="panel padded history-panel">
                <summary>Histórico</summary>
                <ol class="timeline">
                    @foreach ($activities as $activity)
                        <li>
                            <p>{{ $activity->description }}</p>
                            <small>{{ $activity->created_at->format('d/m H:i') }}</small>
                        </li>
                    @endforeach
                </ol>
                {{ $activities->links('partials.pagination') }}
            </details>
        </aside>
    </div>
@endsection
