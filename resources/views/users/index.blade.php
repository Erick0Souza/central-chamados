@extends('layouts.app')
@section('title', 'Usuários')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Usuários</h1>
            <p>Gerencie as contas de acesso.</p>
        </div>
    </div>

    <div class="detail-grid users-grid">
        <section class="panel">
            <div class="panel-heading">
                <h2>{{ $users->total() }} usuário(s)</h2>
            </div>

            <div class="user-list">
                @foreach ($users as $user)
                    <div class="user-row">
                        <span class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->email }}</small>
                        </div>
                        <span class="badge role-badge">{{ $user->roleLabel() }}</span>

                        @if (! auth()->user()->is($user))
                            <form class="user-actions" action="{{ route('users.destroy', $user) }}" method="post"
                                onsubmit="return confirm('Remover este usuário?');">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">Excluir</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            {{ $users->links('partials.pagination') }}
        </section>

        <section class="panel padded">
            <h2>Adicionar usuário</h2>

            <form class="form-stack" action="{{ route('users.store') }}" method="post">
                @csrf

                <label>
                    Nome
                    <input name="name" value="{{ old('name') }}" required maxlength="100">
                </label>

                <label>
                    E-mail
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="255">
                </label>

                <label>
                    Perfil
                    <select name="role">
                        @foreach (['cliente' => 'Cliente', 'tecnico' => 'Técnico', 'admin' => 'Administrador'] as $key => $name)
                            <option value="{{ $key }}" @selected(old('role') === $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Senha inicial
                    <input type="password" name="password" autocomplete="new-password" required minlength="8">
                </label>

                <label>
                    Confirme a senha
                    <input type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
                </label>

                <button class="button full">Cadastrar</button>
            </form>
        </section>
    </div>
@endsection
