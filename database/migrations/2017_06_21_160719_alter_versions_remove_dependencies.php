<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVersionsRemoveDependencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'versions',
            function (Blueprint $table) {
                $table->dropColumn('dependencies');
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
            'versions',
            function (Blueprint $table) {
                $table->string('dependencies')->nullable();
            }
        );
    }
}
