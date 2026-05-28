<button type="button" id="open-ai-compare-modal" class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.912 5.813a2 2 0 001.268 1.268L21 12l-5.82 1.919a2 2 0 00-1.261 1.261L12 21l-1.919-5.82a2 2 0 00-1.261-1.261L3 12l5.813-1.919a2 2 0 001.268-1.268L12 3z"/></svg>
    AI Compare
</button>

<div id="ai-compare-modal" class="hidden fixed inset-0 z-[220]">
    <div class="absolute inset-0 bg-[rgba(15,31,61,0.55)]" id="ai-compare-modal-backdrop"></div>
    <div class="relative z-10 flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#F0EEE9] px-6 py-4">
                <h3 class="text-[16px] font-bold text-[#1A1E2E]">AI Applicant Match</h3>
                <button type="button" id="close-ai-compare-modal" class="rounded-lg p-2 text-[#8892A0] hover:bg-[#F1F3F7]">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">Job Profile</label>
                        <select id="ai-compare-job-id" class="select2 w-full">
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}">{{ ucfirst($job->title) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">Candidates</label>
                        <select id="ai-compare-application-ids" class="select2 w-full" multiple>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <button type="button" id="run-ai-compare" class="rounded-[9px] bg-[#2563EB] px-4 py-2 text-[13px] font-bold text-white hover:bg-[#1d4ed8]">Compare with AI</button>
                    <span id="ai-compare-loading" class="hidden text-[12px] text-[#64748B]">Comparing candidates...</span>
                </div>

                <div id="ai-compare-results" class="mt-4 max-h-[45vh] overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

