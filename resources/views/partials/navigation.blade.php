@php
    $links = [
        __('Planning') => [
            ['route' => 'dashboard', 'label' => __('Emploi du temps')],
            ['route' => 'profile.edit', 'label' => __('Mon profil')],
        ],
    ];

    if (auth()->user()->isAdmin()) {
        $links[__('Administration')] = [
            ['route' => 'admin.special-days', 'label' => __('Jours spéciaux')],
            ['route' => 'admin.groups', 'label' => __('Groupes')],
        ];
    }
@endphp

<nav class="flex flex-1 flex-col gap-6" aria-label="{{ __('Navigation') }}">
    @foreach ($links as $heading => $items)
        <div class="grid gap-1">
            <p class="px-3 text-xs font-medium text-zinc-400">{{ $heading }}</p>
            @foreach ($items as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-white text-zinc-900 shadow-xs dark:bg-zinc-700 dark:text-white' => request()->routeIs($item['route'].'*'),
                        'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => ! request()->routeIs($item['route'].'*'),
                    ])
                    @if (request()->routeIs($item['route'].'*')) aria-current="page" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    @endforeach
</nav>

<div class="mt-6 grid gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
    <div class="flex items-center gap-3 px-1">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-zinc-200 text-xs font-semibold text-zinc-800 dark:bg-zinc-700 dark:text-white">
            {{ auth()->user()->initials() }}
        </span>
        <div class="min-w-0 text-sm leading-tight">
            <p class="truncate font-medium text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
            <p class="truncate text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full cursor-pointer rounded-lg px-3 py-2 text-start text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800" data-test="logout-button">
            {{ __('Log out') }}
        </button>
    </form>
</div>
