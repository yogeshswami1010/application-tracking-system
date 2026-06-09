<?php

use App\Http\Controllers\Admin\AdminAiSettingsController;
use App\Http\Controllers\Admin\AdminApplicationArchiveController;
use App\Http\Controllers\Admin\AdminApplicationStatusController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminCurrencyController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDepartmentController;
use App\Http\Controllers\Admin\AdminDesignationController;
use App\Http\Controllers\Admin\AdminDocumentController;
use App\Http\Controllers\Admin\AdminJobAlertController;
use App\Http\Controllers\Admin\AdminJobApplicationController;
use App\Http\Controllers\Admin\AdminJobCategoryController;
use App\Http\Controllers\Admin\AdminJobOfferQuestionController;
use App\Http\Controllers\Admin\AdminJobOnboardController;
use App\Http\Controllers\Admin\AdminJobsController;
use App\Http\Controllers\Admin\AdminJobTypeController;
use App\Http\Controllers\Admin\AdminLinkedInSettingsController;
use App\Http\Controllers\Admin\AdminLocationsController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminQuestionController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSecurityController;
use App\Http\Controllers\Admin\AdminSkillsController;
use App\Http\Controllers\Admin\AdminSmsSettingsController;
use App\Http\Controllers\Admin\AdminSmtpSettingController;
use App\Http\Controllers\Admin\AdminStickyNotesController;
use App\Http\Controllers\Admin\AdminTeamController;
use App\Http\Controllers\Admin\AdminThemeSettingsController;
use App\Http\Controllers\Admin\AdminTodoItemController;
use App\Http\Controllers\Admin\AdminWorkExperienceController;
use App\Http\Controllers\Admin\AdminZoomMeetingController;
use App\Http\Controllers\Admin\ApplicantNoteController;
use App\Http\Controllers\Admin\ApplicationSettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanySettingsController;
use App\Http\Controllers\Admin\FooterSettingController;
use App\Http\Controllers\Admin\InterviewScheduleController;
use App\Http\Controllers\Admin\LanguageSettingsController;
use App\Http\Controllers\Admin\ManageRolePermissionController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\UpdateApplicationController;
use App\Http\Controllers\Admin\ZoomMeetingSettingController;
use App\Http\Controllers\Front\FrontJobOfferController;
use App\Http\Controllers\Front\FrontJobsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VerifyMobileController;
use App\Http\Controllers\ZoomWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Loaded by RouteServiceProvider inside the `web` middleware group with
| root namespace App\Http\Controllers. Controllers use class references
| (Laravel 12 style); nested groups use name prefixes only.
|
*/

Route::post('/zoom-webhook', [ZoomWebhookController::class, 'index'])->name('zoom-webhook');

