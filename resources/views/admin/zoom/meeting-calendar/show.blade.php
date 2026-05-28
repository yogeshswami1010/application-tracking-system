@extends('layouts.app')

@section('content')
@php
    $nowDate = \Carbon\Carbon::now()->toDateString();
    $isCreator = user()->id == $event->created_by;
    $meetingDate = $event->start_date_time ? $event->start_date_time->toDateString() : null;

    $categoryName = $event->category?->category_name;

    $startUrl = null;
    if ($zoomSetting?->meeting_app == 'in_app' && $isCreator) {
        $startUrl = route('admin.zoom-meeting.startMeeting', $event->id);
    } else {
        $startUrl = $event->start_link;
    }

    $canStart =
        $isCreator
        && $event->status === 'waiting'
        && (is_null($event->occurrence_id) || $nowDate === $meetingDate);

    $canJoin =
        ! $isCreator
        && ($event->status === 'waiting' || $event->status === 'live')
        && (is_null($event->occurrence_id) || $nowDate === $meetingDate);
@endphp

<div class="min-h-screen bg-gray-50">
    {{-- Top Nav Bar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.zoom-meeting.index') }}"
                   class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <div class="h-5 w-px bg-gray-200"></div>
                <nav class="flex items-center gap-1.5 text-sm text-gray-500">
                    <span class="hover:text-gray-800 transition-colors">{{ __('modules.zoommeeting.tableView') }}</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-medium">{{ $event->meeting_name }}</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.zoom-meeting.edit', $event->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fa fa-edit text-[13px]"></i>
                    {{ __('app.edit') }}
                </a>

                @if($canStart)
                    <a href="{{ $startUrl }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
                        <i class="fa fa-play text-[13px]"></i>
                        {{ __('modules.zoommeeting.startMeeting') }}
                    </a>
                @elseif($canJoin)
                    <a href="{{ $event->join_link }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
                        <i class="fa fa-sign-in text-[13px]"></i>
                        {{ __('modules.zoommeeting.joinUrl') }}
                    </a>
                @endif
            </div>
        </div>
    </header>

    {{-- Page Body --}}
    <main class="max-w-6xl mx-auto px-6 py-10">
        {{-- Page Title Row --}}
        <div class="flex items-start justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $event->meeting_name }}</h1>
                    @if($event->status === 'waiting')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                            {{ __('modules.zoommeeting.waiting') }}
                        </span>
                    @elseif($event->status === 'live')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                            {{ __('modules.zoommeeting.live') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                            {{ __('app.finished') }}
                        </span>
                    @endif
                </div>

                @if(! empty($event->description))
                    <p class="text-gray-500 text-base max-w-2xl">{{ $event->description }}</p>
                @endif
            </div>
        </div>

        {{-- Two-column layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Main Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Time & Schedule --}}
                <section>
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ __('modules.zoommeeting.startOn') }}</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                                    <i class="fa fa-clock text-blue-600 text-[14px]"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-500">{{ __('modules.zoommeeting.startOn') }}</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                {{ optional($event->start_date_time)->format('d M Y') ?? '—' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ optional($event->start_date_time)->format('h:i A') ?? '' }}
                            </p>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
                                    <i class="fa fa-clock text-red-500 text-[14px]"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-500">{{ __('modules.zoommeeting.endOn') }}</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                {{ optional($event->end_date_time)->format('d M Y') ?? '—' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ optional($event->end_date_time)->format('h:i A') ?? '' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Meeting Settings --}}
                <section>
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ __('modules.zoommeeting.meetingDetails') }}</h2>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="divide-y divide-gray-50">
                            <div class="flex items-center justify-between px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                                        <i class="fa fa-video text-gray-400 text-[14px]"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ __('modules.zoommeeting.hostVideoStatus') }}</span>
                                </div>
                                @if($event->host_video)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                                        {{ __('app.enable') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                                        {{ __('app.disable') }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                                        <i class="fa fa-desktop text-gray-400 text-[14px]"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ __('modules.zoommeeting.participantVideoStatus') }}</span>
                                </div>
                                @if($event->participant_video)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                                        {{ __('app.enable') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                                        {{ __('app.disable') }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                                        <i class="fa fa-tags text-gray-400 text-[14px]"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ __('modules.zoommeeting.categoryName') }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $categoryName ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Attendees --}}
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest">{{ __('modules.zoommeeting.viewAttendees') }}</h2>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        @forelse($event->attendees->take(5) as $attendee)
                            <div class="flex items-center gap-4 px-6 py-3.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-sm font-semibold text-blue-700 flex-shrink-0 overflow-hidden">
                                    @if(! is_null($attendee->image))
                                        <img src="{{ $attendee->image_url }}" alt="{{ $attendee->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($attendee->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $attendee->name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $attendee->email }}</p>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 font-medium">
                                    Invited
                                </span>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center">
                            <p class="text-sm text-gray-400">No attendees yet</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-6">
                {{-- Host --}}
                <section>
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ __('modules.zoommeeting.meetingHost') }}</h2>
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden bg-blue-100 flex-shrink-0">
                                @if(! is_null($event->host?->image))
                                    <img src="{{ $event->host->image_url }}" alt="{{ $event->host->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-base font-semibold text-blue-700">
                                        {{ strtoupper(substr($event->host?->name ?? '', 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $event->host?->name ?? '--' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $event->host?->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Access --}}
                <section>
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ __('modules.zoommeeting.meetingPassword') }}</h2>
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">{{ __('modules.zoommeeting.meetingPassword') }}</p>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 font-mono text-sm font-semibold text-gray-900 tracking-widest bg-gray-50 px-3 py-2 rounded-xl border border-gray-100">
                                    {{ $event->password ?? '—' }}
                                </code>
                                @if(! empty($event->password))
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $event->password }}')"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
                                            title="Copy password">
                                        <i class="fa fa-copy text-[13px]"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Actions --}}
                <section>
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Actions</h2>
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3">
                        @if($canStart)
                            <a href="{{ $startUrl }}" target="_blank"
                               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                                <i class="fa fa-play"></i>
                                {{ __('modules.zoommeeting.startMeeting') }}
                            </a>
                        @elseif($canJoin)
                            <a href="{{ $event->join_link }}" target="_blank"
                               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                                <i class="fa fa-sign-in"></i>
                                {{ __('modules.zoommeeting.joinUrl') }}
                            </a>
                        @else
                            <div class="text-sm text-gray-500">{{ __('modules.zoommeeting.meetingDetails') }}</div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection
