<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('candidate_email_messages')) {
            // A previous attempt created the columns but failed while adding the
            // constraints. Repair the legacy INT-compatible columns in place.
            DB::statement('ALTER TABLE candidate_email_messages MODIFY job_application_id INT UNSIGNED NOT NULL, MODIFY user_id INT UNSIGNED NULL');
            DB::statement('ALTER TABLE candidate_email_messages ADD CONSTRAINT candidate_email_messages_job_application_id_foreign FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE candidate_email_messages ADD CONSTRAINT candidate_email_messages_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
            return;
        }
        Schema::create('candidate_email_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('job_application_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('direction', ['outbound', 'inbound']);
            $table->string('from_address');
            $table->string('to_address');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable()->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->index(['job_application_id', 'direction', 'read_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('candidate_email_messages'); }
};