// Front (public job board)
Route::name('jobs.')
    ->group(function () {
        Route::get('/', [FrontJobsController::class, 'jobOpenings'])
            ->name('jobOpenings')
            ->middleware('disable-frontend');

        Route::post('/more-data', [FrontJobsController::class, 'moreData'])->name('more-data');
        Route::post('/search-job', [FrontJobsController::class, 'searchJob'])->name('search-job');
        Route::get('/job-offer/{slug?}', [FrontJobOfferController::class, 'index'])->name('job-offer');
        Route::post('/save-offer', [FrontJobOfferController::class, 'saveOffer'])->name('save-offer');
        Route::get('/job/{slug}/{location?}/{hash?}', [FrontJobsController::class, 'jobDetail'])->name('jobDetail');
        Route::get('/jobapply/{slug}/{location?}/{hash?}', [FrontJobsController::class, 'jobApply'])->name('jobApply');
        Route::post('/job/saveApplication', [FrontJobsController::class, 'saveApplication'])->name('saveApplication');
        Route::post('/job/fetch-country-state', [FrontJobsController::class, 'fetchCountryState'])->name('fetchCountryState');
        Route::post('change-language/{code}', [FrontJobsController::class, 'changeLanguage'])->name('changeLanguage');
        Route::get('auth/callback/{provider}', [FrontJobsController::class, 'callback'])->name('linkedinCallback');
        Route::get('auth/redirect/{provider}', [FrontJobsController::class, 'redirect'])->name('linkedinRedirect');
        Route::get('job-alert', [FrontJobsController::class, 'jobAlert'])->name('jobAlert');
        Route::post('save-job-alert', [FrontJobsController::class, 'saveJobAlert'])->name('saveJobAlert');
        Route::post('disable-job-alert/{id}', [FrontJobsController::class, 'disableJobAlert'])->name('disableJobAlert');
    });

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::post('mark-notification-read', [NotificationController::class, 'markAllRead'])->name('mark-notification-read');
    Route::post('mark-read', [NotificationController::class, 'markRead'])->name('mark_single_notification_read');

    Route::name('admin.')
        ->prefix('admin')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('job-categories/getSkills/{categoryId}', [AdminJobCategoryController::class, 'getSkills'])
                ->name('job-categories.getSkills');
            Route::post('job-categories/ai-generate', [AdminJobCategoryController::class, 'aiGenerateCategories'])
                ->name('job-categories.ai-generate');
            Route::resource('job-categories', AdminJobCategoryController::class);

            Route::get('questions/data', [AdminQuestionController::class, 'data'])->name('questions.data');
            Route::post('questions/ai-generate', [AdminQuestionController::class, 'aiGenerateQuestions'])->name('questions.ai-generate');
            Route::resource('questions', AdminQuestionController::class);

            Route::post('todo-items/update-todo-item', [AdminTodoItemController::class, 'updateTodoItem'])
                ->name('todo-items.updateTodoItem');
            Route::get('todo-items/board-data', [AdminTodoItemController::class, 'boardData'])
                ->name('todo-items.board-data');
            Route::post('todo-items/move-column', [AdminTodoItemController::class, 'moveToColumn'])
                ->name('todo-items.move-column');
            Route::post('todo-items/toggle-complete', [AdminTodoItemController::class, 'toggleComplete'])
                ->name('todo-items.toggle-complete');
            Route::post('todo-items/ai-generate-description', [AdminTodoItemController::class, 'aiGenerateDescription'])
                ->name('todo-items.ai-generate-description');
            Route::resource('todo-items', AdminTodoItemController::class);

            Route::get('job_alert/data', [AdminJobAlertController::class, 'data'])->name('job_alert.data');
            Route::resource('job_alert', AdminJobAlertController::class)->only(['index', 'destroy']);

            Route::prefix('settings')->group(function () {
                Route::resource('settings', CompanySettingsController::class)->only(['edit', 'update', 'index']);
                Route::resource('application-setting', ApplicationSettingsController::class);
            Route::post('job-applications/{id}/assign-job', [JobApplicationController::class, 'assignJob'])
                ->name('admin.job-applications.assign-job');
                Route::post('role-permission/assignAllPermission', [ManageRolePermissionController::class, 'assignAllPermission'])
                    ->name('role-permission.assignAllPermission');
                Route::post('role-permission/removeAllPermission', [ManageRolePermissionController::class, 'removeAllPermission'])
                    ->name('role-permission.removeAllPermission');
                Route::post('role-permission/assignRole', [ManageRolePermissionController::class, 'assignRole'])
                    ->name('role-permission.assignRole');
                Route::post('role-permission/detachRole', [ManageRolePermissionController::class, 'detachRole'])
                    ->name('role-permission.detachRole');
                Route::post('role-permission/storeRole', [ManageRolePermissionController::class, 'storeRole'])
                    ->name('role-permission.storeRole');
                Route::post('role-permission/deleteRole', [ManageRolePermissionController::class, 'deleteRole'])
                    ->name('role-permission.deleteRole');
                Route::get('role-permission/showMembers/{id}', [ManageRolePermissionController::class, 'showMembers'])
                    ->name('role-permission.showMembers');
                Route::resource('role-permission', ManageRolePermissionController::class);

                Route::post('language-settings/change-language', [LanguageSettingsController::class, 'changeLanguage'])
                    ->name('language-settings.change-language');
                Route::put('language-settings/change-language-status/{id}', [LanguageSettingsController::class, 'changeStatus'])
                    ->name('language-settings.changeStatus');
                Route::resource('language-settings', LanguageSettingsController::class);

                Route::resource('theme-settings', AdminThemeSettingsController::class);
                Route::post('theme-settings/disable-frontend', [AdminThemeSettingsController::class, 'disableFrontend'])
                    ->name('theme-settings.disableFrontend');

                Route::get('smtp-settings/sent-test-email', [AdminSmtpSettingController::class, 'sendTestEmail'])
                    ->name('smtp-settings.sendTestEmail');
                Route::resource('smtp-settings', AdminSmtpSettingController::class);

                Route::resource('sms-settings', AdminSmsSettingsController::class)->only(['index', 'update']);

                Route::get('ai-settings', [AdminAiSettingsController::class, 'index'])->name('ai-settings.index');
                Route::get('ai-settings/usage', [AdminAiSettingsController::class, 'usage'])->name('ai-settings.usage');
                Route::post('ai-settings', [AdminAiSettingsController::class, 'store'])->name('ai-settings.store');
                Route::post('ai-settings/test-key', [AdminAiSettingsController::class, 'testKey'])->name('ai-settings.test-key');
                Route::post('ai-settings/{aiApiKey}/active', [AdminAiSettingsController::class, 'toggleActive'])->name('ai-settings.toggle-active');
                Route::post('ai-settings/{aiApiKey}/clipboard-key', [AdminAiSettingsController::class, 'clipboardKey'])->name('ai-settings.clipboard-key');
                Route::put('ai-settings/{aiApiKey}', [AdminAiSettingsController::class, 'update'])->name('ai-settings.update');
                Route::delete('ai-settings/{aiApiKey}', [AdminAiSettingsController::class, 'destroy'])->name('ai-settings.destroy');

                Route::resource('linkedin-settings', AdminLinkedInSettingsController::class);

                Route::post('footer-settings/bulk-destroy', [FooterSettingController::class, 'bulkDestroy'])->name('footer-settings.bulk-destroy');
                Route::resource('footer-settings', FooterSettingController::class);

                Route::get('update-application', [UpdateApplicationController::class, 'index'])->name('update-application.index');
            });

            Route::get('zoom-meeting/table', [AdminZoomMeetingController::class, 'tableView'])->name('zoom-meeting.table-view');
            Route::get('zoom-meeting/data', [AdminZoomMeetingController::class, 'data'])->name('zoom-meeting.data');
            Route::get('zoom-meeting/start-meeting/{id}', [AdminZoomMeetingController::class, 'startMeeting'])->name('zoom-meeting.startMeeting');
            Route::post('zoom-meeting/cancel-meeting', [AdminZoomMeetingController::class, 'cancelMeeting'])->name('zoom-meeting.cancelMeeting');
            Route::post('zoom-meeting/end-meeting', [AdminZoomMeetingController::class, 'endMeeting'])->name('zoom-meeting.endMeeting');
            Route::post('zoom-meeting/updateOccurrence/{id}', [AdminZoomMeetingController::class, 'updateOccurrence'])->name('zoom-meeting.updateOccurrence');
            Route::resource('zoom-meeting', AdminZoomMeetingController::class);

            Route::resource('category', CategoryController::class);
            Route::resource('zoom-setting', ZoomMeetingSettingController::class);
            Route::post('zoom-setting/change-status/{id}', [ZoomMeetingSettingController::class, 'changeStatus'])
                ->name('zoom-setting.change-status');

            Route::resource('skills', AdminSkillsController::class);
            Route::post('skills/ai-generate', [AdminSkillsController::class, 'aiGenerateSkills'])
                ->name('skills.ai-generate');
            Route::resource('locations', AdminLocationsController::class);
            Route::post('skills/quick-create', [AdminSkillsController::class, 'quickCreate'])
                ->name('skills.quick-create');
            Route::get('jobs/data', [AdminJobsController::class, 'data'])->name('jobs.data');
            Route::post('jobs/bulk-destroy', [AdminJobsController::class, 'bulkDestroy'])->name('jobs.bulkDestroy');
            Route::post('jobs/{job}/toggle-status', [AdminJobsController::class, 'toggleStatus'])->name('jobs.toggleStatus');
            Route::post('jobs/refresh-date', [AdminJobsController::class, 'refreshDate'])->name('jobs.refreshDate');
            Route::post('jobs/ai-generate-content', [AdminJobsController::class, 'aiGenerateContent'])->name('jobs.ai-generate-content');
            Route::get('jobs/application-data', [AdminJobsController::class, 'applicationData'])->name('jobs.applicationData');
            Route::post('jobs/send-emails', [AdminJobsController::class, 'sendEmails'])->name('jobs.sendEmails');
            Route::get('jobs/send-email', [AdminJobsController::class, 'sendEmail'])->name('jobs.sendEmail');
            Route::resource('jobs', AdminJobsController::class);

            Route::post('job-applications/rating-save/{id?}', [AdminJobApplicationController::class, 'ratingSave'])
                ->name('job-applications.rating-save');
            Route::get('job-applications/create-schedule/{id?}', [AdminJobApplicationController::class, 'createSchedule'])
                ->name('job-applications.create-schedule');
            Route::post('job-applications/store-schedule', [AdminJobApplicationController::class, 'storeSchedule'])
                ->name('job-applications.store-schedule');
            Route::get('job-applications/question/{jobID}/{applicationId?}', [AdminJobApplicationController::class, 'jobQuestion'])
                ->name('job-applications.question');
            Route::get('job-applications/export/{status}/{location}/{startDate}/{endDate}/{jobs}', [AdminJobApplicationController::class, 'export'])
                ->name('job-applications.export');
            Route::get('job-applications/data', [AdminJobApplicationController::class, 'data'])->name('job-applications.data');
            Route::get('job-applications/load-more', [AdminJobApplicationController::class, 'loadMore'])->name('job-applications.loadMore');
            Route::get('job-applications/table-view', [AdminJobApplicationController::class, 'table'])->name('job-applications.table');
            Route::post('job-applications/updateIndex', [AdminJobApplicationController::class, 'updateIndex'])->name('job-applications.updateIndex');
            Route::post('job-applications/archive-job-application/{application}', [AdminJobApplicationController::class, 'archiveJobApplication'])
                ->name('job-applications.archiveJobApplication');
            Route::post('job-applications/unarchive-job-application/{application}', [AdminJobApplicationController::class, 'unarchiveJobApplication'])
                ->name('job-applications.unarchiveJobApplication');
            Route::post('job-applications/add-skills/{applicationId}', [AdminJobApplicationController::class, 'addSkills'])
                ->name('job-applications.addSkills');
            Route::post('job-applications/ai-compare', [AdminJobApplicationController::class, 'aiCompare'])
                ->name('job-applications.ai-compare');
            Route::post('job-applications/ai-update-status', [AdminJobApplicationController::class, 'aiUpdateStatus'])
                ->name('job-applications.ai-update-status');
            Route::get('job-applications/ai-compare-applicants', [AdminJobApplicationController::class, 'aiCompareApplicants'])
                ->name('job-applications.ai-compare-applicants');
            Route::post('job-applications/ai-generate-cover-letter', [AdminJobApplicationController::class, 'aiGenerateCoverLetterAndDetails'])
                ->name('job-applications.ai-generate-cover-letter');
            Route::post('job-applications/ai-parse-resume', [AdminJobApplicationController::class, 'aiParseResumeFromUpload'])
                ->name('job-applications.ai-parse-resume');
            Route::get('job-applications/get-jobs', [AdminJobApplicationController::class, 'getJobs'])->name('job-applications.get-jobs');
            Route::resource('job-applications', AdminJobApplicationController::class);
            Route::post('job-applications/{id}/parse-skills', [AdminJobApplicationController::class, 'parseSkills'])
                ->name('job-applications.parse-skills');
            Route::get('applications-archive/data', [AdminApplicationArchiveController::class, 'data'])->name('applications-archive.data');
            Route::get('applications-archive/export/{skill}', [AdminApplicationArchiveController::class, 'export'])->name('applications-archive.export');
            Route::resource('applications-archive', AdminApplicationArchiveController::class);
            Route::post('applications-archive{id}', [AdminApplicationArchiveController::class, 'deleteRecords'])
                ->name('applications-archive.deleteRecords');

            Route::get('job-onboard/data', [AdminJobOnboardController::class, 'data'])->name('job-onboard.data');
            Route::get('job-onboard/send-offer/{id?}', [AdminJobOnboardController::class, 'sendOffer'])->name('job-onboard.send-offer');
            Route::get('job-onboard/update-status/{id?}', [AdminJobOnboardController::class, 'updateStatus'])->name('job-onboard.update-status');
            Route::resource('job-onboard', AdminJobOnboardController::class);

            Route::get('job-onboard-questions/data', [AdminJobOfferQuestionController::class, 'data'])->name('job-onboard-questions.data');
            Route::resource('job-onboard-questions', AdminJobOfferQuestionController::class);

            Route::resource('profile', AdminProfileController::class);
            Route::resource('application-status', AdminApplicationStatusController::class);

            Route::get('interview-schedule/data', [InterviewScheduleController::class, 'data'])->name('interview-schedule.data');
            Route::get('interview-schedule/table-view', [InterviewScheduleController::class, 'table'])->name('interview-schedule.table-view');
            Route::post('interview-schedule/change-status', [InterviewScheduleController::class, 'changeStatus'])->name('interview-schedule.change-status');
            Route::post('interview-schedule/change-status-multiple', [InterviewScheduleController::class, 'changeStatusMultiple'])
                ->name('interview-schedule.change-status-multiple');
            Route::get('interview-schedule/notify/{id}/{type}', [InterviewScheduleController::class, 'notify'])->name('interview-schedule.notify');
            Route::get('interview-schedule/response/{id}/{type}', [InterviewScheduleController::class, 'employeeResponse'])
                ->name('interview-schedule.response');
            Route::resource('interview-schedule', InterviewScheduleController::class);

            Route::get('team/data', [AdminTeamController::class, 'data'])->name('team.data');
            Route::post('team/change-role', [AdminTeamController::class, 'changeRole'])->name('team.changeRole');
            Route::resource('team', AdminTeamController::class);

            Route::get('company/data', [AdminCompanyController::class, 'data'])->name('company.data');
            Route::resource('company', AdminCompanyController::class);

            Route::post('currency-settings/set-default', [AdminCurrencyController::class, 'setDefault'])->name('currency-settings.set-default');
            Route::post('currency-settings/bulk-destroy', [AdminCurrencyController::class, 'bulkDestroy'])->name('currency-settings.bulk-destroy');
            Route::post('currency-settings/ai-generate', [AdminCurrencyController::class, 'aiGenerateCurrencies'])->name('currency-settings.ai-generate');
            Route::resource('currency-settings', AdminCurrencyController::class);

            Route::get('security-setting/verifyCaptcha', [AdminSecurityController::class, 'verifyCaptcha'])->name('security-setting.verifyCaptcha');
            Route::resource('security-setting', AdminSecurityController::class)->only(['index', 'update']);

            Route::post('storage-settings/test-connection', [StorageController::class, 'testConnection'])
                ->name('storage-settings.test-connection');
            Route::resource('storage-settings', StorageController::class);
            Route::resource('applicant-note', ApplicantNoteController::class);
            Route::resource('sticky-note', AdminStickyNotesController::class);
            Route::resource('departments', AdminDepartmentController::class);
            Route::resource('job-type', AdminJobTypeController::class);
            Route::resource('work-experience', AdminWorkExperienceController::class);
            Route::resource('designations', AdminDesignationController::class);

            Route::get('documents/data', [AdminDocumentController::class, 'data'])->name('documents.data');
            Route::get('documents/download-document/{document}', [AdminDocumentController::class, 'downloadDoc'])->name('documents.downloadDoc');
            Route::resource('documents', AdminDocumentController::class);

            Route::resource('report', AdminReportController::class);
        });

    Route::get('change-mobile', [VerifyMobileController::class, 'changeMobile'])->name('changeMobile');
    Route::post('send-otp-code', [VerifyMobileController::class, 'sendVerificationCode'])->name('sendOtpCode');
    Route::post('send-otp-code/account', [VerifyMobileController::class, 'sendVerificationCode'])->name('sendOtpCode.account');
    Route::post('verify-otp-phone', [VerifyMobileController::class, 'verifyOtpCode'])->name('verifyOtpCode');
    Route::post('verify-otp-phone/account', [VerifyMobileController::class, 'verifyOtpCode'])->name('verifyOtpCode.account');
    Route::get('remove-session', [VerifyMobileController::class, 'removeSession'])->name('removeSession');

});
Route::get('assistmyday', [App\Http\Controllers\Front\FrontJobsController::class, 'assistMyDay'])->name('assistmyday');
// Catch-all custom CMS page (register last)

Route::post('job-applications/bulk-status-update', [AdminJobApplicationController::class, 'bulkStatusUpdate'])->name('admin.job-applications.bulk-status-update');
Route::post('job-applications/bulk-restore-knockout', [AdminJobApplicationController::class, 'bulkRestoreKnockout'])->name('admin.job-applications.bulk-restore-knockout');
Route::get('job-applications/stage-counts', [AdminJobApplicationController::class, 'stageCounts'])->name('admin.job-applications.stage-counts');
Route::name('jobs.')
    ->group(function () {
        Route::get('{slug}', [FrontJobsController::class, 'customPage'])->name('custom-page');
    });
