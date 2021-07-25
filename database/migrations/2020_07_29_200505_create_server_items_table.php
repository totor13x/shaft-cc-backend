<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServerItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->timestamps();
        });
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->dropColumn('server_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_items');
        Schema::table('pointshop_items', function (Blueprint $table) {
            $table->json('server_id')->nullable()->index();
        });
    }
}
