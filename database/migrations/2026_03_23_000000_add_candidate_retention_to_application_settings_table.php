<?php

use App\ApplicationSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->boolean('auto_delete_candidates')->default(false)->after('google_map_api_key');
            $table->integer('candidate_retention_days')->default(0)->after('auto_delete_candidates');
        });

        $setting = ApplicationSetting::first();
        if ($setting) {
            $setting->auto_delete_candidates = (bool) ($setting->auto_delete_candidates ?? false);
            $setting->candidate_retention_days = (int) ($setting->candidate_retention_days ?? 0);
            $setting->save();
        }
    }

    public function down()
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn(['auto_delete_candidates', 'candidate_retention_days']);
        });
    }
};

