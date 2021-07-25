<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('instance')->comment('Это у тип сервера, в случае нашем gm')->index();
            $table->string('id_name')->comment('Это у нас starwarsrp, который принадлежит названию гейммода и addon.json')->index();
            $table->string('beautiful_name')->comment('Это у нас Star Wars RP');
            $table->string('short_name')->comment('Это у нас SWRP');
            $table->json('color')->comment('Это у нас цвет стандартный');
            $table->string('api_token', 60);
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
        Schema::dropIfExists('servers');
    }
}
