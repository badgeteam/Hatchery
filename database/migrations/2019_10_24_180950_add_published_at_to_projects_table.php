<?php

use App\Models\Project;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublishedAtToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('slug');
        });
        $projects = Project::whereHas('versions', function ($query) {
            $query->published();
        });
        foreach ($projects->get() as $project) {
            $project->published_at = $project->versions()->published()->get()->last()->updated_at;
            $project->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
}
