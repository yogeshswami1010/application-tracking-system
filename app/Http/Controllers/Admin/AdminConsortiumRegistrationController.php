<?php

namespace App\Http\Controllers\Admin;

use App\ConsortiumRegistration;
use Illuminate\Support\Facades\Storage;

class AdminConsortiumRegistrationController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Consortium Registrations';
        $this->pageIcon = 'icon-user-follow';
    }

    public function index()
    {
        $this->registrations = ConsortiumRegistration::latest()->paginate(25);
        $this->unreviewedCount = ConsortiumRegistration::whereNull('reviewed_at')->count();
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