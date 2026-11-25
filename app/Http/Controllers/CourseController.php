<?php

namespace App\Http\Controllers;

use App\Enums\CourseChange;
use App\Enums\Weekday;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Group;
use App\Models\SpecialDay;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Throwable;

class CourseController extends Controller
{
    /**
     * Show the form to schedule a course.
     */
    public function create(): View
    {
        Gate::authorize('create', Course::class);

        $today = Weekday::fromDate(CarbonImmutable::now());

        $course = new Course([
            'day_of_week' => $today === Weekday::Sunday ? Weekday::Monday : $today,
            'starts_at' => '08:00',
            'ends_at' => '10:00',
        ]);

        return $this->form($course);
    }

    /**
     * Schedule a course and notify the professor and the students of its group.
     */
    public function store(CourseRequest $request): RedirectResponse
    {
        /** @var User $professor */
        $professor = $request->user();

        $course = $professor->courses()->create($request->validated());

        return $this->notifyAndRedirect(
            $course,
            CourseChange::Created,
            'Cours programmé. Les participants ont été notifiés par email.',
            "Cours programmé, mais les emails n'ont pas pu être envoyés.",
        );
    }

    /**
     * Show the form to change a course.
     */
    public function edit(Course $course): View
    {
        Gate::authorize('update', $course);

        return $this->form($course);
    }

    /**
     * Update a course and notify the professor and the students of its group.
     */
    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return $this->notifyAndRedirect(
            $course,
            CourseChange::Updated,
            'Cours mis à jour. Les participants ont été notifiés par email.',
            "Cours mis à jour, mais les emails n'ont pas pu être envoyés.",
        );
    }

    /**
     * Cancel a course and notify the students of its group.
     */
    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('delete', $course);

        $students = $course->enrolledStudents();

        $course->delete();

        if ($this->notify($students, $course, CourseChange::Cancelled)) {
            return to_route('dashboard')->with('status', 'Cours annulé. Les étudiants ont été notifiés par email.');
        }

        return to_route('dashboard')->with('warning', "Cours annulé, mais les étudiants n'ont pas pu être notifiés par email.");
    }

    /**
     * Render the course form.
     */
    private function form(Course $course): View
    {
        $weekStart = Weekday::currentWeekStart();

        return view('courses.form', [
            'course' => $course,
            'weekStart' => $weekStart,
            'specialDays' => SpecialDay::inWeekOf($weekStart->toDateString()),
            'groups' => Group::query()->withCount('students')->orderBy('name')->get(),
        ]);
    }

    /**
     * Notify every participant, then go back to the calendar with the right message.
     */
    private function notifyAndRedirect(Course $course, CourseChange $change, string $success, string $warning): RedirectResponse
    {
        $course->load('professor', 'group.students');

        $participants = $course->enrolledStudents()->toBase()
            ->push($course->professor)
            ->unique('id')
            ->values();

        if ($this->notify($participants, $course, $change)) {
            return to_route('dashboard')->with('status', $success);
        }

        return to_route('dashboard')->with('warning', $warning);
    }

    /**
     * Send the notification without failing the request if email delivery is down.
     *
     * @param  Collection<int, User>  $recipients
     */
    private function notify(Collection $recipients, Course $course, CourseChange $change): bool
    {
        try {
            Notification::send($recipients, new CourseScheduleNotification($course, $change));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
