<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeUniqueConstraints extends Migration
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
                $table->string('slug')->unique()->change();
            }
        );
        Schema::table(
            'badges',
            function (Blueprint $table) {
                $table->string('slug')->unique()->change();
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
                $table->dropUnique('projects_slug_unique');
            }
        );
        Schema::table(
            'badges',
            function (Blueprint $table) {
                $table->dropUnique('badges_slug_unique');
            }
        );
    }
}
