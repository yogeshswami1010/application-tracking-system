@foreach($applications as $application)
    @include('admin.job-applications.partials.board-card', [
        'application' => $application,
        'columnId' => $application->status->id,
        'columnColor' => $application->status->color,
        'statusSlug' => $application->status->status,
    ])
@endforeach
