<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->isProfessor()) {
            return $course->professor_id === $user->id;
        }

        return $course->students()->whereKey($user->id)->exists();
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->isProfessor();
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->isProfessor() && $course->professor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->isProfessor() && $course->professor_id === $user->id;
    }
}
