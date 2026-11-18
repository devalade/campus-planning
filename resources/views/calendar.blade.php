<x-layouts::app :title="__('Emploi du temps')">
<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-flash />
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <x-ui.heading size="xl">{{ __('Mon emploi du temps') }}</x-ui.heading>
            <x-ui.text class="mt-1">
                Semaine du {{ $weekStart->format('d/m') }} au {{ $weekStart->addDays(5)->format('d/m/Y') }}
                <span class="mx-1 text-zinc-400">·</span>
                {{ $courseCount }} cours
                @if ($courseCount > 0)
                    <span class="mx-1 text-zinc-400">·</span>
                    {{ number_format($hours, 1, ',', ' ') }} h
                @endif
            </x-ui.text>
        </div>

        @if ($user->isProfessor())
            <x-ui.button variant="primary" :href="route('courses.create')">
                {{ __('Programmer un cours') }}
            </x-ui.button>
        @endif
    </div>

    @if ($courseCount === 0)
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <div>
                <x-ui.heading size="sm">{{ __('Aucun cours cette semaine') }}</x-ui.heading>
                <x-ui.text class="mt-1">
                    @if ($user->isProfessor())
                        {{ __('Programmez votre premier cours pour remplir votre calendrier.') }}
                    @else
                        {{ __('Vos cours apparaîtront ici dès qu’un professeur programmera un cours pour l’un de vos groupes.') }}
                    @endif
                </x-ui.text>
            </div>
            @if ($user->isProfessor())
                <x-ui.button size="sm" variant="filled" :href="route('courses.create')">
                    {{ __('Programmer un cours') }}
                </x-ui.button>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="min-w-[58rem]">
            <div class="grid grid-cols-[4.5rem_repeat(6,minmax(0,1fr))] border-b border-zinc-200 dark:border-zinc-700">
                <div class="border-e border-zinc-200 dark:border-zinc-700"></div>

                @foreach ($days as $day)
                    <div class="border-e border-zinc-200 px-3 py-3 text-center last:border-e-0 dark:border-zinc-700">
                        <div class="text-[11px] font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                            {{ $day['weekday']->shortLabel() }}
                        </div>
                        <div class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $day['date']->format('d/m') }}
                        </div>
                        @if ($day['specialDay'])
                            <div class="mt-1 inline-flex max-w-full items-center truncate rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-950/60 dark:text-rose-300" title="{{ $day['specialDay']->label }}">
                                {{ $day['specialDay']->label }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-[4.5rem_repeat(6,minmax(0,1fr))]">
                <div class="border-e border-zinc-200 dark:border-zinc-700">
                    @foreach (range(8, 19) as $hour)
                        <div class="h-16 border-b border-zinc-100 pe-3 pt-2 text-right text-[11px] font-medium text-zinc-400 last:border-b-0 dark:border-zinc-800 dark:text-zinc-500">
                            {{ sprintf('%02d:00', $hour) }}
                        </div>
                    @endforeach
                </div>

                @foreach ($days as $day)
                    <div class="relative h-[48rem] border-e border-zinc-200 last:border-e-0 dark:border-zinc-700">
                        @foreach (range(0, 11) as $slot)
                            <div class="h-16 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800"></div>
                        @endforeach

                        @foreach ($day['courses'] as $course)
                            @php
                                $position = $day['layout'][$course->id];
                                $isMine = $user->isProfessor();
                            @endphp

                            <div
                                class="absolute p-0.5"
                                style="top: {{ $position['top'] }}%; height: {{ $position['height'] }}%; left: {{ $position['left'] }}%; width: {{ $position['width'] }}%;"
                            >
                                <a
                                    @if ($isMine) href="{{ route('courses.edit', $course) }}" @endif
                                    @class([
                                        'flex h-full w-full flex-col overflow-hidden rounded-lg border px-2 py-1.5 text-left transition',
                                        'cursor-pointer hover:shadow-md' => $isMine,
                                        'cursor-default' => ! $isMine,
                                        'border-sky-200 bg-sky-50 text-sky-950 dark:border-sky-800 dark:bg-sky-950/60 dark:text-sky-100' => $course->professor_id % 5 === 1,
                                        'border-violet-200 bg-violet-50 text-violet-950 dark:border-violet-800 dark:bg-violet-950/60 dark:text-violet-100' => $course->professor_id % 5 === 2,
                                        'border-emerald-200 bg-emerald-50 text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-100' => $course->professor_id % 5 === 3,
                                        'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-100' => $course->professor_id % 5 === 4,
                                        'border-rose-200 bg-rose-50 text-rose-950 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-100' => $course->professor_id % 5 === 0,
                                    ])
                                >
                                    <span class="line-clamp-2 text-xs leading-snug font-semibold">{{ $course->title }}</span>
                                    <span class="mt-0.5 text-[11px] opacity-80">
                                        {{ $course->formattedStartTime() }} - {{ $course->formattedEndTime() }}
                                    </span>
                                    @if ($course->durationInMinutes() >= 60)
                                        <span class="mt-auto truncate text-[11px] opacity-70">
                                            {{ $course->room }}
                                            @if ($isMine)
                                                @if ($course->group)
                                                    <span class="mx-0.5">·</span> {{ $course->group->name }}
                                                @endif
                                            @else
                                                <span class="mx-0.5">·</span> {{ $course->professor->name }}
                                            @endif
                                        </span>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
</x-layouts::app>
