<?php

use App\StorageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('storage_settings')
            ->where('filesystem', 'aws')
            ->update(['filesystem' => 'aws_s3']);

        foreach (['digitalocean', 'wasabi', 'minio'] as $filesystem) {
            if (! StorageSetting::where('filesystem', $filesystem)->exists()) {
                StorageSetting::create([
                    'filesystem' => $filesystem,
                    'auth_keys' => null,
                    'status' => 'disabled',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('storage_settings')
            ->where('filesystem', 'aws_s3')
            ->update(['filesystem' => 'aws']);

        StorageSetting::whereIn('filesystem', ['digitalocean', 'wasabi', 'minio'])->delete();
    }
};
