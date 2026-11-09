<?php

namespace App\Notifications;

use App\Enums\CourseChange;
use App\Models\Course;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseScheduleNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Course $course,
        public CourseChange $change,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        $this->course->loadMissing('professor');

        $heading = match ($this->change) {
            CourseChange::Created => 'Nouveau cours : '.$this->course->title,
            CourseChange::Updated => 'Cours modifié : '.$this->course->title,
            CourseChange::Cancelled => 'Cours annulé : '.$this->course->title,
        };

        return (new MailMessage)
            ->subject($heading)
            ->markdown('mail.course-schedule', [
                'course' => $this->course,
                'change' => $this->change,
                'heading' => $heading,
                'recipientName' => $notifiable->name,
            ]);
    }
}
