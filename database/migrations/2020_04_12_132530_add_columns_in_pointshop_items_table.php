<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPointshopItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->boolean('always_equip')->default(false);
            $table->boolean('once')->default(false);
            $table->string('hoe')->nullable();
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
            $table->dropColumn('always_equip');
            $table->dropColumn('once');
            $table->dropColumn('hoe');
        });
    }
}
