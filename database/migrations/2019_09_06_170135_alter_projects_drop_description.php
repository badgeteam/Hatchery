<?php

use App\Models\Project;
use App\Models\File;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectsDropDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        auth()->loginUsingId(1);
        foreach (Project::all() as $project) {
            $version = $project->versions->last();
            if (!empty($project->description)) {
                if ($version && $version->files()->where('name', 'like', 'README.md')->count() === 0) {
                    $file = $version->files()->firstOrNew(['name' => 'README.md']);
                    $file->content = $project->description;
                    $file->save();
                }
            }
        }
        Schema::table(
            'projects',
            function (Blueprint $table) {
                $table->dropColumn('description');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'projects',
            function (Blueprint $table) {
                $table->text('description')->nullable();
            }
        );
        foreach (Project::all() as $project) {
            $version = $project->versions->last();
            if ($version && $version->files()->where('name', 'like', 'README.md')->count() === 1) {
                /** @var File $file */
                $file = $version->files()->where('name', 'like', 'README.md')->first();
                $project->description = $file->content;
                $project->save();
            }
        }
    }
}
