@extends('layouts.app')

@push('head-script')
    <style>
        @keyframes rp-fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .rp-a { opacity: 0; animation: rp-fu 0.42s ease forwards; }
        .rp-a1 { animation-delay: 0.04s; } .rp-a2 { animation-delay: 0.09s; } .rp-a3 { animation-delay: 0.14s; }
        .rp-a4 { animation-delay: 0.18s; } .rp-a5 { animation-delay: 0.22s; } .rp-a6 { animation-delay: 0.26s; }

        .rp-stat-card { border-radius: 12px; padding: 12px 14px 10px; cursor: default; position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .rp-stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(0,0,0,0.1); }
        .rp-stat-card::before { content: ''; position: absolute; right: -14px; top: -14px; width: 52px; height: 52px; border-radius: 50%; opacity: 0.07; }
        .rp-sc-navy { background: #0F1F3D; color: #fff; } .rp-sc-navy::before { background: #fff; }
        .rp-sc-blue { background: #2563EB; color: #fff; } .rp-sc-blue::before { background: #fff; }
        .rp-sc-emerald { background: #059669; color: #fff; } .rp-sc-emerald::before { background: #fff; }
        .rp-sc-violet { background: #7C3AED; color: #fff; } .rp-sc-violet::before { background: #fff; }
        .rp-si { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.12); }
        .rp-si svg { width: 15px; height: 15px; }
        .rp-sn { font-size: 22px; font-weight: 800; letter-spacing: -0.04em; line-height: 1; margin-top: 8px; }
        .rp-sl { font-size: 10px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 2px; opacity: 0.6; }
        .rp-s-change { font-size: 10.5px; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 3px; opacity: 0.8; }

        .rp-chart-card { background: #fff; border: 1px solid #E8E6E1; border-radius: 18px; padding: 22px 24px; }
        .rp-chart-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 12px; flex-wrap: wrap; }
        .rp-chart-title { font-size: 15px; font-weight: 700; color: #1A1E2E; letter-spacing: -0.01em; }
        .rp-chart-sub { font-size: 11.5px; color: #8892A0; margin-top: 2px; }

        .rp-filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
        .rp-fp { padding: 5px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 600; cursor: pointer; border: 1.5px solid #E2DED8; background: #fff; color: #8892A0; transition: all 0.15s; }
        .rp-fp.on { background: #2563EB; color: #fff; border-color: #2563EB; }
        .rp-fp:hover:not(.on) { border-color: #2563EB; color: #2563EB; }

        .rp-report-table { width: 100%; border-collapse: collapse; }
        .rp-report-table thead tr { background: #F8F7F4; }
        .rp-report-table thead th { padding: 10px 16px; text-align: left; font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #8892A0; }
        .rp-report-table tbody tr { border-top: 1px solid #F0EEE9; transition: background 0.12s; }
        .rp-report-table tbody tr:hover { background: #FAFBFD; }
        .rp-report-table tbody td { padding: 12px 16px; font-size: 13px; color: #3D4A5C; }
        .rp-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 600; }

        .rp-prog-bar { height: 6px; background: #EEF0F5; border-radius: 999px; overflow: hidden; }
        .rp-prog-fill { height: 100%; border-radius: 999px; transition: width 1s cubic-bezier(0.22, 1, 0.36, 1); }

        .rp-ldot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        .rp-export-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; border: 1.5px solid #E2DED8; background: #fff; font-size: 12.5px; font-weight: 600; color: #5A6478; cursor: pointer; transition: all 0.15s; }
        .rp-export-btn:hover { border-color: #2563EB; color: #2563EB; background: #EFF6FF; }

        .rp-date-range { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; border: 1.5px solid #E2DED8; background: #fff; font-size: 12.5px; font-weight: 600; color: #5A6478; }

        @media print {
            #ra-sidebar, .ra-topbar, #right-sidebar, #right-sidebar-backdrop,
            #sticky-notes-sidebar, #sticky-notes-backdrop, #sticky-notes-trigger,
            #sticky-notes-trigger-mobile-wrap, #footer-sticky-notes { display: none !important; }
            .ra-main { margin: 0 !important; padding: 0 !important; }
        }
    </style>
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-[-0.025em] text-[#1A1E2E]">{{ __('modules.reportPage.titleAnalytics') }}
        <span class="jc-cat-serif">{{ __('modules.reportPage.titleReport') }}</span>
    </span>
@endsection

@section('page-subtitle')
    {{ __('modules.reportPage.subtitle') }}
@endsection

@section('create-button')
    <div class="flex flex-wrap items-center justify-end gap-3">
        <div class="rp-date-range">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $dateRangeLabel }}
        </div>
        <button type="button" class="rp-export-btn" onclick="window.print()">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('modules.reportPage.exportPdf') }}
        </button>
    </div>
@endsection

@php
    $rpDonutData = array_column($donutSegments, 'count');
    $rpDonutColors = array_column($donutSegments, 'color');
    $rpStatusPills = [
        'applied' => ['bg' => '#DBEAFE', 'color' => '#1D4ED8', 'dot' => '#2563EB'],
        'phone screen' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'dot' => '#059669'],
        'interview' => ['bg' => '#EDE9FE', 'color' => '#5B21B6', 'dot' => '#7C3AED'],
        'hired' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'dot' => '#059669'],
        'rejected' => ['bg' => '#FFE4E6', 'color' => '#9F1239', 'dot' => '#EF4444'],
        'expired' => ['bg' => '#F3F4F6', 'color' => '#4B5563', 'dot' => '#9CA3AF'],
    ];
    $rpCatPills = [
        ['bg' => '#EFF6FF', 'color' => '#1D4ED8'],
        ['bg' => '#F5F3FF', 'color' => '#5B21B6'],
        ['bg' => '#ECFDF5', 'color' => '#065F46'],
        ['bg' => '#FFF7ED', 'color' => '#92400E'],
        ['bg' => '#FEF3C7', 'color' => '#92400E'],
        ['bg' => '#FCE7F3', 'color' => '#9D174D'],
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 rp-a rp-a2">
            <div class="rp-stat-card rp-sc-navy">
                <div class="flex items-center justify-between">
                    <div class="rp-si">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="rgba(255,255,255,0.8)" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider" style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.5);">{{ __('modules.reportPage.statTotal') }}</span>
                </div>
                <div class="rp-sn rp-cnt" data-rp-v="{{ $jobApplication }}">0</div>
                <div class="rp-sl">{{ __('modules.reportPage.kpiApplications') }}</div>
                <div class="rp-s-change">
                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    @if($applicationsMomPercent === null)
                        <span>{{ __('modules.reportPage.momNoCompare') }}</span>
                    @elseif($applicationsMomPercent === 0)
                        <span>{{ __('modules.reportPage.momFlat') }}</span>
                    @else
                        <span>{{ $applicationsMomPercent > 0 ? '+' : '' }}{{ $applicationsMomPercent }}% {{ __('modules.reportPage.momSuffixThisMonth') }}</span>
                    @endif
                </div>
            </div>

            <div class="rp-stat-card rp-sc-blue">
                <div class="flex items-center justify-between">
                    <div class="rp-si">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="rgba(255,255,255,0.8)" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.55);">{{ __('modules.reportPage.statLive') }}</span>
                </div>
                <div class="rp-sn rp-cnt" data-rp-v="{{ $job }}">0</div>
                <div class="rp-sl">{{ __('modules.reportPage.kpiJobsPosted') }}</div>
                <div class="rp-s-change">
                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span>{{ __('modules.reportPage.activeJobs', ['count' => $activeJobs]) }}</span>
                </div>
            </div>

            <div class="rp-stat-card rp-sc-emerald">
                <div class="flex items-center justify-between">
                    <div class="rp-si">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="rgba(255,255,255,0.8)" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider" style="background:rgba(255,255,255,0.14);color:rgba(255,255,255,0.6);">{{ __('modules.reportPage.statHired') }}</span>
                </div>
                <div class="rp-sn rp-cnt" data-rp-v="{{ $candidatesHired }}">0</div>
                <div class="rp-sl">{{ __('modules.reportPage.kpiCandidatesHired') }}</div>
                <div class="rp-s-change">
                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span>{{ __('modules.reportPage.hireRate', ['rate' => $hireRate]) }}</span>
                </div>
            </div>

            <div class="rp-stat-card rp-sc-violet">
                <div class="flex items-center justify-between">
                    <div class="rp-si">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="rgba(255,255,255,0.8)" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.55);">{{ __('modules.reportPage.statToday') }}</span>
                </div>
                <div class="rp-sn rp-cnt" data-rp-v="{{ $interviewTotal }}">0</div>
                <div class="rp-sl">{{ __('modules.reportPage.kpiInterviewSchedule') }}</div>
                <div class="rp-s-change">
                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span>{{ __('modules.reportPage.interviewsToday', ['count' => $interviewsToday]) }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 rp-a rp-a3">
            <div class="rp-chart-card lg:col-span-1">
                <div class="rp-chart-hd">
                    <div>
                        <p class="rp-chart-title">{{ __('modules.reportPage.chartStatusTitle') }}</p>
                        <p class="rp-chart-sub">{{ __('modules.reportPage.chartStatusSub') }}</p>
                    </div>
                </div>
                @if($donutTotal > 0)
                    <div class="relative flex h-[200px] items-center justify-center">
                        <canvas id="rpDonutChart" aria-label="{{ __('modules.reportPage.chartStatusTitle') }}"></canvas>
                        <div class="pointer-events-none absolute text-center">
                            <p class="text-2xl font-extrabold tracking-tight text-[#1A1E2E]">{{ $donutTotal }}</p>
                            <p class="text-[10.5px] font-semibold text-[#8892A0]">{{ __('modules.reportPage.donutCenterTotal') }}</p>
                        </div>
                    </div>
                @else
                    <p class="py-12 text-center text-sm font-medium text-[#8892A0]">{{ __('modules.reportPage.donutEmpty') }}</p>
                @endif
                <div class="mt-4 flex flex-col gap-2">
                    @foreach($donutSegments as $seg)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="rp-ldot" style="background:{{ $seg['color'] }};"></div>
                                <span class="text-xs text-[#5A6478]">{{ $seg['label'] }}</span>
                            </div>
                            <span class="text-[12.5px] font-bold text-[#1A1E2E]">{{ $seg['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rp-chart-card lg:col-span-2">
                <div class="rp-chart-hd">
                    <div>
                        <p class="rp-chart-title">{{ __('modules.reportPage.chartMonthlyTitle') }}</p>
                        <p class="rp-chart-sub">{{ __('modules.reportPage.chartMonthlySub') }}</p>
                    </div>
                    <div class="rp-filter-pills" role="group" aria-label="{{ __('modules.reportPage.chartMonthlyTitle') }}">
                        <button type="button" class="rp-fp on" data-rp-bar="6m">{{ __('modules.reportPage.range6m') }}</button>
                        <button type="button" class="rp-fp" data-rp-bar="3m">{{ __('modules.reportPage.range3m') }}</button>
                        <button type="button" class="rp-fp" data-rp-bar="1m">{{ __('modules.reportPage.range1m') }}</button>
                    </div>
                </div>
                <div class="h-[220px]">
                    <canvas id="rpBarChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 rp-a rp-a4">
            <div class="rp-chart-card lg:col-span-2">
                <div class="rp-chart-hd">
                    <div>
                        <p class="rp-chart-title">{{ __('modules.reportPage.chartFunnelTitle') }}</p>
                        <p class="rp-chart-sub">{{ __('modules.reportPage.chartFunnelSub') }}</p>
                    </div>
                </div>
                <div class="h-[200px]">
                    <canvas id="rpLineChart"></canvas>
                </div>
            </div>

            <div class="rp-chart-card lg:col-span-1">
                <div class="rp-chart-hd">
                    <div>
                        <p class="rp-chart-title">{{ __('modules.reportPage.chartCategoryTitle') }}</p>
                        <p class="rp-chart-sub">{{ __('modules.reportPage.chartCategorySub') }}</p>
                    </div>
                </div>
                @forelse($categoryBreakdown as $cat)
                    <div class="{{ $loop->first ? 'mt-2' : 'mt-4' }}">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-[#1A1E2E]">{{ $cat['name'] }}</span>
                            <span class="text-xs font-bold" style="color:{{ $cat['color'] }};">{{ $cat['count'] }}</span>
                        </div>
                        <div class="rp-prog-bar">
                            <div class="rp-prog-fill rp-prog-anim" style="width:0;background:{{ $cat['color'] }};" data-rp-w="{{ $cat['width'] }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="mt-4 text-sm text-[#8892A0]">{{ __('modules.reportPage.categoryEmpty') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rp-chart-card rp-a rp-a5">
            <div class="rp-chart-hd">
                <div>
                    <p class="rp-chart-title">{{ __('modules.reportPage.recentTitle') }}</p>
                    <p class="rp-chart-sub">{{ __('modules.reportPage.recentSub', ['count' => 5]) }}</p>
                </div>
                <a href="{{ $jobApplicationsIndexUrl }}" class="rp-export-btn no-underline">{{ __('modules.reportPage.viewAll') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="rp-report-table">
                    <thead>
                    <tr>
                        <th>{{ __('modules.reportPage.tableHash') }}</th>
                        <th>{{ __('modules.reportPage.tableCandidate') }}</th>
                        <th>{{ __('modules.reportPage.tablePosition') }}</th>
                        <th>{{ __('modules.reportPage.tableCategory') }}</th>
                        <th>{{ __('modules.reportPage.tableApplied') }}</th>
                        <th>{{ __('modules.reportPage.tableStatus') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentApplications as $app)
                        @php
                            $stKey = strtolower(optional($app->status)->status ?? '');
                            $pill = $rpStatusPills[$stKey] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'dot' => '#6B7280'];
                            $catIdx = ($app->job && $app->job->category_id) ? ((int) $app->job->category_id % count($rpCatPills)) : 0;
                            $cp = $rpCatPills[$catIdx];
                            $catName = $app->job && $app->job->category ? $app->job->category->name : __('modules.reportPage.uncategorized');
                        @endphp
                        <tr>
                            <td class="text-xs text-[#C4CBD4]">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="font-semibold text-[#1A1E2E]">{{ $app->full_name }}</td>
                            <td>{{ $app->job->title ?? '—' }}</td>
                            <td>
                                <span class="rp-status-pill" style="background:{{ $cp['bg'] }};color:{{ $cp['color'] }};">{{ $catName }}</span>
                            </td>
                            <td class="text-[#8892A0]">{{ $app->created_at ? $app->created_at->format('M j, Y') : '—' }}</td>
                            <td>
                                <span class="rp-status-pill" style="background:{{ $pill['bg'] }};color:{{ $pill['color'] }};">
                                    <span class="inline-block h-[5px] w-[5px] shrink-0 rounded-full" style="background:{{ $pill['dot'] }};"></span>
                                    {{ $app->status ? ucfirst($app->status->status) : '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-[#8892A0]">{{ __('modules.reportPage.recentEmpty') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        (function () {
            function rpAnimCnt() {
                document.querySelectorAll('.rp-cnt').forEach(function (el) {
                    var t = parseInt(el.getAttribute('data-rp-v'), 10);
                    if (isNaN(t)) return;
                    var s = null;
                    function step(ts) {
                        if (!s) s = ts;
                        var p = Math.min((ts - s) / 800, 1);
                        el.textContent = Math.round((1 - Math.pow(1 - p, 3)) * t);
                        if (p < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                });
            }

            function rpAnimBars() {
                setTimeout(function () {
                    document.querySelectorAll('.rp-prog-anim').forEach(function (el) {
                        el.style.width = el.getAttribute('data-rp-w') + '%';
                    });
                }, 400);
            }

            var barDatasets = @json($barDatasets);
            var lineChart = @json($lineChart);
            var donutData = @json($rpDonutData);
            var donutColors = @json($rpDonutColors);
            var donutLabels = @json(array_column($donutSegments, 'label'));
            var tooltipWord = @json(__('modules.reportPage.tooltipApplications'));
            var legendApplied = @json(__('modules.reportPage.legendApplied'));
            var legendShortlisted = @json(__('modules.reportPage.legendShortlisted'));
            var legendHired = @json(__('modules.reportPage.legendHired'));

            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
                Chart.defaults.color = '#5A6478';
            }

            window.addEventListener('load', function () {
                setTimeout(rpAnimCnt, 200);
                rpAnimBars();

                if (typeof Chart === 'undefined') return;

                var donutTotal = {{ (int) $donutTotal }};
                if (donutTotal > 0 && document.getElementById('rpDonutChart')) {
                    new Chart(document.getElementById('rpDonutChart'), {
                        type: 'doughnut',
                        data: {
                            labels: donutLabels,
                            datasets: [{
                                data: donutData,
                                backgroundColor: donutColors,
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            cutout: '72%',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' ' + ctx.label + ': ' + ctx.raw;
                                        }
                                    }
                                }
                            },
                            animation: { duration: 900, easing: 'easeInOutQuart' }
                        }
                    });
                }

                var barEl = document.getElementById('rpBarChart');
                if (barEl) {
                    var barChart = new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels: barDatasets['6m'].labels,
                            datasets: [{
                                data: barDatasets['6m'].data,
                                backgroundColor: function (ctx) {
                                    var i = ctx.dataIndex;
                                    var last = ctx.dataset.data.length - 1;
                                    return i === last ? '#2563EB' : '#DBEAFE';
                                },
                                borderRadius: 8,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: { font: { size: 11, weight: '600' }, color: '#B0B8C4' }
                                },
                                y: {
                                    grid: { color: '#F0EEE9' },
                                    border: { display: false },
                                    ticks: { font: { size: 11, weight: '600' }, color: '#B0B8C4' }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' ' + ctx.raw + ' ' + tooltipWord;
                                        }
                                    }
                                }
                            },
                            animation: { duration: 600 }
                        }
                    });

                    document.querySelectorAll('[data-rp-bar]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var range = btn.getAttribute('data-rp-bar');
                            document.querySelectorAll('[data-rp-bar]').forEach(function (b) { b.classList.remove('on'); });
                            btn.classList.add('on');
                            var d = barDatasets[range];
                            if (!d) return;
                            barChart.data.labels = d.labels;
                            barChart.data.datasets[0].data = d.data;
                            barChart.update();
                        });
                    });
                }

                var lineEl = document.getElementById('rpLineChart');
                if (lineEl) {
                    new Chart(lineEl, {
                        type: 'line',
                        data: {
                            labels: lineChart.labels,
                            datasets: [
                                {
                                    label: legendApplied,
                                    data: lineChart.applied,
                                    borderColor: '#2563EB',
                                    backgroundColor: 'rgba(37,99,235,0.08)',
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#2563EB',
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                },
                                {
                                    label: legendShortlisted,
                                    data: lineChart.shortlisted,
                                    borderColor: '#059669',
                                    backgroundColor: 'rgba(5,150,105,0.06)',
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#059669',
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                },
                                {
                                    label: legendHired,
                                    data: lineChart.hired,
                                    borderColor: '#7C3AED',
                                    backgroundColor: 'rgba(124,58,237,0.06)',
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#7C3AED',
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        borderRadius: 3,
                                        useBorderRadius: true,
                                        font: { size: 11, weight: '600' },
                                        color: '#5A6478',
                                        padding: 16
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: { font: { size: 11, weight: '600' }, color: '#B0B8C4' }
                                },
                                y: {
                                    grid: { color: '#F0EEE9' },
                                    border: { display: false },
                                    ticks: { font: { size: 11, weight: '600' }, color: '#B0B8C4' }
                                }
                            },
                            animation: { duration: 800 }
                        }
                    });
                }
            });
        })();
    </script>
@endpush
