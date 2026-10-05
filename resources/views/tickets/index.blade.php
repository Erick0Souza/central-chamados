@extends('layouts.app')
@section('title', 'Chamados')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Chamados</h1>
            <p>Consulte e filtre as solicitações.</p>
        </div>

        <a href="{{ route('tickets.create') }}" class="button">
            <x-icon name="plus" />
            Novo chamado
        </a>
    </div>

    <section class="panel">
        <form class="filters" method="get" action="{{ route('tickets.index') }}">
            <label class="search-field">
                Buscar
                <input name="q" value="{{ request('q') }}" maxlength="150" placeholder="Título ou CH-0001">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Ticket::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Prioridade
                <select name="priority">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Ticket::PRIORITIES as $key => $label)
                        <option value="{{ $key }}" @selected(request('priority') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Categoria
                <select name="category_id">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button class="button secondary">Filtrar</button>
            <a class="clear-filter" href="{{ route('tickets.index') }}">Limpar</a>
        </form>

        <div class="list-summary">
            <strong>{{ $tickets->total() }} chamado(s)</strong>
        </div>

        @include('partials.tickets', ['items' => $tickets])
        {{ $tickets->links('partials.pagination') }}
    </section>
@endsection
