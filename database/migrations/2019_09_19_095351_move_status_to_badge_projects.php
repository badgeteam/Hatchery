<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveStatusToBadgeProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (\App\Models\BadgeProject::all() as $bp) {
            $bp->status = $bp->project->getOriginal('status');
            $bp->save();
        }
        Schema::table(
            'projects',
            function (Blueprint $table) {
                $table->dropColumn('status');
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
                $table->enum('status', ['working', 'in_progress', 'broken', 'unknown'])->default('unknown');
            }
        );
        // NB very naive implementation
        foreach (\App\Models\BadgeProject::all() as $bp) {
            $bp->project->status = $bp->status;
            $bp->project->save();
        }
    }
}
