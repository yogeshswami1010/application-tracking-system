@extends('layouts.app')

@push('head-script')
<style>
    /* Force Add Meeting modal center alignment */
    #my-meeting {
        position: fixed;
        inset: 0;
        z-index: 60;
        padding: 16px;
    }

    #my-meeting.flex {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #my-meeting > #modal-data-application {
        margin: auto !important;
    }

    #my-meeting .modal-content,
    #meetingDetailModal .modal-content,
    #categoryModal .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    #my-meeting .modal-header,
    #meetingDetailModal .modal-header,
    #categoryModal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 22px;
        background: #f8fafc;
    }

    #my-meeting .modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    #my-meeting .modal-body,
    #meetingDetailModal .modal-body,
    #categoryModal .modal-body {
        padding: 22px;
        background: #ffffff;
    }

    #my-meeting .modal-footer,
    #meetingDetailModal .modal-footer,
    #categoryModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
        padding: 14px 22px;
    }

    /* Complete redesign for Add Meeting modal */
    .zoom-create-modal .modal-dialog {
        width: min(1120px, calc(100vw - 32px));
        max-width: 1120px;
        margin: 24px auto;
    }

    .zoom-create-modal .modal-content {
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .zoom-create-modal .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 24px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .zoom-create-modal .modal-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
    }

    .zoom-create-modal .modal-body {
        padding: 20px 24px;
        background: #ffffff;
    }

    .zoom-create-modal .form-body {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .zoom-create-modal .row {
        margin-left: 0;
        margin-right: 0;
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 12px;
    }

    .zoom-create-modal .col-xs-12,
    .zoom-create-modal .col-md-3,
    .zoom-create-modal .col-md-4,
    .zoom-create-modal .col-md-6,
    .zoom-create-modal .col-md-12 {
        width: 100%;
        padding: 0;
        min-height: 0;
    }

    .zoom-create-modal .col-md-12 { grid-column: span 12; }
    .zoom-create-modal .col-md-6 { grid-column: span 7; }
    .zoom-create-modal .col-md-4 { grid-column: span 5; }
    .zoom-create-modal .col-md-3 { grid-column: span 3; }
    .zoom-create-modal .col-xs-6 { grid-column: span 6; }

    .zoom-create-modal .form-group {
        margin-bottom: 0;
    }

    .zoom-create-modal label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .zoom-create-modal .form-control,
    .zoom-create-modal textarea.form-control,
    .zoom-create-modal select.form-control {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.02);
    }

    .zoom-create-modal textarea.form-control {
        height: 88px;
        resize: vertical;
    }

    .zoom-create-modal .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .zoom-create-modal .select2-container {
        width: 100% !important;
    }

    .zoom-create-modal .select2-container .select2-selection--single,
    .zoom-create-modal .select2-container .select2-selection--multiple {
        min-height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
    }

    .zoom-create-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        color: #0f172a;
    }

    .zoom-create-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }

    .zoom-create-modal #addCategory {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 7px;
        background: #10b981;
        color: #fff;
        border: 0;
        margin-left: 6px;
    }

    .zoom-create-modal .radio-inline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 14px;
        font-size: 14px;
        color: #334155;
    }

    .zoom-create-modal .m-b-10 {
        margin-bottom: 8px;
    }

    .zoom-create-modal .inline-flex.items-center.gap-2 {
        margin-top: 6px;
        font-size: 14px;
        color: #334155;
    }

    .zoom-create-modal .modal-footer {
        padding: 14px 24px;
        background: #f8fafc;
    }

    @media (max-width: 991px) {
        .zoom-create-modal .col-md-6,
        .zoom-create-modal .col-md-4,
        .zoom-create-modal .col-md-3,
        .zoom-create-modal .col-xs-6 {
            grid-column: span 12;
        }
    }
</style>


@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('modules.zoommeeting.meetingName')</span>
@endsection

@section('page-subtitle')
    @lang('modules.zoommeeting.tableView')
@endsection

@section('create-button')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.zoom-meeting.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="ti-plus"></i>
            @lang('modules.zoommeeting.addMeeting')
        </a>
        <a href="{{ route('admin.zoom-meeting.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-[#16A34A] bg-white px-5 py-2.5 text-[13px] font-semibold text-[#15803d] shadow-sm transition hover:bg-[#f0fdf4] focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            <i class="fa fa-calendar"></i>
            @lang('modules.zoommeeting.calendarView')
        </a>
    </div>
@endsection

@section('content')

