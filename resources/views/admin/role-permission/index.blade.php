@extends('layouts.app')

@push('head-script')
    <style>
        .rp-serif { font-family: 'Instrument Serif', Georgia, serif; font-style: italic; font-weight: 400; color: #2563EB; font-size: 20px; }
        @keyframes rp-fu { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .rp-a1 { opacity: 0; animation: rp-fu 0.38s ease 0.04s forwards; }
        .rp-a2 { opacity: 0; animation: rp-fu 0.38s ease 0.09s forwards; }
        .rp-a3 { opacity: 0; animation: rp-fu 0.38s ease 0.14s forwards; }
        .rp-peer-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        .rp-toggle-track { display: block; width: 42px; height: 23px; border-radius: 9999px; background: #e5e7eb; position: relative; transition: background-color 0.2s; flex-shrink: 0; }
        .rp-toggle-track--on { background: #2563EB; }
        .rp-toggle-track--on.rp-toggle-track--delete { background: #ef4444; }
        .rp-toggle-thumb { position: absolute; top: 3px; left: 3px; width: 17px; height: 17px; border-radius: 9999px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.12); transition: left 0.18s cubic-bezier(0.4, 0, 0.2, 1); }
        .rp-toggle-track--on .rp-toggle-thumb { left: 22px; }
        .rp-sa-box { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.35); border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.2s, border-color 0.2s; }
        .rp-sa-input:checked + .rp-sa-visual .rp-sa-box { background: #2563EB; border-color: #2563EB; }
        .rp-sa-check { width: 10px; height: 10px; color: #fff; opacity: 0; }
        .rp-sa-input:checked + .rp-sa-visual .rp-sa-check { opacity: 1; }
        .rp-role-tab-active { background: #0F1F3D !important; color: #fff !important; box-shadow: 0 10px 15px -3px rgba(15, 31, 61, 0.2); border-color: transparent !important; }
        .rp-module-rows { max-height: calc(100vh - 360px); overflow-y: auto; }
        @media (max-width: 1023px) {
            .rp-module-rows { max-height: none; }
        }
    </style>
@endpush

@php
    use Illuminate\Support\Str;
    $rpTitleParts = explode(' & ', __('menu.rolesPermission'), 2);
    $rpRoleColors = ['bg-amber-500', 'bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-rose-500', 'bg-teal-500', 'bg-indigo-500'];
    $rpModuleIcons = [
        'job categories' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'job skills' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
        'job applications' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'job locations' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'jobs' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'settings' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'team' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'question' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'schedule' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'company' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    ];
    $rpIconDefault = 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z';
@endphp

@section('page-title-html')
    <span class="text-[22px] font-extrabold tracking-tight text-[#0F1F3D]">
        {{ trim($rpTitleParts[0] ?? __('menu.rolesPermission')) }}@if(!empty($rpTitleParts[1]))
            &amp; <span class="rp-serif">{{ trim($rpTitleParts[1]) }}</span>
        @endif
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.permission.pageSubtitle') }}
@endsection

@section('create-button')
    <button type="button" id="addRole"
            class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-0 bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/25 active:translate-y-0">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
        {{ __('modules.permission.manageRoles') }}
    </button>
@endsection

@section('content')
    <div class="flex flex-col gap-5 lg:flex-row lg:gap-5 rp-a3">
        {{-- Permission matrix --}}
        <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-[#E8E6E1] bg-white">
            {{-- Role tabs --}}
            <div class="rp-a2 mb-0 flex gap-2 overflow-x-auto border-b border-gray-100 bg-[#F8F7F4] px-4 py-3 lg:px-6">
                @foreach($roles as $role)
                    <button type="button"
                            data-role-id="{{ $role->id }}"
                            class="rp-role-tab inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-xl border px-4 py-2.5 text-[13px] font-semibold transition-all {{ $loop->first ? 'rp-role-tab-active border-transparent text-white' : 'border-[#E8E6E1] bg-white text-slate-600 hover:border-[#2563EB] hover:text-[#2563EB]' }}">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $rpRoleColors[$loop->index % count($rpRoleColors)] }}"></span>
                        {{ ucwords($role->display_name) }}
                        @if($role->id <= 1 || $role->name === 'admin')
                            <span class="ml-0.5 rounded-full bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold text-amber-600">{{ __('modules.permission.adminBadge') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @foreach($roles as $role)
                @php
                    $rpLocked = ($role->id <= 1 || $role->name === 'admin');
                    $rpDesc = $rpLocked ? __('modules.permission.roleDescProtected') : __('modules.permission.roleDescConfigurable');
                @endphp
                <div id="rp-panel-{{ $role->id }}" class="rp-role-panel {{ $loop->first ? '' : 'hidden' }}">
                    {{-- Navy header bar --}}
                    <div class="flex flex-col gap-4 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                         style="background: linear-gradient(135deg, #0F1F3D, #162849);">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                <svg class="h-[18px] w-[18px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-[16px] font-bold text-white rp-active-role-name">{{ ucwords($role->display_name) }}</h2>
                                <p class="mt-0.5 text-[12px] text-white/50 rp-active-role-desc">{{ $rpDesc }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="flex cursor-pointer select-none items-center gap-2">
                                <input type="checkbox" value="{{ $role->id }}"
                                       class="rp-peer-sr rp-sa-input select_all_permission"
                                       id="select_all_permission_{{ $role->id }}"
                                       @if(count($role->permissions) == $totalPermissions) checked @endif
                                       @if($rpLocked) disabled @endif>
                                <span class="rp-sa-visual flex items-center gap-2">
                                    <span class="rp-sa-box">
                                        <svg class="rp-sa-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-[12.5px] font-semibold text-white/70">{{ __('modules.permission.selectAll') }}</span>
                                </span>
                            </label>
                            <button type="button" class="rp-save-hint inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-[12.5px] font-semibold text-white transition-all hover:bg-white/20"
                                    data-rp-toast="{{ __('modules.permission.tipImmediate') }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('modules.permission.saveChanges') }}
                            </button>
                        </div>
                    </div>

                    {{-- Grid --}}
                    <div class="grid gap-0">
                        <div class="sticky top-0 z-10 grid grid-cols-5 gap-0 border-b border-gray-100 bg-[#F8F7F4] px-4 py-2.5 sm:px-6">
                            <div class="col-span-1 text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.permission.moduleColumn') }}</div>
                            <div class="text-center text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    @lang('app.add')
                                </span>
                            </div>
                            <div class="text-center text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @lang('app.view')
                                </span>
                            </div>
                            <div class="text-center text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    @lang('app.update')
                                </span>
                            </div>
                            <div class="text-center text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    @lang('app.delete')
                                </span>
                            </div>
                        </div>
                        <div class="rp-module-rows">
                            @foreach($modules as $module)
                                @php
                                    $mkey = strtolower(trim($module->module_name));
                                    $iconPath = $rpModuleIcons[$mkey] ?? $rpIconDefault;
                                @endphp
                                <div class="grid grid-cols-5 items-center border-b border-gray-100 px-4 py-3.5 transition-colors hover:bg-blue-50/20 sm:px-6 {{ $loop->iteration % 2 === 0 ? 'bg-gray-50/50' : 'bg-white' }}">
                                    <div class="col-span-1 flex min-w-0 items-center gap-2.5">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#EEF0F5]">
                                            <svg class="h-3.5 w-3.5 text-[#8892A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $iconPath }}"/>
                                            </svg>
                                        </div>
                                        <span class="truncate text-[13.5px] font-semibold text-[#0F1F3D]">{{ ucwords($module->module_name) }}</span>
                                        @if($module->description != '')
                                            <a class="mytooltip shrink-0 text-[#8892A0]" href="javascript:void(0)" aria-label="Info">
                                                <i class="fa fa-info-circle"></i>
                                                <span class="tooltip-content5"><span class="tooltip-text3"><span class="tooltip-inner2">{{ $module->description }}</span></span></span>
                                            </a>
                                        @endif
                                        @if($rpLocked)
                                            <span class="shrink-0 rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-500">{{ __('modules.permission.adminBadge') }}</span>
                                        @endif
                                    </div>
                                    @foreach($module->permissions as $permission)
                                        @php
                                            $isDelete = Str::contains(strtolower($permission->name), 'delete');
                                        @endphp
                                        <div class="flex justify-center">
                                            <label class="inline-flex cursor-pointer items-center justify-center {{ $rpLocked ? 'cursor-not-allowed opacity-60' : '' }}">
                                                <input type="checkbox"
                                                       class="rp-peer-sr assign-role-permission permission_{{ $role->id }}"
                                                       data-size="small"
                                                       data-color="#2563EB"
                                                       data-permission-id="{{ $permission->id }}"
                                                       data-role-id="{{ $role->id }}"
                                                       @if($role->hasPermission([$permission->name])) checked @endif
                                                       @if($rpLocked) disabled @endif>
                                                <span class="rp-toggle-visual rp-toggle-track {{ $role->hasPermission([$permission->name]) ? 'rp-toggle-track--on' : '' }} {{ $isDelete ? 'rp-toggle-track--delete' : '' }}">
                                                    <span class="rp-toggle-thumb"></span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                    @if(count($module->permissions) < 4)
                                        @for($i = 1; $i <= (4 - count($module->permissions)); $i++)
                                            <div class="flex justify-center"><span class="text-[11.5px] font-medium text-gray-300">—</span></div>
                                        @endfor
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sidebar --}}
        <div class="flex w-full shrink-0 flex-col gap-4 lg:w-[260px] rp-a1">
            <div class="rounded-2xl border border-[#E8E6E1] bg-white p-5">
                <p class="mb-3 text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.permission.roleSummary') }}</p>
                <div id="rp-role-summary" class="flex flex-col gap-2"></div>
            </div>
            <div class="rounded-2xl border border-[#E8E6E1] bg-white p-5">
                <p class="mb-3 text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.permission.legendTitle') }}</p>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="relative h-4 w-8 shrink-0 rounded-full bg-[#2563EB]"><div class="absolute right-0.5 top-0.5 h-3 w-3 rounded-full bg-white shadow-sm"></div></div>
                        <span class="text-[12px] font-medium text-slate-600">{{ __('modules.permission.legendGranted') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="relative h-4 w-8 shrink-0 rounded-full bg-gray-200"><div class="absolute left-0.5 top-0.5 h-3 w-3 rounded-full bg-white shadow-sm"></div></div>
                        <span class="text-[12px] font-medium text-slate-600">{{ __('modules.permission.legendDenied') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-[#2563EB]">
                            <svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-[12px] font-medium text-slate-600">{{ __('modules.permission.legendSelectAll') }}</span>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-[#E8E6E1] bg-white p-5">
                <p class="mb-3 text-[10.5px] font-bold uppercase tracking-widest text-[#8892A0]">{{ __('modules.permission.tipsTitle') }}</p>
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-blue-50"><span class="text-[8px] font-extrabold leading-none text-[#2563EB]">1</span></div>
                        <p class="text-[12px] leading-relaxed text-slate-500">{{ __('modules.permission.tipImmediate') }}</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-blue-50"><span class="text-[8px] font-extrabold leading-none text-[#2563EB]">2</span></div>
                        <p class="text-[12px] leading-relaxed text-slate-500">{{ __('modules.permission.tipAdminProtected') }}</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-orange-50"><span class="text-[8px] font-extrabold leading-none text-orange-500">!</span></div>
                        <p class="text-[12px] leading-relaxed text-slate-500">{{ __('modules.permission.tipDeleteCaution') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden overflow-y-auto" id="managePermissionModal" role="dialog" aria-modal="true" aria-labelledby="modelHeading">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-[#0F1F3D]/50 backdrop-blur-sm" data-dismiss="modal" aria-hidden="true"></div>
            <div class="relative z-10 w-full max-w-[440px] overflow-hidden rounded-2xl bg-white shadow-2xl" id="modal-data-application">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <span id="modelHeading" class="text-[15.5px] font-bold text-[#0F1F3D]"></span>
                    <button type="button" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border-0 bg-transparent text-[#8892A0] transition-colors hover:bg-gray-100" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-role-permission-body max-h-[70vh] overflow-y-auto p-6">Loading...</div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script>
        $(function () {
            var $managePermModal = $('#managePermissionModal');
            // Tailwind modal: Bootstrap's data-dismiss is not wired; match other admin modals (e.g. responsive-modal).
            $(document).on('click', '#managePermissionModal [data-dismiss="modal"]', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $managePermModal.addClass('hidden').css({ display: '', visibility: '', opacity: '' });
            });

            var $firstTab = $('.rp-role-tab').first();
            var firstRoleId = $firstTab.data('role-id');

            function rpSyncToggleVisual($cb) {
                var on = $cb.prop('checked');
                var $track = $cb.siblings('.rp-toggle-visual');
                var isDel = $track.hasClass('rp-toggle-track--delete');
                $track.toggleClass('rp-toggle-track--on', on);
                if (isDel && on) {
                    $track.addClass('rp-toggle-track--delete');
                }
            }

            function rpActiveRoleId() {
                var $vis = $('.rp-role-panel:not(.hidden)').first();
                if (!$vis.length) return null;
                return String($vis.attr('id').replace('rp-panel-', ''));
            }

            var rpSumL = {
                totalMod: @json(__('modules.permission.totalModules')),
                granted: @json(__('modules.permission.grantedLabel')),
                denied: @json(__('modules.permission.deniedLabel')),
                access: @json(__('modules.permission.accessLevel')),
            };
            function rpUpdateSummary(roleId) {
                if (!roleId) return;
                var $panel = $('#rp-panel-' + roleId);
                var total = 0, granted = 0;
                $panel.find('input.assign-role-permission.permission_' + roleId).each(function () {
                    total++;
                    if ($(this).prop('checked')) granted++;
                });
                var denied = total - granted;
                var pct = total ? Math.round((granted / total) * 100) : 0;
                var modCount = {{ count($modules) }};
                $('#rp-role-summary').html(
                    '<div class="flex items-center justify-between border-b border-gray-100 py-2.5">' +
                    '<span class="text-[12.5px] font-semibold text-slate-600">' + rpSumL.totalMod + '</span>' +
                    '<span class="text-[13px] font-bold text-[#0F1F3D]">' + modCount + '</span></div>' +
                    '<div class="flex items-center justify-between border-b border-gray-100 py-2.5">' +
                    '<span class="text-[12.5px] font-semibold text-emerald-600">' + rpSumL.granted + '</span>' +
                    '<span class="text-[13px] font-bold text-emerald-600">' + granted + '</span></div>' +
                    '<div class="flex items-center justify-between py-2.5">' +
                    '<span class="text-[12.5px] font-semibold text-red-400">' + rpSumL.denied + '</span>' +
                    '<span class="text-[13px] font-bold text-red-400">' + denied + '</span></div>' +
                    '<div class="mt-3">' +
                    '<div class="mb-1.5 flex items-center justify-between">' +
                    '<span class="text-[11px] font-semibold text-[#8892A0]">' + rpSumL.access + '</span>' +
                    '<span class="text-[11px] font-bold text-[#2563EB]">' + pct + '%</span></div>' +
                    '<div class="h-1.5 overflow-hidden rounded-full bg-gray-100">' +
                    '<div class="h-full rounded-full bg-gradient-to-r from-[#2563EB] to-emerald-500 transition-all duration-500" style="width:' + pct + '%;"></div></div></div>'
                );
            }

            function rpSyncSelectAll(roleId) {
                var $panel = $('#rp-panel-' + roleId);
                var all = true;
                $panel.find('input.assign-role-permission.permission_' + roleId).each(function () {
                    if (!$(this).prop('checked')) all = false;
                });
                $('#select_all_permission_' + roleId).prop('checked', all);
            }

            $('input.assign-role-permission').each(function () { rpSyncToggleVisual($(this)); });

            $('.rp-role-tab').on('click', function () {
                var rid = $(this).data('role-id');
                $('.rp-role-tab').removeClass('rp-role-tab-active border-transparent text-white').addClass('border-[#E8E6E1] bg-white text-slate-600');
                $(this).addClass('rp-role-tab-active').removeClass('border-[#E8E6E1] bg-white text-slate-600');
                $('.rp-role-panel').addClass('hidden');
                $('#rp-panel-' + rid).removeClass('hidden');
                rpUpdateSummary(rid);
            });

            rpUpdateSummary(firstRoleId);

            var assignRollPermission = function () {
                var $cb = $(this);
                if ($cb.prop('disabled')) return;
                rpSyncToggleVisual($cb);
                var roleId = $cb.data('role-id');
                var permissionId = $cb.data('permission-id');
                var assignPermission = $cb.is(':checked') ? 'yes' : 'no';
                var url = '{{ route('admin.role-permission.store') }}';
                $.easyAjax({
                    url: url,
                    type: 'POST',
                    data: { roleId: roleId, permissionId: permissionId, assignPermission: assignPermission, _token: '{{ csrf_token() }}' },
                    success: function () {
                        rpSyncSelectAll(roleId);
                        rpUpdateSummary(roleId);
                    },
                    error: function () {
                        $cb.prop('checked', !$cb.prop('checked'));
                        rpSyncToggleVisual($cb);
                    }
                });
            };

            $('.assign-role-permission').on('change', assignRollPermission);

            $('.rp-save-hint').on('click', function () {
                var msg = $(this).data('rp-toast') || '';
                if (typeof $.toast !== 'undefined') {
                    $.toast({ text: msg, position: 'top-right', loaderBg: '#2563EB', icon: 'info', hideAfter: 3500, allowToastClose: true });
                } else if (typeof toastr !== 'undefined') {
                    toastr.info(msg);
                }
            });

            var masteranimate = false;

            $('.select_all_permission').on('change', function () {
                var $sel = $(this);
                if ($sel.prop('disabled')) return;
                var roleId = $sel.val();
                if ($sel.is(':checked')) {
                    $.easyAjax({
                        url: '{{ route('admin.role-permission.assignAllPermission') }}',
                        type: 'POST',
                        data: { roleId: roleId, _token: '{{ csrf_token() }}' },
                        success: function () {
                            masteranimate = true;
                            $('.assign-role-permission').off('change', assignRollPermission);
                            $('input.permission_' + roleId).prop('checked', true).each(function () { rpSyncToggleVisual($(this)); });
                            $('.assign-role-permission').on('change', assignRollPermission);
                            masteranimate = false;
                            rpUpdateSummary(roleId);
                        }
                    });
                } else {
                    $.easyAjax({
                        url: '{{ route('admin.role-permission.removeAllPermission') }}',
                        type: 'POST',
                        data: { roleId: roleId, _token: '{{ csrf_token() }}' },
                        success: function () {
                            masteranimate = true;
                            $('.assign-role-permission').off('change', assignRollPermission);
                            $('input.permission_' + roleId).prop('checked', false).each(function () { rpSyncToggleVisual($(this)); });
                            $('.assign-role-permission').on('change', assignRollPermission);
                            masteranimate = false;
                            rpUpdateSummary(roleId);
                        }
                    });
                }
            });

            $('.show-members').on('click', function () {
                var id = $(this).data('role-id');
                var url = '{{ route('admin.role-permission.showMembers', ':id')}}'.replace(':id', id);
                $('#modelHeading').html('{{ __('modules.permission.addRoleMember') }}');
                $.ajaxModal('#managePermissionModal', url);
            });

            $('#addRole').on('click', function () {
                var url = '{{ route('admin.role-permission.create')}}';
                $('#modelHeading').html('{{ __('modules.permission.manageRolesModalTitle') }}');
                $.ajaxModal('#managePermissionModal', url);
            });
        });
    </script>
@endpush
