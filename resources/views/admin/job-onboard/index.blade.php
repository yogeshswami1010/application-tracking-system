@extends('layouts.app')

@push('head-script')
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.jobOnboard')</span>
@endsection

@section('page-subtitle')
    @lang('modules.jobApplication.applicantName') · @lang('menu.jobs') · @lang('app.status')
@endsection

@section('create-button')
    <a href="{{ route('admin.job-onboard-questions.index') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        @lang('menu.customQuestion')
    </a>
@endsection

@section('content')

    <div class="mx-auto pb-8">
        <div class="jc-table-card ra-dt-wrap overflow-hidden">
            <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
                <thead>
                <tr>
                    <th style="width:72px;">#</th>
                    <th>@lang('modules.jobApplication.applicantName')</th>
                    <th>@lang('menu.jobs')</th>
                    <th>@lang('menu.locations')</th>
                    <th>@lang('app.joinDate')</th>
                    <th>@lang('app.acceptLastDate')</th>
                    <th>@lang('app.status')</th>
                    <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="hidden fixed inset-0 z-50 overflow-y-auto" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="$('#exampleModal').addClass('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-900" id="exampleModalLabel">@lang('menu.cancelReason')</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="$('#exampleModal').addClass('hidden')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="save-cancel-reason" method="post">
                    <div class="p-6">
                        <div class="form-group">
                            <label class="form-label required">@lang('app.remark')</label>
                            <textarea type="text" id="cancel_reason" class="form-control" name="cancel_reason" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <button type="button" class="btn btn-secondary" onclick="$('#exampleModal').addClass('hidden')">Close</button>
                        <button type="submit" class="btn btn-primary save-cancel-reason">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('assets/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>

    <script>

        var table;
        tableLoad();

        function tableLoad() {
            table = $('#myTable').DataTable({
                responsive: false,
                processing: false,
                serverSide: true,
                destroy: true,
                stateSave: true,
                ajax: '{!! route('admin.job-onboard.data') !!}',
                language: languageOptions(),
                stripeClasses: [],
                dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                columns: [
                    {data: 'id', name: 'on_board_details.id', orderable: false, searchable: false},
                    {data: 'full_name', name: 'job_applications.full_name', width: '17%'},
                    {data: 'title', name: 'jobs.title', width: '17%'},
                    {data: 'location', name: 'job_locations.location'},
                    {data: 'joining_date', name: 'joining_date'},
                    {data: 'accept_last_date', name: 'accept_last_date'},
                    {data: 'hired_status', name: 'hired_status'},
                    {data: 'action', name: 'action', width: '15%', searchable : false, className: 'jc-td-right'}
                ],
                order: [[4, 'desc']]
            });
        }

        $('body').on('click', '.send-offer', function(){
            var id = $(this).data('row-id');
            swal({
                title: "@lang('errors.areYouSure')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.send') @lang('app.offer')",
                cancelButtonText: "@lang('app.cancel')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.job-onboard.send-offer',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'GET',
                        url: url,
                        container: '#myTable',
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

        // Change status in cancel
        $('body').on('click', '.sa-params', function () {
            var id = $(this).data('row-id');
            $('#save-cancel-reason').data('onboard-id', id);
            $('#cancel_reason').val('');
            $('#exampleModal').removeClass('hidden');
        });

        $('#save-cancel-reason').on('submit', function (e) {
            e.preventDefault();

            var id = $(this).data('onboard-id');
            if (!id) {
                return;
            }

            var formData = $(this).serialize();
            var url = "{{ route('admin.job-onboard.update-status',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                data: formData,
                container: '#save-cancel-reason',
                success: function (response) {
                    if (response.status == "success") {
                        $.unblockUI();
                        table.draw(false);
                        $('#exampleModal').addClass('hidden');
                    }
                }
            });
        });

        $('#myTable').on('click', '.show-detail', function () {
            var $sidebar = $("#right-sidebar");
            $sidebar.removeClass('translate-x-full').addClass("shw-rside");

            var id = $(this).data('row-id');
            var url = "{{ route('admin.job-applications.show',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if (response.status == "success") {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        });
    </script>
@endpush