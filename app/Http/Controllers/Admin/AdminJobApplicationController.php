<?php

namespace App\Http\Controllers\Admin;

use App\AiApiKey;
use App\ApplicationSetting;
use App\ApplicationStatus;
use App\Company;
use App\Exports\JobApplicationExport;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\InterviewSchedule\StoreRequest;
use App\Http\Requests\StoreJobApplication;
use App\Http\Requests\UpdateJobApplication;
use App\InterviewSchedule;
use App\Job;
use App\JobApplication;
use App\JobApplicationAnswer;
use App\JobJobLocation;
use App\JobLocation;
use App\Notifications\CandidateScheduleInterview;
use App\Notifications\CandidateStatusChange;
use App\Notifications\ScheduleInterview;
use App\Question;
use App\Services\ResumeTextExtractor;
use App\Skill;
use App\Support\RaDataTableHtml;
use App\Services\Zoom\ZoomApiClient;
use App\Traits\ZoomSettings;
use App\User;
use App\ZoomMeeting;
use App\ZoomSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Currency;
use App\ApplicantNote;

class AdminJobApplicationController extends AdminBaseController
{
    use ZoomSettings;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.jobApplications');
        $this->pageIcon = 'icon-user';
        $this->perPage = 10;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $date = Carbon::now();
        $this->type = ($request->has('type')) ? 'dash' : '';

        $startDate = $date->subDays(30)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        $this->jobs = Job::all();
        $this->companies = Company::all();
        $this->skills = Skill::all();
        $this->questions = Question::all();
        $boardColumns = ApplicationStatus::withCount(['applications as application_count' => function ($q) use ($request) {
         $q->where('job_applications.is_candidate', 0); // ADD THIS
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $q = $q->where(DB::raw('DATE(job_applications.`created_at`)'), '>=', $request->startDate);
            } else {
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $q = $q->where(DB::raw('DATE(job_applications.`created_at`)'), '<=', $request->endDate);
            } else {
            }

            // Filter By jobs
            if ($request->jobs != 'all' && $request->jobs != '') {
                $q = $q->where('job_applications.job_id', $request->jobs);
            }

            // Filter by EndDate
            if ($request->search != null && $request->search != '') {
                $q = $q->where('full_name', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('email', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$request->search.'%');
            }

            // Filter  by Location
            if ($request->location != 'all' && $request->location != '') {
                $q->where('job_applications.location_id', '=', $request->location);
            }

            // Filter  by question
            if ($request->questions != 'all' && $request->questions != '') {

                $q->join('job_questions', 'job_questions.job_id', 'job_applications.job_id')
                    ->where('job_questions.question_id', '=', $request->questions);
            }

            if ($request->question_value != '' && $request->questions != 'all' && $request->questions != '') {

                $q->join('job_application_answers', 'job_application_answers.job_application_id', 'job_applications.id')
                    ->where('job_application_answers.question_id', $request->questions)
                    ->where('job_application_answers.answer', 'LIKE', '%'.$request->question_value.'%');
            }
            // Filter  by company
            if ($request->company != 'all' && $request->company != '') {
                $q = $q->join('jobs', 'jobs.id', 'job_applications.job_id')
                    ->where('jobs.company_id', '=', $request->company);
            }

            // Filter by skills
            if ($request->skill != 'all' && $request->skill != '') {
                foreach (explode(',', $request->skill) as $key => $skill) {
                    if ($key == 0) {
                        $q->whereJsonContains('skills', $skill);
                    } else {
                        $q->orWhereJsonContains('skills', $skill);
                    }
                }
            }
        }])
            ->with(['applications' => function ($r) use ($request) {
                $r = $r->select('job_applications.*');
                 $r->where('job_applications.is_candidate', 0); // ADD THIS
                if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                    $r = $r->where(DB::raw('DATE(job_applications.`created_at`)'), '>=', $request->startDate);
                } else {
                }

                if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                    $r = $r->where(DB::raw('DATE(job_applications.`created_at`)'), '<=', $request->endDate);
                } else {
                }

                // Filter By jobs
                if ($request->jobs != 'all' && $request->jobs != '') {
                    $r = $r->where('job_applications.job_id', $request->jobs);
                }

                // Filter by EndDate
                if ($request->search != null && $request->search != '') {
                    $r = $r->where('full_name', 'LIKE', '%'.$request->search.'%')
                        ->orWhere('email', 'LIKE', '%'.$request->search.'%')
                        ->orWhere('phone', 'LIKE', '%'.$request->search.'%');
                }

                // Filter By company
                if ($request->company != 'all' && $request->company != '') {
                    $r = $r->join('jobs', 'jobs.id', 'job_applications.job_id')
                        ->where('jobs.company_id', '=', $request->company);
                }

                // Filter  by Location
                if ($request->location != 'all' && $request->location != '') {
                    $r->where('job_applications.location_id', '=', $request->location);
                }

                if ($request->questions != 'all' && $request->questions != '') {

                    $r->join('job_questions', 'job_questions.job_id', 'job_applications.job_id')
                        ->where('job_questions.question_id', '=', $request->questions);
                }

                if ($request->question_value != '' && $request->questions != 'all' && $request->questions != '') {

                    $r->join('job_application_answers', 'job_application_answers.job_application_id', 'job_applications.id')
                        ->where('job_application_answers.question_id', $request->questions)
                        ->where('job_application_answers.answer', 'LIKE', '%'.$request->question_value.'%');
                }

                // Filter by skills
                if ($request->skill != 'all' && $request->skill != '') {
                    foreach (explode(',', $request->skill) as $key => $skill) {
                        if ($key == 0) {
                            $r->whereJsonContains('skills', $skill);
                        } else {
                            $r->orWhereJsonContains('skills', $skill);
                        }
                    }
                }
                $r->with(['schedule', 'job.category']);
            }, 'applications.schedule']);

        $this->boardColumns = $boardColumns->orderBy('position')->get()->map(function ($query) {
            $query->setRelation('applications', $query->applications->take($this->perPage));

            return $query;
        });

        $boardStracture = [];

        foreach ($this->boardColumns as $key => $column) {
            $boardStracture[$column->id] = [];

            foreach ($column->applications as $application) {
                $boardStracture[$column->id][] = $application->id;
            }
        }

        $this->boardStracture = json_encode($boardStracture);
        $this->currentDate = Carbon::now()->timestamp;
        $this->locations = JobLocation::all();
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        if ($request->ajax()) {
            $view = view('admin.job-applications.board-data', $this->data)->render();

            return Reply::dataOnly(['view' => $view]);
        }

        $this->mailSetting = ApplicationSetting::select('id', 'mail_setting')->first();
        $this->applicantsForAiCompare = JobApplication::query()
            ->select('id', 'full_name', 'job_id')
            ->with(['job:id,title'])
            ->latest('id')
            ->limit(300)
            ->get();

        return view('admin.job-applications.board', $this->data);
    }

    

    public function create()
    {
        abort_if(! $this->user->cans('add_job_applications'), 403);

        $this->jobs = Job::activeJobs();

        $this->locations = JobJobLocation::select('job_job_locations.*')
            ->with(['job', 'location_data'])
            ->join('jobs', 'jobs.id', 'job_job_locations.job_id')
            ->get();

        $this->gender = [
            'male' => __('modules.front.male'),
            'female' => __('modules.front.female'),
            'others' => __('modules.front.others'),
        ];

        // Add this
        // $this->currencies = Currency::all();

        return view('admin.job-applications.create', $this->data);
    }

    /**
     * @return mixed
     *
     * @throws \Throwable
     */
    public function jobQuestion($jobID, $applicationId = null)
    {
        $this->jobLocations = JobJobLocation::findOrFail($jobID);

        $this->job = Job::findOrFail($this->jobLocations->job_id);

        $this->jobQuestion = $this->job->questions()
            ->where(function ($query) {
                $query->whereNull('questions.job_category_id')
                    ->orWhere('questions.job_category_id', (int) $this->job->category_id);
            })
            ->with([
                'answers' => function ($query) use ($jobID, $applicationId) {
                    $query->where(['job_application_id' => $applicationId, 'job_id' => $jobID]);
                },
            ])->get();
        $this->gender = [
            'male' => __('modules.front.male'),
            'female' => __('modules.front.female'),
            'others' => __('modules.front.others'),
        ];

        $view = view('admin.job-applications.job-question', $this->data)->render();

        $options = ['job' => $this->job, 'gender' => $this->gender];
        $sections = ['section_visibility' => $this->job->section_visibility];

        if ($applicationId) {
            $application = JobApplication::select('id', 'gender', 'dob', 'country', 'state', 'city')->where('id', $applicationId)->first();

            $options = Arr::add($options, 'application', $application);
            $sections = Arr::add($sections, 'application', $application);
        }

        $requiredColumnsView = view('admin.job-applications.required-columns', $options)->render();
        $requiredSectionsView = view('admin.job-applications.required-sections', $sections)->render();

        $count = count($this->jobQuestion);

        $data = ['status' => 'success', 'view' => $view, 'requiredColumnsView' => $requiredColumnsView, 'requiredSectionsView' => $requiredSectionsView, 'count' => $count, 'jobJobLocation' => $this->jobLocations];

        if ($applicationId) {
            $data = Arr::add($data, 'application', $application);
        }

        return Reply::dataOnly($data);
    }


    public function edit($id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $this->statuses = ApplicationStatus::all();
        $this->application = JobApplication::find($id);
        $this->jobQuestion = $this->application->job->questions;

        $this->jobs = Job::select(
            'id',
            'title',
            'location_id',
            'status',
            'start_date',
            'end_date',
            'section_visibility'
        )->with('location:id,location')->get();

        $this->locations = JobJobLocation::select('job_job_locations.*')
            ->with(['job', 'location'])
            ->join('jobs', 'jobs.id', 'job_job_locations.job_id')
            ->get();

        // Add this
        $this->currencies = Currency::all();

        return view('admin.job-applications.edit', $this->data);
    }

    public function data(Request $request)
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $jobApplications = JobApplication::select('job_applications.id', 'job_applications.job_id', 'status_id', 'full_name', 'skills', 'location_id')
            ->with([
                'location',
                'job.skills',
                'status:id,status,color',
            ]);

        $jobApplications = $jobApplications->where('job_applications.is_candidate', 0);

        // Knockout filter
        // Knockout filter — show applicants who answered a knockout question with knockout answer
        if ($request->knockout == 1) {
            $jobApplications = $jobApplications->whereHas('answers', function ($q) {
                $q->whereHas('question', function ($qq) {
                    $qq->where('type', 'radio')
                    ->where('is_knockout', 1)
                    ->whereNotNull('knockout_answer');
                })->whereRaw('LOWER(TRIM(job_application_answers.answer)) = LOWER(TRIM((SELECT knockout_answer FROM questions WHERE questions.id = job_application_answers.question_id LIMIT 1)))');
            });
        }

        // Filter by status
        if ($request->status != 'all' && $request->status != '') {
            $jobApplications = $jobApplications->where('status_id', $request->status);
        }

        // Filter By jobs
        if ($request->jobs != 'all' && $request->jobs != '') {
            $jobApplications = $jobApplications->where('job_id', $request->jobs);
        }

        // Filter By company
        if ($request->company != 'all' && $request->company != '') {
            $jobApplications = $jobApplications->join('jobs', 'jobs.id', 'job_applications.job_id')
                ->where('jobs.company_id', '=', $request->company);
        }

        // Filter By skills
        if ($request->skill != 'all' && $request->skill != '') {
            foreach (explode(',', $request->skill) as $key => $skill) {
                if ($key == 0) {
                    $jobApplications = $jobApplications->whereJsonContains('skills', $skill);
                } else {
                    $jobApplications = $jobApplications->orWhereJsonContains('skills', $skill);
                }
            }
        }

        // Filter by location
        if ($request->location != 'all' && $request->location != '') {
            $jobApplications = $jobApplications->where('location_id', $request->location);
        }

        if ($request->questions != 'all' && $request->questions != '' && ($request->question_value == '' || is_null($request->question_value))) {
            $jobApplications = $jobApplications->whereHas('job.questions', function ($query) use ($request) {
                $query->where('question_id', $request->questions);
            });
        }

        if ($request->question_value != '' && $request->questions != 'all' && $request->questions != '') {
            $jobApplications = $jobApplications->join('job_application_answers', 'job_application_answers.job_application_id', 'job_applications.id')
                ->where('job_application_answers.question_id', $request->questions)
                ->where('job_application_answers.answer', 'LIKE', '%' . $request->question_value . '%');
        }

        // Filter by StartDate
        if ($request->startDate != null && $request->startDate != '') {
            $jobApplications = $jobApplications->whereDate('job_applications.created_at', '>=', $request->startDate);
        }

        // Filter by EndDate
        if ($request->endDate != null && $request->endDate != '') {
            $jobApplications = $jobApplications->whereDate('job_applications.created_at', '<=', $request->endDate);
        }

        // Build next-stage map once from DB ordered by position
        $allStatuses = ApplicationStatus::orderBy('position')->get();
        $nextMap = [];
        foreach ($allStatuses as $i => $s) {
            $nextStatus = $allStatuses->get($i + 1);
            if ($nextStatus) {
                $nextMap[$s->status] = [
                    'label' => ucwords(str_replace('_', ' ', $nextStatus->status)),
                    'slug'  => $nextStatus->status,
                ];
            }
        }

        // Find rejected & applied status ids for restore button
        $rejectedStatus = $allStatuses->firstWhere('status', 'rejected');
        $appliedStatus  = $allStatuses->firstWhere('status', 'applied');

        $canEdit   = $this->user->cans('edit_job_applications');
        $canView   = $this->user->cans('view_job_applications');
        $canDelete = $this->user->cans('delete_job_applications');

        return DataTables::of($jobApplications)
