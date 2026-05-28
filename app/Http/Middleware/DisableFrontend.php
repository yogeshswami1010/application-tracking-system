<?php

namespace App\Http\Middleware;

use App\ThemeSetting;
use Closure;

class DisableFrontend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $themeSetting = ThemeSetting::first();
        // dd($globalSetting->disable_frontend);

        if ($themeSetting->disable_frontend && request()->route()->getName() != '' && !request()->ajax()) {
            return redirect(route('login'));
        }
        return $next($request);
    }
}
