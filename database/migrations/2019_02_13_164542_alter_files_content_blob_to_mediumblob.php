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
        DB::statement("ALTER TABLE `files` MODIFY `content` MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `files` MODIFY `content` BLOB");
    }
}
