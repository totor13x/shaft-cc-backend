<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->string('id')->unique(); // ID Тега
            $table->boolean('is_primary_gradient')->default(false);
            $table->json('primary_color_1')->nullable();
            $table->json('primary_color_2')->nullable();
            $table->string('primary_text');
            $table->boolean('is_secondary_gradient')->default(false);
            $table->json('secondary_color_1')->nullable();
            $table->json('secondary_color_2')->nullable();
            $table->string('secondary_text')->nullable();
            $table->json('border_color_1')->nullable();
            $table->json('border_color_2')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->string('description')->nullable();
            $table->string('beautiful_text')->nullable();

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
        Schema::dropIfExists('tags');
    }
}
