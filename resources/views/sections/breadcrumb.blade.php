@hasSection('hide-ra-page-header')
@else
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
    <div class="min-w-0">
        @hasSection('page-title-html')
            <h1 class="ra-page-h1">{!! $__env->yieldContent('page-title-html') !!}</h1>
        @else
            <h1 class="ra-page-h1">{{ $pageTitle ?? '' }}</h1>
        @endif
        @hasSection('page-subtitle')
            <p class="text-[12.5px] mt-1 text-[#8892A0]">@yield('page-subtitle')</p>
        @endif
    </div>
    <div class="flex flex-shrink-0 items-center gap-3">
        @yield('create-button')
    </div>
</div>
@endif
