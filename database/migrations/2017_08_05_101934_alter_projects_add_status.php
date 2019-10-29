<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectsAddStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'projects', function (Blueprint $table) {
                $table->enum('status', ['working', 'in_progress', 'broken', 'unknown'])->default('unknown');
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
            'projects', function (Blueprint $table) {
                $table->dropColumn('status');
            }
        );
    }
}
