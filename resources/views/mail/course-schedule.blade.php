<x-mail::message>
# {{ $heading }}

Bonjour {{ $recipientName }},

@if ($change === \App\Enums\CourseChange::Cancelled)
Le cours suivant est annulé et ne sera pas assuré.
@elseif ($change === \App\Enums\CourseChange::Updated)
Un cours de votre emploi du temps vient d'être modifié.
@else
Un nouveau cours a été ajouté à votre emploi du temps.
@endif

- **Cours :** {{ $course->title }}
- **Jour :** {{ $course->day_of_week->label() }}
- **Horaire :** {{ $course->formattedStartTime() }} - {{ $course->formattedEndTime() }}
- **Salle :** {{ $course->room }}
- **Professeur :** {{ $course->professor->name }}

<x-mail::button :url="route('dashboard')">
Voir mon emploi du temps
</x-mail::button>

À bientôt,
{{ config('app.name') }}
</x-mail::message>
