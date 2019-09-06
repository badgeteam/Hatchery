<?php

use Illuminate\Database\Migrations\Migration;

class AlterFilesContentBlobToMediumblob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!in_array(config('database.default'), ['sqlite', 'sqlite_testing'])) {
            DB::statement('ALTER TABLE `files` MODIFY `content` MEDIUMBLOB');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!in_array(config('database.default'), ['sqlite', 'sqlite_testing'])) {
            DB::statement('ALTER TABLE `files` MODIFY `content` BLOB');
        }
    }
}
