<?php

namespace App\Http\Controllers\Admin;

use App\FooterSetting;
use App\Helper\Reply;
use App\Http\Requests\Admin\FooterSetting\StoreRequest;
use App\Http\Requests\Admin\FooterSetting\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class FooterSettingController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.footerSettings');
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->footers = FooterSetting::orderBy('name')->get();

        return view('admin.footer-setting.index', $this->data);
    }

    /**
     * @return Response
     */
    public function create()
    {
        return redirect()->route('admin.footer-settings.index', ['open' => 'create']);
    }

    /**
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $footer = new FooterSetting;

        $footer->name = $request->name;
        $footer->slug = Str::slug($request->name);
        $footer->description = $request->description;
        $footer->status = $request->status;
        $footer->external_url = $request->filled('external_url') ? $request->external_url : null;
        $footer->link_target = in_array($request->input('link_target'), ['_blank', '_self'], true)
            ? $request->input('link_target')
            : '_self';
        $footer->save();

        return Reply::redirect(route('admin.footer-settings.index'), 'messages.feature.addedSuccess');
    }

    /**
     * Display edit form of the resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        return redirect()->route('admin.footer-settings.index', [
            'open' => 'edit',
            'id' => $id,
        ]);
    }

    /**
     * @return array
     */
    public function update(UpdateRequest $request, $id)
    {
        $footer = FooterSetting::findOrFail($id);

        $footer->name = $request->name;
        $footer->slug = Str::slug($request->name);
        $footer->description = $request->description;
        $footer->status = $request->status;
        $footer->external_url = $request->filled('external_url') ? $request->external_url : null;
        $footer->link_target = in_array($request->input('link_target'), ['_blank', '_self'], true)
            ? $request->input('link_target')
            : '_self';
        $footer->save();

        return Reply::redirect(route('admin.footer-settings.index'), 'messages.updatedSuccessfully');
    }

    /**
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        FooterSetting::destroy($id);

        return Reply::success(__('messages.recordDeleted'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids) || $ids === []) {
            return Reply::error(__('modules.footerSettingsPage.bulkNothingSelected'));
        }
        $ids = array_map('intval', $ids);
        FooterSetting::whereIn('id', $ids)->delete();

        return Reply::success(__('messages.recordDeleted'));
    }
}
