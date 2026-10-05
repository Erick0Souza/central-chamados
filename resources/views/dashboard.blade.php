@extends('layouts.app')
@section('title', 'Início')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Olá, {{ explode(' ', auth()->user()->name)[0] }}</h1>
            <p>Acompanhe seus chamados.</p>
        </div>

        <a href="{{ route('tickets.create') }}" class="button">
            <x-icon name="plus" />
            Novo chamado
        </a>
    </div>

    <div class="stats-grid">
        @foreach (\App\Models\Ticket::STATUSES as $key => $label)
            <a class="stat-card" href="{{ route('tickets.index', ['status' => $key]) }}">
                <span>{{ $label }}</span>
                <strong>{{ $counts[$key] ?? 0 }}</strong>
            </a>
        @endforeach
    </div>

    <section class="panel">
        <div class="panel-heading">
            <h2>Chamados recentes</h2>
            <a class="text-link" href="{{ route('tickets.index') }}">Ver todos</a>
        </div>

        @include('partials.tickets', ['items' => $recent])
    </section>
@endsection