<div class="mx-auto pb-8">
    <div class="jc-table-card ra-dt-wrap overflow-hidden">
        <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('modules.meetings.meetingId')</th>
                    <th>@lang('modules.meetings.meetingName')</th>
                    <th>@lang('modules.meetings.startOn')</th>
                    <th>@lang('modules.meetings.endOn')</th>
                    <th>@lang('modules.meetings.status')</th>
                    <th class="jc-th-right">@lang('modules.meetings.action')</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- BEGIN MODAL -->
@include('admin.zoom.meeting-calendar._add-meeting-modal', [
    'attendees' => $users,
    'currentUser' => $user,
    'categories' => $categories
])
{{-- End  --}}
{{--Ajax Modal--}}
<div class="modal fade bs-modal-md" id="meetingDetailModal" role="dialog" aria-labelledby="myModalLabel" tabindex="-1" style="display: none;"
aria-hidden="true">
<div class="modal-dialog modal-lg max-w-5xl" id="modal-data-application">
   <div class="modal-content">
       <div class="modal-header">
           <button type="button" class="text-slate-400 transition hover:text-slate-700" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
           <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
       </div>
       <div class="modal-body">
           Loading...
       </div>
       <div class="modal-footer">
           <button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-dismiss="modal">Close</button>
           <button type="button" class="inline-flex items-center justify-center rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">Save changes</button>
       </div>
   </div>
   <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
{{--Ajax Modal Ends--}}
<div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/45 p-4" id="categoryModal" role="dialog" aria-labelledby="categoryModalHeading" aria-hidden="true">
<div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-2xl" id="modal-data-application">
   <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
       <span class="text-base font-semibold text-slate-800" id="categoryModalHeading">@lang('app.category')</span>
       <button type="button" class="text-slate-400 transition hover:text-slate-700" data-category-close="true" aria-hidden="true"><i class="fa fa-times"></i></button>
   </div>
   <div class="p-6" id="categoryModalBody">
       Loading...
   </div>
</div>
</div>
@endsection
 @push('footer-script')