->addColumn('action', function ($row) use ($nextMap, $rejectedStatus, $appliedStatus, $allStatuses, $canEdit, $canView, $canDelete) {                $parts = [];
                $statusSlug = strtolower($row->status?->status ?? '');

                if ($canEdit) {
             
                if (isset($nextMap[$statusSlug]) && $statusSlug !== 'rejected') {
                    $nextLabel = $nextMap[$statusSlug]['label'];
                    $nextSlug  = $nextMap[$statusSlug]['slug'];
                    $parts[] = '<button type="button" onclick="jaMoveOneBySlug(' . $row->id . ',\'' . $nextSlug . '\')" class="ja-act-btn move" title="Move to ' . $nextLabel . '">' . $nextLabel . ' <i class="fa fa-arrow-right" style="font-size:10px;margin-left:2px;"></i></button>';
                }

                // Move to ANY stage dropdown
                if (!in_array($statusSlug, ['rejected', 'hired'])) {
                    $dropdownId = 'ja-move-drop-' . $row->id;
                    $dropdownHtml = '<div class="ja-move-wrap" style="position:relative;display:inline-flex;">';
                    $dropdownHtml .= '<button type="button" class="ja-act-btn" onclick="jaToggleDrop(\'' . $dropdownId . '\')" title="Move to stage" style="padding:5px 8px;border-radius:8px;"><i class="fa fa-chevron-down" style="font-size:10px;"></i></button>';
                    $dropdownHtml .= '<div id="' . $dropdownId . '" class="ja-move-drop" style="display:none;position:absolute;top:calc(100% + 4px);right:0;background:#fff;border:1.5px solid #E2DED8;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.13);z-index:999;min-width:150px;overflow:hidden;">';

                    foreach ($allStatuses as $s) {
                        if ($s->status === $statusSlug) continue;
                        $stageLabel = ucwords(str_replace('_', ' ', $s->status));
                        $stageDot   = $s->color ?? '#6B7280';
                        $dropdownHtml .= '<button type="button" onclick="jaMoveOneBySlug(' . $row->id . ',\'' . $s->status . '\');jaCloseDrop(\'' . $dropdownId . '\')" style="display:flex;align-items:center;gap:8px;width:100%;padding:8px 14px;border:none;background:none;font-size:13px;font-weight:500;color:#1A1E2E;cursor:pointer;font-family:inherit;text-align:left;" onmouseover="this.style.background=\'#F1F3F7\'" onmouseout="this.style.background=\'none\'">';
                        $dropdownHtml .= '<span style="width:8px;height:8px;border-radius:50%;background:' . $stageDot . ';flex-shrink:0;display:inline-block;"></span>' . $stageLabel;
                        $dropdownHtml .= '</button>';
                    }

                    $dropdownHtml .= '</div></div>';
                    $parts[] = $dropdownHtml;
                }

                // Reject button
                if (!in_array($statusSlug, ['rejected', 'hired'])) {
                    $parts[] = '<button type="button" onclick="jaMoveOneBySlug(' . $row->id . ',\'rejected\')" class="ja-act-btn reject" title="Reject"><i class="fa fa-times"></i></button>';
                }

                // Restore button for rejected
                if ($statusSlug === 'rejected' && $appliedStatus) {
                    $parts[] = '<button type="button" onclick="jaMoveOne(' . $row->id . ',' . $appliedStatus->id . ')" class="ja-act-btn restore" title="Restore to Applied"><i class="fa fa-undo"></i></button>';
                }
                }

                // View detail (sidebar) — always shown
                $parts[] = '<a href="javascript:;" class="show-detail ja-act-btn" data-row-id="' . $row->id . '" title="View profile"><i class="fa fa-eye"></i></a>';

                return '<div class="ja-row-actions">' . implode('', $parts) . '</div>';
            })
            ->editColumn('full_name', function ($row) {
                $name = ucwords($row->full_name);
                $job  = ucfirst($row->job?->title ?? '—');
                $loc  = ucwords($row->location?->location ?? '—');
                return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-[13px] flex-shrink-0">'
                            . strtoupper(substr($row->full_name, 0, 1)) .
                        '</div>
                        <div>
                            <a href="javascript:;" class="show-detail block text-[13.5px] font-semibold text-[#1A1E2E] hover:text-blue-600" data-row-id="' . $row->id . '">' . $name . '</a>
                            <span class="text-[11.5px] text-[#8892A0]">' . $job . ' &middot; ' . $loc . '</span>
                        </div>
                    </div>';
            })
            ->editColumn('title', function ($row) {
                return ucfirst($row->job?->title ?? '—');
            })
            ->addColumn('location_id', function ($row) {
                return ucwords($row->location?->location ?? '—');
            })
            ->editColumn('status', function ($row) {
                $color = $row->status?->color ?? '#6B7280';
                $label = ucwords(str_replace('_', ' ', $row->status?->status ?? ''));
                return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-semibold text-white" style="background:' . $color . '">' . $label . '</span>';
            })
            ->rawColumns(['action', 'full_name', 'status'])
            ->addIndexColumn()
            ->make(true);
    }
   public function stageCounts()
    {
        $counts = ApplicationStatus::withCount(['applications as cnt' => function ($q) {
            $q->where('is_candidate', 0)->whereNull('deleted_at');
        }])->get()->pluck('cnt', 'id');

        // KO count = applicants who answered a knockout question with the knockout answer
        $koCount = JobApplication::where('is_candidate', 0)
            ->whereHas('answers', function ($q) {
                $q->whereHas('question', function ($qq) {
                    $qq->where('type', 'radio')
                    ->where('is_knockout', 1)
                    ->whereNotNull('knockout_answer');
                })->whereColumn(
                    DB::raw('LOWER(TRIM(job_application_answers.answer))'),
                    DB::raw('LOWER(TRIM((SELECT knockout_answer FROM questions WHERE questions.id = job_application_answers.question_id LIMIT 1)))')
                );
            })
            ->count();

        return Reply::dataOnly(['counts' => $counts, 'ko_count' => $koCount]);
    }

    public function bulkStatusUpdate(Request $request)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);
        JobApplication::whereIn('id', $request->ids)->update(['status_id' => $request->status_id]);
        return Reply::success(__('messages.updatedSuccessfully'));
    }

   public function bulkRestoreKnockout(Request $request)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $appliedStatus = ApplicationStatus::where('status', 'applied')->first();

        if ($appliedStatus) {
            JobApplication::whereIn('id', $request->ids)->update([
                'status_id' => $appliedStatus->id
            ]);
        }

        return Reply::success(__('messages.updatedSuccessfully'));
    }
    public function createSchedule(Request $request, $id)
    {
        abort_if(! $this->user->cans('add_schedule'), 403);
        $this->candidates = JobApplication::all();
        $this->users = User::all();
        $this->zoom_setting = ZoomSetting::first();
        $this->scheduleDate = $request->date;
        $this->currentApplicant = JobApplication::findOrFail($id);

        return view('admin.job-applications.interview-create', $this->data)->render();
    }

    public function storeSchedule(StoreRequest $request)
    {
        abort_if(! $this->user->cans('add_schedule'), 403);
        $this->setZoomConfigs();

        $dateTime = $request->scheduleDate.' '.$request->scheduleTime;
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);
        if ($request->interview_type == 'online') {
            $data = $request->all();
            $meeting = new ZoomMeeting;
            $data['meeting_name'] = $request->meeting_title;
            $data['start_date_time'] = $dateTime;
            $data['end_date_time'] = $request->end_date.' '.$request->end_time;
            $meeting = $meeting->create($data);
            $host = User::find($request->create_by);
            $zoom = app(ZoomApiClient::class);
            $meetings = $this->createMeeting($zoom, $meeting, null, null, $host);
        } else {
            $meetings = '';
        }
        // store Schedule
        $interviewSchedule = new InterviewSchedule;
        $interviewSchedule->interview_type = $request->interview_type;
        $interviewSchedule->meeting_id = ($meetings != '') ? $meetings->id : null;
        $interviewSchedule->job_application_id = $request->candidates[0];
        $interviewSchedule->schedule_date = $dateTime;
        $interviewSchedule->save();

        // Update Schedule Status
        $status = ApplicationStatus::where('status', 'interview')->first();
        $jobApplication = $interviewSchedule->jobApplication;
        $jobApplication->status_id = $status->id;
        $jobApplication->save();

        if ($request->comment) {
            $scheduleComment = [
                'interview_schedule_id' => $interviewSchedule->id,
                'user_id' => $this->user->id,
                'comment' => $request->comment,
            ];

            $interviewSchedule->comments()->create($scheduleComment);
        }

        if (! empty($request->employees)) {
            $interviewSchedule->employees()->attach($request->employees);

            // Mail to employee for inform interview schedule
            Notification::send($interviewSchedule->employees, new ScheduleInterview($jobApplication, $meetings));
        }
        if (! $request->interview_type) {
            $meeting = '';
        }
        // mail to candidate for inform interview schedule
        Notification::send($jobApplication, new CandidateScheduleInterview($jobApplication, $interviewSchedule, $meetings));

        return Reply::redirect(route('admin.interview-schedule.index'), __('menu.interviewSchedule').' '.__('messages.createdSuccessfully'));
    }

    public function createMeeting(ZoomApiClient $zoom, ZoomMeeting $meeting, $id, $meetingId = null, $host = null)
    {
        $this->setZoomConfigs();

        // create meeting using zoom API
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
            $savedMeeting = $zoom->createMeeting($commonSettings);

            $meeting->meeting_id = (string) ($savedMeeting['id'] ?? '');
            $meeting->start_link = (string) ($savedMeeting['start_url'] ?? '');
            $meeting->join_link = (string) ($savedMeeting['join_url'] ?? '');
            $meeting->password = (string) ($savedMeeting['password'] ?? '');

            $meeting->save();
        } else {
            $zoom->updateMeeting($meeting->meeting_id, $commonSettings);
        }

        return $meeting;
    }

    public function store(StoreJobApplication $request)
    {

        abort_if(! $this->user->cans('add_job_applications'), 403);
        // Save currency to jobs table
        if ($request->filled('currency_id')) {
         $job = Job::find($request->job_id);

            if ($job) {
                $job->currency_id = $request->currency_id;
                $job->save();
            }
        }

        $jobApplication = new JobApplication;
        $jobApplication->full_name = $request->full_name;
        $jobApplication->job_id = $request->job_id;
        $jobApplication->status_id = 1; // applied status id
        $jobApplication->email = $request->email;
        $jobApplication->location_id = $request->location_id;
        $jobApplication->phone = $request->phone;
        $jobApplication->address = $request->address;
        $jobApplication->cover_letter = $request->cover_letter;
        $jobApplication->column_priority = 0;

        if ($request->entry_type === 'candidate') {
            $jobApplication->is_candidate = 1;  // add this column via migration
        }

        if ($request->has('gender')) {
            $jobApplication->gender = $request->gender;
        }
        if ($request->has('dob')) {
            $jobApplication->dob = $request->dob;
        }
        if ($request->has('country')) {
            $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];
            $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];

            $jobApplication->country = $this->getName($countriesArray, $request->country);
            $jobApplication->state = $this->getName($statesArray, $request->state);
            $jobApplication->city = $request->city;
            $jobApplication->zip_code = $request->zip_code;
        }

        if ($request->hasFile('photo')) {
            $jobApplication->photo = Files::uploadLocalOrS3($request->photo, 'candidate-photos', null, null, false);
        }
        $jobApplication->save();

        if ($request->hasFile('resume')) {
            $hashname = Files::uploadLocalOrS3($request->resume, 'documents/'.$jobApplication->id, null, null, false);
            $jobApplication->documents()->create([
                'name' => 'Resume',
                'hashname' => $hashname,
            ]);
        }

        // Job Application Answer save
        if (isset($request->answer) && ! empty($request->answer)) {
            foreach ($request->answer as $key => $value) {
                $answer = new JobApplicationAnswer;
                $answer->job_application_id = $jobApplication->id;
                $answer->job_id = $request->job_id;
                $answer->question_id = $key;
                if ($request->hasFile('answer.'.$key)) {
                    $answer->file = Files::uploadLocalOrS3($value, 'documents');
                } else {
                    $answer->answer = $value;
                }
                $answer->save();
            }
        }
        
        // Auto-move to rejected if applicant answered the knockout answer
        // Check knockout by loading answers and comparing in PHP
        $knockoutTriggered = false;

        $answers = JobApplicationAnswer::where('job_application_id', $jobApplication->id)
            ->with(['question'])
            ->get();

        foreach ($answers as $ans) {
            $q = $ans->question;
            if (!$q) continue;
            if ($q->type !== 'radio') continue;
            if (!$q->is_knockout) continue;
            if (!$q->knockout_answer) continue;

            if (strtolower(trim($ans->answer)) === strtolower(trim($q->knockout_answer))) {
                $knockoutTriggered = true;
                break;
            }
        }

        if ($knockoutTriggered) {
            $rejectedStatus = ApplicationStatus::where('status', 'rejected')->first();
            if ($rejectedStatus) {
                $jobApplication->status_id = $rejectedStatus->id;
                $jobApplication->save();
            }
            // DO NOT soft-delete — knockout is tracked by the answer, not deletion
        }
        if ($request->filled('notes') && is_array($request->notes)) {
            foreach ($request->notes as $noteText) {
                if (trim($noteText)) {
                    $note = new \App\ApplicantNote();
                    $note->note_text = trim($noteText);
                    $note->user_id   = auth()->id();
                    $note->job_application_id = $jobApplication->id;
                    $note->save();
                }
            }
        }
        return Reply::redirect(route('admin.job-applications.index'), __('menu.jobApplications').' '.__('messages.createdSuccessfully'));
    }

    public function update(UpdateJobApplication $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $mailSetting = ApplicationSetting::select('id', 'mail_setting')->first()->mail_setting;

        $jobApplication = JobApplication::with(['documents'])->findOrFail($id);
        $jobApplication->full_name = $request->full_name;
        $jobApplication->job_id = $request->job_id;
        $jobApplication->status_id = $request->status_id;
        $jobApplication->location_id = $request->location_id;
        $jobApplication->email = $request->email;
        $jobApplication->phone = $request->phone;
        $jobApplication->address = $request->address;
        $jobApplication->cover_letter = $request->cover_letter;

        if ($request->filled('currency_id')) {
            Job::where('id', $request->job_id)
                ->update([
                    'currency_id' => $request->currency_id
                ]);
        }

        if ($request->has('gender')) {
            $jobApplication->gender = $request->gender;
        }
        if ($request->has('dob')) {
            $jobApplication->dob = $request->dob;
        }
        if ($request->has('country')) {
            $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];
            $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];

            $jobApplication->country = $this->getName($countriesArray, $request->country);
            $jobApplication->state = $this->getName($statesArray, $request->state);
            $jobApplication->city = $request->city;
            $jobApplication->zip_code = $request->zip_code;
        }

        if ($request->hasFile('photo')) {
            $jobApplication->photo = Files::uploadLocalOrS3($request->photo, 'candidate-photos', null, null, false);
        }

        $isStatusDirty = $jobApplication->isDirty('status_id');

        $jobApplication->save();

        if ($request->hasFile('resume')) {

            if ($jobApplication->resumeDocument) {
                Files::deleteFile($jobApplication->resumeDocument->hashname, 'documents/'.$jobApplication->id);
            }

            $hashname = Files::uploadLocalOrS3($request->resume, 'documents/'.$jobApplication->id, null, null, false);
            $jobApplication->documents()->updateOrCreate(
                [
                    'documentable_type' => JobApplication::class,
                    'documentable_id' => $jobApplication->id,
                    'name' => 'Resume',
                ],
                [
                    'hashname' => $hashname,
                ]
            );
        }
        // Job Application Answer save
        if (isset($request->answer) && count($request->answer) > 0) {
            foreach ($request->answer as $key => $value) {
                if ($request->hasFile('answer.'.$key)) {
                    $file = Files::upload($value, 'documents');
                } else {
                    $answer = $value;
                }
                JobApplicationAnswer::updateOrCreate([
                    'job_application_id' => $jobApplication->id,
                    'job_id' => $jobApplication->job_id,
                    'question_id' => $key,
                ], ['answer' => ! empty($answer) ? $answer : null, 'file' => ! empty($file) ? $file : null]);
                $answer = '';
            }
        }

        if ($mailSetting[$request->status_id]['status'] && $isStatusDirty) {
            Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
        }
        // Auto-move to rejected column if any radio question answered 'no'
        $knockoutTriggered = false;
        $answers = JobApplicationAnswer::where('job_application_id', $jobApplication->id)
            ->with(['question'])
            ->get();

        foreach ($answers as $ans) {
            $q = $ans->question;
            if (!$q) continue;
            if ($q->type !== 'radio') continue;
            if (!$q->is_knockout) continue;
            if (!$q->knockout_answer) continue;
            if (strtolower(trim($ans->answer)) === strtolower(trim($q->knockout_answer))) {
                $knockoutTriggered = true;
                break;
            }
        }

        if ($knockoutTriggered) {
            $rejectedStatus = ApplicationStatus::where('status', 'rejected')->first();
            if ($rejectedStatus) {
                $jobApplication->status_id = $rejectedStatus->id;
                $jobApplication->save();
            }
            // DO NOT soft-delete — knockout is tracked by the answer, not deletion
        }
        return Reply::redirect(route('admin.job-applications.table'), __('menu.jobApplications').' '.__('messages.updatedSuccessfully'));
    }

    public function destroy($id)
    {
        abort_if(! $this->user->cans('delete_job_applications'), 403);

        $jobApplication = JobApplication::findOrFail($id);

        if ($jobApplication->photo) {
            Storage::delete('candidate-photos/'.$jobApplication->photo);
        }

        $jobApplication->forceDelete();

        return Reply::success(__('messages.recordDeleted'));
    }

    public function show($id)
    {
        $this->application = JobApplication::withTrashed()
            ->with(['schedule', 'notes', 'onboard', 'status', 'schedule.employee', 'schedule.comments.user', 'location'])
            ->find($id);

        if (!$this->application) {
            return Reply::error('Application not found.');
        }

        $this->skills = Skill::select('id', 'name')->get();

        $this->answers = JobApplicationAnswer::with(['question'])
            ->where('job_id', $this->application->job_id)
            ->where('job_application_id', $this->application->id)
            ->get();

        $view = view('admin.job-applications.show', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function updateIndex(Request $request)
    {
        $taskIds = $request->applicationIds;
        $boardColumnIds = $request->boardColumnIds;
        $priorities = $request->prioritys;
        $mailSetting = ApplicationSetting::select('id', 'mail_setting')->first()->mail_setting;

        $date = Carbon::now();
        $startDate = $request->startDate ?: $date->subDays(30)->format('Y-m-d');
        $endDate = $request->endDate ?: $date->format('Y-m-d');

        if ($request->has('applicationIds')) {
            foreach ($taskIds as $key => $taskId) {
                if (! is_null($taskId)) {

                    $task = JobApplication::find($taskId);
                    $task->column_priority = $priorities[$key];
                    $task->status_id = $boardColumnIds[$key];

                    $task->save();
                }
            }

            // Send notification to candidate on update status
            if ($mailSetting[$boardColumnIds[0]]['status'] && $request->draggedTaskId != 0) {
                $jobApplication = JobApplication::findOrFail($request->draggedTaskId);
                Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
            }
        }

        $columnCountByIds = ApplicationStatus::select('id', 'color')
            ->withCount([
                'applications as status_count' => function ($query) use ($startDate, $endDate, $request) {
                    $query->withoutTrashed()->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
                    if ($request->jobs != 'all' && $request->jobs != '') {
                        $query->where('job_id', $request->jobs);
                    }
                    if ($request->search != '') {
                        $query->where('full_name', 'LIKE', '%'.$request->search.'%');
                    }
                },
            ])
            ->get()
            ->toArray();

        return Reply::dataOnly(['status' => 'success', 'columnCountByIds' => $columnCountByIds]);
    }

    public function table()
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $this->boardColumns = ApplicationStatus::all();
        $this->locations = JobLocation::all();
        $this->jobs = Job::all();
        $this->skills = Skill::all();
        $this->questions = Question::all();
        $this->companies = Company::all();
        $this->applicantsForAiCompare = JobApplication::query()
            ->select('id', 'full_name', 'job_id')
            ->with(['job:id,title'])
            ->latest('id')
            ->limit(300)
            ->get();

        return view('admin.job-applications.index', $this->data);
    }

    public function aiCompare(Request $request)
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $validated = $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
            'application_ids' => 'required|array|min:1|max:300',
            'application_ids.*' => 'integer|exists:job_applications,id',
        ]);

        $job = Job::findOrFail((int) $validated['job_id']);
        $applications = JobApplication::query()
            ->where('job_id', (int) $job->id)
            ->whereIn('id', $validated['application_ids'])
            ->get();

        if ($applications->isEmpty()) {
            return Reply::error(__('messages.selectAtLeastOneCandidate'));
        }

        $apiCandidates = $this->collectAiApiCandidates();

        if ($apiCandidates === []) {
            return Reply::error(__('messages.aiApiKeyRequired'));
        }

        $jobProfile = trim(strip_tags((string) $job->job_description.' '.(string) $job->job_requirement));
        $jobSkillNames = $this->getJobSkillNames($job);
        $jobLocationIds = JobJobLocation::query()->where('job_id', $job->id)->pluck('location_id')->map(fn ($id) => (int) $id)->all();
        $candidatePayload = $applications->map(function (JobApplication $application) {
            return [
                'id' => $application->id,
                'full_name' => $application->full_name,
                'skills' => $this->getApplicationSkillNames($application),
                'location' => optional($application->location)->location,
                'location_id' => (int) ($application->location_id ?? 0),
                'inferred_experience_years' => $this->inferExperienceYears($application),
                'profile' => $this->buildApplicantProfileText($application),
            ];
        })->values()->all();

        $lastError = 'AI compare failed. Please try again.';
        foreach ($apiCandidates as $candidate) {
            try {
                $result = $this->compareApplicantsByProvider(
                    $candidate['provider'],
                    $candidate['api_key'],
                    $job->title,
                    $jobProfile,
                    $candidatePayload,
                    $jobSkillNames,
                    $jobLocationIds
                );

                $indexedApplications = $applications->keyBy('id');
                $indexedByName = $applications->keyBy(function (JobApplication $application) {
                    return Str::lower(trim((string) $application->full_name));
                });
                $matches = collect($result)
                    ->values()
                    ->map(function (array $row, int $index) use ($indexedApplications, $indexedByName, $applications) {
                        $rawId = $row['application_id'] ?? $row['applicant_id'] ?? $row['candidate_id'] ?? $row['id'] ?? null;
                        $id = is_numeric($rawId) ? (int) $rawId : 0;
                        $app = $id > 0 ? $indexedApplications->get($id) : null;

                        if (! $app && isset($row['full_name']) && is_string($row['full_name'])) {
                            $app = $indexedByName->get(Str::lower(trim($row['full_name'])));
                        }

                        // Last-resort fallback: preserve row by positional mapping.
                        if (! $app) {
                            $app = $applications->get($index);
                        }

                        if (! $app) {
                            return null;
                        }

                        return [
                            'application_id' => (int) $app->id,
                            'full_name' => $app->full_name,
                            'status_id' => (int) $app->status_id,
                            'match_score' => max(0, min(100, (int) ($row['match_score'] ?? $row['score'] ?? 0))),
                            'rank_tier' => (string) ($row['rank_tier'] ?? $this->scoreToTier((int) ($row['match_score'] ?? $row['score'] ?? 0))),
                            'summary' => (string) ($row['summary'] ?? $row['reason'] ?? $row['explanation'] ?? ''),
                            'strengths' => collect($row['strengths'] ?? [])->filter(fn ($v) => is_string($v))->values()->all(),
                            'gaps' => collect($row['gaps'] ?? [])->filter(fn ($v) => is_string($v))->values()->all(),
                        ];
                    })
                    ->filter()
                    ->sortByDesc('match_score')
                    ->values();

                return Reply::successWithData(__('messages.aiComparisonGeneratedSuccessfully'), [
                    'matches' => $matches,
                    'job_title' => $job->title,
                    'debug' => [
                        'selected_count' => $applications->count(),
                        'ai_rows_count' => count($result),
                        'mapped_count' => $matches->count(),
                    ],
                ]);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return Reply::error(__('messages.aiCompareFailed').': '.$lastError);
    }

    public function aiParseResumeFromUpload(Request $request)
    {
        abort_if(! ($this->user->cans('add_job_applications') || $this->user->cans('edit_job_applications')), 403);

        $request->validate([
            'resume' => 'required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,rtf,txt,jpg,jpeg,png',
        ]);

        $file = $request->file('resume');
        if ($file === null) {
            return Reply::error(__('messages.resumeReadFailed'));
        }

        try {
            $text = app(ResumeTextExtractor::class)->extract($file);
        } catch (\InvalidArgumentException $e) {
            return Reply::error($e->getMessage());
        }

        $apiCandidates = $this->collectAiApiCandidates();
        if ($apiCandidates === []) {
            return Reply::error(__('messages.aiApiKeyRequired'));
        }

        $textForAi = mb_substr($text, 0, 15000);
        $systemPrompt = 'You extract structured applicant details from resume text and write a short professional cover letter. '
            .'Return strict minified JSON only. '
            .'Schema: {"full_name":string,"email":string,"phone":string,"address":string,"country":string,"state":string,"city":string,"zip_code":string,"cover_letter":string}. '
            .'Use empty string for any field you cannot determine. '
            .'For country and state return names (not numeric IDs). '
            .'Cover letter style: 2 short paragraphs, professional tone, no markdown, no bullet points. '
            .'No markdown, no code fences, no extra text.';

        $userPrompt = "Resume text:\n".$textForAi;

        $lastError = 'AI resume parsing failed. Please try again.';
        foreach ($apiCandidates as $candidate) {
            try {
                $decoded = $this->generateCoverLetterJsonByProvider(
                    (string) $candidate['provider'],
                    (string) $candidate['api_key'],
                    $systemPrompt,
                    $userPrompt
                );

                return Reply::successWithData(__('messages.resumeParsedSuccessfully'), [
                    'full_name' => trim((string) data_get($decoded, 'full_name', '')),
                    'email' => trim((string) data_get($decoded, 'email', '')),
                    'phone' => $this->normalizePhoneForForm((string) data_get($decoded, 'phone', '')),
                    'address' => trim((string) data_get($decoded, 'address', '')),
                    'country' => trim((string) data_get($decoded, 'country', '')),
                    'state' => trim((string) data_get($decoded, 'state', '')),
                    'city' => trim((string) data_get($decoded, 'city', '')),
                    'zip_code' => trim((string) data_get($decoded, 'zip_code', '')),
                    'cover_letter' => trim((string) data_get($decoded, 'cover_letter', '')),
                    'resume_text' => mb_substr($text, 0, 20000),
                ]);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return Reply::error(__('messages.aiResumeParsingFailed').': '.$lastError);
    }

    public function aiGenerateCoverLetterAndDetails(Request $request)
    {
        abort_if(! ($this->user->cans('add_job_applications') || $this->user->cans('edit_job_applications')), 403);

        $validated = $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
            'location_id' => 'nullable|integer',
            'full_name' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'address' => 'nullable|string|max:1000',
            'cover_letter' => 'nullable|string|max:20000',
            'resume_text' => 'nullable|string|max:20000',
        ]);

        $job = Job::with('company')->findOrFail((int) $validated['job_id']);
        $jobLocationText = '';
        if (! empty($validated['location_id'])) {
            $jobLocation = JobLocation::find((int) $validated['location_id']);
            $jobLocationText = trim((string) optional($jobLocation)->location);
        }

        $jobProfile = trim(strip_tags((string) $job->job_description.' '.(string) $job->job_requirement));
        $companyName = trim((string) optional($job->company)->company_name);

        $apiCandidates = $this->collectAiApiCandidates();

        if ($apiCandidates === []) {
            return Reply::error(__('messages.aiApiKeyRequired'));
        }

        $existingFullName = (string) ($validated['full_name'] ?? '');
        $existingEmail = (string) ($validated['email'] ?? '');
        $existingPhone = (string) ($validated['phone'] ?? '');
        $existingAddress = (string) ($validated['address'] ?? '');
        $existingCoverLetter = (string) ($validated['cover_letter'] ?? '');
        $resumeText = trim((string) ($validated['resume_text'] ?? ''));

        $systemPrompt = 'You are an expert assistant that writes professional cover letters for job applicants. '
            .'Return strict minified JSON only. '
            .'Schema: {"full_name":string,"email":string,"phone":string,"address":string,"cover_letter":string}. '
            .'Rules: No markdown, no code fences, no extra text.';

        $userPrompt = 'Job title: '.$job->title.'. '
            .($companyName !== '' ? 'Company: '.$companyName.'. ' : '')
            .($jobLocationText !== '' ? 'Location: '.$jobLocationText.'. ' : '')
            .'Job profile: '.$jobProfile.'. '
            .'Existing applicant fields (may be empty): '
            .'full_name="'.trim($existingFullName).'", '
            .'email="'.trim($existingEmail).'", '
            .'phone="'.trim($existingPhone).'", '
            .'address="'.trim($existingAddress).'". '
            .'Existing cover_letter (may be empty): '
            .'cover_letter="'.trim($existingCoverLetter).'". '
            .($resumeText !== '' ? 'Resume excerpt (from uploaded CV, for tailoring the letter): '.mb_substr($resumeText, 0, 12000).' ' : '')
            .'Task: If a field above is empty, generate an appropriate value. '
            .'If the field is NOT empty, copy it exactly. '
            .'Generate a tailored cover letter for the job. '
            .'Cover letter style: 2-3 short paragraphs, professional tone, no bullet points.';

        $lastError = 'AI cover letter generation failed. Please try again.';
        foreach ($apiCandidates as $candidate) {
            try {
                $decoded = $this->generateCoverLetterJsonByProvider(
                    (string) $candidate['provider'],
                    (string) $candidate['api_key'],
                    $systemPrompt,
                    $userPrompt
                );

                $aiFullName = (string) data_get($decoded, 'full_name', '');
                $aiEmail = (string) data_get($decoded, 'email', '');
                $aiPhone = (string) data_get($decoded, 'phone', '');
                $aiAddress = (string) data_get($decoded, 'address', '');
                $aiCoverLetter = (string) data_get($decoded, 'cover_letter', '');

                $finalFullName = trim($existingFullName) !== '' ? $existingFullName : $aiFullName;
                $finalEmail = trim($existingEmail) !== '' ? $existingEmail : $aiEmail;
                $finalPhone = trim($existingPhone) !== '' ? $existingPhone : $aiPhone;
                $finalAddress = trim($existingAddress) !== '' ? $existingAddress : $aiAddress;
                $finalCoverLetter = trim($existingCoverLetter) !== '' ? $existingCoverLetter : $aiCoverLetter;
                $finalPhone = $this->normalizePhoneForForm((string) $finalPhone);

                return Reply::successWithData(__('messages.coverLetterGeneratedSuccessfully'), [
                    'full_name' => $finalFullName,
                    'email' => $finalEmail,
                    'phone' => $finalPhone,
                    'address' => $finalAddress,
                    'cover_letter' => $finalCoverLetter,
                ]);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return Reply::error(__('messages.aiCoverLetterGenerationFailed').': '.$lastError);
    }

    public function aiUpdateStatus(Request $request)
    {
        abort_if(! ($this->user->cans('edit_job_applications')), 403);

        $validated = $request->validate([
            'application_id' => 'required|integer|exists:job_applications,id',
            'status_id' => 'required|integer|exists:application_status,id',
        ]);

        $jobApplication = JobApplication::withTrashed()->findOrFail((int) $validated['application_id']);
        $isStatusDirty = $jobApplication->isDirty('status_id');
        $jobApplication->status_id = (int) $validated['status_id'];
        $jobApplication->save();

        $mailSetting = ApplicationSetting::select('id', 'mail_setting')->first()?->mail_setting ?? [];
        $statusId = (int) $validated['status_id'];

        if ($isStatusDirty && is_array($mailSetting) && isset($mailSetting[$statusId]['status']) && $mailSetting[$statusId]['status'] === true) {
            Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
        }

        return Reply::success(__('messages.updatedSuccessfully'));
    }

    public function aiCompareApplicants(Request $request)
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $validated = $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
        ]);

        $applications = JobApplication::query()
            ->select('id', 'full_name', 'job_id')
            ->where('job_id', (int) $validated['job_id'])
            ->orderBy('id', 'desc')
            ->limit(300)
            ->get();

        return Reply::dataOnly([
            'applications' => $applications->map(fn (JobApplication $a) => [
                'id' => (int) $a->id,
                'full_name' => (string) $a->full_name,
            ])->values()->all(),
        ]);
    }

    public function ratingSave(Request $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $application = JobApplication::withTrashed()->findOrFail($id);
        $application->rating = $request->rating;
        $application->save();

        return Reply::success(__('messages.updatedSuccessfully'));
    }

    // Job Applications data Export
    public function export($status, $location, $startDate, $endDate, $jobs)
    {
        $filters = [
            'status' => $status,
            'location' => $location,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'jobs' => $jobs,
        ];

        $data = [
            'company' => $this->companyName,
        ];

        return Excel::download(new JobApplicationExport($filters, $data), 'job-applications.xlsx', ExcelExcel::XLSX);
    }

    public function getName($arr, $id)
    {
        $result = array_filter($arr, function ($value) use ($id) {
            return $value['id'] == $id;
        });

        return current($result)['name'];
    }

    private function buildApplicantProfileText(JobApplication $application): string
    {
        $skills = implode(', ', $this->getApplicationSkillNames($application));

        $answers = JobApplicationAnswer::query()
            ->where('job_application_id', $application->id)
            ->whereNotNull('answer')
            ->pluck('answer')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(' | ');

        return trim(implode("\n", array_filter([
            'Name: '.$application->full_name,
            'Email: '.((string) $application->email),
            'Phone: '.((string) $application->phone),
            'Location: '.((string) optional($application->location)->location),
            'Skills: '.$skills,
            'Inferred Experience Years: '.$this->inferExperienceYears($application),
            'Cover Letter: '.trim((string) $application->cover_letter),
            'Application Answers: '.$answers,
        ])));
    }

    /**
     * @return array<int, array{provider: string, api_key: string}>
     */
    private function collectAiApiCandidates(): array
    {
        $configuredKeys = AiApiKey::query()->active()->orderBy('sort_order')->orderBy('id')->get();
        $apiCandidates = [];
        foreach ($configuredKeys as $row) {
            $plainKey = $this->resolvePlainApiKey((string) $row->api_key);
            if ($plainKey === '') {
                continue;
            }
            $apiCandidates[] = [
                'provider' => $this->normalizeProvider((string) $row->provider),
                'api_key' => $plainKey,
            ];
        }

        return $apiCandidates;
    }

    private function resolvePlainApiKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        if (Str::startsWith($key, 'eyJpdiI6')) {
            try {
                $decrypted = Crypt::decryptString($key);
                if (is_string($decrypted) && trim($decrypted) !== '') {
                    return trim($decrypted);
                }
            } catch (\Throwable $e) {
            }
        }

        return $key;
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        if ($provider === '' || Str::contains($provider, 'openai') || Str::contains($provider, 'gpt')) {
            return 'openai';
        }
        if (Str::contains($provider, 'gemini') || Str::contains($provider, 'google')) {
            return 'gemini';
        }
        if (Str::contains($provider, 'anthropic') || Str::contains($provider, 'claude')) {
            return 'anthropic';
        }
        if (Str::contains($provider, 'groq') || Str::contains($provider, 'llama')) {
            return 'groq';
        }

        return 'openai';
    }

    private function extractOpenAiLikeContent(array $payload): string
    {
        $content = data_get($payload, 'choices.0.message.content');
        if (is_string($content) && trim($content) !== '') {
            return trim($content);
        }

        if (is_array($content)) {
            $combined = collect($content)
                ->map(fn ($part) => is_array($part) ? (string) ($part['text'] ?? $part['content'] ?? '') : (string) $part)
                ->filter(fn ($chunk) => trim($chunk) !== '')
                ->implode("\n");

            if (trim($combined) !== '') {
                return trim($combined);
            }
        }

        foreach ([data_get($payload, 'choices.0.text'), data_get($payload, 'output_text'), data_get($payload, 'output.0.content.0.text')] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    private function compareApplicantsByProvider(
        string $provider,
        string $apiKey,
        string $jobTitle,
        string $jobProfile,
        array $applicants,
        array $jobSkillNames,
        array $jobLocationIds
    ): array {
        $provider = $this->normalizeProvider($provider);
        $systemPrompt = 'You are an expert technical recruiter. Return strict JSON only. '
            .'Schema: {"matches":[{"application_id":number,"match_score":number,"rank_tier":"best|better|good|not_match","summary":string,"strengths":[string],"gaps":[string]}]}. '
            .'Rules: match_score is integer 0-100. Include all application_ids. '
            .'Scoring must prioritize in order: skill overlap, relevant experience, location fit. '
            .'Do not score based on whether a resume/attachment is present or missing. '
            .'rank_tier mapping: best>=95, better>=75, good>=60.';

        $userPayload = [
            'job_title' => $jobTitle,
            'job_profile' => $jobProfile,
            'required_skills' => $jobSkillNames,
            'job_location_ids' => $jobLocationIds,
            'applicants' => $applicants,
        ];
        $userPrompt = 'Compare applicants against this job profile and rank best matches: '.json_encode($userPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $content = '';
        if ($provider === 'openai' || $provider === 'groq') {
            $url = $provider === 'groq' ? 'https://api.groq.com/openai/v1/chat/completions' : 'https://api.openai.com/v1/chat/completions';
            $model = $provider === 'groq' ? env('AI_JOB_GENERATOR_GROQ_MODEL', 'llama-3.1-8b-instant') : env('AI_JOB_GENERATOR_MODEL', 'gpt-5-nano');
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ];

            if ($provider === 'openai') {
                $payload['max_completion_tokens'] = 2000;
            } else {
                $payload['temperature'] = 0.2;
                $payload['max_tokens'] = 1600;
            }

            $response = Http::timeout(35)->withToken($apiKey)->post($url, $payload);
            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }
            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responsePayload);
            $content = trim($this->extractOpenAiLikeContent($responsePayload));

            if ($content === '' && $provider === 'openai') {
                $retryPayload = $payload;
                $retryPayload['max_completion_tokens'] = max((int) ($retryPayload['max_completion_tokens'] ?? 2000), 2600);
                $retryResponse = Http::timeout(35)->withToken($apiKey)->post($url, $retryPayload);
                if ($retryResponse->successful()) {
                    $retryPayloadData = (array) $retryResponse->json();
                    $this->recordAiUsage($provider, $model, $retryPayloadData);
                    $content = trim($this->extractOpenAiLikeContent($retryPayloadData));
                }
            }
        } elseif ($provider === 'anthropic') {
            $response = Http::timeout(35)->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => env('AI_JOB_GENERATOR_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
                'max_tokens' => 1600,
                'temperature' => 0.2,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);
            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }
            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, env('AI_JOB_GENERATOR_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'), $responsePayload);
            $content = collect((array) data_get($responsePayload, 'content', []))
                ->map(fn ($part) => is_array($part) && ($part['type'] ?? '') === 'text' ? (string) ($part['text'] ?? '') : '')
                ->implode("\n");
        } else {
            $model = env('AI_JOB_GENERATOR_GEMINI_MODEL', 'gemini-2.0-flash');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$apiKey;
            $response = Http::timeout(35)->post($url, [
                'contents' => [[
                    'parts' => [[
                        'text' => $systemPrompt."\n\n".$userPrompt,
                    ]],
                ]],
                'generationConfig' => ['temperature' => 0.2],
            ]);
            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }
            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responsePayload);
            $content = (string) data_get($responsePayload, 'candidates.0.content.parts.0.text', '');
        }

        $content = trim($content);
        if (Str::startsWith($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
            $content = trim($content);
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded) && preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
        }
        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid AI compare response.');
        }

        $matches = $decoded['matches'] ?? $decoded['rankings'] ?? $decoded['results'] ?? $decoded['candidates'] ?? $decoded;
        if (! is_array($matches)) {
            throw new \RuntimeException('AI compare response missing matches.');
        }

        return array_values(array_filter($matches, fn ($row) => is_array($row)));
    }

    private function extractJsonFromAiContent(string $content): array
    {
        $content = trim($content);
        if (Str::startsWith($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
            $content = trim($content);
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: extract the outermost JSON object boundaries.
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \RuntimeException('Invalid AI JSON response.');
    }

    private function generateCoverLetterJsonByProvider(
        string $provider,
        string $apiKey,
        string $systemPrompt,
        string $userPrompt
    ): array {
        $provider = $this->normalizeProvider($provider);

        $content = '';
        if ($provider === 'openai' || $provider === 'groq') {
            $url = $provider === 'groq' ? 'https://api.groq.com/openai/v1/chat/completions' : 'https://api.openai.com/v1/chat/completions';
            $model = $provider === 'groq' ? env('AI_JOB_GENERATOR_GROQ_MODEL', 'llama-3.1-8b-instant') : env('AI_JOB_GENERATOR_MODEL', 'gpt-5-nano');
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ];

            if ($provider === 'openai') {
                $payload['max_completion_tokens'] = 1300;
                $payload['response_format'] = ['type' => 'json_object'];
            } else {
                $payload['temperature'] = 0.3;
                $payload['max_tokens'] = 900;
            }

            $response = Http::timeout(35)->withToken($apiKey)->post($url, $payload);

            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }

            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responsePayload);
            $content = trim($this->extractOpenAiLikeContent($responsePayload));

            if ($content === '' && $provider === 'openai') {
                $retryPayload = $payload;
                $retryPayload['max_completion_tokens'] = max((int) ($retryPayload['max_completion_tokens'] ?? 1300), 2200);
                $retryResponse = Http::timeout(35)->withToken($apiKey)->post($url, $retryPayload);
                if ($retryResponse->successful()) {
                    $retryPayloadData = (array) $retryResponse->json();
                    $this->recordAiUsage($provider, $model, $retryPayloadData);
                    $content = trim($this->extractOpenAiLikeContent($retryPayloadData));
                }
            }
        } elseif ($provider === 'anthropic') {
            $response = Http::timeout(35)->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => env('AI_JOB_GENERATOR_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
                'max_tokens' => 1000,
                'temperature' => 0.3,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }

            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, env('AI_JOB_GENERATOR_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'), $responsePayload);
            $content = collect((array) data_get($responsePayload, 'content', []))
                ->map(fn ($part) => is_array($part) && ($part['type'] ?? '') === 'text' ? (string) ($part['text'] ?? '') : '')
                ->implode("\n");
        } else {
            $model = env('AI_JOB_GENERATOR_GEMINI_MODEL', 'gemini-2.0-flash');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$apiKey;

            $response = Http::timeout(35)->post($url, [
                'contents' => [[
                    'parts' => [[
                        'text' => $systemPrompt."\n\n".$userPrompt,
                    ]],
                ]],
                'generationConfig' => ['temperature' => 0.3],
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException((string) data_get($response->json(), 'error.message', 'Provider request failed.'));
            }

            $responsePayload = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responsePayload);
            $content = (string) data_get($responsePayload, 'candidates.0.content.parts.0.text', '');
        }

        $decoded = $this->extractJsonFromAiContent($content);
        if (! is_array($decoded)) {
            throw new \RuntimeException('AI response did not decode to JSON.');
        }

        return $decoded;
    }

    private function normalizePhoneForForm(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        // Common India format normalization: +91XXXXXXXXXX -> XXXXXXXXXX
        if (Str::startsWith($digits, '91') && strlen($digits) === 12) {
            return substr($digits, 2);
        }

        return $digits;
    }

    private function getApplicationSkillNames(JobApplication $application): array
    {
        return collect((array) $application->skills)
            ->map(function ($skillId) {
                $skill = Skill::find((int) $skillId);

                return $skill?->name;
            })
            ->filter()
            ->map(fn ($name) => trim((string) $name))
            ->values()
            ->all();
    }

    private function getJobSkillNames(Job $job): array
    {
        $skillIds = $job->skills()->pluck('skill_id')->map(fn ($id) => (int) $id)->all();

        if (empty($skillIds)) {
            return [];
        }

        return Skill::query()->whereIn('id', $skillIds)->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values()
            ->all();
    }

    private function inferExperienceYears(JobApplication $application): int
    {
        $text = Str::lower($this->buildRawCandidateExperienceText($application));
        preg_match_all('/(\d+)\s*(\+)?\s*(year|years|yr|yrs)/', $text, $matches);
        if (empty($matches[1])) {
            return 0;
        }

        return (int) max(array_map(fn ($value) => (int) $value, $matches[1]));
    }

    private function buildRawCandidateExperienceText(JobApplication $application): string
    {
        $answers = JobApplicationAnswer::query()
            ->where('job_application_id', $application->id)
            ->whereNotNull('answer')
            ->pluck('answer')
            ->implode(' ');

        return trim((string) $application->cover_letter.' '.$answers);
    }

    private function scoreToTier(int $score): string
    {
        if ($score >= 95) {
            return 'best';
        }
        if ($score >= 75) {
            return 'better';
        }
        if ($score >= 60) {
            return 'good';
        }

        return 'not_match';
    }

    public function archiveJobApplication(Request $request, JobApplication $application)
    {
        abort_if(! $this->user->cans('delete_job_applications'), 403);

        $application->delete();

        return Reply::success(__('messages.applicationArchivedSuccessfully'));
    }

    public function unarchiveJobApplication(Request $request, $application_id)
    {
        abort_if(! $this->user->cans('delete_job_applications'), 403);

        $application = JobApplication::select('id', 'deleted_at')->withTrashed()->where('id', $application_id)->first();

        $application->restore();

        return Reply::success(__('messages.applicationUnarchivedSuccessfully'));
    }

    public function addSkills(Request $request, $applicationId)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $application = JobApplication::withTrashed()->findOrFail($applicationId);
        $application->skills = $request->skills;
        $application->save();

        return Reply::success(__('messages.skillsSavedSuccessfully'));
    }

    public function loadMore(Request $request)
    {

        $startDate = ($request->startDate != 'null') ? Carbon::parse($request->startDate)->toDateString() : null;
        $endDate = ($request->endDate != 'null') ? Carbon::parse($request->endDate)->toDateString() : null;
        $skip = $request->currentTotalRecords;

        $totalRecord = $request->totalRecord;

        $this->currentDate = Carbon::now()->timestamp;

        $applications = JobApplication::with(['status', 'job.category', 'schedule'])->select('job_applications.*')
            ->where('status_id', $request->columnId);
        $applications = $applications->where('job_applications.is_candidate', 0);
        if ($startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $applications = $applications->where(DB::raw('DATE(job_applications.`created_at`)'), '>=', $startDate);
        } else {
        }

        if ($endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $applications = $applications->where(DB::raw('DATE(job_applications.`created_at`)'), '<=', $endDate);
        } else {
        }

        // Filter By jobs
        if ($request->jobs != 'all' && $request->jobs != '') {
            $applications = $applications->where('job_applications.job_id', $request->jobs);
        }

        // Filter By company
        if ($request->company != 'all' && $request->company != '') {
            $applications = $applications->join('jobs', 'jobs.id', 'job_applications.job_id')
                ->where('jobs.company_id', '=', $request->company);
        }

        // Filter by EndDate
        if ($request->search != null && $request->search != '') {
            $applications = $applications->where('full_name', 'LIKE', '%'.$request->search.'%')
                ->orWhere('email', 'LIKE', '%'.$request->search.'%')
                ->orWhere('phone', 'LIKE', '%'.$request->search.'%');
        }

        // Filter  by Location
        if ($request->location != 'all' && $request->location != '') {
            $applications->leftJoin('jobs', 'jobs.id', 'job_applications.job_id')
                ->where('jobs.location_id', '=', $request->location);
        }

        if ($request->questions != 'all' && $request->questions != '') {

            $applications->join('job_questions', 'job_questions.job_id', 'job_applications.job_id')
                ->where('job_questions.question_id', '=', $request->questions);
        }

        if ($request->question_value != '' && $request->questions != 'all' && $request->questions != '') {

            $applications->join('job_application_answers', 'job_application_answers.job_application_id', 'job_applications.id')
                ->where('job_application_answers.question_id', $request->questions)
                ->where('job_application_answers.answer', 'LIKE', '%'.$request->question_value.'%');
        }

        // Filter by skills
        if ($request->skill != 'all' && $request->skill != '') {
            foreach (explode(',', $request->skill) as $key => $skill) {
                if ($key == 0) {
                    $applications->whereJsonContains('skills', $skill);
                } else {
                    $applications->orWhereJsonContains('skills', $skill);
                }
            }
        }

        $applications->orderBy('column_priority')->skip($skip)->take($this->perPage);
        $applications = $applications->get();

        $this->applications = $applications;

        if ($totalRecord <= ($skip + $this->perPage)) {
            $loadStatus = 'hide';
        } else {
            $loadStatus = 'show';
        }

        $view = view('admin.job-applications.load_more', $this->data)->render();

        return Reply::dataOnly(['view' => $view, 'load_more' => $loadStatus]);
    }

    public function getJobs(Request $request)
    {
        $jobs = Job::where('company_id', $request->companyId)->get();

        $html = '<option value="all">'.__('modules.jobApplication.allJobs').'</option>';

        foreach ($jobs as $job) {
            $html .= '<option title="'.ucfirst($job->title).'" value="'.$job->id.'">'.ucfirst($job->title).'</option>';
        }

        return Reply::dataOnly(['jobs' => $html]);
    }
}
