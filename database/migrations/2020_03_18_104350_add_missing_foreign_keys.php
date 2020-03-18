<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'badge_project',
            function (Blueprint $table) {
                $table->foreign('badge_id')->references('id')->on('badges')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            }
        );
        Schema::table(
            'project_user',
            function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
            'badge_project',
            function (Blueprint $table) {
                $table->dropForeign(['badge_id']);
                $table->dropForeign(['project_id']);
            }
        );
        Schema::table(
            'project_user',
            function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropForeign(['user_id']);
            }
        );
    }
}
