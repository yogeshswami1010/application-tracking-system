@if($user->cans('edit_job_applications'))
<button type="button" id="ja-temp-staffing-btn-{{ $application->id }}" onclick="jaToggleTempStaffing({{ $application->id }})" class="ja-pdf-btn" style="{{ $application->is_temp_staffing ? 'background:#EFF6FF;color:#2563EB;border-color:#BFDBFE;' : '' }}">
    <i class="fa fa-users"></i>
    <span>{{ $application->is_temp_staffing ? 'In Temp Staffing' : 'Temp Staffing' }}</span>
</button>
<script>
window.jaToggleTempStaffing = window.jaToggleTempStaffing || function (appId) {
    var button = document.getElementById('ja-temp-staffing-btn-' + appId);
    if (!button || button.disabled) return;
    var currentlyOn = button.querySelector('span').textContent.trim() === 'In Temp Staffing';
    button.disabled = true;
    $.ajax({
        type: 'POST',
        url: @json(route('admin.job-applications.temp-staffing', ':id')).replace(':id', appId),
        data: {_token: @json(csrf_token()), add: currentlyOn ? 0 : 1}
    }).done(function (response) {
        var data = response.data || response;
        var isOn = !!data.is_temp_staffing;
        button.querySelector('span').textContent = isOn ? 'In Temp Staffing' : 'Temp Staffing';
        button.style.background = isOn ? '#EFF6FF' : '';
        button.style.color = isOn ? '#2563EB' : '';
        button.style.borderColor = isOn ? '#BFDBFE' : '';
    }).always(function () { button.disabled = false; });
};
</script>
@endif