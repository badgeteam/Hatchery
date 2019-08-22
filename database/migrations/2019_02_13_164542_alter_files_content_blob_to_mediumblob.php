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
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `files` MODIFY `content` MEDIUMBLOB");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `files` MODIFY `content` BLOB");
        }
    }
}
