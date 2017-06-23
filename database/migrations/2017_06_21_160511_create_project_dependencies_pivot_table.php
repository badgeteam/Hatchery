<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectDependenciesPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dependencies', function(Blueprint $table)
        {
            $table->integer('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')
                ->on('projects')->onDelete('cascade');

            $table->integer('depends_on_project_id')->unsigned()->nullable();
            $table->foreign('depends_on_project_id')->references('id')
                ->on('projects')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dependencies', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['depends_on_project_id']);
        });
        Schema::dropIfExists('dependencies');
    }
}
