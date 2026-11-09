<?php

use App\Enums\CourseChange;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseScheduleNotification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use RuntimeException;

test('a professor can schedule a course and enroll students', function () {
    $professor = User::factory()->professor()->create();
    $alice = User::factory()->student()->create();
    $bob = User::factory()->student()->create();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->call('openCreateModal')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Wednesday->value)
        ->set('startTime', '10:00')
        ->set('endTime', '12:00')
        ->set('studentIds', [(string) $alice->id, (string) $bob->id])
        ->call('saveCourse')
        ->assertHasNoErrors()
        ->assertSet('showCourseModal', false);

    $course = Course::query()->sole();

    expect($course->title)->toBe('Algorithmique avancée')
        ->and($course->room)->toBe('Amphi B')
        ->and($course->day_of_week)->toBe(Weekday::Wednesday)
        ->and($course->formattedStartTime())->toBe('10:00')
        ->and($course->formattedEndTime())->toBe('12:00')
        ->and($course->professor->is($professor))->toBeTrue()
        ->and($course->students->pluck('id')->all())->toEqualCanonicalizing([$alice->id, $bob->id]);
});

test('scheduling a course emails the professor and every enrolled student', function () {
    $professor = User::factory()->professor()->create();
    $alice = User::factory()->student()->create();
    $bob = User::factory()->student()->create();

    Notification::fake();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Wednesday->value)
        ->set('startTime', '10:00')
        ->set('endTime', '12:00')
        ->set('studentIds', [(string) $alice->id, (string) $bob->id])
        ->call('saveCourse')
        ->assertHasNoErrors();

    $course = Course::query()->sole();

    Notification::assertSentTo(
        [$professor, $alice, $bob],
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->course->is($course)
            && $notification->change === CourseChange::Created,
    );
    Notification::assertCount(3);
});

test('a course is saved when email delivery fails', function () {
    $professor = User::factory()->professor()->create();

    $this->mock(ChannelManager::class)
        ->shouldReceive('send')
        ->andThrow(new RuntimeException('Mail is down.'));

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '08:00')
        ->set('endTime', '10:00')
        ->call('saveCourse')
        ->assertHasNoErrors();

    expect(Course::count())->toBe(1);
});

test('students cannot schedule courses', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student);

    Livewire::test('pages::calendar')
        ->set('title', 'Cours non autorisé')
        ->set('room', 'Amphi A')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '08:00')
        ->set('endTime', '10:00')
        ->call('saveCourse')
        ->assertForbidden();

    expect(Course::count())->toBe(0);
});

test('a course requires a title, a room and a time slot', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', '')
        ->set('room', '')
        ->set('startTime', '')
        ->set('endTime', '')
        ->call('saveCourse')
        ->assertHasErrors(['title', 'room', 'startTime', 'endTime']);

    expect(Course::count())->toBe(0);
});

test('a course must use one of the available rooms', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Salle inconnue')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '08:00')
        ->set('endTime', '10:00')
        ->call('saveCourse')
        ->assertHasErrors(['room' => 'in']);

    expect(Course::count())->toBe(0);
});

test('a course must end after it starts', function () {
    $professor = User::factory()->professor()->create();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '12:00')
        ->set('endTime', '11:00')
        ->call('saveCourse')
        ->assertHasErrors(['endTime' => 'after']);

    expect(Course::count())->toBe(0);
});

test('a course cannot enroll another professor', function () {
    $professor = User::factory()->professor()->create();
    $otherProfessor = User::factory()->professor()->create();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '08:00')
        ->set('endTime', '10:00')
        ->set('studentIds', [(string) $otherProfessor->id])
        ->call('saveCourse')
        ->assertHasErrors(['studentIds.0' => 'exists']);

    expect(Course::count())->toBe(0);
});

test('a professor cannot schedule two courses at the same time', function () {
    $professor = User::factory()->professor()->create();

    Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Réseaux informatiques')
        ->set('room', 'Amphi A')
        ->set('dayOfWeek', Weekday::Monday->value)
        ->set('startTime', '09:00')
        ->set('endTime', '11:00')
        ->call('saveCourse')
        ->assertHasErrors(['startTime']);

    expect(Course::count())->toBe(1);
});

test('a professor can update their course and participants are emailed', function () {
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();

    $course = Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'day_of_week' => Weekday::Monday,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
    ]);

    $course->students()->attach($student);

    Notification::fake();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->call('editCourse', $course->id)
        ->assertSet('title', 'Algorithmique avancée')
        ->set('title', 'Algorithmique et complexité')
        ->set('room', 'Amphi C')
        ->call('saveCourse')
        ->assertHasNoErrors();

    expect($course->refresh()->title)->toBe('Algorithmique et complexité')
        ->and($course->room)->toBe('Amphi C');

    Notification::assertSentTo(
        [$professor, $student],
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->course->is($course)
            && $notification->change === CourseChange::Updated,
    );
});

test('a professor cannot edit a course taught by another professor', function () {
    $intruder = User::factory()->professor()->create();
    $course = Course::factory()->create();

    $this->actingAs($intruder);

    Livewire::test('pages::calendar')
        ->call('editCourse', $course->id)
        ->assertForbidden();
});

test('a professor can cancel their course and its students are emailed', function () {
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();

    $course = Course::factory()->for($professor, 'professor')->create();
    $course->students()->attach($student);

    Notification::fake();

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->call('deleteCourse', $course->id)
        ->assertSet('showCourseModal', false);

    expect($course->fresh())->toBeNull();

    Notification::assertSentTo(
        $student,
        CourseScheduleNotification::class,
        fn (CourseScheduleNotification $notification): bool => $notification->change === CourseChange::Cancelled,
    );
    Notification::assertNotSentTo($professor, CourseScheduleNotification::class);
});

test('students cannot delete a course', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();
    $course->students()->attach($student);

    $this->actingAs($student);

    Livewire::test('pages::calendar')
        ->call('deleteCourse', $course->id)
        ->assertForbidden();

    expect($course->fresh())->not->toBeNull();
});
