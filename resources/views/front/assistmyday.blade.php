
@php
    $companyShort = 'AssistMyDay';
@endphp

@section('content')

{{-- ── HERO ── --}}
<section class="relative px-6 pt-20 pb-24 overflow-hidden" style="background: linear-gradient(135deg, #0c1929 0%, #1a3a5c 50%, #0f2b47 100%);">
    {{-- Decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full opacity-[0.07]" style="background: radial-gradient(circle, #38bdf8, transparent 70%);"></div>
        <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] rounded-full opacity-[0.05]" style="background: radial-gradient(circle, #818cf8, transparent 70%);"></div>
    </div>

    <div class="max-w-5xl mx-auto text-center relative z-10">
        {{-- Logo / Brand --}}
        <div class="inline-flex items-center gap-3 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-sky-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-white tracking-tight">Assist<span class="text-sky-400">My</span>Day</span>
        </div>

        <h1 class="text-white font-extrabold leading-[1.08] tracking-[-0.03em] mb-5" style="font-size: clamp(34px, 5vw, 56px);">
            Find Your Next
            <span class="bg-gradient-to-r from-sky-300 to-indigo-300 bg-clip-text text-transparent">Opportunity</span>
        </h1>
        <p class="text-white/50 text-[17px] leading-relaxed max-w-2xl mx-auto mb-10">
            Browse our current openings across multiple locations and categories. Your next career move starts here.
        </p>

        {{-- Search bar --}}
        <div class="max-w-3xl mx-auto bg-white/[0.08] backdrop-blur-sm border border-white/[0.1] rounded-2xl p-2 flex flex-wrap gap-2 items-center">
            <select class="amd-select flex-1 min-w-[140px]" name="location" id="amd-location">
                <option value="all">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ ucfirst($location->location) }}</option>
                @endforeach
            </select>
            <select class="amd-select flex-1 min-w-[140px]" name="category" id="amd-category">
                <option value="all">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                @endforeach
            </select>
            <button type="button" id="amd-search" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white font-semibold rounded-xl px-6 py-3 text-[14px] transition-all shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search Jobs
            </button>
        </div>

        {{-- Stats --}}
        <div class="mt-10 flex items-center justify-center flex-wrap gap-6">
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/[0.06] border border-white/[0.08]">
                <span class="text-white font-bold text-[16px]">{{ $jobCount }}</span>
                <span class="text-white/40 text-[13px]">Open Positions</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/[0.06] border border-white/[0.08]">
                <span class="text-white font-bold text-[16px]">{{ $locations->count() }}</span>
                <span class="text-white/40 text-[13px]">Locations</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/[0.06] border border-white/[0.08]">
                <span class="text-white font-bold text-[16px]">{{ $categories->count() }}</span>
                <span class="text-white/40 text-[13px]">Categories</span>
            </div>
        </div>
    </div>
</section>

{{-- ── JOB LISTINGS ── --}}
<section class="px-6 py-20 bg-[#F7F8FC]">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="text-sky-600 text-[12px] font-bold uppercase tracking-[0.15em] mb-2">Current Openings</p>
            <h2 class="font-bold tracking-[-0.02em] text-[#1A1E2E]" style="font-size: clamp(26px, 3.5vw, 40px);">
                Browse All <span class="font-serif italic font-normal text-sky-600">Positions</span>
            </h2>
            <div class="w-12 h-[3px] rounded-full bg-sky-500 mx-auto mt-4"></div>
        </div>

        <div id="amd-job-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($jobLocations as $index => $location)
                @php
                    $colors = [
                        ['bar' => '#2563EB', 'bg' => '#EFF6FF', 'fg' => '#2563EB'],
                        ['bar' => '#059669', 'bg' => '#ECFDF5', 'fg' => '#059669'],
                        ['bar' => '#7C3AED', 'bg' => '#F5F3FF', 'fg' => '#7C3AED'],
                        ['bar' => '#D97706', 'bg' => '#FFFBEB', 'fg' => '#D97706'],
                    ];
                    $c = $colors[$index % 4];
                @endphp
                <div class="amd-job-item" data-groups="{{ optional($location->location)->location }},{{ optional($location->job->category)->name }}">
                    <a href="{{ route('jobs.jobDetail', [$location->job->slug, optional($location->location)->id]) }}"
                       class="group block bg-white rounded-2xl border border-[#E8E6E1] overflow-hidden transition-all duration-200 hover:shadow-[0_12px_32px_rgba(15,31,61,0.1)] hover:-translate-y-0.5">
                        {{-- Color bar --}}
                        <div class="h-1 w-full" style="background: {{ $c['bar'] }};"></div>

                        <div class="p-5">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $c['bg'] }};">
                                    <svg class="w-5 h-5" style="color: {{ $c['fg'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @if($location->job->jobType)
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }};">
                                        {{ ucfirst($location->job->jobType->job_type) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h3 class="font-bold text-[17px] leading-snug tracking-[-0.01em] text-[#1A1E2E] group-hover:text-sky-600 transition-colors mb-1">
                                {{ ucwords($location->job->title) }}
                            </h3>

                            @if($location->job->company->show_in_frontend == 'true')
                                <p class="text-[13px] font-medium text-[#8892A0] mb-4">
                                    {{ ucwords($location->job->company->company_name) }}
                                </p>
                            @else
                                <p class="text-[13px] mb-4">&nbsp;</p>
                            @endif

                            {{-- Footer --}}
                            <div class="flex items-center justify-between pt-4 border-t border-[#F0EEE9]">
                                <span class="flex items-center gap-1.5 text-[13px] text-[#8892A0]">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ ucwords(optional($location->location)->location ?? 'N/A') }}
                                </span>
                                @if($location->job->category)
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }};">
                                        {{ ucwords($location->job->category->name) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-[#C4CBD4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h4 class="text-xl font-semibold text-[#8892A0]">No open positions right now</h4>
                    <p class="text-[14px] text-[#B0B8C4] mt-2">Check back soon for new opportunities.</p>
                </div>
            @endforelse
        </div>

        {{-- Load more --}}
        <div class="text-center mt-12">
            <button type="button" id="amd-load-more"
                    class="inline-flex items-center gap-2 font-semibold border rounded-xl px-7 py-3 text-[13px] transition-all duration-200 text-[#3D5273] border-[#E2DFD8] hover:bg-white hover:border-sky-300 hover:text-sky-600"
                    style="{{ $jobCount > ($perPage ?? 12) ? '' : 'display:none;' }}">
                Load More Positions
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="px-6 py-16" style="background: linear-gradient(135deg, #0c1929 0%, #1a3a5c 100%);">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-sky-300 text-[12px] font-bold uppercase tracking-[0.15em] mb-3">Stay Connected</p>
        <h2 class="font-bold tracking-[-0.02em] leading-tight mb-3 text-white" style="font-size: clamp(24px, 3vw, 34px);">
            Don't see the right role?
        </h2>
        <p class="text-white/45 text-[15px] leading-relaxed mb-8 max-w-md mx-auto">
            New positions are added regularly. Sign up for alerts and be the first to know.
        </p>
        @if($global->job_alert_status == 1)
            <button type="button" id="amd-alert-cta" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white font-semibold rounded-xl px-8 py-3 text-[14px] transition-all shadow-lg shadow-sky-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Get Job Alerts
            </button>
        @endif
    </div>
</section>

@endsection

@push('footer-script')
<style>
    .amd-select {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        appearance: none;
        cursor: pointer;
        transition: border-color 0.15s;
    }
    .amd-select:focus { border-color: rgba(56,189,248,0.5); }
    .amd-select option { color: #1A1E2E; background: #fff; }
</style>
<script>
$(document).ready(function(){
    var perPage = {{ $perPage ?? 12 }};
    var totalCurrentData = perPage;

    $('#amd-alert-cta').on('click', function() {
        if ($('#job-alert').length) $('#job-alert').trigger('click');
    });

    $('#amd-load-more').on('click', function() {
        var location_id = $('#amd-location').val();
        var category = $('#amd-category').val();
        var token = '{{ csrf_token() }}';
        $(this).html('<b>Loading...</b>');
        $.easyAjax({
            url: "{{ route('jobs.more-data') }}",
            type: 'POST',
            data: { _token: token, totalCurrentData: totalCurrentData, location_id: location_id, category: category, skill: 'all', company: 'all' },
            success: function(response) {
                $('#amd-job-list').append(response.view);
                totalCurrentData = response.data.job_current_count;
                $('#amd-load-more').blur().html('Load More Positions <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
                if (response.data.hideButton === 'yes') $('#amd-load-more').hide();
                else $('#amd-load-more').show();
            }
        });
    });

    $('#amd-search').on('click', function(e) {
        e.preventDefault();
        var location_id = $('#amd-location').val();
        var category = $('#amd-category').val();
        var token = '{{ csrf_token() }}';
        $.easyAjax({
            url: "{{ route('jobs.search-job') }}",
            type: 'POST',
            data: { _token: token, location_id: location_id, category: category, skill: 'all', company: 'all' },
            success: function(response) {
                $('#amd-job-list').html(response.view);
                totalCurrentData = response.data.job_current_count;
                $([document.documentElement, document.body]).animate({ scrollTop: $('#amd-job-list').offset().top - 80 }, 600);
                if (response.data.hideButton === 'yes') $('#amd-load-more').hide();
                else $('#amd-load-more').show();
            }
        });
    });
});
</script>
@endpush