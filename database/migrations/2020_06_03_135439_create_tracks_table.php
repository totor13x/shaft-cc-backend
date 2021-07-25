<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('track_author')->nullable();
            $table->string('track_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('path')->nullable();
            $table->string('waveform')->nullable();
            $table->integer('length')->nullable();
            $table->integer('size')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->json('shared_user_ids')->nullable();
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
        Schema::dropIfExists('tracks');
    }
}
