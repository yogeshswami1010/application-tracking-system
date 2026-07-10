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
        $boardColumns = ApplicationStatus::whereNull('job_id')->withCount(['applications as application_count' => function ($q) use ($request) {
         $q->where('job_applications.is_candidate', 0);
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

        $jobApplications = JobApplication::select(
            'job_applications.id',
            'job_applications.job_id',
            'job_applications.status_id',
            'job_applications.full_name',
            'job_applications.skills',
            'job_applications.location_id',   // ← this was the ambiguous one
            'job_applications.created_at'
        )
            ->with([
                'location',
                'job.skills',
                'status:id,status,color',
            ]);

        $jobApplications = $jobApplications->where('job_applications.is_candidate', 0);

        // Only show the newest application per email — older ones with the same
        // email are merged into it and only visible via the History tab.
        $jobApplications = $jobApplications->whereIn('job_applications.id', function ($sub) {
            $sub->selectRaw('MAX(ja2.id)')
                ->from('job_applications as ja2')
                ->where('ja2.is_candidate', 0)
                ->whereColumn('ja2.email', 'job_applications.email')
                ->groupBy('ja2.email');
        });

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
            $jobApplications = $jobApplications->where('job_applications.location_id', $request->location);
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

        // Load statuses for the action column: global defaults always first,
        // then any job-specific custom statuses appended after.
        $jobFilterId    = (int) $request->input('jobs', 0);
        $actionStatuses = ApplicationStatus::whereNull('job_id')->orderBy('position')->get();
        if ($jobFilterId > 0) {
            try {
                $jobSpecific = ApplicationStatus::where('job_id', $jobFilterId)->orderBy('position')->get();
                $globalIds   = $actionStatuses->pluck('id');
                $extra       = $jobSpecific->filter(fn($s) => !$globalIds->contains($s->id));
                $actionStatuses = $actionStatuses->concat($extra);
            } catch (\Exception $e) {}
        }

        // Build next-stage map from action statuses
        $nextMap = [];
        foreach ($actionStatuses as $i => $s) {
            $nextStatus = $actionStatuses->get($i + 1);
            if ($nextStatus) {
                $nextMap[$s->status] = [
                    'label' => ucwords(str_replace('_', ' ', $nextStatus->status)),
                    'id'    => $nextStatus->id,
                    'slug'  => $nextStatus->status,
                ];
            }
        }

        // Find rejected & applied — check action statuses first, then fall back to global
        $globalStatuses = ApplicationStatus::whereNull('job_id')->orderBy('position')->get();
        $rejectedStatus = $actionStatuses->firstWhere('status', 'rejected')
            ?? $globalStatuses->firstWhere('status', 'rejected');
        $appliedStatus  = $actionStatuses->firstWhere('status', 'applied')
            ?? $globalStatuses->firstWhere('status', 'applied');

        $canEdit   = $this->user->cans('edit_job_applications');
        $canView   = $this->user->cans('view_job_applications');
        $canDelete = $this->user->role_id === 1; // admin only

        return DataTables::of($jobApplications)
                ->addColumn('action', function ($row) use ($nextMap, $rejectedStatus, $appliedStatus, $canEdit, $canView, $canDelete) {
                $parts = [];
                $statusSlug = strtolower($row->status?->status ?? '');

                if ($canEdit) {

                // "Next stage" quick button — derived from the job's pipeline order
                if (isset($nextMap[$statusSlug]) && $statusSlug !== 'rejected') {
                    $nextLabel = $nextMap[$statusSlug]['label'];
                    $nextId    = $nextMap[$statusSlug]['id'];
                    $parts[] = '<button type="button" onclick="jaMoveOne(' . $row->id . ',' . $nextId . ')" class="ja-act-btn move" title="Move to ' . $nextLabel . '">' . $nextLabel . ' <i class="fa fa-arrow-right" style="font-size:10px;margin-left:2px;"></i></button>';
                }

                // "Move to any stage" dropdown — content is populated client-side
                // from jaStages so it always reflects the currently-selected job's pipeline.
                if (!in_array($statusSlug, ['rejected', 'hired'])) {
                    $dropdownId   = 'ja-move-drop-' . $row->id;
                    $dropdownHtml = '<div class="ja-move-wrap" style="position:relative;display:inline-flex;">';
                    $dropdownHtml .= '<button type="button" class="ja-act-btn" onclick="jaToggleDrop(\'' . $dropdownId . '\', this)" title="Move to stage" style="padding:5px 8px;border-radius:8px;"><i class="fa fa-chevron-down" style="font-size:10px;"></i></button>';
                    // Empty container — jaToggleDrop fills it from jaStages on open
                    $dropdownHtml .= '<div id="' . $dropdownId . '" class="ja-move-drop"'
                        . ' data-app-id="' . $row->id . '"'
                        . ' data-current-status="' . e($statusSlug) . '"'
                        . ' style="display:none;position:fixed;background:#fff;border:1.5px solid #E2DED8;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.13);z-index:99999;min-width:160px;max-height:220px;overflow-y:auto;">'
                        . '</div></div>';
                    $parts[] = $dropdownHtml;
                }

                // Reject button
                if (!in_array($statusSlug, ['rejected', 'hired']) && $rejectedStatus) {
                    $parts[] = '<button type="button" onclick="jaMoveOne(' . $row->id . ',' . $rejectedStatus->id . ')" class="ja-act-btn reject" title="Reject"><i class="fa fa-times"></i></button>';
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
            ->orderColumn('created_at', 'job_applications.created_at $1')  // ← add this
            ->rawColumns(['action', 'full_name', 'status'])
            ->addIndexColumn()
            ->make(true);
    }
    public function jobStatuses(Request $request)
    {
        $jobId = (int) $request->input('job_id', 0);

        // Always load global (default) statuses
        try {
            $global = ApplicationStatus::whereNull('job_id')->orderBy('position')->get();
        } catch (\Exception $e) {
            $global = ApplicationStatus::orderBy('position')->get();
        }

        // If a job is selected, append its custom statuses after the global ones
        $statuses = $global;
        if ($jobId > 0) {
            try {
                $jobSpecific = ApplicationStatus::where('job_id', $jobId)->orderBy('position')->get();
                // Only append job-specific ones that are not already in global (by ID)
                $globalIds = $global->pluck('id');
                $extra     = $jobSpecific->filter(fn($s) => !$globalIds->contains($s->id));
                $statuses  = $global->concat($extra);
            } catch (\Exception $e) {
                // job_id column missing — just use global
            }
        }

        return Reply::dataOnly([
            'statuses' => $statuses->map(fn ($s) => [
                'id'    => $s->id,
                'slug'  => $s->status,
                'label' => ucfirst($s->status),
                'color' => $s->color ?? '#2563eb',
            ])->values(),
        ]);
    }

    public function stageCounts(Request $request)
    {
        $company        = $request->input('company', 'all');
        $jobs           = $request->input('jobs', 'all');
        $location       = $request->input('location', 'all');
        $questions      = $request->input('questions', 'all');
        $questionValue  = $request->input('question_value', '');

        // ── Counts per stage ──────────────────────────────────────────
        $countQuery = JobApplication::select(
                'job_applications.status_id',
                DB::raw('COUNT(*) as cnt')
            )
            ->where('job_applications.is_candidate', 0);

        $countQuery->whereIn('job_applications.id', function ($sub) {
            $sub->selectRaw('MAX(ja2.id)')->from('job_applications as ja2')
                ->where('ja2.is_candidate', 0)
                ->whereColumn('ja2.email', 'job_applications.email')
                ->groupBy('ja2.email');
        });

        if ($company !== 'all' && $company !== '') {
            $countQuery->join('jobs as j_co', 'j_co.id', '=', 'job_applications.job_id')
                    ->where('j_co.company_id', $company);
        }
        if ($jobs !== 'all' && $jobs !== '') {
            $countQuery->where('job_applications.job_id', $jobs);
        }
        if ($location !== 'all' && $location !== '') {
            $countQuery->where('job_applications.location_id', $location);
        }
        if ($questions !== 'all' && $questions !== '') {
            $countQuery->whereHas('job.questions', function ($q) use ($questions) {
                $q->where('question_id', $questions);
            });
            if ($questionValue !== '') {
                $countQuery->whereHas('answers', function ($q) use ($questions, $questionValue) {
                    $q->where('question_id', $questions)
                    ->where('answer', 'LIKE', '%' . $questionValue . '%');
                });
            }
        }

        $counts = $countQuery
            ->groupBy('job_applications.status_id')
            ->pluck('cnt', 'job_applications.status_id');

        // ── KO count ──────────────────────────────────────────────────
        $koQuery = JobApplication::where('job_applications.is_candidate', 0)
            ->whereIn('job_applications.id', function ($sub) {
                $sub->selectRaw('MAX(ja2.id)')->from('job_applications as ja2')
                    ->where('ja2.is_candidate', 0)
                    ->whereColumn('ja2.email', 'job_applications.email')
                    ->groupBy('ja2.email');
            })
            ->whereHas('answers', function ($q) {
                $q->whereHas('question', function ($qq) {
                    $qq->where('type', 'radio')
                    ->where('is_knockout', 1)
                    ->whereNotNull('knockout_answer');
                })->whereColumn(
                    DB::raw('LOWER(TRIM(job_application_answers.answer))'),
                    DB::raw('LOWER(TRIM((SELECT knockout_answer FROM questions WHERE questions.id = job_application_answers.question_id LIMIT 1)))')
                );
            });

        if ($company !== 'all' && $company !== '') {
            $koQuery->join('jobs as j_ko', 'j_ko.id', '=', 'job_applications.job_id')
                    ->where('j_ko.company_id', $company);
        }
        if ($jobs !== 'all' && $jobs !== '') {
            $koQuery->where('job_applications.job_id', $jobs);
        }
        if ($location !== 'all' && $location !== '') {
            $koQuery->where('job_applications.location_id', $location);
        }

        $koCount = $koQuery->count('job_applications.id');

        return Reply::dataOnly(['counts' => $counts, 'ko_count' => $koCount]);
    }

        public function bulkStatusUpdate(Request $request)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $apps = JobApplication::whereIn('id', $request->ids)->get(['id', 'status_id']);
        foreach ($apps as $app) {
            $this->logStatusChange($app->id, $app->status_id, (int) $request->status_id, $this->user->id);
        }

        JobApplication::whereIn('id', $request->ids)->update(['status_id' => $request->status_id]);
        return Reply::success(__('messages.updatedSuccessfully'));
    }

    public function bulkRestoreKnockout(Request $request)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $appliedStatus = ApplicationStatus::where('status', 'applied')->first();

        if ($appliedStatus) {
            $apps = JobApplication::whereIn('id', $request->ids)->get(['id', 'status_id']);
            foreach ($apps as $app) {
                $this->logStatusChange($app->id, $app->status_id, $appliedStatus->id, $this->user->id);
            }

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
            //Notification::send($interviewSchedule->employees, new ScheduleInterview($jobApplication, $meetings));
            if (false) Notification::send($interviewSchedule->employees, new ScheduleInterview($jobApplication, $meetings));

        }
        if (! $request->interview_type) {
            $meeting = '';
        }
        // mail to candidate for inform interview schedule
        //Notification::send($jobApplication, new CandidateScheduleInterview($jobApplication, $interviewSchedule, $meetings));
        if (false) Notification::send($jobApplication, new CandidateScheduleInterview($jobApplication, $interviewSchedule, $meetings));

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
        $jobApplication->full_name = collect(explode(' ', trim($request->full_name)))
        ->map(fn($word) => ucfirst(strtolower($word)))
        ->join(' ');
        $jobApplication->job_id = $request->job_id;
       $jobApplication->status_id = ($request->entry_type === 'candidate') ? null : 1; // applied status id
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
                $oldStatusId = $jobApplication->status_id;
                $jobApplication->status_id = $rejectedStatus->id;
                $jobApplication->save();
                $this->logStatusChange($jobApplication->id, $oldStatusId, $rejectedStatus->id, null);
            }
        }
        // ── Process skills from bulk create form ──
        if ($request->filled('skills')) {
            $skillNames = array_filter(array_map('trim', explode(',', $request->skills)));
            $skillIds   = [];

            foreach ($skillNames as $name) {
                if (empty($name)) continue;
                $skill = \App\Skill::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                if (!$skill) {
                    $skill = \App\Skill::create(['name' => $name]);
                }
                $skillIds[] = (string) $skill->id;
            }

            if (!empty($skillIds)) {
                $jobApplication->skills = array_values(array_unique($skillIds));
                $jobApplication->save();
            }
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

            return Reply::successWithData(__('menu.jobApplications').' '.__('messages.createdSuccessfully'),
                $request->input('is_bulk') ? ['id' => $jobApplication->id] : ['redirect' => route('admin.job-applications.table')]
            );
            
    }

    public function update(UpdateJobApplication $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $mailSetting = ApplicationSetting::select('id', 'mail_setting')->first()->mail_setting;

        $jobApplication = JobApplication::with(['documents'])->findOrFail($id);
        $jobApplication->full_name = collect(explode(' ', trim($request->full_name)))
        ->map(fn($word) => ucfirst(strtolower($word)))
        ->join(' ');
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

        // ── Process skills from bulk create form ──
        if ($request->filled('skills')) {
            $skillNames = array_filter(array_map('trim', explode(',', $request->skills)));
            $skillIds   = [];

            foreach ($skillNames as $name) {
                if (empty($name)) continue;

                // Try to find existing skill by name (case-insensitive)
                $skill = \App\Skill::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

                if (!$skill) {
                    // Create it if it doesn't exist
                    $skill = \App\Skill::create(['name' => $name]);
                }

                $skillIds[] = (string) $skill->id;
            }

            if (!empty($skillIds)) {
                $jobApplication->skills = $skillIds;
                $jobApplication->save();
            }
        }

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

        // if ($mailSetting[$request->status_id]['status'] && $isStatusDirty) {
        //     Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
        // }
        if (false && $mailSetting[$request->status_id]['status'] && $isStatusDirty) {
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
        abort_if(!$this->user->hasRole('admin'), 403);

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
        ->with([
            'schedule',
            'schedule.employee',
            'schedule.comments.user',
            'notes',
            'onboard',
            'status',
            'location',
            'job',
            'job.company',
            'job.location',
            'statusHistories.fromStatus',
            'statusHistories.toStatus',
            'statusHistories.user',
        ])
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
                    $oldStatusId = $task->status_id;
                    $task->column_priority = $priorities[$key];
                    $task->status_id = $boardColumnIds[$key];
                    $task->save();

                    $this->logStatusChange($task->id, $oldStatusId, (int) $boardColumnIds[$key], $this->user->id);
                }
            }

            // Send notification to candidate on update status
            // if ($mailSetting[$boardColumnIds[0]]['status'] && $request->draggedTaskId != 0) {
            //     $jobApplication = JobApplication::findOrFail($request->draggedTaskId);
            //     Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
            // }
            if (false && $mailSetting[$boardColumnIds[0]]['status'] && $request->draggedTaskId != 0) {
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
        $oldStatusId = $jobApplication->status_id;
        $isStatusDirty = $jobApplication->isDirty('status_id');
        $jobApplication->status_id = (int) $validated['status_id'];
        $jobApplication->save();

        $this->logStatusChange($jobApplication->id, $oldStatusId, (int) $validated['status_id'], $this->user->id);

        $mailSetting = ApplicationSetting::select('id', 'mail_setting')->first()?->mail_setting ?? [];
        $statusId = (int) $validated['status_id'];

        // if ($isStatusDirty && is_array($mailSetting) && isset($mailSetting[$statusId]['status']) && $mailSetting[$statusId]['status'] === true) {
        //     Notification::send($jobApplication, new CandidateStatusChange($jobApplication));
        // }
        if (false && $isStatusDirty && is_array($mailSetting) && isset($mailSetting[$statusId]['status']) && $mailSetting[$statusId]['status'] === true) {
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

        $skillIds = [];

        foreach ((array) $request->skills as $val) {
            if (str_starts_with((string) $val, 'new:')) {
                $name  = trim(substr($val, 4));
                $skill = Skill::firstOrCreate(['name' => $name]);
                $skillIds[] = $skill->id;
            } else {
                $skillIds[] = $val;
            }
        }

        $application->skills = $skillIds;
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
    public function parseSkills(Request $request, $id)
    {
        \Log::info('parseSkills called for id: ' . $id);

        $application = JobApplication::with('job')->findOrFail($id);

        try {
            // Read resume file directly from disk
            $doc = $application->documents()->where('name', 'Resume')->first();
            if (!$doc || empty($doc->hashname)) {
                return response()->json(['status' => 'error', 'message' => 'No resume found for this applicant.']);
            }

            $filePath = public_path('user-uploads/documents/' . $application->id . '/' . $doc->hashname);
            if (!is_readable($filePath)) {
                return response()->json(['status' => 'error', 'message' => 'Resume file not found on disk.']);
            }

            $ext = strtolower(pathinfo($doc->hashname, PATHINFO_EXTENSION)) ?: 'pdf';

            // Extract text from file
            $extractor = new \App\Services\ResumeTextExtractor();
            $cvText    = $extractor->extractFromPath($filePath, $ext);

            if (empty($cvText)) {
                return response()->json([
                    'status'         => 'success',
                    'matched_skills' => [],
                    'new_skills'     => [],
                    'warning'        => 'Could not read text from this resume (it may be a scanned or image-based PDF).',
                ]);
            }

            // Save to cv_text for AI search indexing
            if (empty($application->cv_text)) {
                $application->cv_text = mb_substr($cvText, 0, 65000);
                $application->save();
            }

            $allSkillNames = mb_substr(\App\Skill::pluck('name')->implode(', '), 0, 2000);

            $prompt = "You are a skill extractor. Read this resume and extract ALL skills mentioned anywhere including technical skills, software, tools, programming languages, soft skills.\n\n"
                . "Compare each skill against this list: [{$allSkillNames}]\n\n"
                . "Resume text:\n" . mb_substr($cvText, 0, 4000) . "\n\n"
                . "You MUST respond with ONLY a raw JSON object, no explanation, no markdown, no backticks:\n"
                . "{\"matched\": [\"exact name from list\"], \"new\": [\"skills not in list\"]}\n\n"
                . "If no skills found still return: {\"matched\": [], \"new\": []}";

            \Log::info('parseSkills calling DeepSeek');
            $text   = $this->callDeepSeek($prompt);
            $parsed = json_decode($text, true);

            \Log::info('parseSkills parsed: ' . json_encode($parsed));

            $matchedSkills = \App\Skill::whereIn('name', $parsed['matched'] ?? [])->get(['id', 'name']);

            return response()->json([
                'status'         => 'success',
                'matched_skills' => $matchedSkills,
                'new_skills'     => $parsed['new'] ?? [],
            ]);

        } catch (\Throwable $e) {
            \Log::error('parseSkills error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() . ' (line ' . $e->getLine() . ')',
            ]);
        }
    }

    /**
     * Send a prompt to DeepSeek API and return the response text.
     * Uses the OpenAI-compatible chat completions endpoint.
     */
    private function callDeepSeek(string $prompt): string
    {
        $key   = config('services.deepseek.key');
        $model = config('services.deepseek.model', 'deepseek-chat');

        if (!$key) {
            throw new \RuntimeException('DEEPSEEK_API_KEY is not configured.');
        }

        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ])
            ->post('https://api.deepseek.com/chat/completions', [
                'model'       => $model,
                'max_tokens'  => 1024,
                'messages'    => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('DeepSeek error: HTTP ' . $response->status() . ' — ' . $response->body());
        }

        $text = $response->json('choices.0.message.content') ?? '';
        $text = trim(preg_replace('/```json|```/', '', $text) ?? '');

        return $text;
    }
    /**
     * Parse extracted CV text into the structured applicant schema using DeepSeek.
     * Returns null on failure — caller marks cv_parse_failed so it isn't retried every batch.
     */
    private function parseCvStructured(string $cvText): ?array
    {
        $schema = '{"personal":{"name":"","email":"","phone":"","location":{"city":"","province":"","country":""}},'
            . '"headline":"","total_experience":{"years":0,"months":0},"job_titles":[],"skills":[],'
            . '"certifications":[],"education":[{"degree":"","field":"","school":""}],'
            . '"employment":[{"company":"","title":"","start":"","end":"","duration_years":0}],'
            . '"languages":[],"availability":{"notice_period":""},"resume_summary":""}';

        $prompt = "Extract structured data from this resume/CV text. Return ONLY minified JSON matching this exact schema, no markdown, no backticks, no explanation:\n\n"
            . $schema . "\n\n"
            . "Rules:\n"
            . "- job_titles: the person's actual title(s) PLUS close synonyms a recruiter might search for (e.g. \"Welder\" -> also \"Metal Fabricator\", \"Production Welder\" if the CV supports it)\n"
            . "- total_experience: compute from employment history dates, don't guess\n"
            . "- If a field is not present in the CV, use empty string / empty array / 0 — never invent data\n"
            . "- skills: normalized short names (e.g. \"MIG Welding\" not \"mig-welding exp.\")\n\n"
            . "CV TEXT:\n" . mb_substr($cvText, 0, 10000);

        try {
            $text   = $this->callDeepSeek($prompt);
            $parsed = json_decode($text, true);

            if (!is_array($parsed) || !isset($parsed['personal'])) {
                \Log::warning('parseCvStructured: invalid JSON shape: ' . $text);
                return null;
            }

            return $parsed;
        } catch (\Throwable $e) {
            \Log::warning('parseCvStructured failed: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Extract plain text from a downloaded resume file and save it to cv_text.
     * Called after parseSkills so the file bytes are already in memory.
     */
    private function saveCvTextFromBytes(JobApplication $application, string $fileBytes, string $ext): void
    {
        try {
            $tmpPath = sys_get_temp_dir() . '/cv_idx_' . $application->id . '_' . time() . '.' . $ext;
            file_put_contents($tmpPath, $fileBytes);
            try {
                $extractor = new \App\Services\ResumeTextExtractor();
                $text = $extractor->extractFromPath($tmpPath, $ext);
                if ($text !== '') {
                    JobApplication::where('id', $application->id)
                        ->update(['cv_text' => mb_substr($text, 0, 65000)]);
                }
            } finally {
                if (file_exists($tmpPath)) @unlink($tmpPath);
            }
        } catch (\Throwable $e) {
            \Log::warning('saveCvTextFromBytes failed for app ' . $application->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Bulk index CV text for all applicants that have a resume but no cv_text yet.
     * Processes up to 20 at a time to avoid timeout.
     */
    public function indexCvs(Request $request)
    {
        abort_if(!$this->user->cans('view_job_applications'), 403);

        $limit = min((int) $request->input('limit', 15), 30); // lower limit — now 2 AI calls per applicant

        // Step 1: text extraction for anyone missing cv_text (unchanged behavior)
        $needsText = JobApplication::whereNull('cv_text')
            ->whereHas('documents', fn($q) => $q->where('name', 'Resume'))
            ->select('id')
            ->limit($limit)
            ->get();

        $processed = 0;
        $failed    = 0;

        foreach ($needsText as $stub) {
            $app = JobApplication::find($stub->id);
            if (!$app) { $failed++; continue; }
            try {
                $doc = $app->documents()->where('name', 'Resume')->first();
                if (!$doc || empty($doc->hashname)) { $failed++; continue; }
                $filePath = public_path('user-uploads/documents/' . $app->id . '/' . $doc->hashname);
                if (!is_readable($filePath)) { $failed++; continue; }
                $ext = strtolower(pathinfo($doc->hashname, PATHINFO_EXTENSION)) ?: 'pdf';
                if (!in_array($ext, ['pdf','docx','xlsx','xls','rtf','txt'])) $ext = 'pdf';
                $bytes = file_get_contents($filePath);
                if ($bytes === false || $bytes === '') { $failed++; continue; }
                $this->saveCvTextFromBytes($app, $bytes, $ext);
                if (JobApplication::where('id', $app->id)->whereNotNull('cv_text')->exists()) {
                    $processed++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                \Log::warning('indexCvs text extraction failed for app ' . $app->id . ': ' . $e->getMessage());
                $failed++;
            }
        }

        // Step 2: structured parse for anyone with cv_text but no parsed_cv_data yet
        $needsParse = JobApplication::whereNotNull('cv_text')
            ->where('cv_text', '!=', '')
            ->whereNull('cv_parsed_at')
            ->where('cv_parse_failed', false)
            ->select('id', 'cv_text')
            ->limit($limit)
            ->get();

        $parsedCount = 0;
        $parseFailed = 0;

        foreach ($needsParse as $app) {
            $data = $this->parseCvStructured($app->cv_text);

            if (!$data) {
                JobApplication::where('id', $app->id)->update([
                    'cv_parse_failed' => true,
                    'cv_parsed_at'    => Carbon::now(),
                ]);
                $parseFailed++;
                continue;
            }

            $years = (float) ($data['total_experience']['years'] ?? 0)
                + ((float) ($data['total_experience']['months'] ?? 0) / 12);

            $locationParts = array_filter([
                $data['personal']['location']['city']     ?? null,
                $data['personal']['location']['province'] ?? null,
                $data['personal']['location']['country']  ?? null,
            ]);

            JobApplication::where('id', $app->id)->update([
                'parsed_cv_data'      => json_encode($data),
                'cv_experience_years' => round($years, 1),
                'cv_job_titles'       => implode(', ', (array) ($data['job_titles'] ?? [])),
                'cv_skills_text'      => implode(', ', (array) ($data['skills'] ?? [])),
                'cv_location_text'    => implode(', ', $locationParts),
                'cv_parsed_at'        => Carbon::now(),
                'cv_parse_failed'     => false,
            ]);

            $parsedCount++;
        }

        $remainingText  = JobApplication::whereNull('cv_text')
            ->whereHas('documents', fn($q) => $q->where('name', 'Resume'))
            ->count();

        $remainingParse = JobApplication::whereNotNull('cv_text')
            ->where('cv_text', '!=', '')
            ->whereNull('cv_parsed_at')
            ->where('cv_parse_failed', false)
            ->count();

        return Reply::dataOnly([
            'processed' => $processed + $parsedCount,
            'failed'    => $failed + $parseFailed,
            'remaining' => $remainingText + $remainingParse,
        ]);
    }
    public function assignJob(Request $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $request->validate(['job_id' => 'required|exists:jobs,id']);

        $application = JobApplication::with('job:id,title')->findOrFail($id);

        $newJobId = (int) $request->job_id;
        $oldJobId = (int) $application->job_id;

        // No-op — nothing to log or save
        if ($oldJobId === $newJobId) {
            return Reply::success('Job assigned successfully.');
        }

        $oldJobTitle = $application->job?->title ?? 'Unassigned';
        $newJobTitle = Job::find($newJobId)?->title ?? 'Unknown job';

        $application->job_id = $newJobId;
        $application->save();

        // Log into the History tab. Uses the applicant's current status for
        // both from/to (same trick as toggleMarketing) so the FK is satisfied
        // without needing a schema change — the notes field carries the message.
        if ($application->status_id) {
            \App\JobApplicationStatusHistory::create([
                'job_application_id' => $application->id,
                'from_status_id'     => $application->status_id,
                'to_status_id'       => $application->status_id,
                'user_id'            => $this->user->id,
                'notes'              => 'Switched job from "' . ucwords($oldJobTitle) . '" to "' . ucwords($newJobTitle) . '"',
            ]);
        }

        return Reply::success('Job assigned successfully.');
    }
    public function bulkParseResume(Request $request)
    {
        abort_if(! $this->user->cans('add_job_applications'), 403);

        if (!$request->hasFile('resume')) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded.']);
        }

        try {
            $file = $request->file('resume');

            // Extract text from the uploaded file (returns empty string on parse errors)
            $extractor = new \App\Services\ResumeTextExtractor();
            try {
                $cvText = $extractor->extract($file);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }

            // If text extraction failed (malformed/scanned PDF), return empty result
            // so the user can fill in the form manually
            if (empty($cvText)) {
                return response()->json([
                    'status'         => 'success',
                    'full_name'      => '',
                    'email'          => '',
                    'phone'          => '',
                    'address'        => '',
                    'matched_skills' => [],
                    'new_skills'     => [],
                    'warning'        => 'Could not read text from this file (it may be a scanned or image-based PDF). Please fill in the details manually.',
                ]);
            }

            $allSkillNames = mb_substr(\App\Skill::pluck('name')->implode(', '), 0, 2000);

            $prompt = "You are a CV parser. Extract the following fields from this resume text and extract skills.\n\n"
                . "Skills list to match against: [{$allSkillNames}]\n\n"
                . "Resume text:\n" . mb_substr($cvText, 0, 4000) . "\n\n"
                . "Respond ONLY with a raw JSON object, no explanation, no markdown, no backticks:\n"
                . "{\"full_name\": \"\", \"email\": \"\", \"phone\": \"\", \"address\": \"\", \"matched_skills\": [\"exact name from list\"], \"new_skills\": [\"skills not in list\"]}\n\n"
                . "Rules:\n"
                . "- full_name: person's full name in Title Case\n"
                . "- email: email address or empty string\n"
                . "- phone: phone number or empty string\n"
                . "- address: city/state/country or full address if found, or empty string\n"
                . "- matched_skills: skills that exactly match the provided list\n"
                . "- new_skills: skills found in CV but NOT in the list\n"
                . "- If a field is not found, use empty string or empty array";

            $text   = $this->callDeepSeek($prompt);
            $parsed = json_decode($text, true);

            if (!$parsed) {
                return response()->json(['status' => 'error', 'message' => 'AI could not parse the CV.']);
            }

            $matchedSkills = \App\Skill::whereIn('name', $parsed['matched_skills'] ?? [])->get(['id', 'name']);

            return response()->json([
                'status'         => 'success',
                'full_name'      => $parsed['full_name']     ?? '',
                'email'          => $parsed['email']         ?? '',
                'phone'          => $parsed['phone']         ?? '',
                'address'        => $parsed['address']       ?? '',
                'matched_skills' => $matchedSkills,
                'new_skills'     => $parsed['new_skills']    ?? [],
            ]);

        } catch (\Throwable $e) {
            \Log::error('bulkParseResume error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function updateBasicInfo(Request $request, $id)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $request->validate([
            'full_name' => 'required|string|max:191',
            'email'     => 'required|email|max:191',
            'phone'     => 'nullable|string|max:50',
        ]);

        $application = JobApplication::findOrFail($id);
        $application->full_name = collect(explode(' ', trim($request->full_name)))
            ->map(fn($w) => ucfirst(strtolower($w)))->join(' ');
        $application->email = $request->email;
        $application->phone = $request->phone;
        $application->save();

        return Reply::successWithData(__('messages.updatedSuccessfully'), [
            'full_name' => $application->full_name,
            'email'     => $application->email,
            'phone'     => $application->phone,
        ]);
    }
    public function aiSearchPage()
    {
        abort_if(!$this->user->cans('view_job_applications'), 403);
        return view('admin.ai-search.index', $this->data);
    }

    /**
     * Parse a natural-language search query using Ollama (local AI).
     * Falls back to keyword passthrough if Ollama is unavailable.
     */
    public function aiParseQuery(Request $request)
    {
        abort_if(!$this->user->cans('view_job_applications'), 403);

        $query = trim($request->input('query', ''));
        if (!$query) {
            return Reply::dataOnly(['skills' => [], 'keywords' => [], 'roles' => [], 'location' => '', 'min_experience' => 0]);
        }

        $prompt = 'Parse this job candidate search query: "' . $query . "\"\n\n"
            . "Extract:\n"
            . "- skills: technical skills, tools, technologies the candidate would have\n"
            . "- keywords: other relevant keywords from their CV\n"
            . "- roles: job titles / roles\n"
            . "- location: city or region mentioned (empty string if none)\n"
            . "- min_experience: minimum years of experience as a number (0 if not mentioned)\n\n"
            . "Be comprehensive with skills/keywords — include synonyms.\n"
            . "Return ONLY minified JSON, no explanation:\n"
            . '{"skills":["skill1"],"keywords":["kw1"],"roles":["role1"],"location":"toronto","min_experience":5}';

        try {
            $text   = $this->callDeepSeek($prompt);
            $parsed = json_decode($text, true);

            if (!is_array($parsed)) {
                throw new \RuntimeException('Invalid JSON from Ollama');
            }

            return Reply::dataOnly([
                'skills'         => (array) ($parsed['skills']         ?? []),
                'keywords'       => (array) ($parsed['keywords']       ?? []),
                'roles'          => (array) ($parsed['roles']          ?? []),
                'location'       => (string) ($parsed['location']      ?? ''),
                'min_experience' => (int) ($parsed['min_experience']   ?? 0),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('DeepSeek aiParseQuery failed: ' . $e->getMessage());
            // Graceful fallback — treat raw query as keyword
            return Reply::dataOnly([
                'skills'         => [],
                'keywords'       => [$query],
                'roles'          => [$query],
                'location'       => '',
                'min_experience' => 0,
                'fallback'       => true,
            ]);
        }
    }

   public function aiSearchResults(Request $request)
    {
        abort_if(!$this->user->cans('view_job_applications'), 403);

        $terms     = array_filter(array_map('trim', (array) $request->input('terms', [])));
        $roles     = array_filter(array_map('trim', (array) $request->input('roles', [])));
        $query     = trim($request->input('query', ''));
        $location  = trim($request->input('location', ''));
        $minExp    = (float) $request->input('min_experience', 0);

        if (empty($terms) && empty($roles) && empty($query)) {
            return Reply::dataOnly(['results' => []]);
        }

        $searchTerms = array_values(array_unique(array_merge($terms, $roles)));

        $matchedSkillIds = \App\Skill::where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('name', 'LIKE', '%' . $term . '%');
            }
        })->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $applicants = \App\JobApplication::select(
                'job_applications.id',
                'job_applications.full_name',
                'job_applications.skills',
                'job_applications.status_id',
                'job_applications.job_id',
                'job_applications.location_id',
                'job_applications.address',
                'job_applications.city',
                'job_applications.state',
                'job_applications.country',
                'job_applications.cv_text',
                'job_applications.cv_experience_years',
                'job_applications.cv_job_titles',
                'job_applications.cv_skills_text',
                'job_applications.cv_location_text',
                'job_applications.cv_parsed_at',
                'job_applications.created_at'
            )
            ->with(['status:id,status,color', 'job:id,title', 'location:id,location'])
            ->where('job_applications.is_candidate', 0)
            ->where(function ($q) use ($searchTerms, $matchedSkillIds, $location) {
                foreach ($matchedSkillIds as $sid) {
                    $q->orWhereJsonContains('job_applications.skills', $sid);
                }
                foreach ($searchTerms as $term) {
                    $q->orWhere('job_applications.full_name',    'LIKE', '%'.$term.'%');
                    $q->orWhere('job_applications.cv_job_titles', 'LIKE', '%'.$term.'%');
                    $q->orWhere('job_applications.cv_skills_text','LIKE', '%'.$term.'%');
                    $q->orWhere('job_applications.cv_text',      'LIKE', '%'.$term.'%');
                }
                if ($location) {
                    $q->orWhere('job_applications.cv_location_text', 'LIKE', '%'.$location.'%');
                    $q->orWhere('job_applications.city',              'LIKE', '%'.$location.'%');
                    $q->orWhere('job_applications.state',             'LIKE', '%'.$location.'%');
                    $q->orWhereHas('location', fn($lq) => $lq->where('location', 'LIKE', '%'.$location.'%'));
                }
            })
            ->limit(150)
            ->get();

        $allSkillIds = collect($applicants->pluck('skills')->flatten())
            ->map(fn($id) => (int) $id)->unique()->filter()->values()->toArray();
        $allSkillsMap = \App\Skill::whereIn('id', $allSkillIds)->pluck('name', 'id');

        $results = $applicants->map(function ($app) use ($searchTerms, $roles, $allSkillsMap, $location, $minExp) {
            $score          = 0;
            $matchedSkills  = [];
            $allSkills      = [];
            $cvJobTitles    = array_filter(array_map('trim', explode(',', (string) $app->cv_job_titles)));
            $cvSkillsList   = array_filter(array_map('trim', explode(',', (string) $app->cv_skills_text)));
            $hasStructured  = !empty($app->cv_parsed_at);

            // ── Manually-tagged skills (existing Skill model) — 25 pts ──
            foreach ((array) $app->skills as $sid) {
                $name = $allSkillsMap[(int) $sid] ?? null;
                if (!$name) continue;
                $allSkills[] = $name;
                foreach ($searchTerms as $term) {
                    if (stripos($name, $term) !== false) {
                        $score += 8;
                        if (!in_array($name, $matchedSkills)) $matchedSkills[] = $name;
                        break;
                    }
                }
            }

            // ── AI-parsed CV skills — 25 pts, only if indexed ──
            if ($hasStructured && $cvSkillsList) {
                foreach ($searchTerms as $term) {
                    foreach ($cvSkillsList as $skill) {
                        if (stripos($skill, $term) !== false) {
                            $score += 6;
                            if (!in_array($skill, $matchedSkills)) $matchedSkills[] = $skill;
                            $allSkills[] = $skill;
                            break;
                        }
                    }
                }
            }

            // ── Role / job title match — 30 pts, strongest signal ──
            $roleTerms = $roles ?: $searchTerms;
            if ($hasStructured && $cvJobTitles) {
                foreach ($roleTerms as $term) {
                    foreach ($cvJobTitles as $title) {
                        if (stripos($title, $term) !== false || stripos($term, $title) !== false) {
                            $score += 30;
                            break 2;
                        }
                    }
                }
            } else {
                // fallback for not-yet-indexed applicants: raw text search
                foreach ($roleTerms as $term) {
                    if (stripos((string) $app->cv_text, $term) !== false) { $score += 12; break; }
                }
            }

            // ── Name / raw text fallback (kept low weight — supporting evidence only) ──
            foreach ($searchTerms as $term) {
                if (stripos($app->full_name, $term) !== false) $score += 6;
                if ($app->cv_text && stripos($app->cv_text, $term) !== false) $score += 2;
            }

            // ── Location — 15 pts ──
            if ($location !== '') {
                $locLower = strtolower($location);
                $locationHaystack = strtolower(trim(
                    ($app->cv_location_text ?? '') . ' ' . $app->city . ' ' . $app->state . ' ' . $app->country
                    . ' ' . ($app->location?->location ?? '') . ' ' . $app->address
                ));
                if (str_contains($locationHaystack, $locLower)) {
                    $score += 15;
                }
            } else {
                $score += 5; // no location filter given, don't penalize
            }

            // ── Experience — 15 pts, uses AI-computed years when available ──
            if ($minExp > 0) {
                if ($hasStructured && $app->cv_experience_years !== null) {
                    $years = (float) $app->cv_experience_years;
                    if ($years >= $minExp) $score += 15;
                    elseif ($years >= $minExp - 1) $score += 8;
                } elseif ($app->cv_text && preg_match_all('/(\d+)\s*\+?\s*(?:year|yr)/i', $app->cv_text, $m)) {
                    // fallback for not-yet-structured-parsed applicants
                    $maxFound = max(array_map('intval', $m[1]));
                    if ($maxFound >= $minExp) $score += 10;
                    elseif ($maxFound >= $minExp - 1) $score += 4;
                }
            } else {
                $score += 5;
            }

            if ($score <= 0) return null;
            $score = min(99, max(5, $score));

            return [
                'id'             => $app->id,
                'full_name'      => $app->full_name,
                'job_title'      => $cvJobTitles[0] ?? ($app->job?->title ?? '—'),
                'location'       => $app->cv_location_text ?: ($app->location?->location ?? trim("{$app->city}, {$app->state}", ', ')) ?: '—',
                'status'         => ucwords(str_replace('_', ' ', $app->status?->status ?? '—')),
                'status_color'   => $app->status?->color ?? '#6b7280',
                'score'          => $score,
                'matched_skills' => array_values(array_unique($matchedSkills)),
                'all_skills'     => array_values(array_unique($allSkills)),
                'has_cv'         => $hasStructured,
                'created_at'     => $app->created_at?->toDateString(),
            ];
        })
        ->filter()
        ->sortByDesc('score')
        ->values()
        ->toArray();

        return Reply::dataOnly(['results' => $results]);
    }
    private function logStatusChange(int $jobApplicationId, ?int $fromStatusId, int $toStatusId, ?int $userId = null): void
    {
        if ($fromStatusId === $toStatusId) {
            return; // no-op move, don't log
        }

        \App\JobApplicationStatusHistory::create([
            'job_application_id' => $jobApplicationId,
            'from_status_id'     => $fromStatusId,
            'to_status_id'       => $toStatusId,
            'user_id'            => $userId,
        ]);
    }
    /**
     * Toggle an applicant's "Candidate Marketing" flag on/off.
     * Used by the button on the applicant profile (job-applications/show.blade.php).
     */
    public function toggleMarketing(Request $request, $id)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $application = JobApplication::withTrashed()->findOrFail($id);

        $turningOn = !$application->is_marketing;

        $application->is_marketing = $turningOn ? 1 : 0;

        if ($turningOn) {
            $application->marketing_added_at = Carbon::now();
            if ($request->filled('marketing_label')) {
                $application->marketing_label = trim($request->marketing_label);
            }
        } else {
            // Switching off clears the timestamp but keeps the label in case they
            // re-enable later and want it pre-filled.
            $application->marketing_added_at = null;
        }

        $application->save();

        // Record in the history tab.
        // Reuse the applicant's current status for both from/to so the FK
        // on to_status_id is satisfied without altering the schema.
        // The notes column carries the description shown in the history view.
        if ($application->status_id) {
            \App\JobApplicationStatusHistory::create([
                'job_application_id' => $application->id,
                'from_status_id'     => $application->status_id,
                'to_status_id'       => $application->status_id,
                'user_id'            => $this->user->id,
                'notes'              => $turningOn ? 'Added to Candidate Marketing' : 'Removed from Candidate Marketing',
            ]);
        }

        return Reply::successWithData(
            $turningOn ? 'Added to Candidate Marketing.' : 'Removed from Candidate Marketing.',
            [
                'is_marketing'    => $turningOn ? 1 : 0,
                'marketing_label' => (string) $application->marketing_label,
            ]
        );
    }

    /**
     * Save/update just the marketing label without toggling the flag.
     * Used when the label input is edited while marketing is already on.
     */
    public function updateMarketingLabel(Request $request, $id)
    {
        abort_if(!$this->user->cans('edit_job_applications'), 403);

        $application = JobApplication::withTrashed()->findOrFail($id);
        $application->marketing_label = trim((string) $request->marketing_label);
        $application->save();

        return Reply::success('Label updated.');
    }
}
