<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Front\ConsortiumRegistrationController;

Route::get('/jobs', [\App\Http\Controllers\Api\JobController::class, 'allJobs']);
Route::get('/assistmyday/jobs', [JobController::class, 'assistMyDayJobs']); // new
Route::post('/consortium-registration', [ConsortiumRegistrationController::class, 'storeApi'])
    ->middleware('throttle:10,1')
    ->name('api.consortium-registration.store');

Route::options('/consortium-registration', function (\Illuminate\Http\Request $request) {
    $origin = $request->headers->get('Origin');
    $allowed = ['https://consortiumstaffing.ca', 'https://www.consortiumstaffing.ca'];
    $response = response('', 204)
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Accept, Content-Type');
    if (in_array($origin, $allowed, true)) {
        $response->header('Access-Control-Allow-Origin', $origin)->header('Vary', 'Origin');
    }
    return $response;
});