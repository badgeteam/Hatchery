<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBadgeProjectAddStatus extends Migration
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
                $table->enum('status', ['working', 'in_progress', 'broken', 'unknown'])->default('unknown');
                $table->nullableTimestamps();
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
                $table->dropColumn('status');
                $table->dropColumn('created_at');
                $table->dropColumn('updated_at');
            }
        );
    }
}
