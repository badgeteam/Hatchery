<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * MEDIUMBLOB tops out at 16 MiB, which is below the upload limit the
     * uploader offers, so a large file was accepted and then failed to store.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `files` MODIFY `content` LONGBLOB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `files` MODIFY `content` MEDIUMBLOB');
    }
};
