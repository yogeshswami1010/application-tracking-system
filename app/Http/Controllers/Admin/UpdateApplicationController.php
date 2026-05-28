<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
class UpdateApplicationController extends AdminBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.updateApplication');
        $this->pageIcon = __('ti-settings');
    }

    public function index()
    {
        \config(['froiden_envato.allow_users_id' => true]);
        $this->data['mysql_version'] = null;
        $this->data['databaseType'] = 'MySQL Version';
        $this->data['serverOs'] = 'Unknown';
        try {
            $results = DB::select('select version()');
            $this->data['mysql_version'] = $results[0]->{'version()'};
            $this->data['databaseType'] = 'MySQL Version';

            if (str_contains($this->data['mysql_version'], 'Maria')) {
                $this->data['databaseType'] = 'Maria Version';
            }
        } catch (\Exception $e) {
            $this->data['mysql_version'] = null;
            $this->data['databaseType'] = 'MySQL Version';
        }


        $this->data['serverOs'] = 'Unknown';


        try {
            if (function_exists('php_uname')) {
                $serverOs = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m');
            } else {
                $serverOs = 'Unavailable (php_uname disabled)';
            }
        } catch (\Exception $e) {
            $serverOs = 'Unknown';
        }
        $this->data['serverOs'] = $serverOs;
        
        return view('admin.update-application.index', $this->data);
    }
}
