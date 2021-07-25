<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnItemIdInUserTtsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_tts_items', function (Blueprint $table) {
            $table->unsignedBigInteger('server_id')->nullable()->index();
            $table->unsignedBigInteger('item_id')->nullable()->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_tts_items', function (Blueprint $table) {
            $table->string('item_id')->nullable()->change();
            $table->dropColumn('server_id');
        });
    }
}
