@php
    $registrationInfoRows = [
        ['Eligible to Work in Canada', $registration->eligible_to_work_canada ? 'Yes' : 'No', 'fa-check-circle-o'],
        ['Status in Canada', $registration->status_in_canada, 'fa-flag-o'],
        ['Preferred Job Type', $registration->preferred_job_type, 'fa-briefcase'],
        ['Mode of Commute', $registration->commute_mode, 'fa-car'],
        ['Years of Experience', !is_null($registration->years_experience) ? rtrim(rtrim(number_format((float) $registration->years_experience, 1), '0'), '.').' years' : null, 'fa-line-chart'],
        ['Original Experience Response', $registration->legacy_experience_text, 'fa-history'],
        ['Industry / Expertise', $registration->industry_expertise, 'fa-cogs'],
        ['Available Weekends', is_null($registration->available_weekends) ? null : ($registration->available_weekends ? 'Yes' : 'No'), 'fa-calendar-check-o'],
        ['Available Night Shifts', is_null($registration->available_night_shifts) ? null : ($registration->available_night_shifts ? 'Yes' : 'No'), 'fa-moon-o'],
        ['Referral Source', $registration->referral_source, 'fa-share-alt'],
        ['Information Certified', $registration->information_certified ? 'Yes' : 'No', 'fa-certificate'],
        ['Applicant Agreement Accepted', $registration->agreement_accepted ? 'Yes' : 'No', 'fa-file-text-o'],
        ['SMS Consent', $registration->sms_consent ? 'Yes' : 'No', 'fa-commenting-o'],
        ['Submitted', $registration->created_at?->timezone($global->timezone)->format('j M Y, g:i A'), 'fa-clock-o'],
        ['Reviewed', $registration->reviewed_at?->timezone($global->timezone)->format('j M Y, g:i A'), 'fa-eye'],
    ];
@endphp
<div class="ja-card" data-consortium-registration-information>
    <div class="ja-card-title"><i class="fa fa-address-card-o"></i> Consortium Registration Information</div>
    @foreach($registrationInfoRows as $row)
        <div class="ja-info-row">
            <span class="ja-info-label"><i class="fa {{ $row[2] }}"></i>{{ $row[0] }}</span>
            <span class="ja-info-val">{{ filled($row[1]) ? $row[1] : '—' }}</span>
        </div>
    @endforeach
    <div class="ja-info-row"><span class="ja-info-label"><i class="fa fa-align-left"></i>Additional Information</span><span class="ja-info-val" style="white-space:pre-wrap">{{ $registration->additional_information ?: '—' }}</span></div>
</div>