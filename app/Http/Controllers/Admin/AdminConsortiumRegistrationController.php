<?php

namespace App\Http\Controllers\Admin;

use App\ConsortiumRegistration;
use App\ApplicationStatus;
use App\Job;
use App\JobApplication;
use App\JobApplicationStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminConsortiumRegistrationController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Consortium Registrations';
        $this->pageIcon = 'icon-user-follow';
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'gender' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'array'],
            'city.*' => ['string', 'max:100'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'available_weekends' => ['nullable', 'in:0,1'],
            'night_shifts' => ['nullable', 'in:0,1'],
        ]);

        $query = ConsortiumRegistration::query()
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('created_at', $filters['month']))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('created_at', $filters['year']))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $filters['gender']))
            ->when(!empty($filters['city']), fn ($q) => $q->whereIn('city', $filters['city']))
            ->when($request->filled('job_type'), fn ($q) => $q->where('preferred_job_type', $filters['job_type']))
            ->when($request->filled('available_weekends'), fn ($q) => $q->where('available_weekends', (int) $filters['available_weekends']))
            ->when($request->filled('night_shifts'), fn ($q) => $q->where('available_night_shifts', (int) $filters['night_shifts']));

        $this->registrations = $query->latest()->paginate(25)->withQueryString();
        $this->unreviewedCount = ConsortiumRegistration::whereNull('reviewed_at')->count();
        $this->filterMonths = ConsortiumRegistration::selectRaw('MONTH(created_at) AS month')->distinct()->orderBy('month')->pluck('month');
        $this->filterYears = ConsortiumRegistration::selectRaw('YEAR(created_at) AS year')->distinct()->orderByDesc('year')->pluck('year');
        $this->filterGenders = ConsortiumRegistration::whereNotNull('gender')->where('gender', '<>', '')->distinct()->orderBy('gender')->pluck('gender');
        $this->filterCities = ConsortiumRegistration::whereNotNull('city')->where('city', '<>', '')->distinct()->orderBy('city')->pluck('city');
        $this->filterJobTypes = ConsortiumRegistration::whereNotNull('preferred_job_type')->where('preferred_job_type', '<>', '')->distinct()->orderBy('preferred_job_type')->pluck('preferred_job_type');

        return view('admin.consortium-registrations.index', $this->data);
    }

    public function show(ConsortiumRegistration $registration)
    {
        if (!$registration->reviewed_at) {
            $registration->update(['reviewed_at' => now()]);
        }
        $this->registration = $registration;
        $this->jobs = Job::with(['company:id,company_name', 'location:id,location', 'jobLocation'])
            ->orderBy('title')
            ->get();
        $this->jobMoves = $registration->jobMoves()
            ->with(['job:id,title,company_id', 'job.company:id,company_name', 'application:id,job_id,status_id', 'movedBy:id,name'])
            ->get();

        $this->tempStaffingHistories = $registration->tempStaffingHistories()
            ->with('user:id,name')
            ->get();

        $profileApplication = $this->jobMoves->first()?->application;
        if (!$profileApplication) {
            $profileApplication = JobApplication::withTrashed()
                ->where('is_candidate', 1)
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim((string) $registration->email))])
                ->latest('id')
                ->first();
        }
        if (!$profileApplication) {
            $profileApplication = new JobApplication;
            $profileApplication->full_name = trim($registration->first_name.' '.$registration->last_name);
            $profileApplication->email = $registration->email;
            $profileApplication->phone = $registration->phone;
            $profileApplication->address = $registration->street_address;
            $profileApplication->city = $registration->city;
            $profileApplication->gender = $registration->gender;
            $profileApplication->dob = $registration->date_of_birth;
            $profileApplication->cover_letter = $registration->additional_information ?: '';
            $profileApplication->is_candidate = 1;
            $profileApplication->column_priority = 0;
            $profileApplication->save();
        }
        if ($registration->resume_file && !$profileApplication->documents()->where('name', 'Resume')->exists()) {
            $source = 'registration-resumes/'.$registration->resume_file;
            $destination = 'documents/'.$profileApplication->id.'/'.$registration->resume_file;
            if (Storage::exists($source) && Storage::copy($source, $destination)) {
                $profileApplication->documents()->create([
                    'name' => 'Resume',
                    'hashname' => $registration->resume_file,
                    'original_name' => $registration->resume_original_name ?: $registration->resume_file,
                ]);
            }
        }
        $this->profileApplicationId = $profileApplication->id;
        return view('admin.consortium-registrations.show', $this->data);
    }

    public function resume(Request $request, ConsortiumRegistration $registration)
    {
        abort_unless($registration->resume_file, 404);
        $path = 'registration-resumes/'.$registration->resume_file;
        $name = $registration->resume_original_name ?: $registration->resume_file;

        if ($request->boolean('inline')) {
            return Storage::response($path, $name, [
                'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $name).'"',
            ]);
        }

        return Storage::download($path, $name);
    }

    public function moveToJob(Request $request, ConsortiumRegistration $registration)
    {
        $validated = $request->validate([
            'job_id' => ['required', 'integer', 'exists:jobs,id'],
        ]);
        $job = Job::with(['location', 'jobLocation'])->findOrFail($validated['job_id']);

        if ($registration->jobMoves()->where('job_id', $job->id)->exists()) {
            return back()->with('error', 'This candidate has already been moved to the selected job.');
        }

        $appliedStatus = ApplicationStatus::ensureAppliedForJob((int) $job->id);
        $matchingLocation = $job->jobLocation->first(function ($location) use ($registration) {
            return mb_strtolower(trim((string) $location->location)) === mb_strtolower(trim((string) $registration->city));
        });
        $locationId = $matchingLocation?->id ?: ($job->location_id ?: $job->jobLocation->first()?->id);
        $copiedResumePath = null;

        try {
            $application = DB::transaction(function () use ($registration, $job, $appliedStatus, $locationId, &$copiedResumePath) {
                $application = new JobApplication;
                $application->full_name = trim($registration->first_name.' '.$registration->last_name);
                $application->email = $registration->email;
                $application->phone = $registration->phone;
                $application->job_id = $job->id;
                $application->status_id = $appliedStatus->id;
                $application->location_id = $locationId;
                $application->address = $registration->street_address;
                $application->city = $registration->city;
                $application->gender = $registration->gender;
                $application->dob = $registration->date_of_birth;
                $application->cover_letter = $registration->additional_information ?: '';
                $application->column_priority = 0;
                $application->is_candidate = 0;
                $application->save();

                if ($registration->resume_file) {
                    $source = 'registration-resumes/'.$registration->resume_file;
                    $copiedResumePath = 'documents/'.$application->id.'/'.$registration->resume_file;
                    if (! Storage::exists($source) || ! Storage::copy($source, $copiedResumePath)) {
                        throw new \RuntimeException('The registration resume could not be copied to the job application.');
                    }
                    $application->documents()->create([
                        'name' => 'Resume',
                        'hashname' => $registration->resume_file,
                        'original_name' => $registration->resume_original_name ?: $registration->resume_file,
                    ]);
                }

                JobApplicationStatusHistory::create([
                    'job_application_id' => $application->id,
                    'from_status_id' => null,
                    'to_status_id' => $appliedStatus->id,
                    'user_id' => $this->user->id,
                    'notes' => 'Moved from Consortium Registration to '.$job->title,
                ]);

                $registration->jobMoves()->create([
                    'job_application_id' => $application->id,
                    'job_id' => $job->id,
                    'moved_by' => $this->user->id,
                ]);

                return $application;
            });
        } catch (\Throwable $exception) {
            if ($copiedResumePath) Storage::delete($copiedResumePath);
            report($exception);
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Candidate moved to '.$job->title.' and added to Job Applications.');
    }
    public function tempStaffingIndex(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $this->pageTitle = 'Temp Staffing';
        $this->registrations = ConsortiumRegistration::query()
            ->where('is_temp_staffing', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%');
                });
            })
            ->latest('temp_staffing_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.temp-staffing.index', $this->data);
    }

    public function toggleTempStaffing(Request $request, ConsortiumRegistration $registration)
    {
        $add = $request->boolean('add');
        $registration->forceFill([
            'is_temp_staffing' => $add,
            'temp_staffing_at' => $add ? now() : null,
            'temp_staffing_by' => $add ? $this->user->id : null,
        ])->save();

        $registration->tempStaffingHistories()->create([
            'user_id' => $this->user->id,
            'action' => $add ? 'added' : 'removed',
        ]);

        return back()->with('success', $add
            ? 'Candidate added to Temp Staffing.'
            : 'Candidate removed from Temp Staffing.');
    }
    public function destroy(ConsortiumRegistration $registration)
    {
        abort_if(! auth()->user()->hasRole('admin'), 403);
        $registration->delete();

        return redirect()->route('admin.consortium-registrations.index')
            ->with('success', 'Registration moved to Trash.');
    }
}