


@foreach($boardColumns as $key => $column)
    <div id="task{{ snake_case($column->status) }}" class="board-column flex w-[280px] shrink-0 flex-col" data-column-id="{{ $column->id }}" data-position="{{ $column->position }}">
        <div class="flex min-h-[min(420px,55vh)] flex-1 flex-col overflow-hidden rounded-2xl bg-[#E8EBF0]">
            <div class="flex shrink-0 items-center justify-between px-3.5 pb-[11px] pt-[13px]">
                <div class="flex min-w-0 items-center gap-[7px]">
                    <span class="truncate text-[13px] font-extrabold tracking-[-0.01em] text-[#1A1E2E]">{{ ucwords($column->status) }}</span>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10.5px] font-bold text-white" style="background: {{ $column->color }};" id="columnCount_{{ $column->id }}">{{ $column->application_count }}</span>
                </div>
                <div class="relative shrink-0">
                    <button type="button" class="flex h-7 w-7 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-black/10 focus:outline-none dropdown-toggle" onclick="$(this).next('.dropdown-menu').toggleClass('hidden')" aria-label="@lang('app.action')">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                    </button>
                    <div class="dropdown-menu absolute right-0 z-20 mt-1 hidden w-44 rounded-xl border border-[#E8E6E1] bg-white py-1 shadow-lg">
                        <a class="block px-4 py-2 text-[12.5px] font-medium text-[#3D4A5C] hover:bg-[#EEF0F5]" href="javascript:editStatus({{ $column->id }});">
                            @lang('app.edit')
                        </a>
                        @if ($column->id > 5)
                            <a class="block px-4 py-2 text-[12.5px] font-medium text-red-600 hover:bg-red-50" href="javascript:deleteStatus({{ $column->id }});">
                                @lang('app.delete')
                            </a>
                        @endif
                    </div>
                </div>
            </div>
    
            <div class="12131313 panel-scroll lobipanel-parent-sortable flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto overflow-x-hidden px-2 py-1.5" data-column-id="{{ $column->id }}" id="drag-container-{{ $column->id }}">
          @foreach($column->applications ?? [] as $application)



<div class="radio-123 flex flex-col gap-2">



    @include('admin.job-applications.partials.board-card', [
        'application' => $application,
        'columnId' => $column->id,
        'columnColor' => $column->color,
        'statusSlug' => $column->status,
    ])

</div>

@endforeach
                <div class="lobipanel" data-sortable="true"></div>
                @if ($column->application_count > count($column->applications))
                    <div class="flex justify-center py-2" id="loadMoreBox{{ $column->id }}">
                        <a class="load-more-application text-[12px] font-semibold text-[#5A6478] hover:text-[#2563EB]" data-column-id="{{ $column->id }}"
                           data-total-tasks="{{ $column->application_count }}"
                           href="javascript:;">@lang('app.loadmore')</a>
                    </div>
                @endif
            </div>
            @if(in_array('add_job_applications', $userPermissions))
                <a href="{{ route('admin.job-applications.create') }}" class="mx-2 mb-2 flex shrink-0 items-center justify-center gap-1.5 rounded-lg border-[1.5px] border-dashed border-[#C4CBD4] bg-transparent py-[7px] pl-2.5 pr-2.5 text-[11.5px] font-semibold text-[#8892A0] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    @lang('app.createNew')
                </a>
            @endif
        </div>
    </div>
