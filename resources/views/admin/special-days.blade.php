@php use App\Enums\Weekday; @endphp

<x-layouts::app :title="__('Jours spéciaux')">

<section class="w-full space-y-6">
    <x-flash />
    <div>
        <x-ui.heading size="xl">{{ __('Jours spéciaux') }}</x-ui.heading>
        <x-ui.text class="mt-1">
            {{ __('Les jours spéciaux bloquent la programmation des cours dans la semaine affichée.') }}
        </x-ui.text>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
        <div class="order-2 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm lg:order-1 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <x-ui.heading size="lg">{{ __('Jours programmés') }}</x-ui.heading>
                <x-ui.badge>{{ $specialDays->count() }}</x-ui.badge>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($specialDays as $specialDay)
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div>
                            <x-ui.text class="font-medium">
                                {{ Weekday::fromDate($specialDay->date)->label() }} {{ $specialDay->date->format('d/m/Y') }}
                            </x-ui.text>
                            <x-ui.text size="sm" class="mt-1">{{ $specialDay->label }}</x-ui.text>
                        </div>

                        <form method="POST" action="{{ route('admin.special-days.destroy', $specialDay) }}" onsubmit="return confirm('{{ __('Supprimer ce jour spécial ?') }}')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button size="sm" variant="danger" type="submit">{{ __('Supprimer') }}</x-ui.button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <x-ui.text>{{ __('Aucun jour spécial programmé.') }}</x-ui.text>
                    </div>
                @endforelse
            </div>
        </div>

        <form method="POST" action="{{ route('admin.special-days.store') }}" class="order-1 h-fit space-y-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm lg:order-2 dark:border-zinc-700 dark:bg-zinc-900">
            @csrf
            <x-ui.heading size="lg">{{ __('Ajouter un jour spécial') }}</x-ui.heading>
            <x-ui.text size="sm">
                {{ __('Le jour sera bloqué pour la programmation des cours de la semaine.') }}
            </x-ui.text>

            <x-ui.field>
                <x-ui.label for="date">{{ __('Date') }}</x-ui.label>
                <x-ui.input type="date" name="date" :value="old('date')" min="{{ now()->toDateString() }}" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="label">{{ __('Libellé') }}</x-ui.label>
                <x-ui.input name="label" :value="old('label')" maxlength="100" placeholder="{{ __('Ex. Journée pédagogique') }}" />
            </x-ui.field>

            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ __('Ajouter le jour') }}
            </x-ui.button>
        </form>
    </div>
</section>
</x-layouts::app>
