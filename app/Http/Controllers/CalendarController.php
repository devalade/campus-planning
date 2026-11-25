<?php

namespace App\Http\Controllers;

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\SpecialDay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Show the week of courses visible to the signed-in user.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $weekStart = Weekday::currentWeekStart();
        $specialDays = SpecialDay::inWeekOf($weekStart->toDateString());

        $courses = Course::query()
            ->with(['professor', 'group'])
            ->visibleTo($user)
            ->orderBy('starts_at')
            ->get();

        $days = collect(Weekday::schedulable())->map(function (Weekday $weekday) use ($weekStart, $courses, $specialDays): array {
            $date = $weekday->dateInWeekOf($weekStart);
            $dayCourses = $courses->filter(fn (Course $course): bool => $course->day_of_week === $weekday)->values();

            return [
                'date' => $date,
                'weekday' => $weekday,
                'courses' => $dayCourses,
                'layout' => $this->layoutCourses($dayCourses),
                'specialDay' => $specialDays->get($date->toDateString()),
            ];
        });

        $weekCourses = $days->flatMap(fn (array $day): Collection => $day['courses']);

        return view('calendar', [
            'user' => $user,
            'weekStart' => $weekStart,
            'days' => $days,
            'courseCount' => $weekCourses->count(),
            'hours' => round($weekCourses->sum(fn (Course $course): int => $course->durationInMinutes()) / 60, 1),
        ]);
    }

    /**
     * Position the courses of a day on the 08:00 to 20:00 grid. Overlapping courses share the width.
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

            $width = 100 / max(count($columnEnds), 1);

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
}
