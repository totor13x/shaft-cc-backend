<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrateTtsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->integer('price')->nullable();
            $table->nullableMorphs('itemable');
            $table->boolean('is_tradable')->default(false)->nullable();
            $table->boolean('is_hidden')->default(false)->nullable();
            $table->boolean('is_once')->default(false)->nullable();
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
        Schema::dropIfExists('tts_items');
    }
}