@push('footer-script')
<script>
    (function () {
        var $modal = $('#ai-compare-modal');
        if (!$modal.length) {
            return;
        }

        var aiCompareStatuses = @json(
            collect($boardColumns ?? [])
                ->map(function ($s) {
                    return ['id' => (int) $s->id, 'label' => ucfirst((string) $s->status)];
                })
                ->values()
                ->all()
        );

        function openAiCompareModal() {
            $modal.removeClass('hidden');

            // If a job is already selected and candidates are empty, load them once on open.
            var jobId = $('#ai-compare-job-id').val();
            if (jobId && !$('#ai-compare-application-ids option').length) {
                loadAiCompareCandidates(jobId);
            }
        }
        function closeAiCompareModal() {
            $modal.addClass('hidden');
        }

        $(document).on('click', '#open-ai-compare-modal', openAiCompareModal);
        $(document).on('click', '#close-ai-compare-modal, #ai-compare-modal-backdrop', closeAiCompareModal);

        function setAiCompareCandidates(applications) {
            var $select = $('#ai-compare-application-ids');
            $select.empty();

            var allOption = $('<option/>').val('__all__').text('All Candidates');
            $select.append(allOption);

            (applications || []).forEach(function (app) {
                var id = app && app.id ? app.id : '';
                if (!id) {
                    return;
                }

                var display = (app.full_name || '').toString();
                var option = $('<option/>').val(String(id)).text(display);
                $select.append(option);
            });

            // Tell Select2 to update its UI after the option list changes.
            $select.trigger('change');
        }

        function loadAiCompareCandidates(jobId) {
            var $jobSelect = $('#ai-compare-job-id');
            var $candidateSelect = $('#ai-compare-application-ids');

            if (!jobId) {
                setAiCompareCandidates([]);
                return;
            }

            $candidateSelect.prop('disabled', true);
            $('#run-ai-compare').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.job-applications.ai-compare-applicants') }}",
                type: 'GET',
                data: {
                    job_id: jobId
                }
            }).done(function (response) {
                var payload = response || {};
                // Controller returns `applications` (existing route).
                var apps = payload.applications || payload.applicants || [];
                if (!Array.isArray(apps)) {
                    apps = [];
                }
                setAiCompareCandidates(apps);
            }).fail(function (xhr) {
                var msg = 'Failed to load candidates for selected job.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                setAiCompareCandidates([]);
            }).always(function () {
                $candidateSelect.prop('disabled', false);
                $('#run-ai-compare').prop('disabled', false);
            });
        }

        $(document).on('change', '#ai-compare-job-id', function () {
            var jobId = $('#ai-compare-job-id').val();
            loadAiCompareCandidates(jobId);
        });

        $(document).on('change', '#ai-compare-application-ids', function () {
            var $select = $(this);
            var selected = $select.val() || [];
            if (!Array.isArray(selected)) {
                selected = [selected];
            }

            if (selected.indexOf('__all__') === -1) {
                return;
            }

            var allRealIds = [];
            $select.find('option').each(function () {
                var val = String($(this).val() || '');
                if (val && val !== '__all__') {
                    allRealIds.push(val);
                }
            });

            $select.val(allRealIds).trigger('change.select2');
        });

        $(document).on('click', '#run-ai-compare', function () {
            var jobId = $('#ai-compare-job-id').val();
            var applicationIds = $('#ai-compare-application-ids').val() || [];

            if (!jobId) {
                alert('Please select a job profile.');
                return;
            }
            if (!applicationIds.length) {
                alert('Please select at least one candidate.');
                return;
            }

            $('#ai-compare-loading').removeClass('hidden');
            $('#run-ai-compare').prop('disabled', true);
            $('#ai-compare-results').html('');

            $.ajax({
                url: "{{ route('admin.job-applications.ai-compare') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    job_id: jobId,
                    application_ids: applicationIds
                }
            }).done(function (response) {
                var payload = response || {};
                if (payload.status && String(payload.status).toLowerCase() === 'fail') {
                    var failMessage = payload.message || 'AI compare failed.';
                    $('#ai-compare-results').html('<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">' + $('<div/>').text(failMessage).html() + '</div>');
                    return;
                }
                var matches = payload.matches || (((payload.data || {}).matches) || []);
                var debug = payload.debug || ((payload.data || {}).debug) || null;
                if (matches && !Array.isArray(matches) && typeof matches === 'object') {
                    matches = Object.keys(matches).map(function (k) { return matches[k]; });
                }
                if (!Array.isArray(matches)) {
                    matches = [];
                }
                if (!matches.length) {
                    var reason = 'No matches returned by AI.';
                    if (debug) {
                        reason += ' Selected: ' + (debug.selected_count || 0) + ', AI rows: ' + (debug.ai_rows_count || 0) + ', mapped: ' + (debug.mapped_count || 0) + '.';
                    }
                    $('#ai-compare-results').html('<div class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3 text-[13px] text-[#475569]">' + $('<div/>').text(reason).html() + '</div>');
                    return;
                }

                var html = '<div class="overflow-hidden rounded-xl border border-[#E2E8F0]"><table class="min-w-full text-[12.5px]"><thead class="bg-[#F8FAFC]"><tr><th class="px-3 py-2 text-left">Candidate</th><th class="px-3 py-2 text-left">Tier</th><th class="px-3 py-2 text-left">Match</th><th class="px-3 py-2 text-left">Status</th><th class="px-3 py-2 text-left">Summary</th></tr></thead><tbody>';
                matches.forEach(function (row) {
                    var tier = (row.rank_tier || '').toString().toLowerCase();
                    var tierLabel = tier
                        ? (tier === 'not_match' ? 'Not Match' : (tier.charAt(0).toUpperCase() + tier.slice(1)))
                        : 'Good';
                    var tierClass = tier === 'best'
                        ? 'bg-emerald-50 text-emerald-700'
                        : (tier === 'better'
                            ? 'bg-blue-50 text-blue-700'
                            : (tier === 'not_match'
                                ? 'bg-gray-100 text-gray-600'
                                : 'bg-amber-50 text-amber-700'));
                    html += '<tr class="border-t border-[#EEF2F7]">';
                    html += '<td class="px-3 py-2 font-semibold text-[#1A1E2E]">' + $('<div/>').text(row.full_name || '').html() + '</td>';
                    html += '<td class="px-3 py-2"><span class="inline-flex rounded-full px-2 py-1 font-bold ' + tierClass + '">' + $('<div/>').text(tierLabel).html() + '</span></td>';
                    html += '<td class="px-3 py-2"><span class="inline-flex rounded-full bg-[#EFF6FF] px-2 py-1 font-bold text-[#1D4ED8]">' + (row.match_score || 0) + '%</span></td>';
                    var statusSelect = '<select class="ai-compare-status-select w-full rounded-lg border border-[#E2DED8] bg-white px-2 py-1 text-[12.5px] font-semibold text-[#5A6478]" data-application-id="'+(row.application_id || '')+'">';
                    aiCompareStatuses.forEach(function (st) {
                        var selected = (String(row.status_id || '') === String(st.id)) ? 'selected' : '';
                        statusSelect += '<option value="' + st.id + '" ' + selected + '>' + $('<div/>').text(st.label || '').html() + '</option>';
                    });
                    statusSelect += '</select>';

                    html += '<td class="px-3 py-2">' + statusSelect + '</td>';
                    html += '<td class="px-3 py-2 text-[#475569]">' + $('<div/>').text(row.summary || '').html() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div>';
                $('#ai-compare-results').html(html);
            }).fail(function (xhr) {
                var msg = 'AI compare failed.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#ai-compare-results').html('<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">' + $('<div/>').text(msg).html() + '</div>');
            }).always(function () {
                $('#ai-compare-loading').addClass('hidden');
                $('#run-ai-compare').prop('disabled', false);
            });
        });

        function refreshJobApplicationsUi() {
            if (typeof loadData === 'function') {
                loadData();
                return;
            }

            try {
                if (typeof table !== 'undefined' && table && typeof table.ajax === 'object' && table.ajax) {
                    table.ajax.reload(null, false);
                    return;
                }
                if (typeof table !== 'undefined' && table && typeof table.draw === 'function') {
                    table.draw(false);
                    return;
                }
            } catch (e) {
                // ignore
            }

            window.location.reload();
        }

        $(document).on('change', '.ai-compare-status-select', function () {
            var $sel = $(this);
            var applicationId = $sel.data('application-id');
            var statusId = $sel.val();

            if (!applicationId || !statusId) {
                return;
            }

            $sel.prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.job-applications.ai-update-status') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    application_id: applicationId,
                    status_id: statusId
                }
            }).done(function () {
                refreshJobApplicationsUi();
            }).always(function () {
                $sel.prop('disabled', false);
            });
        });
    })();
</script>
@endpush
