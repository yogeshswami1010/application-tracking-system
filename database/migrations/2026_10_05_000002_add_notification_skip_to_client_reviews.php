<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidate_client_review_messages', function (Blueprint $table) {
            $table->timestamp('notification_skipped_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('candidate_client_review_messages', function (Blueprint $table) {
            $table->dropColumn('notification_skipped_at');
        });
    }
};
