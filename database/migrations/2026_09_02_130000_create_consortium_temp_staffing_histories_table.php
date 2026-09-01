<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL can leave this empty table behind when a CREATE TABLE foreign key fails.
        Schema::dropIfExists('consortium_temp_staffing_histories');

        Schema::create('consortium_temp_staffing_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consortium_registration_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 20);
            $table->timestamps();
            $table->foreign('consortium_registration_id', 'ctsh_registration_fk')->references('id')->on('consortium_registrations')->cascadeOnDelete();
            $table->foreign('user_id', 'ctsh_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['consortium_registration_id', 'created_at'], 'ctsh_registration_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consortium_temp_staffing_histories');
    }
};