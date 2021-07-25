<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeInPointshopItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->string('type')->comment('Может быть когда нибудь и пригодится')->nullable();
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
            $table->dropColumn('type');
        });
    }
}
