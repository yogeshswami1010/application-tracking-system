<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->text('telnyx_public_key')->nullable()->after('telnyx_from_number');
        });

        Schema::create('applicant_sms_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_application_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('direction', 10);
            $table->string('from_number', 30);
            $table->string('to_number', 30);
            $table->text('message');
            $table->string('telnyx_message_id', 100)->nullable()->unique();
            $table->string('status', 30)->default('received');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['job_application_id', 'created_at']);
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_sms_messages');

        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn('telnyx_public_key');
        });
    }
};
