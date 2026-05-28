@extends('layouts.app')


@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.updateApplication')</span>
@endsection



@section('content')
<div class="flex w-full flex-col">
    @php($updateVersionInfo = \Froiden\Envato\Functions\EnvatoUpdate::updateVersionInfo())
    @include('vendor.froiden-envato.update.update_blade')
    @include('vendor.froiden-envato.update.version_info')
    @include('vendor.froiden-envato.update.changelog')
</div>
@endsection

@push('footer-script')
    @include('vendor.froiden-envato.update.update_script')
@endpush

