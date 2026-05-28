@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <style>
        @keyframes ft-fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .ft-a1 { opacity: 0; animation: ft-fu 0.4s ease 0.04s forwards; }
        .ft-a2 { opacity: 0; animation: ft-fu 0.4s ease 0.09s forwards; }
        .ft-a3 { opacity: 0; animation: ft-fu 0.4s ease 0.14s forwards; }
        .ft-sel { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%239CA3AF' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
        .ft-cb:checked + .ft-cb-box { background: #2563eb; border-color: #2563eb; }
        .ft-cb:checked + .ft-cb-box svg { opacity: 1; }
    </style>
@endpush

@php
    $canManage = in_array('manage_settings', $userPermissions);
    $footers = $footers ?? collect();
    $ftTotal = $footers->count();
    $ftActive = $footers->where('status', 'active')->count();
    $ftInactive = $footers->where('status', 'inactive')->count();
    $footerYear = date('Y');
    $ftOpenEdit = null;
    if (request('open') === 'edit' && request()->filled('id')) {
        $ftOpenEdit = $footers->firstWhere('id', (int) request('id'));
    }
    $ftSymStyles = [
        'bg-blue-50 text-blue-600',
        'bg-emerald-50 text-emerald-600',
        'bg-purple-50 text-purple-600',
        'bg-orange-50 text-orange-600',
        'bg-rose-50 text-rose-600',
        'bg-teal-50 text-teal-600',
    ];
@endphp

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-[-0.025em] text-[#1A1E2E]">{{ __('modules.footerSettingsPage.titleMain') }}
        <span class="jc-cat-serif">{{ __('modules.footerSettingsPage.titleAccent') }}</span>
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.footerSettingsPage.subtitle') }}
@endsection

@if($canManage)
@section('create-button')
    <button type="button" onclick="window.ftModalOpenCreate()"
            class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        @lang('app.createNew')
    </button>
@endsection
@endif

@section('content')
<div class="mx-auto flex  flex-col gap-6 pb-10">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 ft-a1">
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EFF6FF]">
                <svg class="h-4 w-4" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.footerSettingsPage.statTotal') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $ftTotal }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5]">
                <svg class="h-4 w-4" fill="none" stroke="#059669" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.footerSettingsPage.statActive') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $ftActive }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#F1F3F7]">
                <svg class="h-4 w-4" fill="none" stroke="#8892A0" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.footerSettingsPage.statInactive') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $ftInactive }}</p>
            </div>
        </div>
    </div>

    <div class="jc-table-card ft-a2">
        <div class="jc-table-toolbar">
            <div class="flex flex-wrap items-center gap-3">
                <div class="jc-t-search">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search" id="ft-table-search" placeholder="{{ __('modules.footerSettingsPage.searchPlaceholder') }}" autocomplete="off">
                </div>
                <select id="ft-status-filter" class="ft-sel cursor-pointer rounded-xl border border-gray-200 bg-white py-2 pl-3.5 pr-9 text-[12.5px] text-slate-600 outline-none transition focus:border-[#2563EB]">
                    <option value="">{{ __('modules.footerSettingsPage.allStatus') }}</option>
                    <option value="active">{{ __('modules.footerSettingsPage.statusActive') }}</option>
                    <option value="inactive">{{ __('modules.footerSettingsPage.statusInactive') }}</option>
                </select>
            </div>
            <p class="text-[12px] text-[#8892A0]">
                <span class="font-semibold text-[#1A1E2E]" id="ft-show-count">{{ $ftTotal }}</span>
                {{ __('modules.footerSettingsPage.of') }}
                <span class="font-semibold text-[#1A1E2E]" id="ft-total-num">{{ $ftTotal }}</span>
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="jc-cat-table">
                <thead>
                <tr>
                    @if($canManage)
                        <th class="w-12"></th>
                    @endif
                    <th class="w-14">#</th>
                    <th class="min-w-[200px]">{{ __('modules.footerSettingsPage.columnTitle') }}</th>
                    <th>{{ __('modules.footerSettingsPage.columnDescription') }}</th>
                    <th class="w-32">{{ __('modules.footerSettingsPage.columnStatus') }}</th>
                    <th class="jc-th-right w-28" style="padding-right:20px;">@lang('app.action')</th>
                </tr>
                </thead>
                <tbody id="ft-tbody">
                @forelse ($footers as $item)
                    @php
                        $plainDesc = strip_tags((string) $item->description);
                        $shortDesc = \Illuminate\Support\Str::limit($plainDesc, 120);
                        $searchBlob = strtolower($item->name.' '.$plainDesc.' '.(string) $item->external_url);
                        $symClass = $ftSymStyles[$item->id % count($ftSymStyles)];
                        $hrefDisplay = $item->external_url ?: __('modules.footerSettingsPage.internalPageNote');
                    @endphp
                    <tr class="ft-row align-top transition-colors hover:bg-gray-50/50"
                        data-id="{{ $item->id }}"
                        data-status="{{ $item->status }}"
                        data-search="{{ e($searchBlob) }}">
                        @if($canManage)
                            <td class="px-4 pt-4 pb-3.5">
                                <label class="flex cursor-pointer">
                                    <input type="checkbox" class="ft-cb sr-only" value="{{ $item->id }}">
                                    <span class="ft-cb-box flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 border-gray-300 transition-all">
                                        <svg class="h-2.5 w-2.5 text-white opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                </label>
                            </td>
                        @endif
                        <td class="px-4 pt-4 pb-3.5">
                            <span class="ft-row-num text-[12.5px] font-semibold text-[#B0B8C4]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 pt-4 pb-3.5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-[0] {{ $symClass }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13.5px] font-semibold text-[#1A1E2E]">{{ $item->name }}</p>
                                    @if($item->external_url)
                                        <a href="{{ $item->external_url }}" target="_blank" rel="noopener noreferrer" class="block truncate text-[11.5px] text-[#2563EB] hover:underline">{{ $item->external_url }}</a>
                                    @else
                                        <span class="text-[11.5px] text-[#8892A0]">{{ $hrefDisplay }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="max-w-md px-4 pt-4 pb-3.5">
                            <p class="ft-desc-preview text-[13px] leading-relaxed text-slate-500">{{ $shortDesc }}</p>
                            @if(strlen($plainDesc) > 120)
                                @if($canManage)
                                    <button type="button" class="ft-open-edit mt-1 border-none bg-transparent p-0 text-[11.5px] font-semibold text-[#2563EB] hover:underline" data-id="{{ $item->id }}">{{ __('modules.footerSettingsPage.readMore') }}</button>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 pt-4 pb-3.5">
                            @if($item->status === 'active')
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[12.5px] font-semibold text-emerald-700">{{ __('modules.footerSettingsPage.statusActive') }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-gray-300"></span>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[12.5px] font-semibold text-gray-500">{{ __('modules.footerSettingsPage.statusInactive') }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="jc-td-right px-4 pt-4 pb-3.5" style="padding-right:20px;">
                            @if($canManage)
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" class="ft-open-edit flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF] transition-colors hover:bg-[#DBEAFE]" title="@lang('app.edit')" data-id="{{ $item->id }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="ft-open-del flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 transition-colors hover:bg-red-100" title="@lang('app.delete')" data-row-id="{{ $item->id }}" data-name="{{ e($item->name) }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <span class="text-[12.5px] text-[#8892A0]">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="ft-row-initial-empty">
                        <td colspan="{{ $canManage ? 6 : 5 }}">
                            <div class="flex flex-col items-center py-16 text-center">
                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                    <svg class="h-7 w-7 text-[#C4CBD4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <p class="text-[14px] font-semibold text-[#5A6478]">{{ __('modules.footerSettingsPage.emptyTable') }}</p>
                                <p class="mt-1 text-[12px] text-[#B0B8C4]">{{ __('modules.footerSettingsPage.emptyTableHint') }}</p>
                                @if($canManage)
                                    <button type="button" onclick="window.ftModalOpenCreate()" class="mt-4 inline-flex items-center gap-2 rounded-xl border-none bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white transition-colors hover:bg-[#1d4ed8]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('modules.footerSettingsPage.createFirst') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                <tr id="ft-filter-empty" class="hidden">
                    <td colspan="{{ $canManage ? 6 : 5 }}">
                        <div class="flex flex-col items-center py-16 text-center">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                <svg class="h-7 w-7 text-[#C4CBD4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <p class="text-[14px] font-semibold text-[#5A6478]">{{ __('modules.footerSettingsPage.emptyFilter') }}</p>
                            <p class="mt-1 text-[12px] text-[#B0B8C4]">{{ __('modules.footerSettingsPage.emptyFilterHint') }}</p>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#F0EEE9] px-5 py-3.5">
            <p class="text-[12px] text-[#8892A0]">{{ __('modules.footerSettingsPage.footerCredit', ['year' => $footerYear]) }}</p>
            @if($canManage)
                <div id="ft-bulk-bar" class="hidden items-center gap-2">
                    <span class="text-[12.5px] font-semibold text-[#8892A0]" id="ft-sel-count">0 {{ __('modules.footerSettingsPage.bulkSelected') }}</span>
                    <button type="button" id="ft-bulk-delete" class="rounded-lg bg-red-50 px-3 py-1.5 text-[12px] font-semibold text-red-500 transition-colors hover:bg-red-100">{{ __('modules.footerSettingsPage.bulkDelete') }}</button>
                </div>
            @endif
        </div>
    </div>
</div>

@if($canManage)
<div id="ft-modal-form" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="ft-modal-title" onclick="window.ftBackdropCloseForm(event)">
    <div class="jc-modal" style="width:540px;max-width:95vw;" onclick="event.stopPropagation()">
        <div class="jc-modal-hd">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EFF6FF]">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <h3 id="ft-modal-title" class="text-[15.5px] font-bold text-[#1A1E2E]">{{ __('modules.footerSettingsPage.modalAddTitle') }}</h3>
                    <p id="ft-modal-sub" class="mt-0.5 text-[11.5px] text-[#8892A0]">{{ __('modules.footerSettingsPage.modalAddSub') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.ftModalCloseForm()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form class="ajax-form" method="POST" id="ft-settings-form" onsubmit="return false;">
            <div id="ft-form-alert"></div>
            @csrf
            <input type="hidden" name="_method" id="ft-form-method" value="POST">
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="jc-field-label" for="ft-in-name">{{ __('modules.footerSettingsPage.labelTitle') }} <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <input type="text" name="name" id="ft-in-name" autocomplete="off" class="jc-field-input pl-9" placeholder="e.g. About Us">
                        </div>
                    </div>
                    <div>
                        <label class="jc-field-label" for="ft-in-url">{{ __('modules.footerSettingsPage.labelUrl') }}</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            <input type="text" name="external_url" id="ft-in-url" autocomplete="off" class="jc-field-input pl-9" placeholder="{{ __('modules.footerSettingsPage.urlPlaceholder') }}">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="jc-field-label" for="ft-in-desc">{{ __('modules.footerSettingsPage.labelDescription') }} <span class="text-red-400">*</span></label>
                    <textarea name="description" id="ft-in-desc" rows="5" class="bs-f-textarea resize-none leading-relaxed" placeholder="{{ __('modules.footerSettingsPage.descPlaceholder') }}"></textarea>
                    <div class="mt-1.5 flex items-center justify-between">
                        <p class="text-[11px] text-gray-400">{{ __('modules.footerSettingsPage.descHtmlHint') }}</p>
                        <p class="text-[11px] text-gray-400" id="ft-char-count">0 / 50000</p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="jc-field-label" for="ft-in-status">{{ __('modules.footerSettingsPage.labelStatus') }}</label>
                        <select name="status" id="ft-in-status" class="bs-f-sel">
                            <option value="active">{{ __('modules.footerSettingsPage.statusActive') }}</option>
                            <option value="inactive">{{ __('modules.footerSettingsPage.statusInactive') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="jc-field-label" for="ft-in-target">{{ __('modules.footerSettingsPage.labelOpenIn') }}</label>
                        <select name="link_target" id="ft-in-target" class="bs-f-sel">
                            <option value="_self">{{ __('modules.footerSettingsPage.openSameTab') }}</option>
                            <option value="_blank">{{ __('modules.footerSettingsPage.openNewTab') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="jc-modal-ft">
                <button type="button" class="jc-btn-cancel" onclick="window.ftModalCloseForm()">@lang('app.cancel')</button>
                <button type="button" id="ft-save-btn" class="jc-btn-save">
                    <span id="ft-save-label">{{ __('modules.footerSettingsPage.saveLink') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="ft-modal-delete" class="jc-modal-bg" role="dialog" aria-modal="true" onclick="window.ftBackdropCloseDel(event)">
    <div class="jc-modal" style="width:380px;max-width:92vw;" onclick="event.stopPropagation()">
        <div class="jc-modal-hd">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#FFF1F2]">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-[#1A1E2E]">{{ __('modules.footerSettingsPage.deleteTitle') }}</h3>
                    <p class="text-[11.5px] text-[#8892A0]">{{ __('modules.footerSettingsPage.deleteCannotUndo') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.ftModalCloseDel()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] hover:bg-gray-100" aria-label="@lang('app.close')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <p class="text-[13.5px] leading-relaxed text-[#5A6478]" id="ft-del-body"></p>
        </div>
        <div class="jc-modal-ft">
            <button type="button" class="jc-btn-cancel" onclick="window.ftModalCloseDel()">@lang('app.cancel')</button>
            <button type="button" id="ft-del-confirm" class="jc-btn-danger">@lang('app.delete')</button>
        </div>
    </div>
</div>
@endif
@endsection

@push('footer-script')
@if($canManage)
@php
    $ftLinksForJs = $footers->map(function ($f) {
        return [
            'id' => $f->id,
            'name' => $f->name,
            'description' => $f->description ?? '',
            'external_url' => $f->external_url ?? '',
            'link_target' => $f->link_target ?? '_self',
            'status' => $f->status,
        ];
    })->values();
@endphp
<script>
    window.__footerLinks = @json($ftLinksForJs);
</script>
<script>
    (function () {
        var ftStoreUrl = @json(route('admin.footer-settings.store'));
        var ftUpdateTpl = @json(route('admin.footer-settings.update', ['footer_setting' => '__ID__']));
        var ftDelTpl = @json(route('admin.footer-settings.destroy', ['footer_setting' => '__ID__']));
        var ftBulkDestroyUrl = @json(route('admin.footer-settings.bulk-destroy'));
        var ftDelHtml = {!! json_encode(__('modules.footerSettingsPage.deleteConfirmHtml')) !!};
        var ftEditSub = @json(__('modules.footerSettingsPage.modalEditSubPrefix'));
        var token = @json(csrf_token());
        var ftDescMax = 50000;

        function ftLinkById(id) {
            id = parseInt(id, 10);
            var rows = window.__footerLinks || [];
            for (var i = 0; i < rows.length; i++) {
                if (parseInt(rows[i].id, 10) === id) return rows[i];
            }
            return null;
        }

        window.ftBackdropCloseForm = function (e) {
            if (e.target.id === 'ft-modal-form') window.ftModalCloseForm();
        };
        window.ftBackdropCloseDel = function (e) {
            if (e.target.id === 'ft-modal-delete') window.ftModalCloseDel();
        };

        function ftUpdateCharCount() {
            var v = ($('#ft-in-desc').val() || '');
            $('#ft-char-count').text(v.length + ' / ' + ftDescMax);
        }

        window.ftModalOpenCreate = function () {
            $('#ft-settings-form').find('.has-error').removeClass('has-error');
            $('#ft-settings-form').find('.help-block').remove();
            $('#ft-settings-form').find('input, select, textarea').removeClass('border-red-500');
            $('#ft-form-alert').html('');
            $('#ft-form-method').val('POST');
            $('#ft-settings-form').removeData('ft-update-url');
            $('#ft-modal-title').text(@json(__('modules.footerSettingsPage.modalAddTitle')));
            $('#ft-modal-sub').text(@json(__('modules.footerSettingsPage.modalAddSub')));
            $('#ft-save-label').text(@json(__('modules.footerSettingsPage.saveLink')));
            $('#ft-in-name').val('');
            $('#ft-in-url').val('');
            $('#ft-in-desc').val('');
            $('#ft-in-status').val('active');
            $('#ft-in-target').val('_self');
            ftUpdateCharCount();
            $('#ft-modal-form').addClass('is-open');
            $('body').addClass('overflow-hidden');
            setTimeout(function () { $('#ft-in-name').focus(); }, 80);
        };

        window.ftModalOpenEdit = function (id) {
            var l = ftLinkById(id);
            if (!l) return;
            $('#ft-settings-form').find('.has-error').removeClass('has-error');
            $('#ft-settings-form').find('.help-block').remove();
            $('#ft-settings-form').find('input, select, textarea').removeClass('border-red-500');
            $('#ft-form-alert').html('');
            var url = ftUpdateTpl.replace('__ID__', id);
            $('#ft-form-method').val('PUT');
            $('#ft-settings-form').data('ft-update-url', url);
            $('#ft-modal-title').text(@json(__('modules.footerSettingsPage.modalEditTitle')));
            $('#ft-modal-sub').text(ftEditSub + ' "' + l.name + '"');
            $('#ft-save-label').text(@json(__('modules.footerSettingsPage.updateLink')));
            $('#ft-in-name').val(l.name);
            $('#ft-in-url').val(l.external_url || '');
            $('#ft-in-desc').val(l.description || '');
            $('#ft-in-status').val(l.status);
            $('#ft-in-target').val(l.link_target === '_blank' ? '_blank' : '_self');
            ftUpdateCharCount();
            $('#ft-modal-form').addClass('is-open');
            $('body').addClass('overflow-hidden');
            $('#ft-in-name').focus();
        };

        window.ftModalCloseForm = function () {
            $('#ft-modal-form').removeClass('is-open');
            $('body').removeClass('overflow-hidden');
        };

        window.ftModalCloseDel = function () {
            $('#ft-modal-delete').removeClass('is-open');
            $('body').removeClass('overflow-hidden');
        };

        $('#ft-in-desc').on('input', ftUpdateCharCount);

        $('#ft-save-btn').on('click', function () {
            var url = $('#ft-settings-form').data('ft-update-url');
            var isEdit = $('#ft-form-method').val() === 'PUT';
            $.easyAjax({
                url: isEdit ? url : ftStoreUrl,
                container: '#ft-settings-form',
                type: 'POST',
                redirect: true,
                data: $('#ft-settings-form').serialize()
            });
        });

        $(document).on('click', '.ft-open-edit', function (e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'), 10);
            if (!id) return;
            window.ftModalOpenEdit(id);
        });

        $(document).on('click', '.ft-open-del', function () {
            var id = $(this).data('row-id');
            var name = $(this).data('name');
            var safe = $('<div>').text(name).html();
            var html = ftDelHtml.split(':name').join(safe);
            $('#ft-del-body').html(html);
            $('#ft-modal-delete').data('del-id', id).addClass('is-open');
            $('body').addClass('overflow-hidden');
        });

        $('#ft-del-confirm').on('click', function () {
            var id = $('#ft-modal-delete').data('del-id');
            if (!id) return;
            var url = ftDelTpl.replace('__ID__', id);
            $.easyAjax({
                type: 'POST',
                url: url,
                data: { _token: token, _method: 'DELETE' },
                success: function (response) {
                    if (response.status == 'success') {
                        $.unblockUI();
                        window.ftModalCloseDel();
                        window.location.reload();
                    }
                }
            });
        });

        function ftSelectedIds() {
            var ids = [];
            $('.ft-cb:checked').each(function () {
                ids.push($(this).val());
            });
            return ids;
        }

        function ftUpdateBulkBar() {
            var n = ftSelectedIds().length;
            var $bar = $('#ft-bulk-bar');
            if (n > 0) {
                $bar.removeClass('hidden').css('display', 'flex');
                $('#ft-sel-count').text(n + ' ' + @json(__('modules.footerSettingsPage.bulkSelected')));
            } else {
                $bar.addClass('hidden');
            }
        }

        $(document).on('change', '.ft-cb', function () {
            var $tr = $(this).closest('tr.ft-row');
            if ($(this).is(':checked')) {
                $tr.addClass('bg-blue-50/40');
            } else {
                $tr.removeClass('bg-blue-50/40');
            }
            ftUpdateBulkBar();
        });

        $('#ft-bulk-delete').on('click', function () {
            var ids = ftSelectedIds();
            if (!ids.length) return;
            $.easyAjax({
                type: 'POST',
                url: ftBulkDestroyUrl,
                data: { _token: token, ids: ids },
                success: function () {
                    window.location.reload();
                }
            });
        });

        function ftApplyFilter() {
            var q = ($('#ft-table-search').val() || '').toLowerCase().trim();
            var sf = ($('#ft-status-filter').val() || '').toString();
            var visible = 0;
            var n = 0;
            $('#ft-tbody tr.ft-row').each(function () {
                n++;
                var $tr = $(this);
                var blob = ($tr.data('search') || '').toString().toLowerCase();
                var st = ($tr.data('status') || '').toString();
                var matchQ = !q || blob.indexOf(q) !== -1;
                var matchS = !sf || st === sf;
                var match = matchQ && matchS;
                $tr.toggle(match);
                if (match) {
                    visible++;
                    $tr.find('.ft-row-num').text(String(visible).padStart(2, '0'));
                }
            });
            $('#ft-show-count').text(visible);
            $('#ft-total-num').text(n);
            var hasRows = n > 0;
            $('#ft-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
        }

        $('#ft-table-search').on('input', ftApplyFilter);
        $('#ft-status-filter').on('change', ftApplyFilter);

        @if(request('open') === 'create')
        $(function () { window.ftModalOpenCreate(); });
        @endif

        @if($ftOpenEdit)
        $(function () { window.ftModalOpenEdit({{ (int) $ftOpenEdit->id }}); });
        @endif

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                if ($('#ft-modal-form').hasClass('is-open')) window.ftModalCloseForm();
                if ($('#ft-modal-delete').hasClass('is-open')) window.ftModalCloseDel();
            }
        });
    })();
</script>
@else
<script>
    (function () {
        function ftApplyFilterReadonly() {
            var q = ($('#ft-table-search').val() || '').toLowerCase().trim();
            var sf = ($('#ft-status-filter').val() || '').toString();
            var visible = 0;
            var n = 0;
            $('#ft-tbody tr.ft-row').each(function () {
                n++;
                var $tr = $(this);
                var blob = ($tr.data('search') || '').toString().toLowerCase();
                var st = ($tr.data('status') || '').toString();
                var matchQ = !q || blob.indexOf(q) !== -1;
                var matchS = !sf || st === sf;
                var match = matchQ && matchS;
                $tr.toggle(match);
                if (match) {
                    visible++;
                    $tr.find('.ft-row-num').text(String(visible).padStart(2, '0'));
                }
            });
            $('#ft-show-count').text(visible);
            $('#ft-total-num').text(n);
            var hasRows = n > 0;
            $('#ft-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
        }
        $('#ft-table-search').on('input', ftApplyFilterReadonly);
        $('#ft-status-filter').on('change', ftApplyFilterReadonly);
    })();
</script>
@endif
@endpush
