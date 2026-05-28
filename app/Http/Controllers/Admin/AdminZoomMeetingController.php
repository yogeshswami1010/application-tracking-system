<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Events\MeetingInviteEvent;
use App\Helper\Reply;
use App\Http\Requests\ZoomMeeting\StoreMeeting;
use App\Http\Requests\ZoomMeeting\UpdateMeeting;
use App\Http\Requests\ZoomMeeting\UpdateOccurrence;
use App\Services\Zoom\ZoomApiClient;
use App\Traits\ZoomSettings;
use App\User;
use App\ZoomMeeting;
use App\ZoomSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class AdminZoomMeetingController extends AdminBaseController
{
    use ZoomSettings;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.zoom');
        $this->pageIcon = 'icon-film';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->employees = User::get();
        $this->events = ZoomMeeting::all();
        $this->categories = Category::all();

        return view('admin.zoom.meeting-calendar.index', $this->data);
    }

    public function data()
    {
        $model = ZoomMeeting::select('id', 'meeting_id', 'created_by', 'meeting_name', 'start_date_time', 'end_date_time', 'start_link', 'join_link', 'status', 'label_color', 'occurrence_id', 'source_meeting_id',
            'occurrence_order')->get();
        $this->zoomSetting = ZoomSetting::first();

        return DataTables::of($model)
            ->addColumn('action', function ($row) {
                if ($this->zoomSetting->meeting_app == 'in_app') {
                    $url = route('admin.zoom-meeting.startMeeting', $row->id);
                } else {
                    $url = $this->user->id == $row->created_by ? $row->start_link : $row->end_link;
                }
                $menuItem = 'block px-3 py-2 text-[13px] text-[#3D4A5C] no-underline hover:bg-[#F8F7F4]';
                $action = '<div class="relative inline-block text-left"><button aria-expanded="false" data-toggle="dropdown" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#E2DED8] bg-white text-[#5A6478] shadow-sm transition hover:border-[#2563EB] hover:text-[#2563EB]" type="button"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button><ul role="menu" class="hidden absolute right-0 z-[60] mt-1 min-w-[12rem] rounded-xl border border-[#E8E6E1] bg-white py-1 shadow-lg">';
                if ($this->user->cans('view_schedule')) {
                    $action .= '<li><a href="javascript:;" class="'.$menuItem.'" onclick="getEventDetail('.$row->id.')">'.e(__('app.view')).'</a></li>';
                }

                if ($row->status == 'waiting') {
                    $nowDate = Carbon::now()->toDateString();
                    $meetingDate = $row->start_date_time->toDateString();

                    if (
                        (is_null($row->occurrence_id) || $nowDate == $meetingDate)
                        && $row->created_by == $this->user->id
                    ) {
                        $action .= '<li><a target="_blank" rel="noopener noreferrer" class="'.$menuItem.'" href="'.e($url).'">'.e(__('modules.zoommeeting.startUrl')).'</a></li>';
                    }

                    $action .= '<li><a href="javascript:;" class="'.$menuItem.' cancel-meeting" data-meeting-id="'.$row->id.'">'.e(__('modules.zoommeeting.cancelMeeting')).'</a></li>';
                    if ($this->user->cans('edit_schedule')) {
                        $action .= '<li><a href="'.route('admin.zoom-meeting.edit', $row->id).'" class="'.$menuItem.'">'.e(__('app.edit')).'</a></li>';
                    }
                }

                if ($row->status == 'live') {
                    if ($this->user->cans('delete_schedule')) {
                        $action .= '<li><a href="javascript:;" class="'.$menuItem.' end-meeting" data-meeting-id="'.$row->id.'">'.e(__('modules.zoommeeting.endMeeting')).'</a></li>';
                    }
                }

                if ($row->status == 'waiting') {
                    if ($this->user->cans('delete_schedule')) {
                        $action .= '<li><a href="javascript:;" class="'.$menuItem.' sa-params" data-occurrence="'.$row->occurrence_order.'" data-meeting-id="'.$row->id.'">'.e(__('app.delete')).'</a></li>';
                    }
                }

                $action .= '</ul></div>';

                return $action;
            })
            ->editColumn('meeting_id', function ($row) {
                $meetingId = $row->meeting_id;

                if (! is_null($row->occurrence_id)) {
                    $meetingId .= '<br><span class="text-muted">'.__('modules.zoommeeting.occurrence').' - '.$row->occurrence_order.'</span>';
                }

                return $meetingId;
            })
            ->editColumn('meeting_name', function ($row) {
                if ($this->user->cans('view_schedule')) {
                    return '<a href="javascript:;" onclick="getEventDetail('.$row->id.')">'.ucfirst($row->meeting_name).'</a>';
                } else {
                    return '<label>'.ucfirst($row->meeting_name).'</label>';
                }

            })
            ->editColumn('start_date_time', function ($row) {
                return $row->start_date_time;
            })
            ->editColumn('end_date_time', function ($row) {
                return $row->end_date_time;
            })
            ->editColumn('status', function ($row) {

                if ($row->status == 'waiting') {
                    $status = '<label class="label badge label-warning">'.__('modules.zoommeeting.waiting').'</label>';
                } elseif ($row->status == 'live') {
                    $status = '<i class="fa fa-circle Blink" style="color: red"></i> <span class="font-semi-bold">'.__('modules.zoommeeting.live').'</span>';
                } elseif ($row->status == 'canceled') {
                    $status = '<label class="label badge label-danger">'.__('app.canceled').'</label>';
                } elseif ($row->status == 'finished') {
                    $status = '<label class="label badge label-success">'.__('app.finished').'</label>';
                }

                return $status;
            })
            ->rawColumns(['action', 'status', 'meeting_name', 'meeting_id'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->employees = User::get();
        $this->categories = Category::all();

        return view('admin.zoom.meeting-calendar.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreMeeting $request)
    {
        $this->createOrUpdateMeetings($request);

        if (! $request->ajax()) {
            return redirect()->route('admin.zoom-meeting.index')
                ->with('success', __('messages.createdSuccessfully'));
        }

        return Reply::success(__('messages.createdSuccessfully'));
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $event = ZoomMeeting::with('attendees', 'host')->findOrFail($id);
        $this->zoomSetting = ZoomSetting::first();

        // Merge `$this->data` so layout (sidebar) receives `$userPermissions`.
        return view('admin.zoom.meeting-calendar.show', array_merge($this->data, [
            'event' => $event,
            'zoomSetting' => $this->zoomSetting,
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->event = ZoomMeeting::with('attendees')->findOrFail($id);
        $this->employees = User::get();
        $this->categories = Category::all();
        $this->isOccurrence = ! is_null($this->event->occurrence_id);
        $this->submitRoute = $this->isOccurrence
            ? route('admin.zoom-meeting.updateOccurrence', $this->event->id)
            : route('admin.zoom-meeting.update', $this->event->id);

        // Use a single edit view for both meeting and occurrence edits.
        return view('admin.zoom.meeting-calendar.edit-page', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateMeeting $request, $id)
    {
        $this->createOrUpdateMeetings($request, $id);

        if (! $request->ajax()) {
            return redirect()->route('admin.zoom-meeting.index')
                ->with('success', __('messages.updatedSuccessfully'));
        }

        return Reply::success(__('messages.updatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->setZoomConfigs();
        $meeting = ZoomMeeting::findOrFail($id);
        $zoom = app(ZoomApiClient::class);

        // destroy meeting via zoom api
        if (! is_null($meeting->occurrence_id)) {
            if (request()->has('recurring') && request('recurring') == 'yes') {
                // delete all occurrences
                $zoom->deleteMeeting($meeting->meeting_id);
            } else {
                // delete single occurrence
                $zoom->deleteMeeting($meeting->meeting_id, $meeting->occurrence_id);
            }
        } else {
            $zoom->deleteMeeting($meeting->meeting_id);
        }

        $meeting->attendees()->detach();
        $meeting->delete();

        return Reply::success(__('messages.recordDeleted'));
    }

    public function createMeeting(ZoomApiClient $zoom, ZoomMeeting $meeting, $id, $meetingId = null, $host = null)
    {
        $this->setZoomConfigs();
        $commonSettings = [
            'type' => 2,
            'topic' => $meeting->meeting_name,
            'start_time' => Carbon::parse($meeting->start_date_time)->toIso8601String(),
            // Zoom requires a valid positive duration (minutes).
            'duration' => max(1, (int)$meeting->end_date_time->diffInMinutes($meeting->start_date_time)),
            'timezone' => $this->global->timezone,
            'agenda' => $meeting->description,
            'settings' => [
                'host_video' => $meeting->host_video == 1,
                'participant_video' => $meeting->participant_video == 1,
            ],
        ];

        if ($host) {
            $commonSettings['settings']['alternative_hosts'] = $host->email;
        }

        if (is_null($id)) {
            try {
                $savedMeeting = $zoom->createMeeting($commonSettings);
            } catch (RuntimeException $e) {
                // Some accounts reject alternative_hosts for non-licensed users.
                if (isset($commonSettings['settings']['alternative_hosts'])) {
                    unset($commonSettings['settings']['alternative_hosts']);
                    $savedMeeting = $zoom->createMeeting($commonSettings);
                } else {
                    throw $e;
                }
            }
            $meeting->meeting_id = (string) ($savedMeeting['id'] ?? '');
            $meeting->start_link = (string) ($savedMeeting['start_url'] ?? '');
            $meeting->join_link = (string) ($savedMeeting['join_url'] ?? '');
            $meeting->password = (string) ($savedMeeting['password'] ?? '');

            $meeting->save();
        } else {
            try {
                $zoom->updateMeeting($meeting->meeting_id, $commonSettings);
            } catch (RuntimeException $e) {
                if (isset($commonSettings['settings']['alternative_hosts'])) {
                    unset($commonSettings['settings']['alternative_hosts']);
                    $zoom->updateMeeting($meeting->meeting_id, $commonSettings);
                } else {
                    throw $e;
                }
            }
        }


        return $meeting;
    }

    public function createOrUpdateMeetings($request, $id = null)
    {
        $this->setZoomConfigs();
        $hostId = $request->created_by ?: $request->create_by;
        $host = $hostId ? User::find($hostId) : null;
        $zoom = app(ZoomApiClient::class);
        if ($request->has('repeat')) {
            $this->createRepeatMeeting($zoom, $request, $id, $host);
        } else {

            $meeting = is_null($id) ? new ZoomMeeting : ZoomMeeting::find($id);

            $data = $request->all();
            $data['meeting_name'] = $request->meeting_title;
            $data['start_date_time'] = $request->start_date.' '.$request->start_time;
            $data['end_date_time'] = $request->end_date.' '.$request->end_time;
            if (is_null($id)) {
                $meeting = $meeting->create($data);
                $this->createMeeting($zoom, $meeting, null, null, $host);
                $this->syncAttendees($request, $meeting, 'yes');

            } else {
                $meeting->update($data);
                $this->syncAttendees($request, $meeting);
                $this->createMeeting($zoom, $meeting, $id, null, $host);
            }
        }
    }

    public function syncAttendees($request, $meeting, $sendInvitation = null)
    {
        $this->setZoomConfigs();
        $attendees = [];
        if ($request->all_employees) {
            $attendees = User::allEmployees();
        } else {
            if ($request->employee_id) {
                $attendees = User::whereIn('id', $request->employee_id)->get();

            }
        }
        if ($request->all_clients) {
            $attendees = User::allClients()->merge($attendees);
        } elseif ($request->has('client_id')) {
            $attendees = User::whereIn('id', $request->client_id)->get()->merge($attendees);
        }
        if ($attendees) {
            $meeting->attendees()->sync($attendees);
        }

        if ($sendInvitation === 'yes') {

            event(new MeetingInviteEvent($meeting, $attendees));
        }
    }

    public function tableView()
    {
        $this->users = User::get();
        $this->categories = Category::all();

        return view('admin.zoom.meeting-calendar.table', $this->data);

    }

    /**
     * start zoom meeting in app
     *
     * @return Response
     */
    public function startMeeting($id)
    {
        $this->setZoomConfigs();
        $this->zoomSetting = ZoomSetting::first();
        $this->meeting = ZoomMeeting::findOrFail($id);

        return view('admin.zoom.meeting-calendar.start_meeting', $this->data);
    }

    /**
     * cancel meeting
     *
     * @return Response
     */
    public function cancelMeeting()
    {
        $this->setZoomConfigs();
        $id = request('id');
        ZoomMeeting::where('id', $id)->update([
            'status' => 'canceled',
        ]);

        return Reply::success(__('messages.updatedSuccessfully'));
    }

    /**
     * end meeting
     *
     * @return Response
     */
    public function endMeeting()
    {
        $this->setZoomConfigs();
        $id = request('id');
        $meeting = ZoomMeeting::findOrFail($id);
        $zoom = app(ZoomApiClient::class);
        $zoom->endMeeting($meeting->meeting_id);

        $meeting->status = 'finished';
        $meeting->save();

        return Reply::success(__('messages.updatedSuccessfully'));
    }

    /**
     * create repeated meeting
     *
     * @return Response
     */
    public function createRepeatMeeting(ZoomApiClient $zoom, $request, $id, $host = null)
    {
        $this->setZoomConfigs();
        // create first record in db
        $meeting = new ZoomMeeting;
        $data = $request->all();
        $data['meeting_name'] = $request->meeting_title;
        $data['repeat'] = ($request->repeat != null) ? 0 : 1;
        $data['send_reminder'] = ($request->send_reminder != null) ? 0 : 1;
        $data['start_date_time'] = $request->start_date.' '.$request->start_time;
        $data['end_date_time'] = $request->end_date.' '.$request->end_time;
        $meeting = $meeting->create($data);
        $meeting->source_meeting_id = $meeting->id;
        $meeting->occurrence_order = 1;
        $meeting->save();
        $meetingId = $meeting->id;

        $this->syncAttendees($request, $meeting, 'yes');

        // create other records with reference to first
        $repeatCount = $request->repeat_every;
        $repeatType = $request->repeat_type;
        $repeatCycles = $request->repeat_cycles;
        $startDate = $request->start_date;
        $dueDate = $request->end_date;
        $startDate = Carbon::parse($request->start_date);
        $dueDate = Carbon::parse($request->end_date);

        for ($i = 1; $i < $repeatCycles; $i++) {
            $startDate = $startDate->add($repeatCount, str_plural($repeatType));
            $dueDate = $dueDate->add($repeatCount, str_plural($repeatType));

            $otherMeeting = new ZoomMeeting;

            $data['start_date_time'] = $startDate->format($this->global->date_format).''.$request->start_time;
            $data['end_date_time'] = $dueDate->format($this->global->date_format).''.$request->end_time;
            $data['source_meeting_id'] = $meetingId;
            $data['occurrence_order'] = $i + 1;
            $otherMeeting = $otherMeeting->create($data);
            $this->syncAttendees($request, $otherMeeting);
        }

        // create meeting on zoom
        $startDate = Carbon::createFromFormat('Y-m-d H:i', $request->start_date.' '.$request->start_time);

        $repeatInterval = $request->repeat_every;
        $repeatCycles = $request->repeat_cycles;

        if ($request->repeat_type == 'day') {
            $repeatType = 1;
        } elseif ($request->repeat_type == 'week') {
            $repeatType = 2;
        } else {
            $repeatType = 3;
        }

        $repeatData = [
            'type' => $repeatType,
            'repeat_interval' => intval($repeatInterval),
            'end_times' => intval($repeatCycles),
        ];

        if ($repeatType == 2) {
            $repeatData['weekly_days'] = $startDate->dayOfWeekIso;
        }

        $payload = [
            'topic' => $request->meeting_title,
            'type' => 8,
            'start_time' => $startDate->toIso8601String(),
            'timezone' => $this->global->timezone,
            'agenda' => $request->description,
            'recurrence' => $repeatData,
            'settings' => [
                'host_video' => $request->host_video == 1,
                'participant_video' => $request->participant_video == 1,
            ],
        ];

        if ($host) {
            $payload['settings']['alternative_hosts'] = $host->email;
        }

        try {
            $savedMeeting = $zoom->createMeeting($payload);
        } catch (RuntimeException $e) {
            if (isset($payload['settings']['alternative_hosts'])) {
                unset($payload['settings']['alternative_hosts']);
                $savedMeeting = $zoom->createMeeting($payload);
            } else {
                throw $e;
            }
        }

        // save zoom response data
        $meeting->meeting_id = (string) ($savedMeeting['id'] ?? '');
        $meeting->start_link = (string) ($savedMeeting['start_url'] ?? '');
        $meeting->join_link = (string) ($savedMeeting['join_url'] ?? '');
        $meeting->password = (string) ($savedMeeting['password'] ?? '');
        $meeting->save();

        ZoomMeeting::where('source_meeting_id', $meeting->id)->update([
            'meeting_id' => (string) ($savedMeeting['id'] ?? ''),
            'start_link' => (string) ($savedMeeting['start_url'] ?? ''),
            'join_link' => (string) ($savedMeeting['join_url'] ?? ''),
            'password' => (string) ($savedMeeting['password'] ?? ''),
        ]);
    }

    /**
     * update meeting occurrence
     *
     * @return Response
     */
    public function updateOccurrence(UpdateOccurrence $request, $id)
    {
        $this->setZoomConfigs();
        $zoomMeeting = ZoomMeeting::find($id);
        $data = $request->all();
        $data['start_date_time'] = $request->start_date.'T'.$request->start_time;
        $data['end_date_time'] = $request->end_date.'T'.$request->end_time;
        $zoomMeeting->update($data);
        $zoom = app(ZoomApiClient::class);
        $zoom->updateMeeting($zoomMeeting->meeting_id, [
            'start_time' => Carbon::parse($zoomMeeting->start_date_time)->toIso8601String(),
        ], $zoomMeeting->occurrence_id);

        if (! $request->ajax()) {
            return redirect()->route('admin.zoom-meeting.index')
                ->with('success', __('messages.updatedSuccessfully'));
        }

        return Reply::success(__('messages.updatedSuccessfully'));
    }
}
