@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('app.aiUsage')</span>
@endsection

@section('page-subtitle')
    @lang('app.aiUsageSubtitle')
@endsection

@section('create-button')
    <a href="{{ route('admin.ai-settings.index') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        @lang('menu.aiSettings')
    </a>
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bs-set-card p-5">
                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.totalPromptTokens')</p>
                <p class="mt-2 text-[24px] font-bold text-[#1A1E2E]">{{ number_format((int) data_get($aiUsageTotals, 'prompt_tokens', 0)) }}</p>
            </div>
            <div class="bs-set-card p-5">
                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.totalCompletionTokens')</p>
                <p class="mt-2 text-[24px] font-bold text-[#1A1E2E]">{{ number_format((int) data_get($aiUsageTotals, 'completion_tokens', 0)) }}</p>
            </div>
            <div class="bs-set-card p-5">
                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.totalRequests')</p>
                <p class="mt-2 text-[24px] font-bold text-[#1A1E2E]">{{ number_format((int) data_get($aiUsageTotals, 'total_requests', 0)) }}</p>
            </div>
            <div class="bs-set-card p-5">
                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.totalTokens')</p>
                <p class="mt-2 text-[24px] font-bold text-[#1A1E2E]">{{ number_format((int) data_get($aiUsageTotals, 'total_tokens', 0)) }}</p>
            </div>
        </div>

        <div class="bs-set-card overflow-hidden">
            <div class="bs-set-card-hd">
                <div class="bs-set-card-ic" style="background:#EFF6FF;">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('app.aiUsageByUser')</h2>
                    <p class="text-[12px] text-[#8892A0]">@lang('app.aiUsageByUserHint')</p>
                </div>
            </div>
            @if(($aiUsageByUser ?? collect())->isEmpty())
                <p class="px-6 py-8 text-center text-[13px] text-[#5A6478]">@lang('app.aiUsageEmpty')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="jc-cat-table min-w-[760px] w-full">
                        <thead>
                        <tr>
                            <th>@lang('app.user')</th>
                            <th class="jc-th-right">@lang('app.totalPromptTokens')</th>
                            <th class="jc-th-right">@lang('app.totalCompletionTokens')</th>
                            <th class="jc-th-right">@lang('app.totalRequests')</th>
                            <th class="jc-th-right">@lang('app.totalTokens')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($aiUsageByUser as $usageRow)
                            <tr>
                                <td class="font-semibold text-[#1A1E2E]">{{ $usageRow->user_name }}</td>
                                <td class="jc-td-right">{{ number_format((int) $usageRow->prompt_tokens) }}</td>
                                <td class="jc-td-right">{{ number_format((int) $usageRow->completion_tokens) }}</td>
                                <td class="jc-td-right">{{ number_format((int) $usageRow->total_requests) }}</td>
                                <td class="jc-td-right">{{ number_format((int) $usageRow->total_tokens) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
