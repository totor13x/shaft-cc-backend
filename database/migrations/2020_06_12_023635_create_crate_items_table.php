<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crate_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('crate_id');
            $table->morphs('itemable');
            $table->integer('change')->default(0);
            $table->json('color');
            $table->boolean('is_logging')->default(false);
            $table->timestamps();

            $table->foreign('crate_id')->references('id')->on('crates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crate_items');
    }
}
