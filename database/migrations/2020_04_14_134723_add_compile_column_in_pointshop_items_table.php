<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompileColumnInPointshopItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->longText('compile_string_equip')->nullable();
            $table->longText('compile_string_holster')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->dropColumn('compile_string_equip');
            $table->dropColumn('compile_string_holster');
        });
    }
}
