<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('priority', 16)->default('medium')->after('description');
            $table->string('tag', 64)->nullable()->after('priority');
            $table->date('due_date')->nullable()->after('tag');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE todo_items MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'priority', 'tag', 'due_date']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE todo_items MODIFY COLUMN status ENUM('pending','completed') NOT NULL DEFAULT 'pending'");
        }
    }
};
