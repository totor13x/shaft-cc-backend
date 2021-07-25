<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockActiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock_active', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lock_id')->index(); // Родительский бан
            $table->unsignedBigInteger('user_id')->index();
            $table->text('type')->nullable();
            $table->timestamp('locked_at')->default(null)->nullable();
            $table->integer('length')->default(null)->nullable();
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
        Schema::dropIfExists('lock_active');
    }
}
