<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lock_id')->index(); // Родительский бан
            $table->boolean('is_first')->default(false); // первая запись?
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

            $table->foreign('lock_id')->references('id')->on('locks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lock_histories');
    }
}
