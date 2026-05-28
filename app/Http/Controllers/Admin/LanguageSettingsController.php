<?php

namespace App\Http\Controllers\Admin;

use App\CompanySetting;
use App\Helper\Reply;
use App\Http\Requests\Language\StoreLanguage;
use App\LanguageSetting;
use Barryvdh\TranslationManager\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class LanguageSettingsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.language').' '.__('menu.settings');
        $this->pageIcon = 'icon-settings';
        $this->langPath = base_path().'/resources/lang';
    }

    public function index()
    {
        $this->languages = LanguageSetting::orderBy('language_name')->get();

        return view('admin.language-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return redirect()->route('admin.language-settings.index', ['open' => 'create']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreLanguage $request)
    {
        // check and create lang folder
        $langExists = File::exists($this->langPath.'/'.strtolower($request->language_code));

        if (! $langExists) {
            File::makeDirectory($this->langPath.'/'.strtolower($request->language_code));
        }

        $language = new LanguageSetting;

        $language->language_name = ucfirst(strtolower($request->language_name));
        $language->language_code = strtolower($request->language_code);
        $language->status = $request->status;

        $language->save();

        return Reply::redirect(route('admin.language-settings.index'), __('messages.createdSuccessfully'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return redirect()->route('admin.language-settings.index', [
            'open' => 'edit',
            'id' => $id,
        ]);
    }

    public function update(StoreLanguage $request, $id)
    {
        $language = LanguageSetting::findOrFail($id);

        if ($language->language_code === 'en') {
            return Reply::error(__('modules.languageSettings.defaultLanguageCannotBeModified'));
        }

        // check and create lang folder
        $langExists = File::exists($this->langPath.'/'.strtolower($request->language_code));

        if (! $langExists) {
            // update lang folder name
            File::move($this->langPath.'/'.$language->language_code, $this->langPath.'/'.strtolower($request->language_code));

            Translation::where('locale', $language->language_code)->get()->map(function ($translation) {
                $translation->delete();
            });
        }
        if ($language->language_code === $this->global->locale) {
            $this->global->locale = strtolower($request->language_code);
            $this->global->save();

            $language->status = 'enabled';
        } else {
            $language->status = $request->status;
        }

        $language->language_name = ucfirst(strtolower($request->language_name));
        $language->language_code = strtolower($request->language_code);

        $language->save();

        return Reply::redirect(route('admin.language-settings.index'), __('messages.updatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $languages = LanguageSetting::select('id', 'language_code', 'status')->get();

        $language = $languages->first(function ($language, $key) use ($id) {
            return $language->id == $id;
        });

        if ($language->language_code === 'en') {
            return Reply::error(__('modules.languageSettings.defaultLanguageCannotBeModified'));
        }

        // change locale to default
        if ($this->global->locale == $language->language_code) {
            $this->global->locale = 'en';
            // enable status of english language
            $language = $languages->first(function ($language, $key) {
                return $language->language_code == 'en';
            });

            $language->status = 'enabled';
            $language->save();
        }
        $this->global->save();

        LanguageSetting::destroy($id);

        return Reply::success(__('messages.recordDeleted'));
    }

    public function changeStatus(Request $request, $id)
    {
        if (! $request->has('status')) {
            $request->request->add(['status' => 'disabled']);
        }
        $language = LanguageSetting::findOrFail($id);

        $language->status = $request->status;
        if ($request->status == 'disabled' && $language->language_code == $this->global->locale) {
            $this->global->locale = 'en';
        }

        $language->save();
        $this->global->save();

        return Reply::success(__('app.language').' '.__('menu.settings').' '.__('messages.updatedSuccessfully'));
    }

    public function changeLanguage(Request $request)
    {
        $setting = CompanySetting::first();
        $setting->locale = $request->input('lang');
        $setting->save();

        return Reply::success(__('messages.languageSettings.changedSuccessfully'));
    }
}
