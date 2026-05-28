<?php

namespace App\Http\Controllers\Admin;

use App\JobType;
use App\Department;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreJobType;
use App\Http\Requests\Admin\Department\StoreRequest;


class AdminJobTypeController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.department');
        $this->pageIcon = 'icon-user';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function create()
    {
        abort_if(!$this->user->cans('add_job_applications'), 403);

        $this->jobTypes = JobType::all();
        return view('admin.jobType.create', $this->data);
    }


    public function edit($id)
    {
       //
    }

    public function store(StoreJobType $request)
    {
        $jobType = new JobType;
        $jobType->job_type = $request->job_type;
        $jobType->save();

        $jobTypes = JobType::all();
        return Reply::successWithData(__('messages.jobTypeAdded'),['data' => $jobTypes]);
    }

    public function update(StoreJobType $request, $id)
    {
        $jobType = JobType::findOrFail($id);
        $jobType->job_type = $request->job_type;
        $jobType->save();

        $jobTypes = JobType::all();
        return Reply::successWithData(__('messages.jobTypeUpdated'),['data' => $jobTypes]);
    }

    public function destroy($id)
    {
        JobType::destroy($id);

        $jobTypes = JobType::all();
        return Reply::successWithData(__('messages.jobTypeDeleted'),['data' => $jobTypes]);
    }

    public function show($id)
    {
        //
    }
}
