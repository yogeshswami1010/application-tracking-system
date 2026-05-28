@extends('layouts.app')

@if(in_array("add_question", $userPermissions))
@section('create-button')
    <a href="{{ route('admin.job-onboard-questions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0f172a] px-4 py-2 text-[12.5px] font-semibold text-white shadow-sm transition hover:bg-[#1e293b] ml-4">
        <i class="fa fa-plus-circle"></i> @lang('app.createNew')
    </a>
@endsection
@endif

@section('content')

    <div class="flex flex-col gap-4">
        @if(in_array("view_jobs", $userPermissions))
            <a href="{{ route('admin.job-onboard.index') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2 text-[12.5px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8]">
                <i class="icon-badge"></i> @lang('menu.jobOnboard')
            </a>
        @endif
        <div class="jc-table-card ra-dt-wrap overflow-hidden">
            <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
                <thead>
                <tr>
                    <th style="width:72px;">#</th>
                    <th>@lang('app.question')</th>
                    <th>@lang('app.required')</th>
                    <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('footer-script')
    <script>
        var table = $('#myTable').DataTable({
            responsive: false,
            serverSide: true,
            ajax: '{!! route('admin.job-onboard-questions.data') !!}',
            language: languageOptions(),
            stripeClasses: [],
            dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns: [
                { data: 'DT_Row_Index', orderable: false, searchable: false},
                { data: 'question', name: 'question' },
                { data: 'required', name: 'required' },
                { data: 'action', name: 'action', width: '20%', className: 'jc-td-right' }
            ]
        });

        $('body').on('click', '.sa-params', function(){
            var id = $(this).data('row-id');
            swal({
                title: "@lang('errors.areYouSure')",
                text: "@lang('errors.deleteWarning')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.delete')",
                cancelButtonText: "@lang('app.cancel')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.job-onboard-questions.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                                table.draw(false);
                            }
                        }
                    });
                }
            });
        });

    </script>
@endpush
