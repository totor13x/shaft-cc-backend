<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointshopItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pointshop_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->integer('price')->nullable();
            $table->boolean('is_tradable')->comment('Это у нас возможность передачи предмета')->nullable();
            $table->boolean('is_hidden')->comment('Это у нас приватки, обычно')->nullable();
            $table->string('server_id')->nullable();
            $table->json('data')->nullable();
            $table->json('triggers')->nullable();

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
        Schema::dropIfExists('pointshop_items');
    }
}
