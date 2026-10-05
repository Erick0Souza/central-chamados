<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Central</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="{{ asset('js/app.js') }}"></script>
</head>

<body class="auth-body">
    <main class="auth-layout">
        <section class="auth-card">
            <a class="brand auth-brand" href="{{ route('login') }}">Central</a>
            <div class="auth-inner">
                @yield('content')
            </div>
        </section>
    </main>
</body>

</html>
