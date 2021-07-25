<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImportBaseDataToLockReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $a = Storage::get('rules.json');
        $a = json_decode($a);

        \App\Models\Core\Lock\Reason::truncate();

        foreach($a as $dat) {
            $reason = new \App\Models\Core\Lock\Reason;
            $reason->slug = $dat->slug;
            $reason->description = $dat->description;
            $reason->penalties = $dat->penalties;
            $reason->comments = $dat->comments;
            $reason->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\Models\Core\Lock\Reason::truncate();
    }
}
