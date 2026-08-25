<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Favicon icon -->
    <link rel="icon" href="{{$companySetting->favicon_url}}" type="image/x-icon" />
    <link rel="manifest" href="{{ asset('favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">

    <title>@lang('app.adminPanel'){{ isset($pageTitle) && $pageTitle !== '' ? ' | '.$pageTitle : '' }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Simple line icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.css">

    <!-- Themify icons -->
    <link rel="stylesheet" href="{{ asset('assets/icons/themify-icons/themify-icons.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <link href="{{ asset('froiden-helper/helper.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/node_modules_files/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/node_modules_files/sweetalert/sweetalert.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/jquery.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/Magnific-Popup-master/dist/magnific-popup.css') }}">

    @stack('head-script')

    <link rel='stylesheet prefetch'
          href='//cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.2/css/flag-icon.min.css'>

    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    <!-- Alpine.js for dropdown functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>if (window.pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
    
    <style>
        :root {
            --main-color: {{ isset($adminTheme) ? $adminTheme->primary_color : '#2563eb' }};
        }
        pre {
            background: #fff;
            border-radius: 0;
        }

        .ra-scroll { scrollbar-width: auto; scrollbar-color: #111 #E8E6E1; }
        .ra-scroll::-webkit-scrollbar { width: 12px; height: 12px; }
        .ra-scroll::-webkit-scrollbar-track { background: #E8E6E1; }
        .ra-scroll::-webkit-scrollbar-thumb { background: #111; border: 2px solid #E8E6E1; border-radius: 10px; }
        .ra-scroll::-webkit-scrollbar-thumb:hover { background: #000; }

        .btn-group-xs > .btn, .btn-xs {
            padding  : .25rem .4rem;
            font-size  : .875rem;
            line-height  : .5;
            border-radius : .2rem;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 6px 0;
            border-radius: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.428571429;
        }
        .text-truncate-notify{
            white-space: pre-wrap !important;
        }

        .image-container {
            display: flex;
            align-items: center;
        }

        .image-container .image {
            display: inline-block;
            position: relative;
            width: 32px;
            height: 32px;
            overflow: hidden;
            border-radius: 50%;
            margin-right: 10px;
        }

        .image-container .image img {
            width: auto;
            height: 100%;
        }

        #top-notification-dropdown>a {
            position: relative;
        }

        #top-notification-dropdown>a span {
            position: absolute;
            right: 10%;
            top: 10%;
        }

        #top-notification-dropdown>a span.badge {
            padding: 2px 5px;
        }

        .scrollable {
            max-height: 250px;
            overflow-y: scroll;
        }

        {!! isset($adminTheme) ? $adminTheme->admin_custom_css : '' !!}
    </style>

</head>
<body class="ra-admin-body">
<div id="ra-app" class="ra-app" style="--ra-accent: {{ isset($adminTheme) ? $adminTheme->primary_color : '#2563eb' }};">
@auth
    @include('sections.left-sidebar')
@endauth

<div class="ra-main">
    <header class="ra-topbar">
        <div class="flex items-center gap-2 min-w-0">
            @auth
                <button type="button" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors shrink-0" data-ra-sidebar-toggle aria-label="@lang('app.openMenu')">
                    <svg width="17" height="17" fill="none" stroke="#8892A0" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            @endauth
            @auth
                <a href="{{ route('admin.dashboard') }}" class="text-[#8892A0] hover:text-[#1A1E2E] shrink-0" aria-label="@lang('menu.dashboard')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </a>
            @else
                <a href="{{ url('/') }}" class="text-[#8892A0] hover:text-[#1A1E2E] shrink-0" aria-label="@lang('app.home')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </a>
            @endauth
            <span class="ra-bc-sep shrink-0" aria-hidden="true">›</span>
            <span class="ra-bc on truncate">{{ $pageTitle ?? '' }}</span>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            @auth
                <!-- <div class="relative hidden sm:block">
                    <label class="sr-only" for="ra-top-search">@lang('app.searchAnything')</label>
                    <input type="search" id="ra-top-search" placeholder="@lang('app.searchAnything')" class="text-[12.5px] bg-[#EEF0F5] rounded-xl pl-8 pr-4 py-2 outline-none border-0 w-[180px] text-[#5A6478]" autocomplete="off">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 pointer-events-none" fill="none" stroke="#9CA3AF" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div> -->

                <div class="relative" id="top-notification-dropdown" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors" aria-expanded="false" :aria-expanded="open">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="#8892A0" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if(count($user->unreadNotifications) > 0)
                            <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full" aria-hidden="true"></span>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg z-50 border border-[#E8E6E1]" style="display: none;">
                        <div class="max-h-96 overflow-y-auto">
                            @foreach ($user->unreadNotifications as $notification)
                                @include('notifications.'.snake_case(class_basename($notification->type)))
                            @endforeach
                        </div>
                        <div class="border-t border-[#F0EEE9]">
                            @if(count($user->unreadNotifications) > 0)
                                <a id="mark-notification-read" href="javascript:void(0);" class="block px-4 py-3 text-sm text-center text-[#3D4A5C] hover:bg-[#EEF0F5]">@lang('app.markNotificationRead') <i class="fa fa-check"></i></a>
                            @else
                                <a href="javascript:void(0);" class="block px-4 py-3 text-sm text-center text-[#3D4A5C] hover:bg-[#EEF0F5]">@lang('messages.notificationNotFound')</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="relative flex items-center gap-2 pl-3 border-l border-[#ECEAE5]" x-data="{ openUser: false }">
                    <button type="button" @click="openUser = !openUser" class="flex items-center gap-2 rounded-xl hover:bg-gray-50 py-1 pr-1 pl-0.5">
                        <div class="ra-avatar">
                            @if(!empty($user->profile_image_url))
                                <img src="{{ $user->profile_image_url }}" alt="">
                            @else
                                {{ strtoupper(mb_substr($user->name, 0, 1, 'UTF-8')) }}
                            @endif
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-[12.5px] font-semibold text-[#1A1E2E] leading-tight">{{ ucwords($user->name) }}</p>
                            <p class="text-[10.5px] text-[#8892A0] leading-tight">
                                @if($user->is_superadmin)
                                    @lang('app.superAdmin')
                                @else
                                    @lang('app.adminUser')
                                @endif
                            </p>
                        </div>
                        <svg class="w-3 h-3 text-[#9CA3AF] shrink-0 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openUser" @click.away="openUser = false" x-transition class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E8E6E1] py-1 z-50" style="display: none;">
                        <a href="@if(!$user->is_superadmin){{ route('admin.profile.index') }}@else{{ route('superadmin.profile.index') }}@endif" class="block px-4 py-2.5 text-[12.5px] font-medium text-[#3D4A5C] hover:bg-[#EEF0F5]">@lang('menu.myProfile')</a>
                        <a href="{{ route('logout') }}" class="block px-4 py-2.5 text-[12.5px] font-medium text-[#3D4A5C] hover:bg-[#EEF0F5]" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('app.logout')</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </header>

    <div class="ra-scroll">
        @include('sections.breadcrumb')
        @yield('content')
        <footer class="mt-8 pt-6 border-t border-[#E8E6E1] text-[12.5px] text-[#8892A0]">
            &copy; {{ \Carbon\Carbon::today()->year }} @lang('app.by') {{ $companyName ?? config('app.name') }}
        </footer>
    </div>

    @auth
        @include('sections.sticky-notes-sidebar')
        {{--sticky note modal--}}
        <div id="responsive-modal" class="hidden fixed inset-0 z-[220] overflow-y-auto" tabindex="-1" role="dialog" aria-labelledby="sticky-modal-title" aria-modal="true" aria-hidden="true">
            <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
                <div class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true" onclick="$('#responsive-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})"></div>
                <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl">
                    <div class="p-6 !p-0 overflow-y-auto max-h-[min(560px,calc(100vh-3rem))] min-h-[10rem] text-left">
                        <p class="px-6 py-8 text-center text-sm text-slate-500">Loading …</p>
                    </div>
                </div>
            </div>
        </div>
        {{--sticky note modal ends--}}
    @endauth

</div>
{{-- /.ra-main --}}

    {{--Ajax Modal (lg) — Tailwind-centered overlay --}}
    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="application-lg-modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true" aria-hidden="true">
        <div class="flex min-h-full w-full items-center justify-center p-4 sm:p-6">
            <div class="fixed inset-0 z-0 bg-black/50 transition-opacity" aria-hidden="true" onclick="$('#application-lg-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})"></div>
            <div class="relative z-10 flex w-full max-w-4xl max-h-[min(90vh,calc(100dvh-2rem))] flex-col overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5" id="modal-data-application">
                <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold tracking-tight text-slate-900" id="modelHeading"></h3>
                    <button type="button" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2" onclick="$('#application-lg-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})" aria-label="@lang('app.close')">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto p-6">
                    Loading...
                </div>
                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" onclick="$('#application-lg-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})"><i class="fa fa-times mr-1.5"></i> @lang('app.cancel')</button>
                    <button type="button" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"><i class="fa fa-check mr-1.5"></i> @lang('app.save')</button>
                </div>
            </div>
        </div>
    </div>

    {{--Ajax Modal (md) — Tailwind-centered overlay --}}
    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="application-md-modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true" aria-hidden="true">
        <div class="flex min-h-full w-full items-center justify-center p-4 sm:p-6">
            <div class="fixed inset-0 z-0 bg-black/50 transition-opacity" aria-hidden="true" onclick="$('#application-md-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})"></div>
            <div class="relative z-10 flex w-full max-w-2xl max-h-[min(90vh,calc(100dvh-2rem))] flex-col overflow-hidden rounded-2xl border border-[#E8E6E1] bg-white shadow-2xl" id="modal-data-application">
                <div class="flex shrink-0 items-center justify-between border-b border-[#F0EEE9] px-6 py-5">
                    <h3 class="text-[15.5px] font-bold tracking-tight text-[#1A1E2E]" id="modelHeading"></h3>
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-slate-300" onclick="$('#application-md-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                    Loading...
                </div>
                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-[#F0EEE9] bg-white px-6 py-4">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-[#F1F3F7] px-5 py-2.5 text-[13px] font-semibold text-[#5A6478] transition hover:bg-[#E8EBF0] focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2" onclick="$('#application-md-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})">@lang('app.cancel')</button>
                    <button type="button" id="application-md-modal-save-btn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @lang('app.save')
                    </button>
                </div>
            </div>
        </div>
    </div>


    @include('sections.right-sidebar')
</div>
<!-- ./ra-app -->

<!-- Load jQuery first (CDN + local fallback) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Fallback to local jQuery if CDN is blocked/unavailable.
    if (!window.jQuery) {
        document.write('<script src="{{ asset('assets/node_modules_files/jquery-asColorPicker-master/libs/jquery.min.js') }}"><\/script>');
    }
</script>
<script>
    // Ensure jQuery aliases exist only when jQuery is present.
    if (window.jQuery) {
        window.$ = window.jQuery;
    }
</script>

{{-- helper.js ends with })(jQuery); must run immediately after jQuery is on window --}}
<script src="{{ asset('froiden-helper/helper.js') }}"></script>

<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.js') }}"></script>

<script src="{{ asset('assets/node_modules_files/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/toast-master/js/jquery.toast.js') }}"></script>
<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
@if(file_exists(public_path('assets/plugins/icheck/icheck.min.js')))
<script src="{{ asset('assets/plugins/icheck/icheck.min.js') }}"></script>
@endif
@if(file_exists(public_path('assets/plugins/icheck/icheck.init.js')))
<script src="{{ asset('assets/plugins/icheck/icheck.init.js') }}"></script>
@endif
<script src="{{ asset('assets/node_modules_files/Magnific-Popup-master/dist/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/Magnific-Popup-master/dist/jquery.magnific-popup-init.js') }}"></script>

<!-- Vite handles additional dependencies via app.js (lodash, axios) - loaded after jQuery -->
@vite(['resources/js/app.js'])

<script>
    $('body').on('click', '.right-side-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.raCloseRightSidebar) window.raCloseRightSidebar();
    });
    
    // Close sidebar when clicking on backdrop
    $(document).on('click', '#right-sidebar-backdrop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.raCloseRightSidebar) window.raCloseRightSidebar();
    });

    window.raCloseRightSidebar = function () {
        var $sidebar = $("#right-sidebar");
        var $backdrop = $("#right-sidebar-backdrop");
        if ($sidebar.length === 0) return;
        $sidebar.removeClass('translate-x-0').addClass('translate-x-full');
        // Clear the overlay even when a profile opener added an inline
        // display:block style. The hidden utility alone cannot override that
        // inline style in every compiled CSS build.
        $backdrop.addClass('hidden').css('display', 'none');
    };

    window.raCloseStickyNotesSidebar = function () {
        var $panel = $('#sticky-notes-sidebar');
        var $bd = $('#sticky-notes-backdrop');
        if (!$panel.length) return;
        $panel.addClass('translate-x-full').removeClass('translate-x-0').attr('aria-hidden', 'true');
        $bd.addClass('hidden').attr('aria-hidden', 'true');
        $('#sticky-notes-trigger, #sticky-notes-trigger-mobile').attr('aria-expanded', 'false');
        $(document).off('keydown.raStickyNotes');
    };

    window.raOpenStickyNotesSidebar = function () {
        var $panel = $('#sticky-notes-sidebar');
        var $bd = $('#sticky-notes-backdrop');
        if (!$panel.length) return;
        if (window.raCloseRightSidebar) window.raCloseRightSidebar();
        $panel.removeClass('translate-x-full').addClass('translate-x-0').attr('aria-hidden', 'false');
        $bd.removeClass('hidden').attr('aria-hidden', 'false');
        $('#sticky-notes-trigger, #sticky-notes-trigger-mobile').attr('aria-expanded', 'true');
        $(document).off('keydown.raStickyNotes').on('keydown.raStickyNotes', function (e) {
            if (e.key === 'Escape') window.raCloseStickyNotesSidebar();
        });
    };

    window.raToggleStickyNotesSidebar = function () {
        var $panel = $('#sticky-notes-sidebar');
        if (!$panel.length) return;
        if ($panel.hasClass('translate-x-0')) {
            window.raCloseStickyNotesSidebar();
        } else {
            window.raOpenStickyNotesSidebar();
        }
    };

    $(document).on('click', '#sticky-notes-trigger, #sticky-notes-trigger-mobile', function (e) {
        e.preventDefault();
        window.raToggleStickyNotesSidebar();
    });
    $(document).on('click', '#sticky-notes-backdrop, #sticky-notes-close', function (e) {
        e.preventDefault();
        window.raCloseStickyNotesSidebar();
    });

    $(function () {
        if (typeof $.fn.selectpicker === 'function') {
            $('.selectpicker').selectpicker({
                style: 'btn-info',
                size: 4
            });
        }
    });

    function languageOptions() {
        return {
            processing:     "@lang('modules.datatables.processing')",
            search:         "@lang('modules.datatables.search')",
            lengthMenu:    "@lang('modules.datatables.lengthMenu')",
            info:           "@lang('modules.datatables.info')",
            infoEmpty:      "@lang('modules.datatables.infoEmpty')",
            infoFiltered:   "@lang('modules.datatables.infoFiltered')",
            infoPostFix:    "@lang('modules.datatables.infoPostFix')",
            loadingRecords: "@lang('modules.datatables.loadingRecords')",
            zeroRecords:    "@lang('modules.datatables.zeroRecords')",
            emptyTable:     "@lang('modules.datatables.emptyTable')",
            paginate: {
                first:      "@lang('modules.datatables.paginate.first')",
                previous:   "@lang('modules.datatables.paginate.previous')",
                next:       "@lang('modules.datatables.paginate.next')",
                last:       "@lang('modules.datatables.paginate.last')",
            },
            aria: {
                sortAscending:  "@lang('modules.datatables.aria.sortAscending')",
                sortDescending: "@lang('modules.datatables.aria.sortDescending')",
            },
        }
    }

    $('.language-switcher').change(function () {
        var lang = $(this).val();
        $.easyAjax({
            url: '{{ route("admin.language-settings.change-language") }}',
            data: {'lang': lang},
            success: function (data) {
                if (data.status == 'success') {
                    window.location.reload();
                }
            }
        });
    });

    $('#mark-notification-read').click(function () {
        var token = '{{ csrf_token() }}';
        $.easyAjax({
            type: 'POST',
            url: '{{ route("mark-notification-read") }}',
            data: {'_token': token},
            success: function (data) {
                if (data.status == 'success') {
                    $('.top-notifications').remove();
                    $('#top-notification-dropdown .notify').remove();
                    window.location.reload();
                }
            }
        });
    });

    // $('body').on('click', '.view-notification', function(event) {
    $('.read-notification').click(function () {
            event.preventDefault();
            var id = $(this).data('notification-id');
            var dataUrl = $(this).data('link');

            $.easyAjax({
                url: "{{ route('mark_single_notification_read') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function() {
                    if (typeof dataUrl !== 'undefined') {
                        window.location = dataUrl;
                    }
                }
            });
        });

    function addOrEditStickyNote(id)
    {
        var url = '';
        var method = 'POST';
        if(id === undefined || id == "" || id == null) {
            url =  '{{ route('admin.sticky-note.store') }}'
        } else{

            url = "{{ route('admin.sticky-note.update',':id') }}";
            url = url.replace(':id', id);
            var stickyID = $('#stickyID').val();
            method = 'PUT'
        }

        var noteText = $('#notetext').val();
        var stickyColor = $('#stickyColor').val();
        $.easyAjax({
            url: url,
            container: '#responsive-modal',
            type: method,
            data:{'notetext':noteText,'stickyColor':stickyColor,'_token':'{{ csrf_token() }}'},
            success: function (response) {
                $("#responsive-modal").addClass('hidden').css({display: '', visibility: '', opacity: ''});
                getNoteData();
            }
        });
    }

    // FOR SHOWING FEEDBACK DETAIL IN MODEL
    function showCreateNoteModal(){
        if (window.raCloseStickyNotesSidebar) window.raCloseStickyNotesSidebar();
        var url = '{{ route('admin.sticky-note.create') }}';
        $.ajaxModal('#responsive-modal', url);

        return false;
    }

    // FOR SHOWING FEEDBACK DETAIL IN MODEL
    function showEditNoteModal(id){
        if (window.raCloseStickyNotesSidebar) window.raCloseStickyNotesSidebar();
        var url = '{{ route('admin.sticky-note.edit',':id') }}';
        url  = url.replace(':id',id);
        $.ajaxModal('#responsive-modal', url);
        return false;
    }
    function selectColor(id){
        $('[data-sticky-swatch]').removeClass('ring-2 ring-offset-2 ring-slate-800').attr('aria-pressed', 'false');
        $('#' + id).addClass('ring-2 ring-offset-2 ring-slate-800').attr('aria-pressed', 'true');
        $('#stickyColor').val(id);
    }

    function deleteSticky(id){

        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the deleted Sticky Note!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('admin.sticky-note.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        $('#stickyBox_'+id).fadeOut();
                        $("#responsive-modal").addClass('hidden').css({display: '', visibility: '', opacity: ''});
                        getNoteData();
                    }
                });
            }
        });
    }


    //getting all chat data according to user
    function getNoteData(){

        var url = "{{ route('admin.sticky-note.index') }}";

        $.easyAjax({
            type: 'GET',
            url: url,
            messagePosition: '',
            data:  {},
            container: ".noteBox",
            success: function (response) {
                $('#sticky-note-list').html(response.stickyNotes);
                var c = response.count;
                $('#sticky-note-count').text(c);
                $('#sticky-note-count-badge').text(c);
                $('#sticky-note-count-badge-mobile').text(c);
            }
        });
    }

    // search input implementation
    function search($input, doneTypingInterval, type) {
        var $anchor = $input.siblings('a');
        var typingTimer, fn;

        if (type == 'data') {
            fn = loadData;
        }
        if (type == 'table') {
            fn = redrawTable;                    
        }

        $input.on('keyup', function (e) {
            if ($(this).val() !== '' || ($(this).val().length >= 0 && e.key === 'Backspace')) {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    fn();
                }, doneTypingInterval);
            }

            $(this).val() !== '' ? $anchor.removeClass('hidden') : $anchor.addClass('hidden');
        })

        $input.on('keydown', function () {
            clearTimeout(typingTimer);
        });

        $anchor.click(function(e) {
            $(this).siblings('input').val('');
            fn();
            $anchor.addClass('hidden');
            $(this).siblings('input').focus();
        })
    }

    window.raStickyNotesRefreshLayout = function () {};
    $('body').on('click', '.toggle-password', function() {
        var $selector = $(this).parent().find('input.form-control');
        $(this).toggleClass("fa-eye fa-eye-slash");
        var $type = $selector.attr("type") === "password" ? "text" : "password";
        $selector.attr("type", $type);
    });

    window.raToggleSidebar = function () {
        var app = document.getElementById('ra-app');
        if (!app) return;
        if (window.matchMedia('(max-width: 1023px)').matches) {
            app.classList.toggle('ra-mobile-nav-open');
            return;
        }
        app.classList.toggle('ra-sidebar-mini');
        var mini = app.classList.contains('ra-sidebar-mini');
        localStorage.setItem('raSidebarMini', mini ? '1' : '0');
        var ico = document.getElementById('ra-collapse-ico');
        if (ico) {
            ico.innerHTML = mini
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>';
        }
        var btn = document.getElementById('ra-collapse-btn');
        if (btn) btn.setAttribute('aria-expanded', mini ? 'false' : 'true');
    };

    $(document).ready(function () {
        var app = document.getElementById('ra-app');
        if (app && localStorage.getItem('raSidebarMini') === '1') {
            app.classList.add('ra-sidebar-mini');
            var ico = document.getElementById('ra-collapse-ico');
            if (ico) {
                ico.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>';
            }
            var btn = document.getElementById('ra-collapse-btn');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('click', function (e) {
        var app = document.getElementById('ra-app');
        if (!app || !app.classList.contains('ra-mobile-nav-open')) return;
        var sb = document.getElementById('ra-sidebar');
        var t = e.target;
        if (sb && !sb.contains(t) && !(t.closest && t.closest('[data-ra-sidebar-toggle]'))) {
            app.classList.remove('ra-mobile-nav-open');
        }
    });

    $(document).on('click', '[data-ra-sidebar-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.raToggleSidebar) window.raToggleSidebar();
    });

</script>

<script>
(function () {
    var syncUrl = @json(route('admin.ats-sync-state'));
    var presenceHeartbeatUrl = @json(route('admin.ats-presence-heartbeat'));
    var lastSignature = null;
    var pendingRefresh = false;
    var refreshRunning = false;

    function updatePresenceDots(onlineUserIds) {
        var online = {};
        (onlineUserIds || []).forEach(function (id) { online[String(id)] = true; });
        $('[data-ats-presence-user]').each(function () {
            var isOnline = !!online[String($(this).data('ats-presence-user'))];
            $(this)
                .toggleClass('bg-emerald-500', isOnline)
                .toggleClass('bg-red-500', !isOnline)
                .attr('title', isOnline ? 'Online' : 'Offline')
                .attr('aria-label', isOnline ? 'Online' : 'Offline');
        });
        $('[data-ats-presence-label]').each(function () {
            var isOnline = !!online[String($(this).data('ats-presence-label'))];
            $(this)
                .text(isOnline ? 'Online' : 'Offline')
                .toggleClass('text-emerald-600', isOnline)
                .toggleClass('text-red-500', !isOnline);
        });
    }

    function sendPresenceHeartbeat() {
        if (document.hidden) return;
        $.ajax({
            url: presenceHeartbeatUrl,
            type: 'POST',
            data: { _token: @json(csrf_token()) },
            global: false,
            timeout: 5000
        }).done(function (response) {
            updatePresenceDots(response.online_user_ids || []);
        });
    }
    function userIsEditing() {
        var active = document.activeElement;
        var editable = active && (/^(INPUT|TEXTAREA|SELECT)$/.test(active.tagName) || active.isContentEditable);
        return editable || $('.modal:visible, .swal-overlay:visible, .sweet-alert:visible').length > 0;
    }

    function reloadOpenApplicantProfile(done) {
        var $profile = $('#right-sidebar-content .ja-two-col-wrap[data-ats-sync-url]');
        var $sidebar = $('#right-sidebar');
        if (!$profile.length || !$sidebar.hasClass('translate-x-0')) {
            done();
            return;
        }

        var activeTab = $profile.find('.ja-tab.active').data('tab') || null;
        $.ajax({ url: $profile.data('ats-sync-url'), type: 'GET', cache: false })
            .done(function (response) {
                if (response && response.status === 'success' && response.view) {
                    $('#right-sidebar-content').html(response.view);
                    if (activeTab) {
                        $('#right-sidebar-content .ja-tab[data-tab="' + activeTab + '"]').trigger('click');
                    }
                }
            }).always(done);
    }

    function applyRemoteChanges() {
        if (!pendingRefresh || refreshRunning || userIsEditing()) return;
        refreshRunning = true;
        pendingRefresh = false;

        var hasDataTable = false;
        if ($.fn.dataTable) {
            var tables = $.fn.dataTable.tables({ visible: true, api: true });
            hasDataTable = !!(tables && tables.context && tables.context.length);
            if (hasDataTable) tables.ajax.reload(null, false);
        }

        reloadOpenApplicantProfile(function () {
            refreshRunning = false;
            if (!hasDataTable && !$('#right-sidebar').hasClass('translate-x-0')) {
                window.location.reload();
            }
        });
    }

    function checkForChanges() {
        if (document.hidden || refreshRunning) return;
        $.ajax({ url: syncUrl, type: 'GET', cache: false, global: false, timeout: 5000 })
            .done(function (response) {
                if (!response || !response.signature) return;
                if (lastSignature === null) {
                    lastSignature = response.signature;
                    return;
                }
                if (lastSignature !== response.signature) {
                    lastSignature = response.signature;
                    pendingRefresh = true;
                }
                applyRemoteChanges();
            });
    }

    setInterval(checkForChanges, 7000);
    setInterval(sendPresenceHeartbeat, 25000);
    setTimeout(sendPresenceHeartbeat, 300);
    setInterval(applyRemoteChanges, 1500);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) { checkForChanges(); sendPresenceHeartbeat(); }
    });
    setTimeout(checkForChanges, 1200);
})();
</script>
@stack('footer-script')

</body>
</html>
