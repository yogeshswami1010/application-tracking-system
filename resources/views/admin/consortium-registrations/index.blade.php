@extends('layouts.app')
@section('page-title-html') Consortium <em>Registrations</em> @endsection
@section('page-subtitle') Candidate registrations submitted from consortiumstaffing.ca @endsection
@section('content')
<div class="rounded-2xl border border-[#E8E6E1] bg-white shadow-sm overflow-hidden"><div class="flex items-center justify-between border-b border-[#EEECE8] px-5 py-4"><div><h2 class="text-[15px] font-bold text-[#1A1E2E]">Registration Submissions</h2><p class="text-[11px] text-[#8892A0]">{{ $unreviewedCount }} awaiting review</p></div><div class="flex items-center gap-2">@if(request()->hasAny(['month','year','gender','city','job_type','available_weekends','night_shifts']))<a href="{{ route('admin.consortium-registrations.index') }}" class="rounded-lg border border-[#DDE2EA] bg-white px-3 py-1.5 text-[10.5px] font-bold text-[#5A6478] hover:bg-[#F1F3F7]">Reset Filters</a>@endif<span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-600">{{ $registrations->total() }} total</span></div></div>
<form id="consortium-registration-filters" method="GET" action="{{ route('admin.consortium-registrations.index') }}" class="border-b border-[#EEECE8] bg-[#FBFCFE] px-5 py-4">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Submitted Month</label><select name="month" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All Months</option>@foreach($filterMonths as $month)<option value="{{ $month }}" @selected((int) request('month') === (int) $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>@endforeach</select></div>
        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Submitted Year</label><select name="year" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All Years</option>@foreach($filterYears as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach</select></div>
        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Gender</label><select name="gender" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All Genders</option>@foreach($filterGenders as $gender)<option value="{{ $gender }}" @selected(request('gender') === $gender)>{{ $gender }}</option>@endforeach</select></div>
        @php($selectedCities = array_values(array_filter((array) request('city', []))))
        <div class="relative" id="registration-city-filter">
            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">City</label>
            <button type="button" id="registration-city-toggle" class="flex w-full items-center justify-between rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-left text-[12px] text-[#3D4A5C] outline-none hover:border-[#BFC7D3] focus:border-blue-500">
                <span id="registration-city-summary" class="truncate" title="{{ count($selectedCities) ? implode(', ', $selectedCities) : 'All Cities' }}">{{ count($selectedCities) ? implode(', ', $selectedCities) : 'All Cities' }}</span>
                <svg class="h-3.5 w-3.5 shrink-0 text-[#8892A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div id="registration-city-menu" class="absolute left-0 top-full z-50 mt-2 hidden w-[300px] overflow-hidden rounded-xl border border-[#DDE2EA] bg-white shadow-xl">
                <div class="border-b border-[#EEF0F4] p-2.5"><input type="search" id="registration-city-search" placeholder="Search city..." autocomplete="off" class="w-full rounded-lg border border-[#DDE2EA] bg-[#F8F9FB] px-3 py-2 text-[12px] outline-none focus:border-blue-500"></div>
                <div id="registration-city-options" class="max-h-60 overflow-y-auto p-2">
                    @foreach($filterCities as $city)
                        <label class="registration-city-option flex cursor-pointer items-center gap-2.5 rounded-lg border px-2.5 py-2 text-[12px] transition {{ in_array($city, $selectedCities, true) ? 'border-blue-200 bg-blue-50 font-semibold text-blue-700' : 'border-transparent text-[#3D4A5C] hover:bg-[#F1F5FF]' }}" data-city-name="{{ strtolower($city) }}">
                            <input type="checkbox" name="city[]" value="{{ $city }}" @checked(in_array($city, $selectedCities, true)) class="h-4 w-4 rounded border-[#C8D0DC] text-blue-600 focus:ring-blue-500">
                            <span>{{ $city }}</span>
                        </label>
                    @endforeach
                    <p id="registration-city-empty" class="hidden px-3 py-6 text-center text-[11px] text-[#8892A0]">No city found.</p>
                </div>
                <div class="flex items-center justify-between border-t border-[#EEF0F4] bg-[#FBFCFE] px-3 py-2.5">
                    <button type="button" id="registration-city-clear" class="text-[11px] font-semibold text-[#6B7280] hover:text-[#DC2626]">Clear</button>
                    <button type="button" id="registration-city-apply" class="rounded-lg bg-[#2563EB] px-3 py-2 text-[11px] font-bold text-white hover:bg-[#1D4ED8]">Apply Cities</button>
                </div>
            </div>
        </div>
        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Job Type</label><select name="job_type" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All Job Types</option>@foreach($filterJobTypes as $jobType)<option value="{{ $jobType }}" @selected(request('job_type') === $jobType)>{{ $jobType }}</option>@endforeach</select></div>        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Available Weekends</label><select name="available_weekends" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All</option><option value="1" @selected(request('available_weekends') === '1')>Yes</option><option value="0" @selected(request('available_weekends') === '0')>No</option></select></div>
        <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[#8892A0]">Night Shifts</label><select name="night_shifts" class="registration-auto-filter w-full rounded-xl border border-[#DDE2EA] bg-white px-3 py-2.5 text-[12px] text-[#3D4A5C] outline-none focus:border-blue-500"><option value="">All</option><option value="1" @selected(request('night_shifts') === '1')>Yes</option><option value="0" @selected(request('night_shifts') === '0')>No</option></select></div>
        
    </div>
</form><div class="overflow-x-auto"><table class="w-full text-left"><thead class="bg-[#F8F9FB] text-[10px] uppercase tracking-wider text-[#8892A0]"><tr><th class="px-5 py-3">Candidate</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">City</th><th class="px-5 py-3">Eligibility</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3 text-right">Action</th></tr></thead><tbody>
@forelse($registrations as $registration)<tr class="border-t border-[#F0EEE9] hover:bg-[#FBFCFE]"><td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-[12px] font-bold text-blue-600">{{ strtoupper(substr($registration->first_name,0,1).substr($registration->last_name,0,1)) }}</span><div><a href="{{ route('admin.consortium-registrations.show', $registration) }}" class="text-[13px] font-semibold text-[#1A1E2E] transition hover:text-[#2563EB] hover:underline">{{ $registration->first_name }} {{ $registration->last_name }}</a>@if(!$registration->reviewed_at)<span class="text-[9px] font-bold uppercase text-orange-500">New</span>@endif</div></div></td><td class="px-5 py-4 text-[12px] text-[#5A6478]">{{ $registration->email }}<br>{{ $registration->phone }}</td><td class="px-5 py-4 text-[12px] text-[#5A6478]">{{ $registration->city }}</td><td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $registration->eligible_to_work_canada ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">{{ $registration->eligible_to_work_canada ? 'Eligible' : 'Not eligible' }}</span></td><td class="px-5 py-4 text-[11px] text-[#8892A0]">{{ $registration->created_at->timezone($global->timezone)->format('M j, Y g:i A') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('admin.consortium-registrations.show',$registration) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-[11px] font-bold text-blue-600 hover:bg-blue-100">View Details</a></td></tr>
@empty<tr><td colspan="6" class="px-5 py-16 text-center text-sm text-[#8892A0]">No registrations received yet.</td></tr>@endforelse
</tbody></table></div><div class="border-t border-[#EEECE8] px-5 py-4">{{ $registrations->links() }}</div></div>
@endsection

@push('footer-script')
<script>
$(document).on('change', '.registration-auto-filter', function () {
    $('#consortium-registration-filters').trigger('submit');
});
$('#registration-city-toggle').on('click', function (event) {
    event.stopPropagation();
    $('#registration-city-menu').toggleClass('hidden');
    if (!$('#registration-city-menu').hasClass('hidden')) $('#registration-city-search').trigger('focus');
});
$('#registration-city-menu').on('click', function (event) { event.stopPropagation(); });
$(document).on('click', function () { $('#registration-city-menu').addClass('hidden'); });
$('#registration-city-search').on('input', function () {
    var query = $.trim($(this).val()).toLowerCase();
    var visible = 0;
    $('.registration-city-option').each(function () {
        var matches = !query || String($(this).data('city-name')).indexOf(query) !== -1;
        $(this).toggleClass('hidden', !matches);
        if (matches) visible++;
    });
    $('#registration-city-empty').toggleClass('hidden', visible > 0);
});
$('#registration-city-clear').on('click', function () {
    $('#registration-city-options input[type="checkbox"]').prop('checked', false);
    updateSelectedCities();
});
var cityPageScrollTop = null;
var cityOptionsScrollTop = null;
$('#registration-city-options').on('mousedown', 'input[type="checkbox"], label', function () {
    cityPageScrollTop = $(window).scrollTop();
    cityOptionsScrollTop = $('#registration-city-options').scrollTop();
});
function updateSelectedCities() {
    var pageScrollTop = cityPageScrollTop === null ? $(window).scrollTop() : cityPageScrollTop;
    var optionsScrollTop = cityOptionsScrollTop === null ? $('#registration-city-options').scrollTop() : cityOptionsScrollTop;
    var selectedNames = $('#registration-city-options input[type="checkbox"]:checked').map(function () {
        return $(this).val();
    }).get();

    $('#registration-city-summary')
        .text(selectedNames.length ? selectedNames.join(', ') : 'All Cities')
        .attr('title', selectedNames.length ? selectedNames.join(', ') : 'All Cities');

    $('.registration-city-option').each(function () {
        var selected = $(this).find('input[type="checkbox"]').is(':checked');
        $(this)
            .toggleClass('border-blue-200 bg-blue-50 font-semibold text-blue-700', selected)
            .toggleClass('border-transparent text-[#3D4A5C] hover:bg-[#F1F5FF]', !selected);
    });

    $('#registration-city-options').scrollTop(optionsScrollTop);
    window.scrollTo(window.scrollX, pageScrollTop);
    requestAnimationFrame(function () {
        $('#registration-city-options').scrollTop(optionsScrollTop);
        window.scrollTo(window.scrollX, pageScrollTop);
        cityPageScrollTop = null;
        cityOptionsScrollTop = null;
    });
}
$('#registration-city-options').on('change', 'input[type="checkbox"]', function (event) {
    event.stopPropagation();
    updateSelectedCities();
});
$('#registration-city-apply').on('click', function () {
    $('#consortium-registration-filters').trigger('submit');
});
</script>
@endpush