@endforeach
{{--  --}}
<script>
    $('.example-fontawesome').barrating({
        theme: 'fontawesome-stars',
        showSelectedRating: false,
        readonly: true,

    });

    $(function () {
        $('.bar-rating').each(function () {
            const val = $(this).data('value');

            $(this).barrating('set', val ? val : '');
        });
    });

    $('.example-fontawesome').barrating('set', '');

    // Send Reminder and notification to Candidate
    function sendReminder(id, type, status) {
        var msg;

        if (type == 'notify') {
            if (status == 'hired') {
                msg = "@lang('errors.sendHiredNotification')";
            } else {
                msg = "@lang('errors.sendRejectedNotification')";
            }
        } else {
            msg = "@lang('errors.sendInterviewReminder')";
        }
        swal({
            title: "@lang('errors.areYouSure')",
            text: msg,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.cancel')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                var url = "{{ route('admin.interview-schedule.notify',[':id',':type']) }}";
                url = url.replace(':id', id);
                url = url.replace(':type', type);

                // update values for all tasks
                $.easyAjax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                    }
                });
            }
        });
    }

    $(function () {
        // Getting Data of all colomn and applications
        boardStracture = JSON.parse('{!! $boardStracture !!}');

        var oldParentId, currentParentId, oldElementIds = [], i = 1;

        let draggingTaskId = 0;
        let draggedTaskId = 0;
        let missingElementId = 0;
        let currentApplicationId = 0;

        var isDragging = 0;
        var $sortableContainers = $('.lobipanel-parent-sortable');
        $sortableContainers.each(function () {
            var $el = $(this);
            if ($el.hasClass('ui-sortable')) {
                $el.sortable('destroy');
            }
        });
        $sortableContainers.sortable({
            items: '.task-card',
            connectWith: '.lobipanel-parent-sortable',
            tolerance: 'pointer',
            helper: 'clone',
            distance: 8,
            placeholder: 'ui-sortable-placeholder',
            start: function (event, ui) {
                isDragging = 1;
                ui.item.data('old-column-id', ui.item.closest('.board-column').data('column-id'));
                $('.board-column .panel-scroll').css('overflow-y', 'unset');
            },
            stop: function (event, ui) {
                var oldCol = parseInt(ui.item.data('old-column-id'), 10) || 0;
                var newCol = parseInt(ui.item.closest('.board-column').data('column-id'), 10) || 0;
                var movedApplicationId = parseInt(ui.item.data('application-id'), 10) || 0;
                currentApplicationId = movedApplicationId;
                oldParentId = oldCol;
                currentParentId = newCol;
                ui.item.attr('data-column-id', newCol);

                var $children = ui.item.parent().children('.show-detail');
                var boardColumnIds = [];
                var applicationIds = [];
                var prioritys = [];
                $children.each(function (ind, el) {
                    boardColumnIds.push($(el).closest('.board-column').data('column-id'));
                    applicationIds.push($(el).data('application-id'));
                    prioritys.push($(el).index());
                });

                if (oldParentId == 3 && currentParentId == 4) {
                    $('#buttonBox' + oldParentId + currentApplicationId).removeClass('hidden');
                    var button = '<button onclick="sendReminder(' + currentApplicationId + ', \'notify\')" type="button" class="px-2 py-1 text-xs bg-cyan-600 text-white rounded hover:bg-cyan-700 notify-button">@lang('app.notify')</button>';
                    $('#buttonBox' + oldParentId + currentApplicationId).html(button);
                    $('#buttonBox' + oldParentId + currentApplicationId).attr('id', 'buttonBox' + currentParentId + currentApplicationId);
                } else if (oldParentId == 4 && currentParentId == 3) {
                    var timeStamp = $('#buttonBox' + oldParentId + currentApplicationId).data('timestamp');
                    var currentDate = {{$currentDate}};
                    if (currentDate < timeStamp) {
                        $('#buttonBox' + oldParentId + currentApplicationId).removeClass('hidden');
                        var button = '<button onclick="sendReminder(' + currentApplicationId + ', \'reminder\')" type="button" class="px-2 py-1 text-xs bg-cyan-600 text-white rounded hover:bg-cyan-700 notify-button">@lang('app.reminder')</button>';
                        $('#buttonBox' + oldParentId + currentApplicationId).html(button);
                    }
                    $('#buttonBox' + oldParentId + currentApplicationId).attr('id', 'buttonBox' + currentParentId + currentApplicationId);
                } else {
                    $('#buttonBox' + oldParentId + currentApplicationId).addClass('hidden');
                    $('#buttonBox' + oldParentId + currentApplicationId).attr('id', 'buttonBox' + currentParentId + currentApplicationId);
                }

                var startDate = $('#date-start').val();
                var jobs = $('#jobs').val();
                var search = $('#search').val();
                if (startDate == '') startDate = null;
                var endDate = $('#date-end').val();
                if (endDate == '') endDate = null;

                $.easyAjax({
                    url: '{{ route("admin.job-applications.updateIndex") }}',
                    type: 'POST',
                    container: '.container-row',
                    data: {
                        boardColumnIds: boardColumnIds,
                        applicationIds: applicationIds,
                        prioritys: prioritys,
                        startDate: startDate,
                        jobs: jobs,
                        search: search,
                        endDate: endDate,
                        draggingTaskId: movedApplicationId,
                        draggedTaskId: movedApplicationId,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (movedApplicationId !== 0) {
                            $.each(response.columnCountByIds, function (key, value) {
                                $('#columnCount_' + value.id).html((value.status_count));
                            });
                        }
                    }
                });

                $('.board-column .panel-scroll').css('overflow-y', 'auto');
                setTimeout(function () {
                    isDragging = 0;
                }, 50);
            }
        }).disableSelection();

        $('.show-detail').click(function (event) {
            if ($(event.target).hasClass('notify-button')) {
                return false;
            }
            var id = $(this).data('application-id');
            draggingTaskId = currentApplicationId = id;

            if (isDragging == 0) {
                var $sidebar = $("#right-sidebar");
                var $backdrop = $("#right-sidebar-backdrop");
                $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
                $backdrop.removeClass('hidden');

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
            }
        })
    })

</script>
