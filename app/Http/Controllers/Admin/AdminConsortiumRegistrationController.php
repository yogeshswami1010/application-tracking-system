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
        return view('admin.consortium-registrations.show', $this->data);
    }

    public function resume(ConsortiumRegistration $registration)
    {
        abort_unless($registration->resume_file, 404);
        return Storage::download('registration-resumes/'.$registration->resume_file, $registration->resume_original_name ?: $registration->resume_file);
    }

    public function destroy(ConsortiumRegistration $registration)
    {
        abort_if(! auth()->user()->hasRole('admin'), 403);
        $registration->delete();

        return redirect()->route('admin.consortium-registrations.index')
            ->with('success', 'Registration moved to Trash.');
    }
}