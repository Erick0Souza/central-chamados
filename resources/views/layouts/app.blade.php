<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173b31">
    <title>@yield('title', 'Painel') · Central</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="{{ asset('js/app.js') }}"></script>
</head>

<body>
    <a class="skip" href="#conteudo">Pular para conteúdo</a>

    <div class="shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">Central</a>

            <nav aria-label="Navegação principal">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}"
                    @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                    <x-icon name="grid" />
                    Início
                </a>

                <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}"
                    href="{{ route('tickets.index') }}"
                    @if (request()->routeIs('tickets.*')) aria-current="page" @endif>
                    <x-icon name="ticket" />
                    Chamados
                </a>

                @if (auth()->user()->isAdmin())
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}"
                        @if (request()->routeIs('users.*')) aria-current="page" @endif>
                        <x-icon name="users" />
                        Usuários
                    </a>
                @endif
            </nav>

            <div class="profile">
                <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->roleLabel() }}</small>
                </div>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="icon-button" aria-label="Sair da conta" title="Sair">
                        <x-icon name="logout" />
                    </button>
                </form>
            </div>
        </aside>

        <button class="scrim" type="button" data-close-menu aria-label="Fechar menu"></button>

        <div class="workspace">
            <header class="topbar">
                <button class="icon-button menu-toggle" aria-label="Abrir menu" aria-expanded="false"
                    aria-controls="sidebar" data-menu>
                    <x-icon name="menu" />
                </button>
                <strong>@yield('title', 'Início')</strong>
            </header>

            <main id="conteudo" tabindex="-1">
                @if (session('success'))
                    <div class="notice success" role="status">
                        <x-icon name="check" />
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="notice error" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
