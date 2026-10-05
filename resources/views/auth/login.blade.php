@extends('layouts.guest')
@section('title', 'Entrar')
@section('content')
    <h1>Entrar</h1>
    <p class="muted">Acesse sua conta para continuar.</p>

    @if ($errors->any())
        <div class="notice error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ route('login') }}" class="form-stack">
        @csrf

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                placeholder="voce@empresa.com" required autofocus maxlength="255">
        </label>

        <label>
            Senha
            <div class="password-wrap">
                <input type="password" name="password" id="password" autocomplete="current-password" required>
                <button type="button" data-password="password" aria-label="Mostrar senha">Mostrar</button>
            </div>
        </label>

        <label class="check-label">
            <input type="checkbox" name="remember" value="1">
            Manter conectado
        </label>

        <button class="button full" type="submit">Entrar</button>
    </form>

    <p class="auth-switch">Não tem conta? <a href="{{ route('register') }}">Criar conta</a></p>
@endsection
