@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <style>
        @keyframes lang-fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .lang-a1 { opacity: 0; animation: lang-fu 0.4s ease 0.04s forwards; }
        .lang-a2 { opacity: 0; animation: lang-fu 0.4s ease 0.09s forwards; }
        .lang-a3 { opacity: 0; animation: lang-fu 0.4s ease 0.14s forwards; }
        .lang-def-thumb { transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .lang-stat-thumb { transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .lang-sel { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%239CA3AF' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
    </style>
@endpush

@php
    $canManage = in_array('manage_settings', $userPermissions);
    $languages = $languages ?? collect();
    $langTotal = $languages->count();
    $langActive = $languages->where('status', 'enabled')->count();
    $defaultEnglish = $languages->first(function ($l) {
        return strtolower($l->language_code) === 'en';
    });
    $defaultName = $defaultEnglish ? $defaultEnglish->language_name : '—';
    $footerYear = date('Y');
    $langOpenEdit = null;
    if (request('open') === 'edit' && request()->filled('id')) {
        $langOpenEdit = $languages->firstWhere('id', (int) request('id'));
    }
    $flagMap = [
        'ar' => '🇸🇦', 'zh-cn' => '🇨🇳', 'zh-tw' => '🇹🇼', 'nl' => '🇳🇱', 'en' => '🇬🇧',
        'et' => '🇪🇪', 'fr' => '🇫🇷', 'de' => '🇩🇪', 'hi' => '🇮🇳', 'ja' => '🇯🇵',
        'pt' => '🇧🇷', 'pt-br' => '🇧🇷', 'es' => '🇪🇸', 'ru' => '🇷🇺', 'tr' => '🇹🇷', 'ur' => '🇵🇰',
        'fa' => '🇮🇷', 'gr' => '🇬🇷', 'it' => '🇮🇹', 'pl' => '🇵🇱', 'ro' => '🇷🇴',
    ];
    $flagFor = function ($code) use ($flagMap) {
        $k = strtolower(str_replace('_', '-', $code));

        return $flagMap[$k] ?? '🌐';
    };
@endphp

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-[-0.025em] text-[#1A1E2E]">{{ __('modules.languageSettingsPage.titleMain') }}
        <span class="jc-cat-serif">{{ __('modules.languageSettingsPage.titleAccent') }}</span>
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.languageSettingsPage.subtitle') }}
@endsection

@section('create-button')
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ url('/translations') }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2 rounded-xl border border-[#E8E6E1] bg-white px-4 py-2.5 text-[13px] font-semibold text-slate-600 transition hover:border-[#2563EB] hover:text-[#2563EB]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
            @lang('app.translations')
        </a>
        @if($canManage)
            <button type="button" onclick="window.langModalOpenCreate()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                @lang('app.createNew')
            </button>
        @endif
    </div>
@endsection

@section('content')
<div class="mx-auto flex  flex-col gap-6 pb-10">
    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lang-a1">
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EFF6FF]">
                <svg class="h-4 w-4" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.languageSettingsPage.statTotal') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $langTotal }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5]">
                <svg class="h-4 w-4" fill="none" stroke="#059669" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.languageSettingsPage.statActive') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $langActive }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 rounded-xl border border-[#E8E6E1] bg-white px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                <svg class="h-4 w-4" fill="none" stroke="#D97706" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.languageSettingsPage.statDefault') }}</p>
                <p class="text-[18px] font-extrabold tracking-tight text-[#1A1E2E]">{{ $defaultName }}</p>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="jc-table-card lang-a2">
        <div class="jc-table-toolbar">
            <div class="flex flex-wrap items-center gap-3">
                <div class="jc-t-search">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search" id="lang-table-search" placeholder="{{ __('modules.languageSettingsPage.searchPlaceholder') }}" autocomplete="off">
                </div>
                <select id="lang-status-filter" class="lang-sel cursor-pointer rounded-xl border border-gray-200 bg-white py-2 pl-3.5 pr-9 text-[12.5px] text-slate-600 outline-none transition focus:border-[#2563EB]">
                    <option value="">{{ __('modules.languageSettingsPage.allStatus') }}</option>
                    <option value="enabled">{{ __('modules.languageSettingsPage.statusActive') }}</option>
                    <option value="disabled">{{ __('modules.languageSettingsPage.statusInactive') }}</option>
                </select>
            </div>
            <p class="text-[12px] text-[#8892A0]">
                <span class="font-semibold text-[#1A1E2E]" id="lang-show-count">{{ $langTotal }}</span>
                {{ __('modules.languageSettingsPage.of') }}
                <span class="font-semibold text-[#1A1E2E]" id="lang-total-num">{{ $langTotal }}</span>
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="jc-cat-table">
                <thead>
                <tr>
                    <th class="w-14">#</th>
                    <th>{{ __('modules.languageSettingsPage.columnLanguage') }}</th>
                    <th>{{ __('modules.languageSettingsPage.columnCode') }}</th>
                    <th>{{ __('modules.languageSettingsPage.columnStatus') }}</th>
                    <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                </tr>
                </thead>
                <tbody id="lang-tbody">
                @forelse ($languages as $item)
                    @php
                        $codeLower = strtolower($item->language_code);
                        $isDefault = $codeLower === 'en';
                        $isCurrentLocale = strtolower($global->locale) === $codeLower;
                        $isEnabled = $item->status === 'enabled';
                        $searchBlob = strtolower($item->language_name.' '.$item->language_code);
                    @endphp
                    <tr class="lang-row transition-colors hover:bg-gray-50/60"
                        data-id="{{ $item->id }}"
                        data-status="{{ $item->status }}"
                        data-search="{{ e($searchBlob) }}">
                        <td class="px-5 py-3.5">
                            <span class="lang-row-num text-[12.5px] font-semibold text-[#B0B8C4]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="text-[22px] leading-none" aria-hidden="true">{{ $flagFor($item->language_code) }}</span>
                                <div>
                                    <p class="text-[13.5px] font-semibold text-[#1A1E2E]">{{ $item->language_name }}</p>
                                    @if($isDefault)
                                        <span class="mt-0.5 inline-block rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600">{{ __('modules.languageSettingsPage.defaultBadge') }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-[12.5px] font-bold tracking-widest text-slate-600">{{ $codeLower }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($isDefault)
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span class="text-[12.5px] font-semibold text-emerald-600">{{ __('modules.languageSettingsPage.statusActiveLabel') }}</span>
                                </div>
                            @elseif($isCurrentLocale)
                                <span class="text-[12.5px] font-semibold {{ $isEnabled ? 'text-emerald-600' : 'text-[#8892A0]' }}"
                                      title="{{ __('modules.languageSettings.statusDisabledNote') }}">
                                    {{ $isEnabled ? __('app.enable') : __('app.disable') }}
                                </span>
                            @elseif($canManage)
                                <button type="button"
                                        class="lang-status-toggle relative h-[23px] w-[42px] shrink-0 cursor-pointer rounded-full border-none transition-colors duration-200 {{ $isEnabled ? 'bg-[#2563EB]' : 'bg-[#D1D5DB]' }}"
                                        data-lang-id="{{ $item->id }}"
                                        data-enabled="{{ $isEnabled ? '1' : '0' }}"
                                        aria-pressed="{{ $isEnabled ? 'true' : 'false' }}">
                                    <span class="lang-stat-thumb absolute top-[3px] h-[17px] w-[17px] rounded-full bg-white shadow-sm" style="left: {{ $isEnabled ? '22px' : '3px' }};"></span>
                                </button>
                            @else
                                <span class="text-[12.5px] text-[#8892A0]">{{ $isEnabled ? __('app.enable') : __('app.disable') }}</span>
                            @endif
                        </td>
                        <td class="jc-td-right px-5 py-3.5" style="padding-right:20px;">
                            @if($isDefault)
                                <span class="inline-block rounded-lg bg-amber-50 px-3 py-1.5 text-[11.5px] font-semibold text-amber-600">{{ __('modules.languageSettingsPage.defaultLocked') }}</span>
                            @elseif($canManage)
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" class="lang-open-edit flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF] transition-colors hover:bg-[#DBEAFE]"
                                            title="@lang('app.edit')"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ e($item->language_name) }}"
                                            data-code="{{ e($codeLower) }}"
                                            data-status="{{ $item->status }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="lang-open-del flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 transition-colors hover:bg-red-100"
                                            title="@lang('app.delete')"
                                            data-row-id="{{ $item->id }}"
                                            data-name="{{ e($item->language_name) }}">
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
                    <tr id="lang-row-initial-empty">
                        <td colspan="5">
                            <div class="flex flex-col items-center py-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                    <svg class="h-6 w-6 text-[#C4CBD4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10"/>
                                    </svg>
                                </div>
                                <p class="text-[14px] font-semibold text-[#5A6478]">@lang('modules.datatables.emptyTable')</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                <tr id="lang-filter-empty" class="hidden">
                    <td colspan="5">
                        <div class="flex flex-col items-center py-12 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F3F7]">
                                <svg class="h-6 w-6 text-[#C4CBD4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10"/>
                                </svg>
                            </div>
                            <p class="text-[14px] font-semibold text-[#5A6478]">{{ __('modules.languageSettingsPage.emptyFilter') }}</p>
                            <p class="mt-1 text-[12.5px] text-[#B0B8C4]">{{ __('modules.languageSettingsPage.emptyFilterHint') }}</p>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#F0EEE9] px-5 py-3.5">
            <p class="text-[12px] text-[#8892A0]">{{ __('modules.languageSettingsPage.footerCredit', ['year' => $footerYear]) }}</p>
        </div>
    </div>
</div>

@if($canManage)
{{-- Add / Edit modal --}}
<div id="lang-modal-form" class="jc-modal-bg" role="dialog" aria-modal="true" aria-labelledby="lang-modal-title" onclick="window.langBackdropCloseForm(event)">
    <div class="jc-modal" style="width:500px;max-width:94vw;" onclick="event.stopPropagation()">
        <div class="jc-modal-hd">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EFF6FF]">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                </div>
                <div>
                    <h3 id="lang-modal-title" class="text-[15.5px] font-bold text-[#1A1E2E]">{{ __('modules.languageSettingsPage.modalAddTitle') }}</h3>
                    <p id="lang-modal-sub" class="mt-0.5 text-[11.5px] text-[#8892A0]">{{ __('modules.languageSettingsPage.modalAddSub') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.langModalCloseForm()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100" aria-label="@lang('app.close')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form class="ajax-form" method="POST" id="lang-settings-form" onsubmit="return false;">
            <div id="lang-form-alert"></div>
            @csrf
            <input type="hidden" name="_method" id="lang-form-method" value="POST">
            <input type="hidden" name="id" id="lang-form-record-id" value="">
            <input type="hidden" name="status" id="lang-form-status" value="disabled">
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="jc-field-label" for="lang-in-name">{{ __('modules.languageSettingsPage.labelLanguageName') }} <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10"/>
                            </svg>
                            <input type="text" name="language_name" id="lang-in-name" autocomplete="off" class="jc-field-input pl-9" placeholder="e.g. Spanish">
                        </div>
                        <p id="lang-err-name" class="mt-1.5 hidden text-[11.5px] text-red-500"></p>
                    </div>
                    <div>
                        <label class="jc-field-label" for="lang-in-code">{{ __('modules.languageSettingsPage.labelLanguageCode') }} <span class="text-red-400">*</span></label>
                        <input type="text" name="language_code" id="lang-in-code" maxlength="16" autocomplete="off"
                               class="jc-field-input text-center text-[13.5px] font-bold tracking-widest" placeholder="e.g. es">
                        <p id="lang-err-code" class="mt-1.5 hidden text-[11.5px] text-red-500"></p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-xl bg-[#F5F6FA] px-4 py-3">
                    <div>
                        <p class="text-[13px] font-semibold text-[#1A1E2E]">{{ __('modules.languageSettingsPage.languageStatusLabel') }}</p>
                        <p class="mt-0.5 text-[11.5px] text-[#8892A0]">{{ __('modules.languageSettingsPage.languageStatusHint') }}</p>
                    </div>
                    <button type="button" id="lang-modal-status-toggle" class="relative h-[23px] w-[42px] shrink-0 cursor-pointer rounded-full border-none bg-[#D1D5DB] transition-colors duration-200" style="padding:0;" aria-pressed="false">
                        <span id="lang-modal-status-thumb" class="lang-def-thumb absolute left-[3px] top-[3px] h-[17px] w-[17px] rounded-full bg-white shadow-sm"></span>
                    </button>
                </div>
            </div>
            <div class="jc-modal-ft">
                <button type="button" class="jc-btn-cancel" onclick="window.langModalCloseForm()">@lang('app.cancel')</button>
                <button type="button" id="lang-save-btn" class="jc-btn-save">
                    <span id="lang-save-label">{{ __('modules.languageSettingsPage.saveLanguage') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete modal --}}
<div id="lang-modal-delete" class="jc-modal-bg" role="dialog" aria-modal="true" onclick="window.langBackdropCloseDel(event)">
    <div class="jc-modal" style="width:380px;max-width:92vw;" onclick="event.stopPropagation()">
        <div class="jc-modal-hd">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#FFF1F2]">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-[#1A1E2E]">{{ __('modules.languageSettingsPage.deleteTitle') }}</h3>
                    <p class="text-[11.5px] text-[#8892A0]">{{ __('modules.languageSettingsPage.deleteCannotUndo') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.langModalCloseDel()" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] hover:bg-gray-100" aria-label="@lang('app.close')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <p class="text-[13.5px] leading-relaxed text-[#5A6478]" id="lang-del-body"></p>
        </div>
        <div class="jc-modal-ft">
            <button type="button" class="jc-btn-cancel" onclick="window.langModalCloseDel()">@lang('app.cancel')</button>
            <button type="button" id="lang-del-confirm" class="jc-btn-danger">@lang('app.delete')</button>
        </div>
    </div>
</div>
@endif
@endsection

@push('footer-script')
@if($canManage)
<script>
    (function () {
        var langStoreUrl = @json(route('admin.language-settings.store'));
        var langUpdateTpl = @json(route('admin.language-settings.update', ['language_setting' => '__ID__']));
        var langDelTpl = @json(route('admin.language-settings.destroy', ['language_setting' => '__ID__']));
        var langStatusTpl = @json(route('admin.language-settings.changeStatus', ['id' => '__ID__']));
        var langDelHtml = {!! json_encode(__('modules.languageSettingsPage.deleteConfirmHtml')) !!};
        var langEditSub = @json(__('modules.languageSettingsPage.modalEditSubPrefix'));
        var token = @json(csrf_token());
        var msgNameReq = @json(__('app.name').' '.__('errors.fieldRequired'));
        var msgCodeReq = @json(__('app.code').' '.__('errors.fieldRequired'));

        window.langBackdropCloseForm = function (e) {
            if (e.target.id === 'lang-modal-form') window.langModalCloseForm();
        };
        window.langBackdropCloseDel = function (e) {
            if (e.target.id === 'lang-modal-delete') window.langModalCloseDel();
        };

        function langResetModalStatus(on) {
            var btn = document.getElementById('lang-modal-status-toggle');
            var thumb = document.getElementById('lang-modal-status-thumb');
            var inp = document.getElementById('lang-form-status');
            if (!btn || !thumb || !inp) return;
            inp.value = on ? 'enabled' : 'disabled';
            btn.style.backgroundColor = on ? '#2563EB' : '#D1D5DB';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            thumb.style.left = on ? '22px' : '3px';
        }

        window.langModalOpenCreate = function () {
            $('#lang-settings-form').find('.has-error').removeClass('has-error');
            $('#lang-settings-form').find('.help-block').remove();
            $('#lang-settings-form').find('input, select, textarea').removeClass('border-red-500');
            $('#lang-form-alert').html('');
            $('#lang-form-method').val('POST');
            $('#lang-form-record-id').val('');
            $('#lang-settings-form').removeData('lang-update-url');
            $('#lang-modal-title').text(@json(__('modules.languageSettingsPage.modalAddTitle')));
            $('#lang-modal-sub').text(@json(__('modules.languageSettingsPage.modalAddSub')));
            $('#lang-save-label').text(@json(__('modules.languageSettingsPage.saveLanguage')));
            $('#lang-in-name').val('');
            $('#lang-in-code').val('');
            $('#lang-err-name, #lang-err-code').addClass('hidden').text('');
            langResetModalStatus(false);
            $('#lang-modal-form').addClass('is-open');
            $('body').addClass('overflow-hidden');
            setTimeout(function () { $('#lang-in-name').focus(); }, 80);
        };

        window.langModalOpenEdit = function (id, name, code, status) {
            $('#lang-settings-form').find('.has-error').removeClass('has-error');
            $('#lang-settings-form').find('.help-block').remove();
            $('#lang-settings-form').find('input, select, textarea').removeClass('border-red-500');
            $('#lang-form-alert').html('');
            var url = langUpdateTpl.replace('__ID__', id);
            $('#lang-form-method').val('PUT');
            $('#lang-form-record-id').val(id);
            $('#lang-settings-form').data('lang-update-url', url);
            $('#lang-modal-title').text(@json(__('modules.languageSettingsPage.modalEditTitle')));
            $('#lang-modal-sub').text(langEditSub + ' "' + name + '"');
            $('#lang-save-label').text(@json(__('modules.languageSettingsPage.updateLanguage')));
            $('#lang-in-name').val(name);
            $('#lang-in-code').val(code);
            $('#lang-err-name, #lang-err-code').addClass('hidden').text('');
            langResetModalStatus(status === 'enabled');
            $('#lang-modal-form').addClass('is-open');
            $('body').addClass('overflow-hidden');
            $('#lang-in-name').focus();
        };

        window.langModalCloseForm = function () {
            $('#lang-modal-form').removeClass('is-open');
            $('body').removeClass('overflow-hidden');
        };

        window.langModalCloseDel = function () {
            $('#lang-modal-delete').removeClass('is-open');
            $('body').removeClass('overflow-hidden');
        };

        $('#lang-modal-status-toggle').on('click', function () {
            var on = $('#lang-form-status').val() !== 'enabled';
            langResetModalStatus(on);
        });

        $('#lang-in-code').on('input', function () {
            this.value = this.value.toLowerCase();
        });

        $('#lang-save-btn').on('click', function () {
            var name = ($('#lang-in-name').val() || '').trim();
            var code = ($('#lang-in-code').val() || '').trim();
            var ok = true;
            if (!name) {
                $('#lang-err-name').removeClass('hidden').text(msgNameReq);
                ok = false;
            } else {
                $('#lang-err-name').addClass('hidden').text('');
            }
            if (!code) {
                $('#lang-err-code').removeClass('hidden').text(msgCodeReq);
                ok = false;
            } else {
                $('#lang-err-code').addClass('hidden').text('');
            }
            if (!ok) return;

            var url = $('#lang-settings-form').data('lang-update-url');
            var isEdit = $('#lang-form-method').val() === 'PUT';
            $.easyAjax({
                url: isEdit ? url : langStoreUrl,
                container: '#lang-settings-form',
                type: 'POST',
                redirect: true,
                data: $('#lang-settings-form').serialize()
            });
        });

        $(document).on('click', '.lang-open-edit', function () {
            var $t = $(this);
            window.langModalOpenEdit(
                $t.data('id'),
                $t.data('name'),
                $t.data('code'),
                $t.data('status')
            );
        });

        function langSetRowToggle($btn, enabled) {
            var $thumb = $btn.find('.lang-stat-thumb');
            $btn.toggleClass('bg-[#2563EB]', enabled).toggleClass('bg-[#D1D5DB]', !enabled);
            $btn.data('enabled', enabled ? '1' : '0');
            $btn.attr('aria-pressed', enabled ? 'true' : 'false');
            if ($thumb.length) {
                $thumb.css('left', enabled ? '22px' : '3px');
            }
            var $tr = $btn.closest('tr.lang-row');
            $tr.attr('data-status', enabled ? 'enabled' : 'disabled');
            langApplyFilter();
        }

        $(document).on('click', '.lang-status-toggle', function () {
            var $btn = $(this);
            var id = $btn.data('lang-id');
            var nowOn = $btn.data('enabled') == 1;
            var next = !nowOn;
            var url = langStatusTpl.replace('__ID__', id);
            $.easyAjax({
                url: url,
                type: 'POST',
                data: {
                    _token: token,
                    _method: 'PUT',
                    id: id,
                    status: next ? 'enabled' : 'disabled'
                },
                success: function () {
                    langSetRowToggle($btn, next);
                }
            });
        });

        $(document).on('click', '.lang-open-del', function () {
            var id = $(this).data('row-id');
            var name = $(this).data('name');
            var safe = $('<div>').text(name).html();
            var html = langDelHtml.split(':name').join(safe);
            $('#lang-del-body').html(html);
            $('#lang-modal-delete').data('del-id', id).addClass('is-open');
            $('body').addClass('overflow-hidden');
        });

        $('#lang-del-confirm').on('click', function () {
            var id = $('#lang-modal-delete').data('del-id');
            if (!id) return;
            var url = langDelTpl.replace('__ID__', id);
            $.easyAjax({
                type: 'POST',
                url: url,
                data: { _token: token, _method: 'DELETE' },
                success: function (response) {
                    if (response.status == 'success') {
                        $.unblockUI();
                        window.langModalCloseDel();
                        window.location.reload();
                    }
                }
            });
        });

        function langApplyFilter() {
            var q = ($('#lang-table-search').val() || '').toLowerCase().trim();
            var sf = ($('#lang-status-filter').val() || '').toString();
            var visible = 0;
            var n = 0;
            $('#lang-tbody tr.lang-row').each(function () {
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
                    $tr.find('.lang-row-num').text(String(visible).padStart(2, '0'));
                }
            });
            $('#lang-show-count').text(visible);
            $('#lang-total-num').text(n);
            var hasRows = n > 0;
            $('#lang-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
        }

        $('#lang-table-search').on('input', langApplyFilter);
        $('#lang-status-filter').on('change', langApplyFilter);

        @if(request('open') === 'create')
        $(function () { window.langModalOpenCreate(); });
        @endif

        @if($langOpenEdit && strtolower($langOpenEdit->language_code) !== 'en')
        $(function () {
            window.langModalOpenEdit(
                {{ (int) $langOpenEdit->id }},
                @json($langOpenEdit->language_name),
                @json(strtolower($langOpenEdit->language_code)),
                @json($langOpenEdit->status)
            );
        });
        @endif

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                if ($('#lang-modal-form').hasClass('is-open')) window.langModalCloseForm();
                if ($('#lang-modal-delete').hasClass('is-open')) window.langModalCloseDel();
            }
        });
    })();
</script>
@else
<script>
    (function () {
        function langApplyFilterReadonly() {
            var q = ($('#lang-table-search').val() || '').toLowerCase().trim();
            var sf = ($('#lang-status-filter').val() || '').toString();
            var visible = 0;
            var n = 0;
            $('#lang-tbody tr.lang-row').each(function () {
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
                    $tr.find('.lang-row-num').text(String(visible).padStart(2, '0'));
                }
            });
            $('#lang-show-count').text(visible);
            $('#lang-total-num').text(n);
            var hasRows = n > 0;
            $('#lang-filter-empty').toggleClass('hidden', !(hasRows && visible === 0));
        }
        $('#lang-table-search').on('input', langApplyFilterReadonly);
        $('#lang-status-filter').on('change', langApplyFilterReadonly);
    })();
</script>
@endif
@endpush
