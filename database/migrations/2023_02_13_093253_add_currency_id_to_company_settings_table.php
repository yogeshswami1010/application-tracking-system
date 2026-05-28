<?php

use App\CompanySetting;
use App\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->integer('currency_id')->nullable();
        });

        $currency = Currency::where('currency_name', 'Dollars')->first();
        $currency->default = 1;
        $currency->save();

        CompanySetting::where('id', 1)->update(['currency_id' => $currency->id]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
        
    }
};
