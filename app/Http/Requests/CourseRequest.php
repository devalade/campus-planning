<?php

namespace App\Http\Requests;

use App\Enums\Room;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\SpecialDay;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CourseRequest extends FormRequest
{
    /**
     * Only a professor may create a course, and only its own professor may change it.
     */
    public function authorize(): bool
    {
        $course = $this->editedCourse();

        return $course === null
            ? $this->user()->can('create', Course::class)
            : $this->user()->can('update', $course);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', Rule::in(Room::values())],
            'day_of_week' => ['required', 'integer', Rule::in(array_map(
                fn (Weekday $weekday): int => $weekday->value,
                Weekday::schedulable(),
            ))],
            'starts_at' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:ends_at'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at', 'before_or_equal:20:00'],
            'group_id' => ['required', 'integer', Rule::exists('groups', 'id')],
        ];
    }

    /**
     * Get the French error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du cours est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'room.required' => 'La salle est obligatoire.',
            'room.in' => 'Choisissez une salle valide.',
            'day_of_week.required' => 'Choisissez un jour.',
            'day_of_week.integer' => 'Le jour sélectionné est invalide.',
            'day_of_week.in' => 'Le cours doit être programmé du lundi au samedi.',
            'starts_at.required' => "L'heure de début est obligatoire.",
            'starts_at.date_format' => "L'heure de début doit être au format HH:MM.",
            'starts_at.after_or_equal' => 'Le cours doit commencer à 08:00 au plus tôt.',
            'starts_at.before' => "L'heure de début doit précéder l'heure de fin.",
            'ends_at.required' => "L'heure de fin est obligatoire.",
            'ends_at.date_format' => "L'heure de fin doit être au format HH:MM.",
            'ends_at.after' => "L'heure de fin doit être postérieure à l'heure de début.",
            'ends_at.before_or_equal' => 'Le cours doit se terminer à 20:00 au plus tard.',
            'group_id.required' => 'Choisissez le groupe qui suit ce cours.',
            'group_id.integer' => 'Le groupe sélectionné est invalide.',
            'group_id.exists' => 'Le groupe sélectionné est invalide.',
        ];
    }

    /**
     * Check the rules that need the database once the fields are valid.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->fallsOnASpecialDay()) {
                    $validator->errors()->add('day_of_week', 'Ce jour est spécial, aucun cours ne peut y être programmé.');

                    return;
                }

                if ($this->overlapsAnotherCourse()) {
                    $validator->errors()->add('starts_at', 'Ce créneau chevauche un autre cours que vous enseignez déjà.');
                }
            },
        ];
    }

    /**
     * Get the course being edited, or null when a course is created.
     */
    public function editedCourse(): ?Course
    {
        $course = $this->route('course');

        return $course instanceof Course ? $course : null;
    }

    /**
     * Determine if the chosen day is a special day of the current week.
     */
    private function fallsOnASpecialDay(): bool
    {
        $weekStart = Weekday::currentWeekStart();
        $date = Weekday::from($this->integer('day_of_week'))->dateInWeekOf($weekStart);

        return SpecialDay::inWeekOf($weekStart)->has($date->toDateString());
    }

    /**
     * Determine if the professor already teaches another course in this slot.
     */
    private function overlapsAnotherCourse(): bool
    {
        /** @var User $professor */
        $professor = $this->user();

        return $professor->courses()
            ->where('day_of_week', $this->integer('day_of_week'))
            ->when($this->editedCourse(), fn ($query, Course $course) => $query->whereKeyNot($course->id))
            ->where('starts_at', '<', $this->string('ends_at').':00')
            ->where('ends_at', '>', $this->string('starts_at').':00')
            ->exists();
    }
}
