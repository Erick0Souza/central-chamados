@extends('layouts.app')
@section('title', 'Novo chamado')
@section('content')
    <a class="back-link" href="{{ route('tickets.index') }}">
        <x-icon name="back" />
        Voltar
    </a>

    <div class="page-heading">
        <div>
            <h1>Novo chamado</h1>
            <p>Informe o problema e envie a solicitação.</p>
        </div>
    </div>

    <section class="panel padded form-panel">
        <form method="post" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="form-stack">
            @csrf

            <label>
                Assunto <span class="required">*</span>
                <input name="title" value="{{ old('title') }}" minlength="5" maxlength="150" required
                    placeholder="Ex.: Não consigo acessar meu e-mail">
            </label>

            <div class="form-row">
                <label>
                    Categoria <span class="required">*</span>
                    <select name="category_id" required>
                        <option value="">Selecione</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Prioridade <span class="required">*</span>
                    <select name="priority" required>
                        @foreach (\App\Models\Ticket::PRIORITIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('priority', 'media') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label>
                Descrição <span class="required">*</span>
                <textarea name="description" rows="6" required minlength="15" maxlength="10000"
                    placeholder="Descreva o problema de forma objetiva.">{{ old('description') }}</textarea>
            </label>

            <label>
                Anexo <span class="muted">(opcional)</span>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,.txt">
                <small>JPG, PNG, WEBP, PDF ou TXT. Até 5 MB.</small>
            </label>

            <div class="form-actions">
                <button class="button">Abrir chamado</button>
            </div>
        </form>
    </section>
@endsection
