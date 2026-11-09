<?php

use App\Enums\CourseChange;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;

test('the course email escapes user-provided content', function () {
    $professor = User::factory()->professor()->create([
        'name' => "O'Reilly <script>alert('professor')</script>",
    ]);

    $course = Course::factory()->for($professor, 'professor')->create([
        'title' => '<script>alert("title")</script>',
        'room' => '<script>alert("room")</script>',
        'day_of_week' => Weekday::Monday,
    ]);

    $student = User::factory()->student()->create([
        'name' => "<script>alert('student')</script>",
    ]);

    $html = (string) (new CourseScheduleNotification($course, CourseChange::Created))
        ->toMail($student)
        ->render();

    expect($html)
        ->toContain('&lt;script&gt;')
        ->not->toContain('<script>alert("title")</script>')
        ->not->toContain('<script>alert("room")</script>')
        ->not->toContain("<script>alert('professor')</script>")
        ->not->toContain("<script>alert('student')</script>");
});
