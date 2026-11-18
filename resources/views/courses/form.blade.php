@php
    use App\Enums\Room;
    use App\Enums\Weekday;

    $isEditing = $course->exists;
    $selectedRoom = old('room', $course->room);
    $selectedDay = (int) old('day_of_week', $course->day_of_week?->value);
    $selectedGroup = (int) old('group_id', $course->group_id);
@endphp

<x-layouts::app :title="$isEditing ? __('Modifier le cours') : __('Programmer un cours')">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <x-ui.link :href="route('dashboard')" class="text-sm">← {{ __('Retour à l’emploi du temps') }}</x-ui.link>
            <x-ui.heading size="xl" class="mt-2">
                {{ $isEditing ? __('Modifier le cours') : __('Programmer un cours') }}
            </x-ui.heading>
            <x-ui.text class="mt-1">
                {{ __('Le professeur et les étudiants du groupe recevront un email.') }}
            </x-ui.text>
            @if ($specialDays->isNotEmpty())
                <x-ui.text class="mt-2 text-sm text-rose-600 dark:text-rose-400">
                    {{ __('Certains jours de cette semaine sont spéciaux et ne peuvent pas être programmés.') }}
                </x-ui.text>
            @endif
        </div>

        <form
            method="POST"
            action="{{ $isEditing ? route('courses.update', $course) : route('courses.store') }}"
            class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid items-start gap-4 sm:grid-cols-2">
                <x-ui.field class="sm:col-span-2">
                    <x-ui.label for="title">{{ __('Intitulé du cours') }}</x-ui.label>
                    <x-ui.input name="title" :value="old('title', $course->title)" placeholder="{{ __('Ex. Algorithmique avancée') }}" autofocus />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="room">{{ __('Salle') }}</x-ui.label>
                    <x-ui.select name="room">
                        <x-ui.option value="">{{ __('Choisir une salle') }}</x-ui.option>
                        @foreach (Room::cases() as $room)
                            <x-ui.option value="{{ $room->value }}" :selected="$selectedRoom === $room->value">
                                {{ $room->value }}
                            </x-ui.option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="day_of_week">{{ __('Jour') }}</x-ui.label>
                    <x-ui.select name="day_of_week">
                        @foreach (Weekday::schedulable() as $weekday)
                            @php($specialDay = $specialDays->get($weekday->dateInWeekOf($weekStart)->toDateString()))
                            <x-ui.option
                                value="{{ $weekday->value }}"
                                :selected="$selectedDay === $weekday->value"
                                :disabled="$specialDay !== null"
                            >
                                {{ $weekday->label() }}{{ $specialDay ? ' · '.$specialDay->label : '' }}
                            </x-ui.option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="starts_at">{{ __('Heure de début') }}</x-ui.label>
                    <x-ui.input type="time" name="starts_at" :value="old('starts_at', $course->starts_at ? $course->formattedStartTime() : null)" min="08:00" max="20:00" step="900" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="ends_at">{{ __('Heure de fin') }}</x-ui.label>
                    <x-ui.input type="time" name="ends_at" :value="old('ends_at', $course->ends_at ? $course->formattedEndTime() : null)" min="08:00" max="20:00" step="900" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label for="group_id">{{ __('Groupe') }}</x-ui.label>
                <x-ui.text class="mb-2">
                    {{ __('Les étudiants du groupe suivent ce cours et reçoivent ses notifications. Les groupes sont gérés par l’administration.') }}
                </x-ui.text>

                @if ($groups->isEmpty())
                    <div class="rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        {{ __('Aucun groupe pour le moment. Demandez à l’administration d’en créer un.') }}
                    </div>
                @else
                    <x-ui.select name="group_id">
                        <x-ui.option value="">{{ __('Choisir un groupe') }}</x-ui.option>
                        @foreach ($groups as $group)
                            <x-ui.option value="{{ $group->id }}" :selected="$selectedGroup === $group->id">
                                {{ $group->name }} · {{ trans_choice('{0} Aucun étudiant|{1} :count étudiant|[2,*] :count étudiants', $group->students_count) }}
                            </x-ui.option>
                        @endforeach
                    </x-ui.select>
                @endif

            </x-ui.field>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <x-ui.button variant="ghost" :href="route('dashboard')">{{ __('Annuler') }}</x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    {{ __('Enregistrer et notifier') }}
                </x-ui.button>
            </div>
        </form>

        @if ($isEditing)
            <form
                method="POST"
                action="{{ route('courses.destroy', $course) }}"
                onsubmit="return confirm('{{ __('Supprimer ce cours ? Les étudiants seront notifiés de son annulation.') }}')"
                class="flex items-center justify-between gap-4 rounded-xl border border-red-200 p-4 dark:border-red-900"
            >
                @csrf
                @method('DELETE')
                <x-ui.text>{{ __('Les étudiants du groupe recevront un email d’annulation.') }}</x-ui.text>
                <x-ui.button variant="danger" type="submit">{{ __('Annuler le cours') }}</x-ui.button>
            </form>
        @endif
    </div>
</x-layouts::app>
