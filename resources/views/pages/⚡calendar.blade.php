<?php

use App\Enums\CourseChange;
use App\Enums\Role;
use App\Enums\Room;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Emploi du temps')] class extends Component
{
    public bool $showCourseModal = false;

    public ?int $editingCourseId = null;

    public string $title = '';

    public string $room = '';

    public int $dayOfWeek = 1;

    public string $startTime = '08:00';

    public string $endTime = '10:00';

    /** @var array<int, int|string> */
    public array $studentIds = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $today = Weekday::fromDate(CarbonImmutable::now());

        $this->dayOfWeek = $today === Weekday::Sunday
            ? Weekday::Monday->value
            : $today->value;
    }

    /**
     * Get the authenticated user.
     */
    #[Computed]
    public function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    /**
     * Get the first day of the displayed week.
     */
    #[Computed]
    public function weekStart(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfWeek(Weekday::Monday->value);
    }

    /**
     * Get the courses visible to the authenticated user, grouped by weekday.
     *
     * @return Collection<int, Collection<int, Course>>
     */
    #[Computed]
    public function courses(): Collection
    {
        return Course::query()
            ->with(['professor', 'students'])
            ->visibleTo($this->currentUser)
            ->whereIn('day_of_week', array_map(
                fn (Weekday $weekday): int => $weekday->value,
                Weekday::schedulable(),
            ))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Course $course): int => $course->day_of_week->value);
    }

    /**
     * Get the six university days displayed in the calendar.
     *
     * @return array<int, array{date: CarbonImmutable, weekday: Weekday, courses: Collection<int, Course>, layout: array<int, array{top: float, height: float, left: float, width: float}>}>
     */
    #[Computed]
    public function weekDays(): array
    {
        return collect(range(0, 5))
            ->map(function (int $offset): array {
                $date = $this->weekStart->addDays($offset);
                $weekday = Weekday::fromDate($date);
                $courses = $this->courses->get($weekday->value, collect());

                return [
                    'date' => $date,
                    'weekday' => $weekday,
                    'courses' => $courses,
                    'layout' => $this->layoutCourses($courses),
                ];
            })
            ->all();
    }

    /**
     * Get the number of courses in the displayed week.
     */
    #[Computed]
    public function weekCourseCount(): int
    {
        return $this->courses->flatten()->count();
    }

    /**
     * Get the number of teaching hours in the displayed week.
     */
    #[Computed]
    public function weekHours(): float
    {
        return round(
            $this->courses->flatten()->sum(fn (Course $course): int => $course->durationInMinutes()) / 60,
            1,
        );
    }

    /**
     * Get the students that can be enrolled by the authenticated professor.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function students(): Collection
    {
        if (! $this->currentUser->isProfessor()) {
            return collect();
        }

        return User::query()
            ->where('role', Role::Student->value)
            ->orderBy('name')
            ->get();
    }

    /**
     * Open the form for creating a course.
     */
    public function openCreateModal(): void
    {
        Gate::authorize('create', Course::class);

        $this->resetForm();
        $this->showCourseModal = true;
    }

    /**
     * Open the form for editing one of the professor's courses.
     */
    public function editCourse(int $courseId): void
    {
        $course = Course::query()
            ->with('students')
            ->findOrFail($courseId);

        Gate::authorize('update', $course);

        $this->editingCourseId = $course->id;
        $this->title = $course->title;
        $this->room = $course->room;
        $this->dayOfWeek = $course->day_of_week->value;
        $this->startTime = $course->formattedStartTime();
        $this->endTime = $course->formattedEndTime();
        $this->studentIds = $course->students->pluck('id')->map(fn (int $id): string => (string) $id)->all();
        $this->resetValidation();
        $this->showCourseModal = true;
    }

    /**
     * Create or update a course and notify every participant.
     */
    public function saveCourse(): void
    {
        Gate::authorize('create', Course::class);

        $editingCourse = $this->editingCourseId !== null
            ? Course::query()->findOrFail($this->editingCourseId)
            : null;

        if ($editingCourse !== null) {
            Gate::authorize('update', $editingCourse);
        }

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', Rule::in(Room::values())],
            'dayOfWeek' => ['required', 'integer', Rule::in($this->schedulableDayValues())],
            'startTime' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:endTime'],
            'endTime' => ['required', 'date_format:H:i', 'after:startTime', 'before_or_equal:20:00'],
            'studentIds' => ['array'],
            'studentIds.*' => ['integer', Rule::exists('users', 'id')->where('role', Role::Student->value)],
        ], [
            'title.required' => 'Le titre du cours est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'room.required' => 'La salle est obligatoire.',
            'room.in' => 'Choisissez une salle valide.',
            'dayOfWeek.required' => 'Choisissez un jour.',
            'dayOfWeek.integer' => 'Le jour sélectionné est invalide.',
            'dayOfWeek.in' => 'Le cours doit être programmé du lundi au samedi.',
            'startTime.required' => "L'heure de début est obligatoire.",
            'startTime.date_format' => "L'heure de début doit être au format HH:MM.",
            'startTime.after_or_equal' => 'Le cours doit commencer à 08:00 au plus tôt.',
            'startTime.before' => "L'heure de début doit précéder l'heure de fin.",
            'endTime.required' => "L'heure de fin est obligatoire.",
            'endTime.date_format' => "L'heure de fin doit être au format HH:MM.",
            'endTime.after' => "L'heure de fin doit être postérieure à l'heure de début.",
            'endTime.before_or_equal' => 'Le cours doit se terminer à 20:00 au plus tard.',
            'studentIds.array' => 'La liste des étudiants est invalide.',
            'studentIds.*.integer' => 'Un étudiant sélectionné est invalide.',
            'studentIds.*.exists' => 'Seuls les étudiants peuvent être inscrits à un cours.',
        ]);

        $this->ensureTheSlotIsFree($editingCourse);

        $course = $editingCourse ?? new Course(['professor_id' => $this->currentUser->id]);

        $course->fill([
            'title' => $this->title,
            'room' => $this->room,
            'day_of_week' => $this->dayOfWeek,
            'starts_at' => $this->startTime,
            'ends_at' => $this->endTime,
        ]);

        $course->save();
        $course->students()->sync($this->studentIds);
        $course->load('professor', 'students');

        $isNewCourse = $course->wasRecentlyCreated;

        $notified = $this->notifyParticipants(
            $course->students->concat([$course->professor])->unique('id')->values(),
            $course,
            $isNewCourse ? CourseChange::Created : CourseChange::Updated,
        );

        $this->showCourseModal = false;
        $this->resetForm();
        $this->refreshCalendar();

        if ($notified) {
            Flux::toast(
                variant: 'success',
                text: $isNewCourse
                    ? 'Cours programmé. Les participants ont été notifiés par email.'
                    : 'Cours mis à jour. Les participants ont été notifiés par email.',
            );

            return;
        }

        Flux::toast(
            variant: 'warning',
            text: $isNewCourse
                ? "Cours programmé, mais les emails n'ont pas pu être envoyés."
                : "Cours mis à jour, mais les emails n'ont pas pu être envoyés.",
        );
    }

    /**
     * Delete a course and notify its students.
     */
    public function deleteCourse(int $courseId): void
    {
        $course = Course::query()
            ->with(['professor', 'students'])
            ->findOrFail($courseId);

        Gate::authorize('delete', $course);

        $students = $course->students;

        $course->delete();

        $notified = $this->notifyParticipants(
            $students,
            $course,
            CourseChange::Cancelled,
        );

        $this->showCourseModal = false;
        $this->resetForm();
        $this->refreshCalendar();

        if ($notified) {
            Flux::toast(
                variant: 'success',
                text: 'Cours annulé. Les étudiants ont été notifiés par email.',
            );

            return;
        }

        Flux::toast(
            variant: 'warning',
            text: "Cours annulé, mais les étudiants n'ont pas pu être notifiés par email.",
        );
    }

    /**
     * Notify every course participant without failing the course change if email delivery is down.
     *
     * @param  Collection<int, User>  $recipients
     */
    private function notifyParticipants(Collection $recipients, Course $course, CourseChange $change): bool
    {
        try {
            Notification::send($recipients, new CourseScheduleNotification($course, $change));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * Check that the professor does not already teach another course at this time.
     */
    private function ensureTheSlotIsFree(?Course $editingCourse): void
    {
        $overlaps = Course::query()
            ->where('professor_id', $this->currentUser->id)
            ->where('day_of_week', $this->dayOfWeek)
            ->when(
                $editingCourse !== null,
                fn (Builder $query): Builder => $query->where('id', '!=', $editingCourse->id),
            )
            ->where('starts_at', '<', $this->endTime.':00')
            ->where('ends_at', '>', $this->startTime.':00')
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'startTime' => 'Ce créneau chevauche un autre cours que vous enseignez déjà.',
            ]);
        }
    }

    /**
     * Reset the course form.
     */
    private function resetForm(): void
    {
        $this->reset('editingCourseId', 'title', 'room', 'startTime', 'endTime', 'studentIds');
        $this->startTime = '08:00';
        $this->endTime = '10:00';
        $this->resetValidation();
    }

    /**
     * Clear the cached calendar data after a change.
     */
    private function refreshCalendar(): void
    {
        unset($this->courses, $this->weekDays, $this->weekCourseCount, $this->weekHours);
    }

    /**
     * Get the values of the schedulable weekdays.
     *
     * @return array<int, int>
     */
    private function schedulableDayValues(): array
    {
        return array_map(
            fn (Weekday $weekday): int => $weekday->value,
            Weekday::schedulable(),
        );
    }

    /**
     * Position the courses of a day on the time grid, including overlaps.
     *
     * @param  Collection<int, Course>  $courses
     * @return array<int, array{top: float, height: float, left: float, width: float}>
     */
    private function layoutCourses(Collection $courses): array
    {
        $dayStart = 8 * 60;
        $dayDuration = 12 * 60;

        $sortedCourses = $courses
            ->sortBy(fn (Course $course): int => $course->startMinutes())
            ->values();

        $clusters = [];
        $cluster = [];
        $clusterEnd = null;

        foreach ($sortedCourses as $course) {
            if ($clusterEnd !== null && $course->startMinutes() >= $clusterEnd) {
                $clusters[] = $cluster;
                $cluster = [];
                $clusterEnd = null;
            }

            $cluster[] = $course;
            $clusterEnd = max($clusterEnd ?? 0, $course->endMinutes());
        }

        if ($cluster !== []) {
            $clusters[] = $cluster;
        }

        $layout = [];

        foreach ($clusters as $clusterCourses) {
            /** @var array<int, int> $columnEnds */
            $columnEnds = [];

            /** @var array<int, int> $courseColumns */
            $courseColumns = [];

            foreach ($clusterCourses as $course) {
                $column = null;

                foreach ($columnEnds as $index => $end) {
                    if ($course->startMinutes() >= $end) {
                        $column = $index;
                        $columnEnds[$index] = $course->endMinutes();

                        break;
                    }
                }

                if ($column === null) {
                    $columnEnds[] = $course->endMinutes();
                    $column = array_key_last($columnEnds);
                }

                $courseColumns[$course->id] = $column;
            }

            $columnCount = max(count($columnEnds), 1);
            $width = 100 / $columnCount;

            foreach ($clusterCourses as $course) {
                $layout[$course->id] = [
                    'top' => max(0, (($course->startMinutes() - $dayStart) / $dayDuration) * 100),
                    'height' => ($course->durationInMinutes() / $dayDuration) * 100,
                    'left' => $courseColumns[$course->id] * $width,
                    'width' => $width,
                ];
            }
        }

        return $layout;
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Mon emploi du temps') }}</flux:heading>
            <flux:text class="mt-1">
                Semaine du {{ $this->weekStart->format('d/m') }} au {{ $this->weekStart->addDays(5)->format('d/m/Y') }}
                <span class="mx-1 text-zinc-400">·</span>
                {{ $this->weekCourseCount }} cours
                @if ($this->weekCourseCount > 0)
                    <span class="mx-1 text-zinc-400">·</span>
                    {{ number_format($this->weekHours, 1, ',', ' ') }} h
                @endif
            </flux:text>
        </div>

        @if ($this->currentUser->isProfessor())
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('Programmer un cours') }}
            </flux:button>
        @endif
    </div>

    @if ($this->weekCourseCount === 0)
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <div class="flex size-12 items-center justify-center rounded-full bg-white text-zinc-400 shadow-sm dark:bg-zinc-800 dark:text-zinc-500">
                <flux:icon name="calendar-days" class="size-6" />
            </div>
            <div>
                <flux:heading size="sm">{{ __('Aucun cours cette semaine') }}</flux:heading>
                <flux:text class="mt-1">
                    @if ($this->currentUser->isProfessor())
                        {{ __('Programmez votre premier cours pour remplir votre calendrier.') }}
                    @else
                        {{ __('Vos cours apparaîtront ici dès que votre professeur vous y aura inscrit.') }}
                    @endif
                </flux:text>
            </div>
            @if ($this->currentUser->isProfessor())
                <flux:button size="sm" variant="filled" icon="plus" wire:click="openCreateModal">
                    {{ __('Programmer un cours') }}
                </flux:button>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="min-w-[58rem]">
            <div class="grid grid-cols-[4.5rem_repeat(6,minmax(0,1fr))] border-b border-zinc-200 dark:border-zinc-700">
                <div class="border-e border-zinc-200 dark:border-zinc-700"></div>

                @foreach ($this->weekDays as $day)
                    <div class="border-e border-zinc-200 px-3 py-3 text-center last:border-e-0 dark:border-zinc-700">
                        <div class="text-[11px] font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                            {{ $day['weekday']->shortLabel() }}
                        </div>
                        <div class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $day['date']->format('d/m') }}
                        </div>
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

                @foreach ($this->weekDays as $day)
                    <div class="relative h-[48rem] border-e border-zinc-200 last:border-e-0 dark:border-zinc-700">
                        @foreach (range(0, 11) as $slot)
                            <div class="h-16 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800"></div>
                        @endforeach

                        @foreach ($day['courses'] as $course)
                            @php($position = $day['layout'][$course->id])
                            @php($isMine = $this->currentUser->isProfessor())

                            <div
                                class="absolute p-0.5"
                                wire:key="course-{{ $course->id }}"
                                style="top: {{ $position['top'] }}%; height: {{ $position['height'] }}%; left: {{ $position['left'] }}%; width: {{ $position['width'] }}%;"
                            >
                                <button
                                    type="button"
                                    @if ($isMine) wire:click="editCourse({{ $course->id }})" @endif
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
                                            @unless ($isMine)
                                                <span class="mx-0.5">·</span> {{ $course->professor->name }}
                                            @endunless
                                        </span>
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <flux:modal wire:model.self="showCourseModal" class="md:w-[42rem]">
        <form wire:submit="saveCourse" class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">
                        {{ $editingCourseId ? __('Modifier le cours') : __('Programmer un cours') }}
                    </flux:heading>
                    <flux:text class="mt-1">
                        {{ __('Le professeur et les étudiants sélectionnés recevront un email.') }}
                    </flux:text>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Intitulé du cours') }}</flux:label>
                    <flux:input wire:model="title" placeholder="{{ __('Ex. Algorithmique avancée') }}" autofocus />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Salle') }}</flux:label>
                    <flux:select wire:model="room" placeholder="{{ __('Choisir une salle') }}">
                        @foreach (Room::cases() as $availableRoom)
                            <flux:select.option value="{{ $availableRoom->value }}">{{ $availableRoom->value }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="room" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Jour') }}</flux:label>
                    <flux:select wire:model.number="dayOfWeek">
                        @foreach (Weekday::schedulable() as $weekday)
                            <flux:select.option value="{{ $weekday->value }}">{{ $weekday->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="dayOfWeek" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Heure de début') }}</flux:label>
                    <flux:input type="time" wire:model="startTime" min="08:00" max="20:00" step="900" />
                    <flux:error name="startTime" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Heure de fin') }}</flux:label>
                    <flux:input type="time" wire:model="endTime" min="08:00" max="20:00" step="900" />
                    <flux:error name="endTime" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Étudiants inscrits') }}</flux:label>
                <flux:text class="mb-2">
                    {{ __('Sélectionnez les étudiants qui doivent recevoir les notifications de ce cours.') }}
                </flux:text>

                @if ($this->students->isEmpty())
                    <div class="rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        {{ __('Aucun étudiant inscrit sur la plateforme pour le moment.') }}
                    </div>
                @else
                    <div class="max-h-56 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:checkbox.group wire:model="studentIds" class="grid gap-2 sm:grid-cols-2">
                            @foreach ($this->students as $student)
                                <flux:checkbox
                                    wire:key="student-{{ $student->id }}"
                                    value="{{ $student->id }}"
                                    label="{{ $student->name }}"
                                />
                            @endforeach
                        </flux:checkbox.group>
                    </div>
                @endif

                <flux:error name="studentIds" />
                <flux:error name="studentIds.*" />
            </flux:field>

            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
                <div>
                    @if ($editingCourseId)
                        <flux:button
                            variant="danger"
                            type="button"
                            icon="trash"
                            wire:click="deleteCourse({{ $editingCourseId }})"
                            wire:confirm="{{ __('Supprimer ce cours ? Les étudiants seront notifiés de son annulation.') }}"
                        >
                            {{ __('Supprimer') }}
                        </flux:button>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button">{{ __('Annuler') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit" icon="paper-airplane">
                        {{ __('Enregistrer et notifier') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
