<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInCratesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crates', function (Blueprint $table) {
            $table->integer('sell_key')->after('sell');
            $table->integer('buy_key')->after('sell');
            $table->renameColumn('buy', 'buy_case');
            $table->renameColumn('sell', 'sell_case');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crates', function (Blueprint $table) {
            $table->dropColumn(['buy_key', 'sell_key']);
            $table->renameColumn('buy_case', 'buy');
            $table->renameColumn('sell_case', 'sell');
        });
    }
}
