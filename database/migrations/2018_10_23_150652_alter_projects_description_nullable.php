<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectsDescriptionNullable extends Migration
{
    public function __construct()
    {
        // workaround for Doctrine DBAL issue in table with enum
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        // https://stackoverflow.com/a/42107554
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'projects', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
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
                $table->text('description')->change();
            }
        );
    }
}
