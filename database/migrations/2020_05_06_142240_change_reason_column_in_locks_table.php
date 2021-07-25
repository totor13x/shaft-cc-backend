<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeReasonColumnInLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locks', function (Blueprint $table) {
            $table->json('reason')->change();
        });
        Schema::table('lock_histories', function (Blueprint $table) {
            $table->json('reason')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locks', function (Blueprint $table) {
            $table->text('reason')->change();
        });
        Schema::table('lock_histories', function (Blueprint $table) {
            $table->text('reason')->change();
        });
    }
}
