<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPointshopCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pointshop_categories', function (Blueprint $table) {
            $table->boolean('have_preview')->default(false);
            // $table->boolean('have_hoe')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pointshop_categories', function (Blueprint $table) {
            $table->dropColumn('have_preview');
            // $table->dropColumn('have_hoe');
        });
    }
}
