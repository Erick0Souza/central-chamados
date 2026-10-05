@extends('layouts.guest')
@section('title', 'Criar conta')
@section('content')
    <h1>Criar conta</h1>
    <p class="muted">Preencha seus dados para começar.</p>

    @if ($errors->any())
        <div class="notice error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('register') }}" class="form-stack">
        @csrf

        <label>
            Nome
            <input name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="100">
        </label>

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255">
        </label>

        <label>
            Senha
            <input type="password" name="password" autocomplete="new-password" required minlength="8">
        </label>

        <label>
            Confirme a senha
            <input type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
        </label>

        <button class="button full">Criar conta</button>
    </form>

    <p class="auth-switch">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
@endsection
