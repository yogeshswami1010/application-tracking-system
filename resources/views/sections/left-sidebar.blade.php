<aside id="ra-sidebar" class="ra-sidebar" aria-label="@lang('app.adminPanel')">
    <a href="{{ route('admin.dashboard') }}" class="ra-logo-wrap">
        @if(!empty($global->logo_url))
            <img src="{{ $global->logo_url }}" alt="{{ $companyName ?? 'Logo' }}" class="ra-logo-img h-6">
            
        @else
            <div class="ra-logo-icon" aria-hidden="true">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 2h4.5a3.5 3.5 0 010 7H2V2z" fill="#fff"/><circle cx="10" cy="11.5" r="2" fill="#fff" opacity="0.55"/></svg>
            </div>
            <span class="ra-logo-txt">{{ $companyName ?? config('app.name') }}</span>
        @endif
    </a>

    <div class="ra-nav-scroll">
        <div class="ra-sec-title">@lang('menu.main')</div>

        <a href="{{ route('admin.dashboard') }}" class="ra-nav-link {{ request()->is('admin/dashboard*') ? 'on' : '' }}">
            <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="dashboard" /></span>
            <span class="ra-nl">@lang('menu.dashboard')</span>
        </a>

        

        @if(in_array("view_jobs", $userPermissions))
            <a href="{{ route('admin.jobs.index') }}" class="ra-nav-link {{ request()->is('admin/jobs*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="jobs" /></span>
                <span class="ra-nl">@lang('menu.jobs')</span>
            </a>
        @endif

        @if(in_array("view_job_applications", $userPermissions))
            <a href="{{ route('admin.job-applications.table') }}" class="ra-nav-link {{ request()->is('admin/job-applications*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="job-applications" /></span>
                <span class="ra-nl">@lang('menu.jobApplications')</span>
            </a>
           
        @endif
        @if(in_array("view_job_applications", $userPermissions))
            <a href="{{ route('admin.ai-search') }}" class="ra-nav-link {{ request()->is('admin/ai-search*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </span>
                <span class="ra-nl">AI Search</span>
            </a>
        @endif
        <div class="ra-sec-title">@lang('menu.recruitment')</div>

        @if(in_array("view_job_applications", $userPermissions))
            <a href="{{ route('admin.job-onboard.index') }}" class="ra-nav-link {{ request()->is('admin/job-onboard*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="job-onboard" /></span>
                <span class="ra-nl">@lang('menu.jobOnboard')</span>
            </a>
        @endif

        @if(in_array("view_schedule", $userPermissions))
            <a href="{{ route('admin.interview-schedule.index') }}" class="ra-nav-link {{ request()->is('admin/interview-schedule*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="interview-schedule" /></span>
                <span class="ra-nl">@lang('menu.interviewSchedule')</span>
            </a>
        @endif

        @if(in_array("view_team", $userPermissions))
            <a href="{{ route('admin.team.index') }}" class="ra-nav-link {{ request()->is('admin/team*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="team" /></span>
                <span class="ra-nl">@lang('menu.team')</span>
            </a>
        @endif

        <div class="ra-sec-title">@lang('menu.general')</div>

        @if ($user->roles->count() > 0)
            <a href="{{ route('admin.todo-items.index') }}" class="ra-nav-link {{ request()->is('admin/todo-items*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="todos" /></span>
                <span class="ra-nl">@lang('menu.todoList')</span>
            </a>
        @endif

        @if ($user->roles->count() > 0)
            <a href="{{ route('admin.job_alert.index') }}" class="ra-nav-link {{ request()->is('admin/job_alert*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="job-alert" /></span>
                <span class="ra-nl">@lang('menu.jobAlert')</span>
            </a>
        @endif

        <a href="{{ route('admin.report.index') }}" class="ra-nav-link {{ request()->is('admin/report*') ? 'on' : '' }}">
            <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="report" /></span>
            <span class="ra-nl">@lang('app.reports')</span>
        </a>

        @if(in_array("view_schedule", $userPermissions))
            @if(isset($zoom_setting->enable_zoom) && $zoom_setting->enable_zoom == 1)
                <a href="{{ route('admin.zoom-meeting.table-view') }}" class="ra-nav-link {{ request()->is('admin/zoom-meeting*') ? 'on' : '' }}">
                    <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="zoom" /></span>
                    <span class="ra-nl">@lang('menu.zoomMeeting')</span>
                </a>
            @endif
        @endif

        <div x-data="{ open: {{ \Request()->is('admin/settings/*') || \Request()->is('admin/profile') ? 'true' : 'false' }} }">
            <button type="button" @click.prevent="open = !open" class="ra-nav-link {{ \Request()->is('admin/settings/*') || \Request()->is('admin/profile') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="settings" /></span>
                <span class="ra-nl">@lang('menu.settings')</span>
                <svg class="ra-settings-chevron h-2.5 w-2.5 shrink-0 text-white/40 transition-transform ml-auto" :class="open ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <ul x-show="open"
                x-transition
                class="ra-nav-sub"
                style="display: {{ \Request()->is('admin/settings/*') || \Request()->is('admin/profile') ? 'block' : 'none' }};">
                <li>
                    <a href="@if(!$user->is_superadmin){{ route('admin.profile.index') }}@else{{ route('superadmin.profile.index') }}@endif"
                       class="ra-nav-sublink {{ request()->is('admin/profile*') ? 'on' : '' }}">
                        <span class="ra-sublink-dot" aria-hidden="true"></span>
                        <span>@lang('menu.myProfile')</span>
                    </a>
                </li>
                @if(in_array('manage_settings', $userPermissions))
                    <li>
                        <a href="{{ route('admin.settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.businessSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.application-setting.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/application-setting') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.applicationFormSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.currency-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/currency-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.currencySetting')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.role-permission.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/role-permission') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.rolesPermission')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.language-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/language-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('app.language') @lang('menu.settings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.footer-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/footer-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.footerSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.theme-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/theme-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.themeSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.smtp-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/smtp-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.mailSetting')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.sms-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/sms-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.smsSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.ai-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/ai-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.aiSettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.storage-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/storage-settings*') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.storageSetting')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.security-setting.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/security-setting') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.securitySettings')</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.linkedin-settings.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/linkedin-settings') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.linkedInSettings')</span>
                        </a>
                    </li>
                    @if($global->system_update == 1)
                        <li>
                            <a href="{{ route('admin.update-application.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/update-application') ? 'on' : '' }}">
                                <span class="ra-sublink-dot" aria-hidden="true"></span>
                                <span>@lang('menu.updateApplication')</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('admin.zoom-setting.index') }}" class="ra-nav-sublink {{ request()->is('admin/settings/zoom-setting') ? 'on' : '' }}">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.zoomSetting')</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://froiden.freshdesk.com/support/solutions/" class="ra-nav-sublink" target="_blank" rel="noopener noreferrer">
                            <span class="ra-sublink-dot" aria-hidden="true"></span>
                            <span>@lang('menu.help')</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="ra-sec-title">@lang('menu.miscellaneous')</div>
        <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="ra-nav-link">
            <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="front-website" /></span>
            <span class="ra-nl">@lang('menu.frontWebsite')</span>
        </a>
        @if(in_array("view_category", $userPermissions))
            <a href="{{ route('admin.job-categories.index') }}" class="ra-nav-link {{ request()->is('admin/job-categories*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="job-categories" /></span>
                <span class="ra-nl">@lang('menu.jobCategories')</span>
            </a>
        @endif

        @if(in_array("view_skills", $userPermissions))
            <a href="{{ route('admin.skills.index') }}" class="ra-nav-link {{ request()->is('admin/skills*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="skills" /></span>
                <span class="ra-nl">@lang('menu.skills')</span>
            </a>
        @endif

        @if(in_array("view_company", $userPermissions))
            <a href="{{ route('admin.company.index') }}" class="ra-nav-link {{ request()->is('admin/company*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="company" /></span>
                <span class="ra-nl">@lang('menu.companies')</span>
            </a>
        @endif

        @if(in_array("view_locations", $userPermissions))
            <a href="{{ route('admin.locations.index') }}" class="ra-nav-link {{ request()->is('admin/locations*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="locations" /></span>
                <span class="ra-nl">@lang('menu.locations')</span>
            </a>
             <a href="{{ route('admin.applications-archive.index') }}" class="ra-nav-link {{ request()->is('admin/applications-archive*') ? 'on' : '' }}">
                <span class="ra-ni" aria-hidden="true"><x-ra-sidebar-icon name="candidate-database" /></span>
                <span class="ra-nl">@lang('menu.candidateDatabase')</span>
            </a>
        @endif
    </div>

    <div class="ra-collapse-row">
        <button type="button" class="ra-collapse-btn" id="ra-collapse-btn" onclick="window.raToggleSidebar && window.raToggleSidebar()" aria-expanded="true">
            <svg id="ra-collapse-ico" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <span class="ra-clabel">@lang('app.collapse')</span>
        </button>
    </div>
</aside>
