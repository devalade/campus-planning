<?php

namespace App\Models;

use App\Enums\Weekday;
use Carbon\CarbonImmutable;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $professor_id
 * @property string $title
 * @property string $room
 * @property Weekday $day_of_week
 * @property string $starts_at
 * @property string $ends_at
 * @property User $professor
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['professor_id', 'title', 'room', 'day_of_week', 'starts_at', 'ends_at'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => Weekday::class,
        ];
    }

    /**
     * Normalize the start time before storing it.
     *
     * @return Attribute<string, string>
     */
    protected function startsAt(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => CarbonImmutable::parse($value)->format('H:i:s'),
        );
    }

    /**
     * Normalize the end time before storing it.
     *
     * @return Attribute<string, string>
     */
    protected function endsAt(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => CarbonImmutable::parse($value)->format('H:i:s'),
        );
    }

    /**
     * Get the professor teaching the course.
     *
     * @return BelongsTo<User, $this>
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * Get the students enrolled in the course.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student', 'course_id', 'user_id');
    }

    /**
     * Limit the query to the courses visible to the given user.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isProfessor()) {
            return $query->where('professor_id', $user->id);
        }

        return $query->whereHas(
            'students',
            fn (Builder $query): Builder => $query->whereKey($user->id),
        );
    }

    /**
     * Get the formatted start time.
     */
    public function formattedStartTime(): string
    {
        return CarbonImmutable::parse($this->starts_at)->format('H:i');
    }

    /**
     * Get the formatted end time.
     */
    public function formattedEndTime(): string
    {
        return CarbonImmutable::parse($this->ends_at)->format('H:i');
    }

    /**
     * Get the start time in minutes since midnight.
     */
    public function startMinutes(): int
    {
        $time = CarbonImmutable::parse($this->starts_at);

        return ($time->hour * 60) + $time->minute;
    }

    /**
     * Get the end time in minutes since midnight.
     */
    public function endMinutes(): int
    {
        $time = CarbonImmutable::parse($this->ends_at);

        return ($time->hour * 60) + $time->minute;
    }

    /**
     * Get the course duration in minutes.
     */
    public function durationInMinutes(): int
    {
        return $this->endMinutes() - $this->startMinutes();
    }
}
