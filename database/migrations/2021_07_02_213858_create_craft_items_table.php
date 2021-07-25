<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCraftItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('craft_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
		
        Schema::create('craft_recipes', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('output');
			$table->json('data')->nullable();
			$table->json('items')->nullable();
			$table->boolean('is_reworkable')->default(false)->nullable();
			$table->boolean('is_open')->default(false)->nullable();
            $table->timestamps();
        });
		
        Schema::create('user_craft_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('craft_item_id')->nullable()->index();
            $table->json('data')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });
        Schema::create('user_craft_recipes', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('output');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('craft_recipe_id')->nullable()->index();
            $table->json('data')->nullable();
            $table->json('items')->nullable();
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
        Schema::dropIfExists('craft_items');
        Schema::dropIfExists('craft_recipes');
        Schema::dropIfExists('user_craft_items');
        Schema::dropIfExists('user_craft_recipes');
    }
}
