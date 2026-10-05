@props(['name' => 'grid'])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
        @break

        @case('ticket')
            <path d="M4 4h16v5a3 3 0 0 0 0 6v5H4v-5a3 3 0 0 0 0-6Z" />
            <path d="M14 4v3m0 3v4m0 3v3" />
        @break

        @case('users')
            <circle cx="9" cy="8" r="3" />
            <path d="M3 21v-3a6 6 0 0 1 12 0v3m1-16a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 5" />
        @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
        @break

        @case('arrow')
            <path d="M5 12h14m-5-5 5 5-5 5" />
        @break

        @case('back')
            <path d="M19 12H5m5-5-5 5 5 5" />
        @break

        @case('search')
            <circle cx="10" cy="10" r="6" />
            <path d="m15 15 6 6" />
        @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
        @break

        @case('logout')
            <path d="M9 4H4v16h5m0-8h12m-4-4 4 4-4 4" />
        @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
        @break

        @case('clip')
            <path d="m8 12 6-6a3 3 0 0 1 4 4l-8 8a5 5 0 0 1-7-7l9-9m-7 11 8-8" />
        @break

        @case('alert')
            <path d="m12 3 10 18H2L12 3Z" />
            <path d="M12 9v5m0 3h.01" />
        @break

        @case('shield')
            <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z" />
            <path d="m8 12 3 3 5-6" />
        @break
    @endswitch
</svg>
