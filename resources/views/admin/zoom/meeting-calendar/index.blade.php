@extends('layouts.app')

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('modules.zoommeeting.meetingName')</span>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('assets/plugins/calendar/dist/fullcalendar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">

<style>
    .zoom-create-modal .modal-dialog {
        width: min(1120px, calc(100vw - 32px));
        max-width: 1120px;
        margin: 24px auto;
    }

    .zoom-create-modal .modal-content,
    .zoom-ajax-modal .modal-content {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .zoom-create-modal .modal-header,
    .zoom-ajax-modal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 22px;
        background: #f8fafc;
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

    .zoom-create-modal .modal-body,
    .zoom-ajax-modal .modal-body {
        padding: 22px;
        background: #ffffff;
    }

    .zoom-create-modal .modal-footer,
    .zoom-ajax-modal .modal-footer {
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
        padding: 14px 22px;
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

    .zoom-create-modal .inline-flex.items-center.gap-2 {
        margin-top: 6px;
    }
    </style>
@endpush

@section('page-subtitle')
    @lang('modules.zoommeeting.calendarView')
@endsection

@section('create-button')
    <div class="flex items-center gap-2">
        @if(in_array("add_schedule", $userPermissions))
            <a href="{{ route('admin.zoom-meeting.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="ti-plus"></i>
                @lang('modules.zoommeeting.addMeeting')
            </a>
        @endif
        <a href="{{ route('admin.zoom-meeting.table-view') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-[#16A34A] bg-white px-5 py-2.5 text-[13px] font-semibold text-[#15803d] shadow-sm transition hover:bg-[#f0fdf4] focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            <i class="ti-list"></i>
            @lang('modules.zoommeeting.tableView')
        </a>
    </div>
@endsection

@section('content')
    <div class="mx-auto pb-8">
        <div class="jc-table-card">
            <div class="p-4 md:p-6">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <!-- .row -->

    <!-- BEGIN MODAL -->
    <div class="modal fade bs-modal-md zoom-create-modal" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" tabindex="-1" style="display: none;"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" id="modal-data-application">
       <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"><i class="icon-plus"></i> @lang('modules.zoommeeting.addMeeting')</h4>
            <button type="button" class="text-slate-400 transition hover:text-slate-700" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
            </div>
           <div class="modal-body">
            <form id="createMeeting" class="ajax-form" method="POST">
                @csrf
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">@lang('modules.zoommeeting.meetingName')</label>
                                <input type="text" name="meeting_title" id="meeting_title" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" style ="margin: -5px;">
                                <label class="control-label">@lang('app.category')
                                    <a href="javascript:;" id="addCategory" class="btn btn-xs btn-success btn-outline fs-12"><i class="fa fa-plus"></i></a>
                                     </label>
                                     <select class="select2 form-control" id="category_id" name="category_id" style="width: 100%;!important">
                                        <option value="">@lang('modules.message.pleaseSelectCategory')</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                                            @endforeach
                                        </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-md-12 ">
                            <div class="form-group">
                                <label>@lang('modules.zoommeeting.description')</label>
                                <textarea type="text" name="description" id="description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group chooseCandidate ">
                                <label class="required">@lang('modules.zoommeeting.startOn')</label>
                                <input type="text" name="start_date" id="start_date" class="form-control new_date" >
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group  bootstrap-timepicker timepicker">
                                <label>&nbsp;</label>
                                <input type="text" name="start_time" id="start_time" class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group chooseCandidate bootstrap-timepicker timepicker">
                                <label class="required">@lang('modules.zoommeeting.endOn')</label>
                                <input type="text" name="end_date" id="end_date" class="form-control" autocomplete="none">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group  bootstrap-timepicker timepicker">
                                <label>&nbsp;</label>
                                <input type="text" name="end_time" id="end_time" class="form-control">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-md-12" id="member-attendees">
                            <label class="col-xs-3 m-t-10">@lang('modules.meetings.addEmployees')</label>
                            <div class="form-group col-xs-12 col-md-12 p-0">
                                <select class="select2 m-b-10 select2-multiple " style="width:100%" multiple="multiple"
                                        data-placeholder="@lang('modules.message.chooseMember')" name="employee_id[]">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                                (YOU) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <div class="m-b-10">
                                    <label class="control-label">@lang('modules.zoommeeting.hostVideoStatus')</label>
                                </div>
                                <div class="radio radio-inline">
                                    <input type="radio" name="host_video" id="host_video1" value="1">
                                    <label for="host_video1" class=""> @lang('app.enable') </label>
                                </div>
                                <div class="radio radio-inline ">
                                    <input type="radio" name="host_video" id="host_video2" value="0" checked>
                                    <label for="host_video2" class=""> @lang('app.disable') </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="m-b-10">
                                    <label class="control-label">@lang('modules.zoommeeting.participantVideoStatus')</label>
                                </div>
                                <div class="radio radio-inline">
                                    <input type="radio" name="participant_video" id="participant_video1" value="1">
                                    <label for="participant_video1" class=""> @lang('app.enable') </label>
                                </div>
                                <div class="radio radio-inline ">
                                    <input type="radio" name="participant_video" id="participant_video2" value="0" checked>
                                    <label for="participant_video2" class=""> @lang('app.disable') </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                  <label class="control-label">@lang('modules.zoommeeting.meetingHost')</label>
                                  <select class="select2 form-control created_by" style="width:100%"id="created_by" name="created_by">
                                      @foreach($employees as $emp)
                                          <option @if($emp->id == $user->id)
                                              selected
                                              @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                                      @endforeach
                                  </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-xs-6">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" value="1" name="repeat" id ="repeat-meeting-new">
                                    @lang('modules.zoommeeting.repeat')
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="repeat-fields" style="display: none">
                        <div class="col-xs-6 col-md-3 ">
                            <div class="form-group">
                                <label>@lang('modules.zoommeeting.repeatEvery')</label>
                                <input type="number" min="1" value="1" name="repeat_every" class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <select name="repeat_type" id="" style=" height: 32px !important;"class="form-control ">
                                    <option value="day">@lang('modules.zoommeeting.day')</option>
                                    <option value="week">@lang('modules.zoommeeting.week')</option>
                                    <option value="month">@lang('modules.zoommeeting.month')</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-6 col-md-3">
                            <div class="form-group">
                                <label>@lang('modules.zoommeeting.cycles') <a class="mytooltip" href="javascript:void(0)"> </a></label>
                                <input type="text" name="repeat_cycles" id="repeat_cycles" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-xs-6">
                                <label class="inline-flex items-center gap-2">
                                    <input type="hidden" name="send_reminder" value="0">
                                    <input type="checkbox" name="send_reminder" id ="send_reminder_new" value="1">
                                    @lang('modules.zoommeeting.reminder')
                                </label>
                                {{-- <div class="checkbox checkbox-info">
                                    <input id="send_reminder" name="send_reminder" value="1"
                                            type="checkbox">
                                    <label for="send_reminder">@lang('modules.zoommeeting.reminder')</label>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="row" id="reminder-fields" style="display: none;">
                        <div class="col-xs-6 col-md-3">
                            <div class="form-group">
                                <label>@lang('modules.zoommeeting.remindBefore')</label>
                                <input type="number" min="1" value="1" name="remind_time" class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <select name="remind_type" id="" style=" height: 32px !important;" class="form-control">
                                    <option value="day">@lang('modules.zoommeeting.day')</option>
                                    <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                    <option value="minute">@lang('modules.zoommeeting.minute')</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                </form>

        </div>
           <div class="modal-footer">
               <button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-dismiss="modal">@lang('app.close')</button>
               <button type="button" class="save-meeting inline-flex items-center justify-center rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">@lang('app.submit')</button>
           </div>
       </div>
    </div>
    </div>
    {{-- End  --}}

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md zoom-ajax-modal" id="meetingDetailModal" role="dialog" aria-labelledby="myModalLabel" tabindex="-1" style="display: none;"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
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
    {{--Category Modal--}}
    <div class="modal fade bs-modal-md zoom-ajax-modal" id="categoryModal" role="dialog" aria-labelledby="myModalLabel" tabindex="-1" style="display: none;"
         aria-hidden="true">
        <div class="modal-dialog modal-md" id="modal-data-application">
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
    {{--Category Ajax Modal Ends--}}

@endsection

@push('footer-script')
<script>
    var taskEvents = [
        @foreach($events as $event)
        {
            id: '{{ ucfirst($event->id) }}',
            title: '{{ ucfirst($event->meeting_name) }}',
            start: '{{ $event->start_date_time }}',
            end:  '{{ $event->end_date_time }}',
            className: '{{ $event->label_color }}'
        },
        @endforeach
    ];

    var getEventDetail = function (id) {
        var url = "{{ route('admin.zoom-meeting.show', ':id')}}";
        url = url.replace(':id', id);
        // Open meeting details in a dedicated page (not inside modal)
        window.location.href = url;
    }

    var calendarLocale = '{{ $global->locale }}';
</script>
<script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"
type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}"
type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>

<script src="{{ asset('assets/plugins/calendar/dist/fullcalendar.min.js') }}"></script>
<script src="{{ asset('assets/plugins/calendar/dist/jquery.fullcalendar.js') }}"></script>
<script src="{{ asset('assets/plugins/calendar/dist/locale-all.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/meeting-calendar.js') }}"></script>


<script>
    $(function () {
        $('#my-meeting, #meetingDetailModal, #categoryModal')
            .removeClass('in show')
            .hide()
            .attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    $('body').on('click', '#open-add-meeting', function (e) {
        e.preventDefault();
        if ($.fn.modal) {
            $('#my-meeting').modal('show');
            return;
        }

        $('#my-meeting').show().addClass('in show').attr('aria-hidden', 'false');
        if (!$('.modal-backdrop').length) {
            $('body').append('<div class="modal-backdrop fade in"></div>');
        }
        $('body').addClass('modal-open');
    });

    $('body').on('click', '#my-meeting [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        if ($.fn.modal) {
            $('#my-meeting').modal('hide');
            return;
        }

        $('#my-meeting').hide().removeClass('in show').attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
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

    function addEventModal(start, end, allDay){
        if(start){
            var sd = new Date(start);
            var curr_date = sd.getDate();
            if (curr_date < 10) {
                curr_date = '0' + curr_date;
            }
            var curr_month = sd.getMonth() + 1;
            if (curr_month < 10) {
                curr_month = '0' + curr_month;
            }
            var curr_year = sd.getFullYear();
            var scheduleDate = curr_year + '-' + curr_month + '-' + curr_date;
            window.location.href = "{{ route('admin.zoom-meeting.create') }}" + '?start_date=' + scheduleDate + '&end_date=' + scheduleDate;
            return;
        }

        window.location.href = "{{ route('admin.zoom-meeting.create') }}";
    }

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
    $('#repeat-meeting-new').on('change', function () {
        $('#repeat-fields').toggle(this.checked);
    });

    $('#send_reminder_new').on('change', function () {
        $('#reminder-fields').toggle(this.checked);
    });

    $('#addCategory').click(function () {
        var url = '{{ route('admin.category.create')}}';
        $('#modelHeading').html('...');
        $.ajaxModal('#categoryModal', url);
    })

</script>

@endpush
