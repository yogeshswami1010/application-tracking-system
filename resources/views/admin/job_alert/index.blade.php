@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex items-center p-4 bg-blue-600">
                <div class="flex-shrink-0">
                    <i class="icon-badge text-white text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-white">@lang('modules.dashboard.totalJobsAlert')</p>
                    <p class="text-2xl font-semibold text-white">{{ number_format($totalJobAlert) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex items-center p-4 bg-green-600">
                <div class="flex-shrink-0">
                    <i class="icon-badge text-white text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-white">@lang('modules.dashboard.activeJobsAlert')</p>
                    <p class="text-2xl font-semibold text-white">{{ number_format($activeJobsAlert) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex items-center p-4 bg-red-600">
                <div class="flex-shrink-0">
                    <i class="icon-badge text-white text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-white">@lang('modules.dashboard.inactiveJobsAlert')</p>
                    <p class="text-2xl font-semibold text-white">{{ number_format($inactiveJobsAlert) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="form-group">
            <select name="" id="filter-status" class="form-control">
                <option value="">@lang('app.filter') @lang('app.status'): @lang('app.viewAll')</option>
                <option value="active">@lang('app.active')</option>
                <option value="inactive">@lang('app.inactive')</option>
            </select>
        </div>
        <div class="form-group">
            <select name="" id="filter-location" class="form-control">
                <option value="">@lang('app.filter') @lang('menu.locations'): @lang('app.viewAll')</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ ucwords($location->location) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="jc-table-card ra-dt-wrap overflow-hidden">
        <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
            <thead>
                <tr>
                    <th style="width:72px;">#</th>
                    <th>@lang('app.email')</th>
                    <th>@lang('menu.locations')</th>
                    <th>@lang('modules.jobs.workExperience')</th>
                    <th>@lang('modules.jobs.jobType')</th>
                    <th>@lang('app.status')</th>
                    <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection


@push('footer-script')
    <script>
        var table = $('#myTable').DataTable({
            responsive: false,
            serverSide: true,
            ajax: {
                'url': '{!! route('admin.job_alert.data') !!}',
                "data": function(d) {
                    return $.extend({}, d, {
                        "filter_status": $('#filter-status').val(),
                        "filter_location": $('#filter-location').val(),
                    });
                }
            },
            language: languageOptions(),
            stripeClasses: [],
            dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'DT_Row_Index',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'location_id',
                    name: 'location_id'
                },
                {
                    data: 'work_experience_id',
                    name: 'work_experience_id'
                },
                {
                    data: 'job_type_id',
                    name: 'job_type_id'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    width: '20%',
                    className: 'jc-td-right'
                }
            ]
        });

        $('#filter-status, #filter-location').change(function() {
            table.draw(false);
        })

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

                    let url = "{{ route('admin.job_alert.destroy', ':id') }}";
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
