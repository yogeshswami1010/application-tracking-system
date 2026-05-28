@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <style>
        /* Rich editor placeholder (same look as jobs create) */
        #editSettings .job-rich-editor-editor:empty::before {
            content: attr(data-placeholder);
            color: #C4CBD4;
            pointer-events: none;
        }
    </style>
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.applicationFormSettings')</span>
@endsection

@section('page-subtitle')
    @lang('modules.applicationSetting.formSettings') · @lang('modules.applicationSetting.mailSettings') · @lang('modules.applicationSetting.googlemapapiSetting')
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <form id="editSettings" class="ajax-form space-y-4" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bs-set-card bs-set-card--rich mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.applicationSetting.formSettings')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.applicationSetting.legalTermText')</p>
                    </div>
                </div>
                <div class="p-6">
                    @include('admin.jobs.partials.job-rich-field', [
                        'name' => 'legal_term',
                        'textareaId' => 'legal_term',
                        'editorId' => 'legal_term_editor',
                        'label' => __('modules.applicationSetting.legalTermText'),
                        'placeholder' => __('modules.applicationSetting.legalTermPlaceholder'),
                        'required' => false,
                        'showLinkButton' => true,
                        'value' => old('legal_term', $setting->legal_term ?? ''),
                    ])
                </div>
            </div>

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#ECFDF5;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.applicationSetting.mailSettings')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.jobs.jobapplicationlimitation')</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div id="mail-setting">
                        <p class="bs-set-lbl mb-3">@lang('modules.applicationSetting.mailOnBoardColumn')</p>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @forelse($setting->mail_setting as $key => $mailSetting)
                                <label class="flex cursor-pointer items-center gap-3 rounded-[11px] border border-[#F0EEE9] bg-[#FAFAF8] px-4 py-3 transition hover:border-[#E2DED8]">
                                    <input
                                        type="checkbox"
                                        value="{{ $key }}"
                                        name="checkBoardColumn[]"
                                        class="peer sr-only"
                                        @if(in_array($key, old('checkBoardColumn', [])) || !empty($mailSetting['status'])) checked @endif
                                    >
                                    <span class="bs-toggle-track" aria-hidden="true">
                                        <span class="bs-toggle-thumb"></span>
                                    </span>
                                    <span class="text-[13px] font-medium text-[#3D4A5C]">{{ ucwords($mailSetting['name']) }}</span>
                                </label>
                            @empty
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <label for="job_application_limitation" class="bs-set-lbl">@lang('modules.jobs.jobapplicationlimitation')</label>
                        <input type="number" id="job_application_limitation" class="bs-f-input max-w-xs" name="job_application_limitation" value="{{ $setting->job_application_limitation ?? '0' }}">
                    </div>
                </div>
            </div>

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#EFF6FF;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.applicationSetting.candidateRetentionTitle')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.applicationSetting.candidateRetentionText')</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <label class="flex cursor-pointer items-center gap-3 rounded-[11px] border border-[#F0EEE9] bg-[#FAFAF8] px-4 py-3 transition hover:border-[#E2DED8]">
                        <input
                            type="checkbox"
                            id="auto_delete_candidates"
                            name="auto_delete_candidates"
                            value="1"
                            class="peer sr-only"
                            @if(!empty($setting->auto_delete_candidates)) checked @endif
                        >
                        <span class="bs-toggle-track" aria-hidden="true">
                            <span class="bs-toggle-thumb"></span>
                        </span>
                        <span class="text-[13px] font-medium text-[#3D4A5C]">@lang('modules.applicationSetting.autoDeleteCandidatesLabel')</span>
                    </label>

                    <div>
                        <label for="candidate_retention_days" class="bs-set-lbl">@lang('modules.applicationSetting.candidateRetentionDaysLabel')</label>
                        <input
                            type="number"
                            id="candidate_retention_days"
                            class="bs-f-input max-w-xs"
                            name="candidate_retention_days"
                            value="{{ $setting->candidate_retention_days ?? '0' }}"
                            min="0"
                            @if(empty($setting->auto_delete_candidates)) disabled @endif
                        >
                        <p class="text-[12px] text-[#8892A0] mt-2">@lang('modules.applicationSetting.candidateRetentionDaysHint')</p>
                    </div>
                </div>
            </div>

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#FFF7ED;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#F97316" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.applicationSetting.googlemapapiSetting')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.jobs.gogglemapapikey')</p>
                    </div>
                </div>
                <div class="p-6">
                    <label for="google_map_api_key" class="bs-set-lbl">@lang('modules.jobs.gogglemapapikey')</label>
                    <input type="text" id="google_map_api_key" class="bs-f-input" name="google_map_api_key" value="{{ $setting->google_map_api_key ?? '' }}">
                </div>
            </div>

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#F5F3FF;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#7C3AED" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.jobs.metaTitle')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.jobs.metaDescription')</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label for="meta-title" class="bs-set-lbl">@lang('modules.jobs.metaTitle')</label>
                        <input type="text" id="meta-title" class="bs-f-input" name="meta_title" value="{{ optional($meta_details)->title ?? '' }}">
                    </div>
                    <div>
                        <label for="meta-description" class="bs-set-lbl">@lang('modules.jobs.metaDescription')</label>
                        <textarea id="meta-description" class="bs-f-textarea" name="meta_description" rows="3">{{ optional($meta_details)->description ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" id="save-form" class="inline-flex items-center justify-center gap-2 rounded-[11px] bg-[#2563EB] px-8 py-3 text-[13.5px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-md">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @lang('app.save')
                </button>
                <button type="reset" class="rounded-[11px] border-[1.5px] border-[#E2DED8] bg-[#F1F3F7] px-6 py-3 text-[13.5px] font-semibold text-[#5A6478] transition hover:border-[#C4CBD4] hover:bg-[#E8EBEF]">@lang('app.reset')</button>
            </div>
        </form>
    </div>
@endsection

@push('footer-script')
    
    <script>
        $(function () {
            function syncCandidateRetentionDays() {
                var $cb = $('#auto_delete_candidates');
                var checked = $cb.prop('checked');
                $('#candidate_retention_days').prop('disabled', !checked);
                if (!checked) {
                    // When user unchecks auto-delete, force days to 0.
                    $('#candidate_retention_days').val('0');
                }
            }

            $('#auto_delete_candidates').on('change', function () {
                syncCandidateRetentionDays();
            });

            // Keep UI consistent with initial server-rendered state.
            syncCandidateRetentionDays();

            var linkUrlPrompt = @json(__('modules.jobs.linkUrlPrompt'));

            function richSyncField(editorId, textareaId) {
                var $ed = $('#' + editorId);
                var $ta = $('#' + textareaId);
                if ($ed.length && $ta.length) {
                    $ta.val($ed.html());
                }
            }

            function richSyncAll() {
                richSyncField('legal_term_editor', 'legal_term');
            }

            function richInitEditor(editorId, textareaId) {
                var $ta = $('#' + textareaId);
                var $ed = $('#' + editorId);
                if (!$ta.length || !$ed.length) {
                    return;
                }
                var html = $ta.val() || '';
                if (html.trim() === '') {
                    $ed.empty();
                } else {
                    $ed.html(html);
                }
            }

            $(document).on('click', '.job-rte-btn', function (e) {
                e.preventDefault();
                var $ed = $(this).closest('.job-rich-field').find('.job-rich-editor-editor');
                var el = $ed[0];
                if (!el) {
                    return;
                }
                el.focus();
                var cmd = $(this).data('cmd');
                if (cmd === 'createLink') {
                    var url = prompt(linkUrlPrompt);
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                } else if (cmd) {
                    document.execCommand(cmd, false, null);
                }
                richSyncAll();
            });

            $('#legal_term_editor').on('input', function () {
                richSyncAll();
            });

            richInitEditor('legal_term_editor', 'legal_term');
            richSyncAll();

            $('#save-form').on('click', function () {
                richSyncAll();
                $.easyAjax({
                    url: '{{ route('admin.application-setting.update', $global->id) }}',
                    container: '#editSettings',
                    type: 'POST',
                    redirect: true,
                    data: $('#editSettings').serialize(),
                    file: false,
                });
            });
        });
    </script>
@endpush
