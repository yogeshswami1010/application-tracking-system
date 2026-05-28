@extends('layouts.app')

@push('head-script')
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
@endpush

@php
    $jcOpenEdit = null;
    if (request('open') === 'edit' && request()->filled('id')) {
        $jcOpenEdit = $categories->firstWhere('id', (int) request('id'));
    }
    $jcColors = ['#2563EB', '#059669', '#7C3AED', '#F97316', '#F43F5E', '#0D9488', '#D97706', '#6366F1', '#EC4899', '#0891B2'];
    $jcTotal = $categories->count();
@endphp

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-[-0.025em] text-[#1A1E2E]">{{ __('modules.jobCategoriesPage.titleJob') }}
        <span class="jc-cat-serif">{{ __('modules.jobCategoriesPage.titleAccent') }}</span>
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.jobCategoriesPage.subtitle') }}
@endsection

@if(in_array('add_category', $userPermissions))
@section('create-button')
    <button type="button" onclick="window.jcModalOpenCreate()"
            class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        @lang('app.createNew')
    </button>
@endsection
@endif

@section('content')
    <div class="flex flex-col gap-6">
        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 jc-a jc-a1">
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF]">
                    <svg class="h-4 w-4" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.jobCategoriesPage.statTotal') }}</p>
                    <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]" id="jc-stat-total">{{ $jcTotal }}</p>
                </div>
                <span class="ml-auto rounded-full bg-[#EFF6FF] px-2 py-0.5 text-[10.5px] font-semibold text-[#2563EB]">{{ __('modules.jobCategoriesPage.statCategoriesTag') }}</span>
            </div>
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ECFDF5]">
                    <svg class="h-4 w-4" fill="none" stroke="#059669" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.jobCategoriesPage.statActiveJobs') }}</p>
                    <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $jcStatActiveJobs }}</p>
                </div>
                <span class="ml-auto rounded-full bg-[#ECFDF5] px-2 py-0.5 text-[10.5px] font-semibold text-[#059669]">{{ __('modules.jobCategoriesPage.statOpeningsTag') }}</span>
            </div>
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FFF7ED]">
                    <svg class="h-4 w-4" fill="none" stroke="#F97316" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.jobCategoriesPage.statApplications') }}</p>
                    <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $jcStatApplications }}</p>
                </div>
                <span class="ml-auto rounded-full bg-[#FFF7ED] px-2 py-0.5 text-[10.5px] font-semibold text-[#F97316]">{{ __('modules.jobCategoriesPage.statApplicationsTag') }}</span>
            </div>
        </div>

        {{-- Table card --}}
        <div class="jc-table-card jc-a jc-a2">
            <div class="jc-table-toolbar">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="jc-t-search">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" id="jc-table-search" placeholder="{{ __('modules.jobCategoriesPage.searchPlaceholder') }}" autocomplete="off">
                    </div>
                    <select id="jc-sort" class="cursor-pointer rounded-xl border border-[#E2DED8] bg-white px-3 py-2 text-[12.5px] font-medium text-[#5A6478] outline-none focus:border-[#2563EB]">
                        <option value="name-asc">{{ __('modules.jobCategoriesPage.sortNameAsc') }}</option>
                        <option value="name-desc">{{ __('modules.jobCategoriesPage.sortNameDesc') }}</option>
                        <option value="jobs-desc">{{ __('modules.jobCategoriesPage.sortJobsDesc') }}</option>
                    </select>
                    @if(in_array('add_category', $userPermissions))
                        <button type="button"
                                onclick="window.jcModalOpenAiGenerate()"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#2563EB] bg-white px-4 py-2.5 text-[12.5px] font-semibold text-[#2563EB] shadow-sm transition hover:bg-[#EFF6FF] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.912 5.813a2 2 0 001.268 1.268L21 12l-5.82 1.919a2 2 0 00-1.261 1.261L12 21l-1.919-5.82a2 2 0 00-1.261-1.261L3 12l5.813-1.919a2 2 0 001.268-1.268L12 3z"/></svg>
                            {{ __('modules.jobCategoriesPage.aiGenerateButton') }}
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-[12px] text-[#8892A0]">
                    <span>{{ __('modules.jobCategoriesPage.showing') }}</span>
                    <span class="font-semibold text-[#1A1E2E]" id="jc-show-count">{{ $jcTotal }}</span>
                    <span>{{ __('modules.jobCategoriesPage.of') }}</span>
                    <span class="font-semibold text-[#1A1E2E]" id="jc-total-num">{{ $jcTotal }}</span>
                    <span>{{ __('modules.jobCategoriesPage.categories') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="jc-cat-table">
                    <thead>
                    <tr>
                        <th style="width:72px;">#</th>
                        <th>{{ __('modules.jobCategoriesPage.categoryName') }}</th>
                        <th>{{ __('modules.jobCategoriesPage.jobs') }}</th>
                        <th>{{ __('modules.jobCategoriesPage.created') }}</th>
                        <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                    </tr>
                    </thead>
                    <tbody id="jc-cat-tbody">
                    @forelse($categories as $category)
                        @php
                            $dot = $jcColors[$category->id % count($jcColors)];
                        @endphp
                        <tr class="jc-cat-row"
                            data-id="{{ $category->id }}"
                            data-name="{{ e($category->name) }}"
                            data-name-sort="{{ e(strtolower($category->name)) }}"
                            data-jobs="{{ (int) $category->jobs_count }}">
                            <td class="jc-td-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="jc-cat-pill">
                                    <span class="jc-cat-dot" style="background:{{ $dot }};"></span>
                                    <span class="jc-cat-name">{{ ucfirst($category->name) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="jc-job-count" style="background:{{ $dot }}18;color:{{ $dot }};">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $category->jobs_count }} {{ trans_choice('modules.jobCategoriesPage.jobCountLabel', $category->jobs_count) }}
                                </span>
                            </td>
                            <td class="text-[12.5px] text-[#8892A0]">{{ $category->created_at ? $category->created_at->format('M j, Y') : '—' }}</td>
                            <td class="jc-td-right" style="padding-right:20px;">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array('edit_category', $userPermissions))
                                        <button type="button"
                                                class="jc-action-btn jc-btn-edit jc-open-edit"
                                                title="@lang('app.edit')"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    @if(in_array('delete_category', $userPermissions))
                                        <button type="button"
                                                class="jc-action-btn jc-btn-del jc-open-del"
                                                title="@lang('app.delete')"
                                                data-row-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="jc-row-initial-empty">
                            <td colspan="5">
                                <div class="jc-empty-state">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                        <svg class="h-6 w-6" fill="none" stroke="#C4CBD4" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-[14px] font-semibold text-[#5A6478]">@lang('modules.emptyTable')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="jc-filter-empty" class="hidden">
                        <td colspan="5">
                            <div class="jc-empty-state">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                    <svg class="h-6 w-6" fill="none" stroke="#C4CBD4" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-[14px] font-semibold text-[#5A6478]">{{ __('modules.jobCategoriesPage.emptyFilter') }}</p>
                                <p class="mt-1 text-[12.5px] text-[#B0B8C4]">{{ __('modules.jobCategoriesPage.emptyFilterHint') }}</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(in_array('add_category', $userPermissions))
        <div id="jc-modal-create" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="jc-create-title" onclick="window.jcBackdropCloseCreate(event)">
            <div class="jc-modal" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="jc-create-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">@lang('app.createNew')</h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]">{{ __('modules.jobCategoriesPage.modalCreateSub') }}</p>
                    </div>
                    <button type="button" onclick="window.jcModalCloseCreate()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="ajax-form" method="POST" id="jc-form-create" onsubmit="return false;">
                    @csrf
                    <div id="alert"></div>
                    <div class="jc-modal-body">
                        <p class="jc-field-label">{{ __('menu.jobCategories') }} {{ __('app.name') }} <span class="text-red-400">*</span></p>
                        <div id="jc-create-rows" class="space-y-3" data-placeholder="{{ __('menu.jobCategories') }} {{ __('app.name') }}">
                            <div class="flex items-start gap-2 jc-create-row">
                                <div class="form-group min-w-0 flex-1">
                                    <input type="text" name="name[]" autocomplete="off" class="jc-field-input"
                                           placeholder="{{ __('menu.jobCategories') }} {{ __('app.name') }}">
                                </div>
                                <button type="button" id="jc-add-row" class="mt-0.5 flex h-[42px] w-10 shrink-0 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" title="@lang('app.addMore')">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.jcModalCloseCreate()">@lang('app.cancel')</button>
                        <button type="button" id="jc-save-create" class="jc-btn-save">@lang('app.save')</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if(in_array('edit_category', $userPermissions))
        <div id="jc-modal-edit" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="jc-edit-title" onclick="window.jcBackdropCloseEdit(event)">
            <div class="jc-modal" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="jc-edit-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">@lang('app.edit')</h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]" id="jc-edit-sub"></p>
                    </div>
                    <button type="button" onclick="window.jcModalCloseEdit()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="ajax-form" method="POST" id="jc-form-edit" onsubmit="return false;">
                    <div id="alert"></div>
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="jc-edit-id" value="">
                    <div class="jc-modal-body">
                        <div class="form-group">
                            <label for="jc-edit-name" class="jc-field-label">{{ __('app.name') }} <span class="text-red-400">*</span></label>
                            <input type="text" name="name" id="jc-edit-name" autocomplete="off" class="jc-field-input"
                                   placeholder="{{ __('menu.jobCategories') }} {{ __('app.name') }}">
                        </div>
                    </div>
                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.jcModalCloseEdit()">@lang('app.cancel')</button>
                        <button type="button" id="jc-save-edit" class="jc-btn-save">@lang('app.save')</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if(in_array('delete_category', $userPermissions))
        <div id="jc-modal-delete" class="jc-modal-bg" role="dialog" aria-modal="true" onclick="window.jcBackdropCloseDel(event)">
            <div class="jc-modal" style="width:380px;max-width:92vw;" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FFF1F2]">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-[#1A1E2E]">{{ __('modules.jobCategoriesPage.deleteCategory') }}</h3>
                            <p class="text-[11.5px] text-[#8892A0]">{{ __('modules.jobCategoriesPage.deleteCannotUndo') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.jcModalCloseDel()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="jc-modal-body">
                    <p class="text-[13.5px] leading-relaxed text-[#5A6478]" id="jc-del-body"></p>
                </div>
                <div class="jc-modal-ft">
                    <button type="button" class="jc-btn-cancel" onclick="window.jcModalCloseDel()">@lang('app.cancel')</button>
                    <button type="button" id="jc-del-confirm" class="jc-btn-danger">@lang('app.delete')</button>
                </div>
            </div>
        </div>
    @endif

    @if(in_array('add_category', $userPermissions))
        <div id="jc-modal-ai-generate" class="jc-modal-bg" role="dialog" aria-modal="true"
             aria-labelledby="jc-ai-generate-title" onclick="window.jcBackdropCloseAiGenerate(event)">
            <div class="jc-modal" style="width:520px;max-width:92vw;" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="jc-ai-generate-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">
                            {{ __('modules.jobCategoriesPage.aiGenerateModalTitle') }}
                        </h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]">
                            {{ __('modules.jobCategoriesPage.aiGenerateModalSub') }}
                        </p>
                    </div>
                    <button type="button" onclick="window.jcModalCloseAiGenerate()"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100"
                            aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form class="ajax-form" method="POST" id="jc-form-ai-generate" onsubmit="return false;">
                    @csrf
                    <div id="jc-ai-alert"></div>

                    <div class="jc-modal-body">
                        <div class="form-group">
                            <label for="jc-ai-count" class="jc-field-label">
                                {{ __('modules.jobCategoriesPage.aiGenerateCountLabel') }}
                                <span class="text-red-400">*</span>
                            </label>
                            <input type="number" id="jc-ai-count" name="count" class="jc-field-input cursor-pointer"
                                   min="3" max="15" step="1" value="7">
                        </div>

                        <div class="mt-4">
                            <label for="jc-ai-company-description" class="jc-field-label">
                                {{ __('modules.jobCategoriesPage.aiGenerateCompanyDescriptionLabel') }}
                                <span class="text-red-400">*</span>
                            </label>
                            <textarea id="jc-ai-company-description"
                                      name="company_description"
                                      class="jc-field-input min-h-[110px] resize-y cursor-text"
                                      required
                                      placeholder="{{ __('modules.jobCategoriesPage.aiGenerateCompanyDescriptionPlaceholder') }}">{{ old('company_description', '') }}</textarea>
                            <p class="mt-1 text-[12px] text-[#8892A0]">
                                {{ __('modules.jobCategoriesPage.aiGenerateCompanyDescriptionHint') }}
                            </p>
                        </div>

                        <p class="mt-3 text-[12px] text-[#8892A0]">
                            {{ __('modules.jobCategoriesPage.aiGenerateHint') }}
                        </p>
                    </div>

                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.jcModalCloseAiGenerate()">
                            @lang('app.cancel')
                        </button>
                        <button type="button" id="jc-ai-generate-btn" class="jc-btn-save">
                            {{ __('modules.jobCategoriesPage.aiGenerateConfirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('footer-script')
    <script>
        (function () {
            var jcCreateRoom = 1;
            var jcUpdateUrlTemplate = @json(route('admin.job-categories.update', ['job_category' => '__ID__']));
            var jcDelTemplate = {!! json_encode(__('modules.jobCategoriesPage.deleteConfirmHtml')) !!};
            var jcEditSubPrefix = @json(__('modules.jobCategoriesPage.modalEditSubPrefix'));
            var jcAiGenerateUrl = @json(route('admin.job-categories.ai-generate'));

            window.jcBackdropCloseCreate = function (e) {
                if (e.target.id === 'jc-modal-create') window.jcModalCloseCreate();
            };
            window.jcBackdropCloseEdit = function (e) {
                if (e.target.id === 'jc-modal-edit') window.jcModalCloseEdit();
            };
            window.jcBackdropCloseDel = function (e) {
                if (e.target.id === 'jc-modal-delete') window.jcModalCloseDel();
            };

            window.jcBackdropCloseAiGenerate = function (e) {
                if (e.target.id === 'jc-modal-ai-generate') window.jcModalCloseAiGenerate();
            };

            window.jcModalOpenCreate = function () {
                jcResetCreateForm();
                $('#jc-modal-create').addClass('is-open');
                $('body').addClass('overflow-hidden');
                $('#jc-create-rows input[name="name[]"]').first().focus();
            };

            window.jcModalCloseCreate = function () {
                $('#jc-modal-create').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.jcModalOpenEdit = function (id, name) {
                $('#jc-form-edit').find('.has-error').removeClass('has-error');
                $('#jc-form-edit').find('.help-block').remove();
                $('#jc-form-edit').find('input, select, textarea').removeClass('border-red-500');
                $('#jc-form-edit').find('#alert').html('');
                $('#jc-edit-id').val(id);
                $('#jc-edit-name').val(name);
                $('#jc-edit-sub').text(jcEditSubPrefix + ' "' + name + '"');
                var url = jcUpdateUrlTemplate.replace('__ID__', id);
                $('#jc-form-edit').data('jc-update-url', url);
                $('#jc-modal-edit').addClass('is-open');
                $('body').addClass('overflow-hidden');
                $('#jc-edit-name').focus();
            };

            window.jcModalCloseEdit = function () {
                $('#jc-modal-edit').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.jcModalCloseDel = function () {
                $('#jc-modal-delete').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.jcModalOpenAiGenerate = function () {
                $('#jc-form-ai-generate').find('#jc-ai-alert').html('');
                $('#jc-ai-generate-btn').prop('disabled', false);
                $('#jc-ai-count').val($('#jc-ai-count').attr('value') || 7);
                $('#jc-modal-ai-generate').addClass('is-open');
                $('body').addClass('overflow-hidden');
                $('#jc-ai-count').focus();
            };

            window.jcModalCloseAiGenerate = function () {
                $('#jc-modal-ai-generate').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            function jcResetCreateForm() {
                jcCreateRoom = 1;
                $('#jc-create-rows').find('.jc-create-row-extra').remove();
                $('#jc-create-rows').find('input[name="name[]"]').val('').first().val('');
                $('#jc-form-create').find('.has-error').removeClass('has-error');
                $('#jc-form-create').find('.help-block').remove();
                $('#jc-form-create').find('input, select, textarea').removeClass('border-red-500');
                $('#jc-form-create').find('#alert').html('');
            }

            function jcRowCount() {
                return $('#jc-cat-tbody tr.jc-cat-row').length;
            }

            function jcApplyFilter() {
                var q = ($('#jc-table-search').val() || '').toLowerCase();
                var visible = 0;
                $('#jc-cat-tbody tr.jc-cat-row').each(function () {
                    var n = ($(this).data('name-sort') || '').toString().toLowerCase();
                    var match = n.indexOf(q) !== -1;
                    $(this).toggle(match);
                    if (match) visible++;
                });
                $('#jc-show-count').text(visible);
                var hasRows = jcRowCount() > 0;
                $('#jc-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
            }

            function jcSortRows() {
                var val = $('#jc-sort').val();
                var $tb = $('#jc-cat-tbody');
                var rows = $tb.find('tr.jc-cat-row').get();
                rows.sort(function (a, b) {
                    var $a = $(a), $b = $(b);
                    if (val === 'name-asc') return $a.data('name-sort').localeCompare($b.data('name-sort'));
                    if (val === 'name-desc') return $b.data('name-sort').localeCompare($a.data('name-sort'));
                    if (val === 'jobs-desc') return ($b.data('jobs') || 0) - ($a.data('jobs') || 0);
                    return 0;
                });
                $.each(rows, function (i, r) {
                    $tb.append(r);
                });
                $('#jc-cat-tbody tr.jc-cat-row').each(function (i) {
                    $(this).find('td.jc-td-num').first().text(String(i + 1).padStart(2, '0'));
                });
                jcApplyFilter();
            }

            $('#jc-table-search').on('input', jcApplyFilter);
            $('#jc-sort').on('change', jcSortRows);

            $('#jc-add-row').on('click', function () {
                jcCreateRoom++;
                var ph = $('#jc-create-rows').data('placeholder') || '';
                var esc = $('<div>').text(ph).html();
                var row = $(
                    '<div class="flex items-start gap-2 jc-create-row jc-create-row-extra removeclass' + jcCreateRoom + '">' +
                    '<div class="form-group min-w-0 flex-1">' +
                    '<input type="text" name="name[]" autocomplete="off" class="jc-field-input" placeholder="' + esc + '">' +
                    '</div>' +
                    '<button type="button" class="mt-0.5 flex h-[42px] w-10 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 jc-remove-row" data-rid="' + jcCreateRoom + '">' +
                    '<i class="fa fa-minus" aria-hidden="true"></i></button></div>'
                );
                $('#jc-create-rows').append(row);
                row.find('input').first().focus();
            });

            $(document).on('click', '.jc-remove-row', function () {
                var rid = $(this).data('rid');
                $('.removeclass' + rid).remove();
            });

            $('#jc-save-create').on('click', function () {
                $.easyAjax({
                    url: '{{ route('admin.job-categories.store') }}',
                    container: '#jc-form-create',
                    type: 'POST',
                    redirect: true,
                    data: $('#jc-form-create').serialize()
                });
            });

            $('#jc-save-edit').on('click', function () {
                var url = $('#jc-form-edit').data('jc-update-url');
                if (!url) return;
                $.easyAjax({
                    url: url,
                    container: '#jc-form-edit',
                    type: 'POST',
                    redirect: true,
                    data: $('#jc-form-edit').serialize()
                });
            });

            $('#jc-ai-generate-btn').on('click', function () {
                var $btn = $(this);
                var data = $('#jc-form-ai-generate').serialize();
                $btn.prop('disabled', true);
                $('#jc-ai-alert').html('');

                $.easyAjax({
                    url: jcAiGenerateUrl,
                    container: '#jc-form-ai-generate',
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response && response.status === 'success') {
                            window.jcModalCloseAiGenerate();
                            window.location.reload();
                            return;
                        }

                        var msg = response && response.message ? response.message : 'AI generation failed.';
                        $('#jc-ai-alert').html('<p class="text-[13px] text-red-500">' + msg + '</p>');
                        $btn.prop('disabled', false);
                    },
                    error: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.jc-open-edit', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                if (typeof name === 'number') name = String(name);
                window.jcModalOpenEdit(id, name);
            });

            $(document).on('click', '.jc-open-del', function () {
                var id = $(this).data('row-id');
                var name = $(this).data('name');
                var safe = $('<div>').text(name).html();
                var html = jcDelTemplate.split(':name').join(safe);
                $('#jc-del-body').html(html);
                $('#jc-modal-delete').data('del-id', id).addClass('is-open');
                $('body').addClass('overflow-hidden');
            });

            $('#jc-del-confirm').on('click', function () {
                var id = $('#jc-modal-delete').data('del-id');
                if (!id) return;
                var url = "{{ route('admin.job-categories.destroy', ':id') }}".replace(':id', id);
                var token = "{{ csrf_token() }}";
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $.unblockUI();
                            window.jcModalCloseDel();
                            window.location.reload();
                        }
                    }
                });
            });

            @if(request('open') === 'create' && in_array('add_category', $userPermissions))
            $(function () {
                window.jcModalOpenCreate();
            });
            @endif

            @if($jcOpenEdit && in_array('edit_category', $userPermissions))
            $(function () {
                window.jcModalOpenEdit({{ (int) $jcOpenEdit->id }}, @json($jcOpenEdit->name));
            });
            @endif

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    if ($('#jc-modal-create').hasClass('is-open')) window.jcModalCloseCreate();
                    if ($('#jc-modal-edit').hasClass('is-open')) window.jcModalCloseEdit();
                    if ($('#jc-modal-delete').hasClass('is-open')) window.jcModalCloseDel();
                    if ($('#jc-modal-ai-generate').hasClass('is-open')) window.jcModalCloseAiGenerate();
                }
            });
        })();
    </script>
@endpush
