<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Server;
class AddDefaultValueInServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $online_srv = new Server;
        $online_srv->instance = 'on';
        $online_srv->id_name = 'online';
        $online_srv->beautiful_name = 'Online';
        $online_srv->short_name = 'ON';
        $online_srv->color = ['r' => '20', 'g' => '120', 'b' => '200'];
        $online_srv->api_token = generate_token();
        $online_srv->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            //
        });
    }
}
