<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTableAlterProjectsAddCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('slug');
                $table->softDeletes();
                $table->nullableTimestamps();
            }
        );
        Schema::table(
            'projects', function (Blueprint $table) {
                $table->integer('category_id')->unsigned()->default(1)->after('id');
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
                $table->dropColumn('category_id');
            }
        );
        Schema::dropIfExists('categories');
    }
}
