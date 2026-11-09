<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen overflow-x-hidden bg-zinc-950 text-white antialiased">
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_20%_0%,rgba(56,189,248,0.14),transparent_32%),radial-gradient(circle_at_80%_20%,rgba(167,139,250,0.12),transparent_30%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-6 sm:px-8">
            <header class="flex items-center justify-between gap-4">
                <x-app-logo href="{{ route('home') }}" />

                <nav class="flex items-center gap-2">
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary" icon="calendar-days" wire:navigate>
                            {{ __('Ouvrir mon calendrier') }}
                        </flux:button>
                    @else
                        <flux:button :href="route('login')" variant="ghost" wire:navigate class="hidden sm:inline-flex">
                            {{ __('Se connecter') }}
                        </flux:button>
                        <flux:button :href="route('register')" variant="primary" wire:navigate>
                            {{ __('Créer un compte') }}
                        </flux:button>
                    @endauth
                </nav>
            </header>

            <main class="grid flex-1 items-center gap-16 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:py-20">
                <section class="max-w-2xl">
                    <flux:badge color="sky" icon="academic-cap">{{ __('Planning universitaire') }}</flux:badge>

                    <h1 class="mt-6 text-4xl font-semibold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                        {{ __("L'emploi du temps de la semaine, enfin lisible.") }}
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-zinc-400">
                        {{ __('Programmez vos cours, retrouvez votre semaine en un coup d\'œil et prévenez automatiquement les étudiants et les professeurs par email.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @guest
                            <flux:button :href="route('register')" variant="primary" icon="arrow-right" wire:navigate class="h-11 px-5">
                                {{ __('Commencer gratuitement') }}
                            </flux:button>
                            <flux:button :href="route('login')" wire:navigate class="h-11 px-5">
                                {{ __('J\'ai déjà un compte') }}
                            </flux:button>
                        @else
                            <flux:button :href="route('dashboard')" variant="primary" icon="calendar-days" wire:navigate class="h-11 px-5">
                                {{ __('Voir ma semaine') }}
                            </flux:button>
                        @endguest
                    </div>

                    <div class="mt-10 flex flex-col gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-sm text-zinc-400 sm:flex-row sm:items-center sm:gap-6">
                        <div class="flex items-center gap-2">
                            <flux:icon name="envelope" class="size-4 text-sky-400" />
                            {{ __('Notifications email automatiques') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon name="user-group" class="size-4 text-violet-400" />
                            {{ __('Vue professeur et étudiant') }}
                        </div>
                    </div>

                    <div class="mt-6 rounded-lg border border-white/10 bg-zinc-900/60 px-4 py-3 text-sm text-zinc-400">
                        <span class="font-medium text-zinc-200">{{ __('Compte de démonstration') }}</span>
                        <span class="mx-2 text-zinc-600">·</span>
                        <span class="font-mono text-xs text-zinc-300">awa.sossou@univ.test</span>
                        <span class="mx-1 text-zinc-600">/</span>
                        <span class="font-mono text-xs text-zinc-300">password</span>
                    </div>
                </section>

                <section class="relative mx-auto w-full max-w-xl lg:max-w-none" aria-hidden="true">
                    <div class="absolute -inset-6 rounded-[2rem] bg-sky-400/5 blur-3xl"></div>

                    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-zinc-900/80 shadow-2xl shadow-black/40 backdrop-blur">
                        <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                            <div>
                                <p class="text-[11px] font-semibold tracking-widest text-zinc-500 uppercase">{{ __('Cette semaine') }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-white">{{ __('Mon emploi du temps') }}</p>
                            </div>
                            <div class="flex size-9 items-center justify-center rounded-full bg-white text-xs font-semibold text-zinc-900">AS</div>
                        </div>

                        <div class="grid grid-cols-[3rem_repeat(4,minmax(0,1fr))] gap-px bg-white/10">
                            <div class="bg-zinc-900"></div>
                            @foreach (['Lun.', 'Mar.', 'Mer.', 'Jeu.'] as $day)
                                <div class="bg-zinc-900 px-2 py-2 text-center text-[10px] font-semibold tracking-wide text-zinc-500 uppercase">
                                    {{ $day }}
                                </div>
                            @endforeach

                            <div class="flex flex-col justify-between bg-zinc-900 px-2 py-3 text-right text-[9px] text-zinc-600">
                                <span>08:00</span>
                                <span>10:00</span>
                                <span>12:00</span>
                                <span>14:00</span>
                                <span>16:00</span>
                            </div>

                            <div class="relative h-64 bg-zinc-900">
                                <div class="absolute inset-x-1 top-1 h-16 rounded-lg border border-sky-400/40 bg-sky-400/15 p-2">
                                    <p class="text-[10px] font-semibold text-sky-100">Algorithmique</p>
                                    <p class="mt-0.5 text-[9px] text-sky-300/70">Amphi B</p>
                                </div>
                            </div>

                            <div class="relative h-64 bg-zinc-900">
                                <div class="absolute inset-x-1 top-[4.5rem] h-16 rounded-lg border border-violet-400/40 bg-violet-400/15 p-2">
                                    <p class="text-[10px] font-semibold text-violet-100">Bases de données</p>
                                    <p class="mt-0.5 text-[9px] text-violet-300/70">Salle 12</p>
                                </div>
                            </div>

                            <div class="relative h-64 bg-zinc-900">
                                <div class="absolute inset-x-1 top-[9rem] h-16 rounded-lg border border-emerald-400/40 bg-emerald-400/15 p-2">
                                    <p class="text-[10px] font-semibold text-emerald-100">Réseaux</p>
                                    <p class="mt-0.5 text-[9px] text-emerald-300/70">Salle 07</p>
                                </div>
                            </div>

                            <div class="relative h-64 bg-zinc-900">
                                <div class="absolute inset-x-1 top-1 h-16 rounded-lg border border-amber-400/40 bg-amber-400/15 p-2">
                                    <p class="text-[10px] font-semibold text-amber-100">Génie logiciel</p>
                                    <p class="mt-0.5 text-[9px] text-amber-300/70">Amphi A</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 border-t border-white/10 px-5 py-3 text-[11px] text-zinc-500">
                            <flux:icon name="paper-airplane" class="size-3.5 text-sky-400" />
                            {{ __('4 participants notifiés par email') }}
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-white/10 py-5 text-xs text-zinc-600">
                {{ config('app.name') }} · {{ __('Gestion de calendrier universitaire') }}
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
