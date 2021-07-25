<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->text('type')->nullable();
            $table->text('reason')->nullable();
            $table->text('comment')->nullable();
            $table->integer('immunity')->default(0)->nullable();
            $table->timestamp('locked_at')->default(null)->nullable();
            $table->integer('length')->default(null)->nullable();
            $table->unsignedBigInteger('executor_user_id');
            $table->timestamps();
            $table->timestamp('unlock_at')->default(null)->nullable();
            $table->text('unlock_reason')->nullable();
            $table->unsignedBigInteger('unlock_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locks');
    }
}
