<div class="flex items-center justify-between p-4 border-b border-gray-200">
    <h4 class="text-lg font-semibold text-gray-900">@lang('modules.jobApplication.documents')</h4>
    <button type="button" class="text-gray-400 hover:text-gray-600" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="p-4">
    <button id="addNewDoc" type="button" class="btn btn-sm btn-primary mb-3">
        @lang('app.addNew')
    </button>
    <form id="addDocument" class="ajax-form mb-3 space-y-4" action="{{ route('admin.documents.store') }}" method="POST" autocomplete="off">
        @csrf
        <div class="form-body">
            <input type="hidden" name="documentable_type" value="{{ $documentable_type }}">
            <input type="hidden" name="documentable_id" value="{{ $documentable_id }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label required">@lang('modules.jobApplication.document.name')</label>
                    <input type="text" class="form-control" id="name" name="name">
                </div>
                <div class="form-group">
                    <label class="form-label required">@lang('modules.jobApplication.document.file')</label>
                    <input class="select-file" accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx,.rtf,.gif,.txt" id="file" type="file" name="file">
                    <span class="text-sm text-gray-500 mt-1 block">@lang('modules.jobApplication.document.fileNote')</span>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" id="create-document" class="btn btn-sm btn-success">@lang('app.add') <i class="fa fa-arrow-right"></i></button>
        </div>
    </form>
    <hr class="my-3">
    <div class="jc-table-card ra-dt-wrap my-3 overflow-hidden">
        <table id="docTable" class="jc-cat-table display w-full" style="width:100%">
            <thead>
            <tr>
                <th style="width:72px;">#</th>
                <th>@lang('app.name')</th>
                <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200">
    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">
        <i class="fa fa-times"></i> @lang('app.close')
    </button>
</div>

<script>
    function resetForm() {
        $('#addDocument').find('#name').val('');
        $('#addDocument').find('#file').val('');
    }

    var docTable = $('#docTable').DataTable({
        responsive: false,
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: "{!! route('admin.documents.data', ['documentable_type' => $documentable_type, 'documentable_id' => $documentable_id]) !!}",
        language: languageOptions(),
        stripeClasses: [],
        dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
        drawCallback: function () {
            $('[data-toggle="tooltip"]').tooltip();
        },
        order: [[1, 'asc']],
        columns: [
            { data: 'DT_Row_Index', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', width: '15%', searchable: false, className: 'jc-td-right' }
        ]
    });

    $('#addDocument').addClass('hidden');

    $('#addNewDoc').click(function() {
        $('#addDocument').toggleClass('hidden');
    });

    $('#addDocument').submit(function(e) {
        e.preventDefault();

        const form = $(this);

        $.easyAjax({
            url: form.attr('action'),
            type: 'POST',
            container: '#addDocument',
            file: true,
            success: function (response) {
                if (response.status == 'success') {
                    docTable.draw(false)
                    resetForm();
                }
            }
        })
    });
</script>