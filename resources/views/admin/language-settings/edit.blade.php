<form role="form" id="editLangForm" class="ajax-form space-y-4" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $language->id }}">
    <div>
        <label class="jc-field-label" for="language_name">@lang('app.language') @lang('app.name')</label>
        <input type="text" name="language_name" id="language_name" class="jc-field-input" value="{{ $language->language_name }}" required>
    </div>
    <div>
        <label class="jc-field-label" for="language_code">@lang('app.language') @lang('app.code')</label>
        <input type="text" name="language_code" id="language_code" class="jc-field-input" value="{{ $language->language_code }}" required>
    </div>
    @if ($language->language_code !== $global->locale)
        <div>
            <label class="jc-field-label" for="lang-status">@lang('app.language') @lang('app.status')</label>
            <select name="status" id="lang-status" class="bs-f-sel">
                <option @selected($language->status == 'enabled') value="enabled">@lang('app.enable')</option>
                <option @selected($language->status == 'disabled') value="disabled">@lang('app.disable')</option>
            </select>
        </div>
    @else
        <input id="lang-status" type="hidden" name="status" value="enabled">
    @endif
    {{-- Primary save: triggered by layout modal footer #application-md-modal-save-btn --}}
    <button type="button" id="lang-modal-save-action" class="sr-only" tabindex="-1" aria-hidden="true">@lang('app.save')</button>
</form>

<script>
    $('#lang-modal-save-action').off('click.langEdit').on('click.langEdit', function () {
        var form = $('#editLangForm');
        $.easyAjax({
            url: '{{ route('admin.language-settings.update', $language->id) }}',
            container: '#editLangForm',
            type: 'PUT',
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
