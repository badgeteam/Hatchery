<?php

use App\Models\Project;
use App\Models\Version;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublishedAtToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'projects',
            function (Blueprint $table) {
                $table->timestamp('published_at')->nullable()->after('slug');
            }
        );
        $projects = Project::whereHas(
            'versions',
            function ($query) {
                $query->published();
            }
        );
        foreach ($projects->get() as $project) {
            /** @var Version $version */
            $version = $project->versions()->published()->get()->last();
            $project->published_at = $version->updated_at;
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
        Schema::table(
            'projects',
            function (Blueprint $table) {
                $table->dropColumn('published_at');
            }
        );
    }
}
