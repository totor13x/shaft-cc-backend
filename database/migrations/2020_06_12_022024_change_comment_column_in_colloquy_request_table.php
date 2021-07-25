<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeCommentColumnInColloquyRequestTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('colloquy_requests', function (Blueprint $table) {
            $table->string('comment', 512)->nullable()->change();
            $table->string('old_comment', 512)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('colloquy_requests', function (Blueprint $table) {
            $table->string('comment')->nullable()->change();
            $table->string('old_comment')->nullable()->change();
        });
    }
}
