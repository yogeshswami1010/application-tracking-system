<?php

namespace App\Http\Controllers\Admin;

use App\ConsortiumRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'city' => ['nullable', 'string', 'max:100'],
            'job_type' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ConsortiumRegistration::query()
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('created_at', $filters['month']))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('created_at', $filters['year']))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $filters['gender']))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $filters['city']))
            ->when($request->filled('job_type'), fn ($q) => $q->where('preferred_job_type', $filters['job_type']));

        $this->registrations = $query->latest()->paginate(25)->withQueryString();
        $this->unreviewedCount = ConsortiumRegistration::whereNull('reviewed_at')->count();
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
        return view('admin.consortium-registrations.show', $this->data);
    }

    public function resume(ConsortiumRegistration $registration)
    {
        abort_unless($registration->resume_file, 404);
        return Storage::download('registration-resumes/'.$registration->resume_file, $registration->resume_original_name ?: $registration->resume_file);
    }
}