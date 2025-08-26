<div>
    <div class="container">
        <h2>User Activity Timeline</h2>
        <ul class="timeline">
            @foreach ($activities as $activity)
            <li class="timeline-item">
                <span class="badge bg-primary">{{ $activity->created_at->format('d M, Y H:i') }}</span>
                <p>
                    <strong>{{ $activity->log_name }}</strong>:
                    {{ $activity->description }}
                </p>
                <small class="text-muted">
                    Caused by: {{ $activity->causer ? $activity->causer->name : 'System' }}
                </small>
            </li>
            @endforeach
        </ul>
    </div>
</div>