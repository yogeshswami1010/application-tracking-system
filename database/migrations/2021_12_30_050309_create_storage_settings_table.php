<?php

use App\StorageSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStorageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filesystem')->default('local');
            $table->text('auth_keys')->nullable();
            $table->enum('status', ['enabled', 'disabled'])->default('disabled');
            $table->timestamps();
        });

        $storage = new StorageSetting();
        $storage->filesystem = 'local';
        $storage->status = 'enabled';
        $storage->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage_settings');
    }
}