<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script>
    function openAddMeetingModal() {
        $('#my-meeting').removeClass('hidden').addClass('flex').attr('aria-hidden', 'false');
        $('body').addClass('overflow-hidden');
    }

    function closeAddMeetingModal() {
        $('#my-meeting').removeClass('flex').addClass('hidden').attr('aria-hidden', 'true');
        $('body').removeClass('overflow-hidden');
    }

    function openCategoryModal() {
        $('#categoryModal').css('display', 'flex').removeClass('hidden').addClass('flex').attr('aria-hidden', 'false');
    }

    function closeCategoryModal() {
        $('#categoryModal').css('display', 'none').removeClass('flex').addClass('hidden').attr('aria-hidden', 'true');
        $('#categoryModalBody').html('Loading...');
    }

    $(function () {
        closeAddMeetingModal();
        closeCategoryModal();
        $('#meetingDetailModal')
            .removeClass('in show')
            .hide()
            .attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    // Toggle action dropdown (3-dot menu) for DataTable rows
    $('body').on('click', 'button[data-toggle="dropdown"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $menu = $(this).next('ul[role="menu"]');
        if ($menu.length) {
            $menu.toggleClass('hidden');
        }
    });
    $('body').on('click', function () {
        $('ul[role="menu"]').addClass('hidden');
    });

    // Close meeting detail modal (used by ajaxModal)
    $('body').on('click', '#meetingDetailModal [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        $('#meetingDetailModal').removeClass('in show').hide().attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    $('body').on('click', '#open-add-meeting', function (e) {
        e.preventDefault();
        openAddMeetingModal();
    });

    $('body').on('click', '[data-modal-close="my-meeting"]', function (e) {
        e.preventDefault();
        closeAddMeetingModal();
    });

    $('body').on('click', '#my-meeting', function (e) {
        if (e.target.id === 'my-meeting') {
            closeAddMeetingModal();
        }
    });

    $('body').on('click', '[data-category-close="true"]', function (e) {
        e.preventDefault();
        closeCategoryModal();
    });

    $('body').on('click', '#categoryModal', function (e) {
        if (e.target.id === 'categoryModal') {
            closeCategoryModal();
        }
    });

       $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        },
        width: '100%'
    });
    
   
  // Datepicker set
     $('#start_date, #end_date').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: true,
        minDate : new Date()
    });

    // Timepicker Set
    $('#start_time, #end_time').bootstrapMaterialDatePicker
    ({
        date: false,
        shortTime: true,   // look it
        format: 'HH:mm',
        switchOnClick: true
    });
    var table = $('#myTable').DataTable({
            responsive: false,
            serverSide: true,
            ajax: {'url' : '{!! route('admin.zoom-meeting.data') !!}',
                    "data": function ( d ) {
                        return $.extend( {}, d, {
                            "filter_company": $('#filter-company').val(),
                            "filter_status": $('#filter-status').val(),
                        } );
                    }
                },
            language: languageOptions(),
            stripeClasses: [],
            dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
            
            columns: [
                { data: 'DT_Row_Index', name: 'DT_Row_Index' , orderable: false, searchable: false},
                { data: 'meeting_id', name: 'meeting_id' },
                { data: 'meeting_name', name: 'meeting_title' },
                { data: 'start_date_time', name: 'start_date' },
                { data: 'end_date_time', name: 'end_date' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', width: '20%', className: 'jc-td-right' }
            ]
        });

        $('#filter-company, #filter-status').change(function () {
            table.draw(false);
        })
        
    $('#repeat-meeting').on('change', function () {
        $('#repeat-fields').toggle(this.checked);
    });

    $('#send_reminder').on('change', function () {
        $('#reminder-fields').toggle(this.checked);
    });
   
    var getEventDetail = function (id) {
        var url = "{{ route('admin.zoom-meeting.show', ':id')}}";
        url = url.replace(':id', id);
        // Open meeting details in a dedicated page (not inside modal)
        window.location.href = url;
    }
   
  
    $(function() {

        $('body').on('click', '.sa-params', function(){
            var id = $(this).data('meeting-id');
            var occurrence = $(this).data('occurrence');
            var buttons = {
                cancel: "@lang('app.no')",
                confirm: {
                    text: "@lang('app.yes')",
                    value: 'confirm',
                    visible: true,
                    className: "danger",
                }
            };
            if(occurrence == '1')
            {
                buttons.recurring = {
                    text: "{{ trans('modules.zoommeeting.deleteAllOccurrences') }}",
                    value: 'recurring'
                }
            }
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

                    var url = "{{ route('admin.zoom-meeting.destroy',':id') }}";
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
        
     
        $('body').on('click', '.end-meeting', function(){
            var id = $(this).data('meeting-id');
            console.log(id);
            var buttons = {
                cancel: "@lang('app.no')",
                confirm: {
                    text: "@lang('app.yes')",
                    value: 'confirm',
                    visible: true,
                    className: "danger",
                }
            };  
            
            swal({
                title: "@lang('errors.areYouSure')",
                text: "@lang('errors.endWarning')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.yes')",
                cancelButtonText: "@lang('app.no')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {
                    var token = "{{ csrf_token() }}";

                    var url = "{{ route('admin.zoom-meeting.endMeeting')  }}";
                    var dataObject = {'_token': token, 'id': id};


                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: dataObject,
                        success: function (response) {
                            if (response.status == "success") {
                                loadTable();
                            }
                        }
                    });
                }
            });
        });
        
        $('body').on('click', '.cancel-meeting', function(){
            var id = $(this).data('meeting-id');
            swal({
                title: "@lang('errors.areYouSure')",
                text: "@lang('errors.cancelWarning')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.yes')",
                cancelButtonText: "@lang('app.no')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.zoom-meeting.cancelMeeting',':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, 'id': id},
                        success: function (response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
        

        $('body').on('click', '.btnedit', function() {
            $('.modal').modal('hide');
            
            var id = $(this).data('id');
            var url = "{{ route('admin.zoom-meeting.edit', ':id')}}";
            url = url.replace(':id', id);
            $('#modelHeading').html('');
            $.ajaxModal('#meetingDetailModal', url);   
        });

        $('.save-meeting').click(function () {
            $.easyAjax({
                url: "{{ route('admin.zoom-meeting.store') }}",
                container: '#modal-data-application',
                type: "POST",
                data: $('#createMeeting').serialize(),
                success: function (response) {
                    if(response.status == 'success'){
                        window.location.reload();
                    }
                }
            })
        })
        
    });
    $('#addCategory').click(function () {
        openCategoryModal();
        var url = '{{ route('admin.category.create')}}';
        $('#categoryModalBody').html('Loading...');
        $.easyAjax({
            type: 'GET',
            url: url,
            success: function (response) {
                if (response.status === 'success' && response.view) {
                    $('#categoryModalBody').html(response.view);
                } else if (typeof response === 'string') {
                    $('#categoryModalBody').html(response);
                }
            }
        });
    })

</script>


@endpush