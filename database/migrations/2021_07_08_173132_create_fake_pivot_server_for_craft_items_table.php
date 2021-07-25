<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFakePivotServerForCraftItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pivot_craft_items_fake', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craft_item_id')->nullable()->index();
            $table->unsignedBigInteger('server_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pivot_craft_items_fake');
    }
}
