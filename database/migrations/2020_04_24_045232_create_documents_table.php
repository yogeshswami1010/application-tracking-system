<?php

use App\JobApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('documentable_id');
            $table->string('documentable_type');
            $table->string('name', 100);
            $table->string('hashname', 100);
            $table->timestamps();

            $table->unique(['documentable_id', 'documentable_type', 'name']);
        });

        $applications = JobApplication::select('id', 'resume')->get();
        
        foreach ($applications as $application) {
            $application->documents()->create([
                'name' => 'Resume',
                'hashname' => $application->resume
            ]);

            if (!is_dir(public_path('user-uploads/documents'))) {
                File::makeDirectory(public_path('user-uploads/documents'));
            }
            if (!is_dir(public_path('user-uploads/documents/'.$application->id))) {
                File::makeDirectory(public_path('user-uploads/documents/'.$application->id));
            }
            
            File::move(public_path('user-uploads/resumes/'.$application->resume), public_path('user-uploads/documents/'.$application->id.'/'.$application->resume));
        }

        File::deleteDirectory('user-uploads/resumes');

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('resume');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
