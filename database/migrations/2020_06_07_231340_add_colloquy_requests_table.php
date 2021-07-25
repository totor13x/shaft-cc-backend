<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColloquyRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colloquy_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('colloquy_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('server_id')->nullable();
            $table->string('username')->nullable();
            $table->string('comment')->nullable();
            $table->string('old_comment')->nullable();
            $table->enum('result', ['0', '1', '2'])->default(0);
            $table->string('admin_comment')->nullable();
            $table->enum('admin_result', ['0', '1', '2'])->default(0);
            $table->string('curator_comment')->nullable();
            $table->enum('curator_result', ['0', '1', '2'])->default(0);
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
        Schema::dropIfExists('colloquy_requests');
    }
}
