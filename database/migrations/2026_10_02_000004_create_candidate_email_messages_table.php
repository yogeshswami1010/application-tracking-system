<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
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
