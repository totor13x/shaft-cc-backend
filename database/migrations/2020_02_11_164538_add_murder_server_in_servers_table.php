<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Server;

class AddMurderServerInServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_murder';
        $online_srv->beautiful_name = 'Murder';
        $online_srv->short_name = 'MU';
        $online_srv->color = ['r' => '243', 'g' => '80', 'b' => '15'];
        $online_srv->api_token = generate_token();
        $online_srv->save();

        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_deathrun';
        $online_srv->beautiful_name = 'Deathrun';
        $online_srv->short_name = 'DR';
        $online_srv->color = ['r' => '12', 'g' => '120', 'b' => '60'];
        $online_srv->api_token = generate_token();
        $online_srv->save();

        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_cinema';
        $online_srv->beautiful_name = 'Cinema';
        $online_srv->short_name = 'CN';
        $online_srv->color = ['r' => '255', 'g' => '127', 'b' => '36'];
        $online_srv->api_token = generate_token();
        $online_srv->save();

        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_prophunt';
        $online_srv->beautiful_name = 'Prop Hunt';
        $online_srv->short_name = 'PH';
        $online_srv->color = ['r' => '155', 'g' => '45', 'b' => '48'];
        $online_srv->api_token = generate_token();
        $online_srv->save();

        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_starwarsrp';
        $online_srv->beautiful_name = 'Star Wars RP';
        $online_srv->short_name = 'SWRP';
        $online_srv->color = ['r' => '155', 'g' => '45', 'b' => '48'];
        $online_srv->api_token = generate_token();
        $online_srv->save();

        $online_srv = new Server;
        $online_srv->instance = 'gm';
        $online_srv->id_name = 'gm_yandererp';
        $online_srv->beautiful_name = 'Yandere RP';
        $online_srv->short_name = 'YARP';
        $online_srv->color = ['r' => '245', 'g' => '37', 'b' => '120'];
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
