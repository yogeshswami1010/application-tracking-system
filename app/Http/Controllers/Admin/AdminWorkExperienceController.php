<?php

namespace App\Http\Controllers\Admin;

use App\JobType;
use App\Department;
use App\Helper\Reply;
use App\WorkExperience;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Department\StoreRequest;
use App\Http\Requests\Admin\StoreWorkExperience;

class AdminWorkExperienceController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.jobs.workExperience');
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

        $this->workExperiences = WorkExperience::all();

        return view('admin.work-experience.create', $this->data);
    }


    public function edit($id)
    {
       //
    }

    public function store(StoreWorkExperience $request)
    {
        $workExperience = new WorkExperience;
        $workExperience->work_experience = $request->work_experience;
        $workExperience->save();

        $workExperiences = WorkExperience::all();
        return Reply::successWithData(__('messages.workExperienceAdded'),['data' => $workExperiences]);
    }

    public function update(StoreWorkExperience $request, $id)
    {
        $workExperience = WorkExperience::findOrFail($id);
        $workExperience->work_experience = $request->work_experience;
        $workExperience->save();

        $workExperiences = WorkExperience::all();
        return Reply::successWithData(__('messages.workExperienceUpdated'),['data' => $workExperiences]);
    }

    public function destroy($id)
    {
        WorkExperience::destroy($id);

        $workExperiences = WorkExperience::all();
        return Reply::successWithData(__('messages.workExperienceDeleted'),['data' => $workExperiences]);
    }

    public function show($id)
    {
        //
    }
}
