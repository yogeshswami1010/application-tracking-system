<?php

use App\ZoomSetting;
use Illuminate\Database\Seeder;
class ZoomDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = ZoomSetting::first();
        if(is_null($setting)){
            $setting = new ZoomSetting();
            $setting->save();
        }
    }
}
