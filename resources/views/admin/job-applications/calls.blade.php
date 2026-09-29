@extends('layouts.app')

@section('page-title-html')
    <span class="text-[22px] font-bold">Candidate calls</span>
@endsection

@section('content')
<div class="mx-auto max-w-4xl space-y-6 pb-10" id="candidate-calls"
     data-start="{{ route('admin.candidate-calls.start', $application->id) }}"
     data-token="{{ csrf_token() }}">
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold">{{ $application->full_name }}</h2>
        <p class="my-2 text-gray-600">{{ $application->phone ?: 'No phone number saved' }}</p>
        @if($canCall)
        <p class="mb-4 text-sm text-gray-600">Call using your microphone and headphones. After the candidate agrees, start recording to generate a summary. Keep this page open until the summary is saved.</p>
        <div class="flex flex-wrap gap-3">
            <button type="button" id="call-start" class="rounded-lg bg-blue-600 px-4 py-2 text-white" @disabled(!$application->phone)>Call candidate</button>
            <button type="button" id="call-mute" class="rounded-lg border px-4 py-2" disabled>Mute</button>
            <button type="button" id="call-end" class="rounded-lg bg-red-600 px-4 py-2 text-white" disabled>End call</button>
        </div>
        <div id="call-consent" class="mt-4" hidden>
            <label><input type="checkbox" id="call-consent-check"> The candidate agreed to recording and AI transcription.</label>
            <button type="button" id="call-record" class="ml-3 rounded-lg border px-4 py-2">Start recording</button>
        </div>
        <p id="call-status" class="mt-4 text-sm" role="status" aria-live="polite">Ready to call.</p>
        <button type="button" id="call-retry-upload" class="mt-3 rounded-lg border px-4 py-2" hidden>Retry saving call</button>
        <audio id="call-remote" autoplay></audio>
        @endif
    </div>
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-bold">Call history</h2>
        <p class="mb-4 text-sm text-gray-600">AI summaries cover the recorded portion of each call. Review them against the transcript.</p>
        @forelse($calls as $call)
        <article class="mb-4 rounded-lg border p-4">
            <div class="flex flex-wrap justify-between gap-2 text-sm">
                <strong>{{ $call->created_at->format('M j, Y H:i') }} · {{ $call->phone }} · Called by {{ $call->user?->name ?? 'Unknown team member' }}</strong>
                <span>{{ str_replace('_', ' ', $call->status) }} · {{ gmdate('i:s', $call->duration_seconds) }}</span>
            </div>
            @if($call->summary)
                <p class="mt-3 whitespace-pre-wrap">{{ $call->summary }}</p>
            @elseif($call->status === 'not_recorded')
                <p class="mt-3 text-sm text-gray-500">No recording was saved; an automatic summary is unavailable.</p>
            @elseif($call->status === 'initiated')
                <p class="mt-3 text-sm text-gray-500">Call started; no completed recording has been received.</p>
            @else
                <p class="mt-3 text-sm text-gray-500">Recording saved. Summary {{ $call->status === 'failed' ? 'needs retry' : 'pending' }}.</p>
            @endif
            @if($call->transcript)
                <details class="mt-3 text-sm"><summary class="cursor-pointer">View transcript</summary><p class="mt-2 whitespace-pre-wrap">{{ $call->transcript }}</p></details>
            @endif
            @if($canCall && $call->user_id === $user->id && in_array($call->status, ['pending', 'failed', 'processing']))
                <button type="button" class="call-process mt-3 rounded-lg border px-3 py-2 text-sm" data-url="{{ route('admin.candidate-calls.process', [$application->id, $call->id]) }}">Generate / retry summary</button>
            @endif
        </article>
        @empty
            <p class="text-gray-500">No calls yet.</p>
        @endforelse
        {{ $calls->links() }}
    </div>
</div>
@endsection

@push('footer-script')
<script src="https://unpkg.com/@telnyx/webrtc@2.9.0/lib/bundle.js"></script>
<script src="{{ asset('js/candidate-calls.js') }}"></script>
@endpush
