<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'versions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('project_id')->unsigned();
                $table->integer('revision')->unsigned();
                $table->string('dependencies');
                $table->string('zip');
                $table->softDeletes();
                $table->nullableTimestamps();
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
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
            'versions', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
            }
        );
        Schema::dropIfExists('versions');
    }
}
