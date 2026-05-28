@extends('layouts.app')

@section('content')
<div class="flex flex-col">
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('app.createNew')</h4>

                <form id="editSettings" class="ajax-form space-y-4">
                    @csrf

                    <div class="form-group">
                        <label for="currency_name" class="form-label">@lang('modules.currency.currencyName')</label>
                        <input type="text" class="form-control" id="currency_name" name="currency_name">
                    </div>
                    <div class="form-group">
                        <label for="currency_symbol" class="form-label">@lang('modules.currency.currencySymbol')</label>
                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol">
                    </div>
                    <div class="form-group">
                        <label for="currency_code" class="form-label">@lang('modules.currency.currencyCode')</label>
                        <input type="text" class="form-control" id="currency_code" name="currency_code">
                    </div>
               
                    <div class="flex items-center gap-3">
                        <button type="button" id="save-form" class="btn btn-success">
                            @lang('app.save')
                        </button>
                        <button type="reset" class="btn btn-secondary">@lang('app.reset')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-script')

<script>

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route("admin.currency-settings.store")}}',
                container: '#editSettings',
                type: "POST",
                redirect: true,
                file: true
            })
        });
</script>

@endpush