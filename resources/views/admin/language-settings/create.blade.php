<form role="form" id="createLangForm" class="ajax-form space-y-4" method="POST">
    @csrf
    <div>
        <label class="jc-field-label" for="language_name">@lang('app.language') @lang('app.name') <span class="text-red-400">*</span></label>
        <input type="text" name="language_name" id="language_name" class="jc-field-input" value="" required>
    </div>
    <div>
        <label class="jc-field-label" for="language_code">@lang('app.language') @lang('app.code') <span class="text-red-400">*</span></label>
        <input type="text" name="language_code" id="language_code" class="jc-field-input" value="" required>
    </div>
    <div>
        <label class="jc-field-label" for="lang-status">@lang('app.language') @lang('app.status')</label>
        <select name="status" id="lang-status" class="bs-f-sel">
            <option value="enabled">@lang('app.enable')</option>
            <option value="disabled">@lang('app.disable')</option>
        </select>
    </div>
    <button type="button" id="lang-modal-save-action" class="sr-only" tabindex="-1" aria-hidden="true">@lang('app.save')</button>
</form>

<script>
    $('#lang-modal-save-action').off('click.langCreate').on('click.langCreate', function () {
        var form = $('#createLangForm');
        $.easyAjax({
            url: '{{ route('admin.language-settings.store') }}',
            container: '#createLangForm',
            type: 'POST',
            redirect: true,
            data: form.serialize(),
            success: function (response) {
                if (response.status == 'success') {
                    $('#application-md-modal').addClass('hidden').css({display: '', visibility: '', opacity: ''});
                    window.location.reload();
                }
            }
        });
    });
</script>
