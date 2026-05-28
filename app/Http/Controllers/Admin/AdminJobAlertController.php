<?php

namespace App\Http\Controllers\Admin;

use App\JobAlert;
use App\JobLocation;
use App\Support\RaDataTableHtml;
use Froiden\Envato\Helpers\Reply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class AdminJobAlertController extends AdminBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.jobAlert');
        $this->pageIcon = 'icon-badge';
    }

    public function index()
    {
        $this->totalJobAlert = JobAlert::count();
        $this->activeJobsAlert = JobAlert::where('status', 'active')->count();
        $this->inactiveJobsAlert = JobAlert::where('status', 'inactive')->count();
        $this->locations = JobLocation::all();

        return view('admin.job_alert.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        abort_if(! $this->user->cans('delete_jobs'), 403);

        JobAlert::destroy($id);

        return Reply::success(__('messages.recordDeleted'));
    }

    public function data(Request $request)
    {
        abort_if(! $this->user->cans('view_jobs'), 403);

        $categories = JobAlert::with(['alertLocation', 'workExperience', 'alertCategory', 'jobType']);

        if (\request('filter_status') != '') {
            $categories->where('status', \request('filter_status'));
        }

        if ($request->filter_location != '') {
            $categories = $categories->join('job_alert_locations', 'job_alert_locations.job_alert_id', 'job_alerts.id')
                ->where('job_alert_locations.location_id', $request->filter_location);
        }

        $categories->get();

        return DataTables::of($categories)
            ->addColumn('action', function ($row) {
                if (! $this->user->cans('delete_jobs')) {
                    return '';
                }

                return RaDataTableHtml::wrap(RaDataTableHtml::deleteSaParams($row->id));
            })
            ->editColumn('email', function ($row) {
                return ucfirst($row->email);
            })
            ->editColumn('location_id', function ($row) {
                $locations = '<ul>';
                foreach ($row->alertLocation as $value) {

                    $locations .= '<li>'.$value->location.'</li>';
                }
                $locations .= '</ul>';

                return ucfirst($locations);
            })
            ->editColumn('work_experience_id', function ($row) {
                return $row->workExperience->work_experience;
            })

            ->editColumn('job_type_id', function ($row) {
                return $row->jobType->job_type;
            })

            ->editColumn('status', function ($row) {
                if ($row->status == 'active') {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">'.__('app.active').'</span>';
                }
                if ($row->status == 'inactive') {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">'.__('app.inactive').'</span>';
                }
                if ($row->status == 'expired') {
                    return '<label class="badge" style="background: #FF8C00;">'.__('app.expired').'</label>';
                }
            })
            ->rawColumns(['status', 'action', 'location_id'])
            ->addIndexColumn()
            ->make(true);
    }
}
