@extends('layouts.app')

@push('head-script')
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
@endpush

@php
    $skOpenEdit = null;
    if (request('open') === 'edit' && request()->filled('id')) {
        $skOpenEdit = $skills->firstWhere('id', (int) request('id'));
    }
    $skColors = ['#2563EB', '#059669', '#7C3AED', '#F97316', '#F43F5E', '#0D9488', '#D97706', '#6366F1', '#EC4899', '#0891B2'];
    $skTotal = $skills->count();
@endphp

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-[-0.025em] text-[#1A1E2E]">{{ __('modules.skillsPage.titleSkills') }}
        <span class="jc-cat-serif">{{ __('modules.skillsPage.titleAccent') }}</span>
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.skillsPage.subtitle') }}
@endsection

@if(in_array('add_skills', $userPermissions))
@section('create-button')
    <button type="button" onclick="window.skModalOpenCreate()"
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
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF]">
                    <svg class="h-4 w-4" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.skillsPage.statTotal') }}</p>
                    <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]" id="sk-stat-total">{{ $skTotal }}</p>
                </div>
                <span class="ml-auto rounded-full bg-[#EFF6FF] px-2 py-0.5 text-[10.5px] font-semibold text-[#2563EB]">{{ __('modules.skillsPage.statTotalTag') }}</span>
            </div>
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F5F3FF]">
                    <svg class="h-4 w-4" fill="none" stroke="#7C3AED" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.skillsPage.statCategories') }}</p>
                    <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $skStatCategoryCount }}</p>
                </div>
                <span class="ml-auto rounded-full bg-[#F5F3FF] px-2 py-0.5 text-[10.5px] font-semibold text-[#7C3AED]">{{ __('modules.skillsPage.statCategoriesTag') }}</span>
            </div>
            <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ECFDF5]">
                    <svg class="h-4 w-4" fill="none" stroke="#059669" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.skillsPage.statMostUsed') }}</p>
                    <p class="truncate text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $skTotal ? ucfirst($skMostUsedCategoryName) : __('modules.skillsPage.statMostUsedEmpty') }}</p>
                </div>
                <span class="ml-auto shrink-0 rounded-full bg-[#ECFDF5] px-2 py-0.5 text-[10.5px] font-semibold text-[#059669]">
                    {{ trans_choice('modules.skillsPage.statMostUsedTag', $skMostUsedCategoryCount, ['count' => $skMostUsedCategoryCount]) }}
                </span>
            </div>
        </div>

        <div class="jc-table-card">
            <div class="jc-table-toolbar">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="jc-t-search">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" id="sk-table-search" placeholder="{{ __('modules.skillsPage.searchPlaceholder') }}" autocomplete="off">
                    </div>
                    <select id="sk-cat-filter" class="cursor-pointer rounded-xl border border-[#E2DED8] bg-white px-3 py-2 text-[12.5px] font-medium text-[#5A6478] outline-none focus:border-[#2563EB]">
                        <option value="">{{ __('modules.skillsPage.filterAllCategories') }}</option>
                        @foreach($jobCategories as $cat)
                            <option value="{{ $cat->id }}">{{ ucfirst($cat->name) }}</option>
                        @endforeach
                    </select>
                    @if(in_array('add_skills', $userPermissions))
                        <button type="button"
                                onclick="window.skModalOpenAiGenerate()"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#2563EB] bg-white px-4 py-2.5 text-[12.5px] font-semibold text-[#2563EB] shadow-sm transition hover:bg-[#EFF6FF] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.912 5.813a2 2 0 001.268 1.268L21 12l-5.82 1.919a2 2 0 00-1.261 1.261L12 21l-1.919-5.82a2 2 0 00-1.261-1.261L3 12l5.813-1.919a2 2 0 001.268-1.268L12 3z"/></svg>
                            {{ __('modules.skillsPage.aiGenerateButton') }}
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-[12px] text-[#8892A0]">
                    <span>{{ __('modules.skillsPage.showing') }}</span>
                    <span class="font-semibold text-[#1A1E2E]" id="sk-show-count">{{ $skTotal }}</span>
                    <span>{{ __('modules.skillsPage.of') }}</span>
                    <span class="font-semibold text-[#1A1E2E]" id="sk-total-num">{{ $skTotal }}</span>
                    <span>{{ __('modules.skillsPage.skills') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="jc-cat-table">
                    <thead>
                    <tr>
                        <th style="width:56px;">#</th>
                        <th>{{ __('modules.skillsPage.skillName') }}</th>
                        <th>{{ __('modules.skillsPage.jobCategory') }}</th>
                        <th>{{ __('modules.skillsPage.added') }}</th>
                        <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                    </tr>
                    </thead>
                    <tbody id="sk-skill-tbody">
                    @forelse($skills as $skill)
                        @php
                            $cid = (int) $skill->category_id;
                            $dot = $skColors[$cid % count($skColors)];
                            $catName = $skill->category ? $skill->category->name : '';
                            $letter = mb_strtoupper(mb_substr($skill->name, 0, 1));
                        @endphp
                        <tr class="sk-skill-row"
                            data-id="{{ $skill->id }}"
                            data-name="{{ e($skill->name) }}"
                            data-name-sort="{{ e(strtolower($skill->name)) }}"
                            data-cat-id="{{ $cid }}"
                            data-cat-sort="{{ e(strtolower($catName)) }}">
                            <td class="jc-td-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="sk-skill-name">
                                    <div class="sk-skill-icon" style="background:{{ $dot }}18;color:{{ $dot }};">{{ $letter }}</div>
                                    <span class="font-semibold text-[#1A1E2E]">{{ ucfirst($skill->name) }}</span>
                                </div>
                            </td>
                            <td>
                                @if($skill->category)
                                    <span class="sk-cat-pill" style="background:{{ $dot }}18;color:{{ $dot }};">
                                        <span class="jc-cat-dot" style="background:{{ $dot }};"></span>
                                        {{ ucfirst($skill->category->name) }}
                                    </span>
                                @else
                                    <span class="text-[12.5px] text-[#8892A0]">—</span>
                                @endif
                            </td>
                            <td class="text-[12.5px] text-[#8892A0]">{{ $skill->created_at ? $skill->created_at->format('M j, Y') : '—' }}</td>
                            <td class="jc-td-right" style="padding-right:20px;">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array('edit_skills', $userPermissions))
                                        <button type="button"
                                                class="jc-action-btn jc-btn-edit sk-open-edit"
                                                title="@lang('app.edit')"
                                                data-id="{{ $skill->id }}"
                                                data-name="{{ $skill->name }}"
                                                data-category-id="{{ $skill->category_id }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    @if(in_array('delete_skills', $userPermissions))
                                        <button type="button"
                                                class="jc-action-btn jc-btn-del sk-open-del"
                                                title="@lang('app.delete')"
                                                data-row-id="{{ $skill->id }}"
                                                data-name="{{ $skill->name }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="sk-row-initial-empty">
                            <td colspan="5">
                                <div class="jc-empty-state">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                        <svg class="h-6 w-6" fill="none" stroke="#C4CBD4" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <p class="text-[14px] font-semibold text-[#5A6478]">@lang('modules.emptyTable')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="sk-filter-empty" class="hidden">
                        <td colspan="5">
                            <div class="jc-empty-state">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                    <svg class="h-6 w-6" fill="none" stroke="#C4CBD4" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-[14px] font-semibold text-[#5A6478]">{{ __('modules.skillsPage.emptyFilter') }}</p>
                                <p class="mt-1 text-[12.5px] text-[#B0B8C4]">{{ __('modules.skillsPage.emptyFilterHint') }}</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(in_array('add_skills', $userPermissions))
        <div id="sk-modal-create" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="sk-create-title" onclick="window.skBackdropCloseCreate(event)">
            <div class="jc-modal" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="sk-create-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">{{ __('modules.skillsPage.modalCreateTitle') }}</h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]">{{ __('modules.skillsPage.modalCreateSub') }}</p>
                    </div>
                    <button type="button" onclick="window.skModalCloseCreate()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="ajax-form" method="POST" id="sk-form-create" onsubmit="return false;">
                    @csrf
                    <div id="alert"></div>
                    <div class="jc-modal-body">
                        <div class="form-group">
                            <label for="sk-create-name" class="jc-field-label">{{ __('modules.skillsPage.skillNameLabel') }} <span class="text-red-400">*</span></label>
                            <input type="text" name="name[]" id="sk-create-name" autocomplete="off" class="jc-field-input"
                                   placeholder="{{ __('modules.skillsPage.skillNamePlaceholder') }}">
                        </div>
                        <div class="form-group">
                            <label for="sk-create-category" class="jc-field-label">{{ __('modules.skillsPage.jobCategoryLabel') }} <span class="text-red-400">*</span></label>
                            <select name="category_id" id="sk-create-category" class="jc-field-input cursor-pointer">
                                <option value="">{{ __('modules.skillsPage.selectCategory') }}</option>
                                @foreach($jobCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ ucfirst($cat->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.skModalCloseCreate()">@lang('app.cancel')</button>
                        <button type="button" id="sk-save-create" class="jc-btn-save">{{ __('modules.skillsPage.saveSkill') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if(in_array('edit_skills', $userPermissions))
        <div id="sk-modal-edit" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="sk-edit-title" onclick="window.skBackdropCloseEdit(event)">
            <div class="jc-modal" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="sk-edit-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">{{ __('modules.skillsPage.modalEditTitle') }}</h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]" id="sk-edit-sub"></p>
                    </div>
                    <button type="button" onclick="window.skModalCloseEdit()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="ajax-form" method="POST" id="sk-form-edit" onsubmit="return false;">
                    <div id="alert"></div>
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="sk-edit-id" value="">
                    <div class="jc-modal-body">
                        <div class="form-group">
                            <label for="sk-edit-name" class="jc-field-label">{{ __('modules.skillsPage.skillNameLabel') }} <span class="text-red-400">*</span></label>
                            <input type="text" name="name" id="sk-edit-name" autocomplete="off" class="jc-field-input"
                                   placeholder="{{ __('modules.skillsPage.skillNamePlaceholder') }}">
                        </div>
                        <div class="form-group">
                            <label for="sk-edit-category" class="jc-field-label">{{ __('modules.skillsPage.jobCategoryLabel') }} <span class="text-red-400">*</span></label>
                            <select name="category_id" id="sk-edit-category" class="jc-field-input cursor-pointer">
                                <option value="">{{ __('modules.skillsPage.selectCategory') }}</option>
                                @foreach($jobCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ ucfirst($cat->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.skModalCloseEdit()">@lang('app.cancel')</button>
                        <button type="button" id="sk-save-edit" class="jc-btn-save">{{ __('modules.skillsPage.saveSkill') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if(in_array('delete_skills', $userPermissions))
        <div id="sk-modal-delete" class="jc-modal-bg" role="dialog" aria-modal="true" onclick="window.skBackdropCloseDel(event)">
            <div class="jc-modal" style="width:380px;max-width:92vw;" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FFF1F2]">
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-[#1A1E2E]">{{ __('modules.skillsPage.deleteSkill') }}</h3>
                            <p class="text-[11.5px] text-[#8892A0]">{{ __('modules.skillsPage.deleteCannotUndo') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.skModalCloseDel()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="jc-modal-body">
                    <p class="text-[13.5px] leading-relaxed text-[#5A6478]" id="sk-del-body"></p>
                </div>
                <div class="jc-modal-ft">
                    <button type="button" class="jc-btn-cancel" onclick="window.skModalCloseDel()">@lang('app.cancel')</button>
                    <button type="button" id="sk-del-confirm" class="jc-btn-danger">@lang('app.delete')</button>
                </div>
            </div>
        </div>
    @endif

    @if(in_array('add_skills', $userPermissions))
        <div id="sk-modal-ai-generate" class="jc-modal-bg" role="dialog" aria-modal="true"
             aria-labelledby="sk-ai-generate-title" onclick="window.skBackdropCloseAiGenerate(event)">
            <div class="jc-modal" style="width:520px;max-width:92vw;" onclick="event.stopPropagation()">
                <div class="jc-modal-hd">
                    <div>
                        <h3 id="sk-ai-generate-title" class="text-[16px] font-bold tracking-[-0.01em] text-[#1A1E2E]">
                            {{ __('modules.skillsPage.aiGenerateModalTitle') }}
                        </h3>
                        <p class="mt-0.5 text-[12px] text-[#8892A0]">
                            {{ __('modules.skillsPage.aiGenerateModalSub') }}
                        </p>
                    </div>
                    <button type="button" onclick="window.skModalCloseAiGenerate()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form class="ajax-form" method="POST" id="sk-form-ai-generate" onsubmit="return false;">
                    @csrf
                    <div id="sk-ai-alert"></div>
                    <div class="jc-modal-body">
                        <p class="mb-3 text-[12.5px] text-[#8892A0]">{{ __('modules.skillsPage.aiGenerateSelectHint') }}</p>
                        <div id="sk-ai-cat-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto pr-1">
                            @foreach($jobCategories as $cat)
                                <label class="sk-ai-cat-option flex items-center gap-2 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2 text-[13px] text-[#1A1E2E] cursor-pointer transition-colors"
                                       data-cat-id="{{ $cat->id }}">
                                    <input type="checkbox"
                                           class="sk-ai-cat-checkbox h-4 w-4 accent-white"
                                           name="category_ids[]"
                                           value="{{ $cat->id }}">
                                    <span class="sk-ai-cat-name">{{ ucfirst($cat->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="jc-modal-ft">
                        <button type="button" class="jc-btn-cancel" onclick="window.skModalCloseAiGenerate()">@lang('app.cancel')</button>
                        <button type="button" id="sk-ai-generate-btn" class="jc-btn-save">{{ __('modules.skillsPage.aiGenerateConfirm') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('footer-script')
    <script>
        (function () {
            var skUpdateUrlTemplate = @json(route('admin.skills.update', ['skill' => '__ID__']));
            var skDelTemplate = {!! json_encode(__('modules.skillsPage.deleteConfirmHtml')) !!};
            var skEditSubPrefix = @json(__('modules.skillsPage.modalEditSubPrefix'));
            var skAiGenerateUrl = @json(route('admin.skills.ai-generate'));
            var skAiEmptySelection = @json(__('modules.skillsPage.aiGenerateEmptySelection'));

            function skAiApplySelectedCategoryStyles() {
                $('#sk-form-ai-generate input[name="category_ids[]"]').each(function () {
                    var $cb = $(this);
                    var checked = $cb.is(':checked');
                    var $label = $cb.closest('label.sk-ai-cat-option');

                    $label.toggleClass('bg-[#2563EB] border-[#2563EB] text-white', checked);
                    $label.toggleClass('bg-white border-[#E8E6E1] text-[#1A1E2E]', !checked);
                });
            }

            window.skBackdropCloseCreate = function (e) {
                if (e.target.id === 'sk-modal-create') window.skModalCloseCreate();
            };
            window.skBackdropCloseEdit = function (e) {
                if (e.target.id === 'sk-modal-edit') window.skModalCloseEdit();
            };
            window.skBackdropCloseDel = function (e) {
                if (e.target.id === 'sk-modal-delete') window.skModalCloseDel();
            };

            window.skBackdropCloseAiGenerate = function (e) {
                if (e.target.id === 'sk-modal-ai-generate') window.skModalCloseAiGenerate();
            };

            window.skModalOpenAiGenerate = function () {
                $('#sk-form-ai-generate').find('.has-error').removeClass('has-error');
                $('#sk-form-ai-generate').find('.help-block').remove();
                $('#sk-form-ai-generate').find('input, select, textarea').removeClass('border-red-500');
                $('#sk-ai-alert').html('');

                $('#sk-form-ai-generate input[name="category_ids[]"]').prop('checked', false);
                var cat = ($('#sk-cat-filter').val() || '').toString();
                if (cat) {
                    $('#sk-form-ai-generate input[name="category_ids[]"][value="' + cat + '"]').prop('checked', true);
                }

                skAiApplySelectedCategoryStyles();
                $('#sk-ai-generate-btn').prop('disabled', false);
                $('#sk-modal-ai-generate').addClass('is-open');
                $('body').addClass('overflow-hidden');
            };

            window.skModalCloseAiGenerate = function () {
                $('#sk-modal-ai-generate').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.skModalOpenCreate = function () {
                $('#sk-form-create').find('.has-error').removeClass('has-error');
                $('#sk-form-create').find('.help-block').remove();
                $('#sk-form-create').find('input, select, textarea').removeClass('border-red-500');
                $('#sk-form-create').find('#alert').html('');
                $('#sk-create-name').val('');
                $('#sk-create-category').val('');
                $('#sk-modal-create').addClass('is-open');
                $('body').addClass('overflow-hidden');
                $('#sk-create-name').focus();
            };

            window.skModalCloseCreate = function () {
                $('#sk-modal-create').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.skModalOpenEdit = function (id, name, categoryId) {
                $('#sk-form-edit').find('.has-error').removeClass('has-error');
                $('#sk-form-edit').find('.help-block').remove();
                $('#sk-form-edit').find('input, select, textarea').removeClass('border-red-500');
                $('#sk-edit-alert').html('');
                $('#sk-edit-id').val(id);
                $('#sk-edit-name').val(name);
                $('#sk-edit-category').val(categoryId || '');
                $('#sk-edit-sub').text(skEditSubPrefix + ' "' + name + '"');
                var url = skUpdateUrlTemplate.replace('__ID__', id);
                $('#sk-form-edit').data('sk-update-url', url);
                $('#sk-modal-edit').addClass('is-open');
                $('body').addClass('overflow-hidden');
                $('#sk-edit-name').focus();
            };

            window.skModalCloseEdit = function () {
                $('#sk-modal-edit').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            window.skModalCloseDel = function () {
                $('#sk-modal-delete').removeClass('is-open');
                $('body').removeClass('overflow-hidden');
            };

            function skRowCount() {
                return $('#sk-skill-tbody tr.sk-skill-row').length;
            }

            function skRenumberVisible() {
                var n = 0;
                $('#sk-skill-tbody tr.sk-skill-row:visible').each(function () {
                    n++;
                    $(this).find('td.jc-td-num').first().text(String(n).padStart(2, '0'));
                });
            }

            function skApplyFilter() {
                var q = ($('#sk-table-search').val() || '').toLowerCase();
                var cat = ($('#sk-cat-filter').val() || '').toString();
                var visible = 0;
                $('#sk-skill-tbody tr.sk-skill-row').each(function () {
                    var $r = $(this);
                    var nameMatch = ($r.data('name-sort') || '').toString().toLowerCase().indexOf(q) !== -1;
                    var catMatch = ($r.data('cat-sort') || '').toString().toLowerCase().indexOf(q) !== -1;
                    var textMatch = nameMatch || catMatch;
                    var filterMatch = !cat || String($r.data('cat-id')) === cat;
                    var match = textMatch && filterMatch;
                    $r.toggle(match);
                    if (match) visible++;
                });
                $('#sk-show-count').text(visible);
                skRenumberVisible();
                var hasRows = skRowCount() > 0;
                $('#sk-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
            }

            $('#sk-table-search').on('input', skApplyFilter);
            $('#sk-cat-filter').on('change', skApplyFilter);

            $('#sk-form-ai-generate').on('change', 'input[name="category_ids[]"]', function () {
                skAiApplySelectedCategoryStyles();
            });

            $('#sk-ai-generate-btn').on('click', function () {
                var selected = $('#sk-form-ai-generate input[name="category_ids[]"]:checked')
                    .map(function () {
                        return $(this).val();
                    })
                    .get();

                if (!selected.length) {
                    $('#sk-ai-alert').html('<p class="text-[13px] text-red-500">' + skAiEmptySelection + '</p>');
                    return;
                }

                $('#sk-ai-alert').html('');
                $('#sk-ai-generate-btn').prop('disabled', true);

                $.easyAjax({
                    url: skAiGenerateUrl,
                    container: '#sk-form-ai-generate',
                    type: 'POST',
                    data: $('#sk-form-ai-generate').serialize(),
                    success: function (response) {
                        if (response && response.status == 'success') {
                            window.skModalCloseAiGenerate();
                            window.location.reload();
                            return;
                        }

                        var msg = response && response.message ? response.message : 'AI generation failed.';
                        $('#sk-ai-alert').html('<p class="text-[13px] text-red-500">' + msg + '</p>');
                        $('#sk-ai-generate-btn').prop('disabled', false);
                    },
                    error: function () {
                        $('#sk-ai-generate-btn').prop('disabled', false);
                    }
                });
            });

            $('#sk-save-create').on('click', function () {
                $.easyAjax({
                    url: '{{ route('admin.skills.store') }}',
                    container: '#sk-form-create',
                    type: 'POST',
                    redirect: true,
                    data: $('#sk-form-create').serialize()
                });
            });

            $('#sk-save-edit').on('click', function () {
                var url = $('#sk-form-edit').data('sk-update-url');
                if (!url) return;
                $.easyAjax({
                    url: url,
                    container: '#sk-form-edit',
                    type: 'POST',
                    redirect: true,
                    data: $('#sk-form-edit').serialize()
                });
            });

            $(document).on('click', '.sk-open-edit', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var categoryId = $(this).data('category-id');
                if (typeof name === 'number') name = String(name);
                window.skModalOpenEdit(id, name, categoryId);
            });

            $(document).on('click', '.sk-open-del', function () {
                var id = $(this).data('row-id');
                var name = $(this).data('name');
                var safe = $('<div>').text(name).html();
                var html = skDelTemplate.split(':name').join(safe);
                $('#sk-del-body').html(html);
                $('#sk-modal-delete').data('del-id', id).addClass('is-open');
                $('body').addClass('overflow-hidden');
            });

            $('#sk-del-confirm').on('click', function () {
                var id = $('#sk-modal-delete').data('del-id');
                if (!id) return;
                var url = @json(route('admin.skills.destroy', ['skill' => '__ID__'])).replace('__ID__', id);
                var token = "{{ csrf_token() }}";
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $.unblockUI();
                            window.skModalCloseDel();
                            window.location.reload();
                        }
                    }
                });
            });

            @if(request('open') === 'create' && in_array('add_skills', $userPermissions))
            $(function () {
                window.skModalOpenCreate();
            });
            @endif

            @if($skOpenEdit && in_array('edit_skills', $userPermissions))
            $(function () {
                window.skModalOpenEdit(
                    {{ (int) $skOpenEdit->id }},
                    @json($skOpenEdit->name),
                    @json($skOpenEdit->category_id)
                );
            });
            @endif

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    if ($('#sk-modal-create').hasClass('is-open')) window.skModalCloseCreate();
                    if ($('#sk-modal-edit').hasClass('is-open')) window.skModalCloseEdit();
                    if ($('#sk-modal-delete').hasClass('is-open')) window.skModalCloseDel();
                    if ($('#sk-modal-ai-generate').hasClass('is-open')) window.skModalCloseAiGenerate();
                }
            });
        })();
    </script>
@endpush
