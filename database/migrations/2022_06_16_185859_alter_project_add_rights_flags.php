<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectAddRightsFlags extends Migration
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
                $table->boolean('allow_team_fixes')->default(true);
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
                $table->dropColumn('allow_team_fixes');
            }
        );
    }
}
