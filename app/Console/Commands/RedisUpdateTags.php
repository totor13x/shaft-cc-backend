<?php

namespace App\Console\Commands;

use App\Models\Economy\Tag;
use Illuminate\Support\Facades\Redis;
use Illuminate\Console\Command;

class RedisUpdateTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:tag-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Redis tags cache update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $checksum = Tag::checksum();
        if ($checksum != Redis::get('cache:tag_table.checksum')) {
            Redis::set('cache:tag_table.checksum', $checksum);

            $data = Tag::all();
            $ob_html = [];
            $ob_gmod = [];
            foreach($data as $tag)
            {
                $ob_html[$tag->id] = implode('', $tag->setCompile('addensive')->setType('html')->generate());
                $ob_gmod[$tag->id] = $tag->setCompile('standart')->setType('gmod')->generate();
            }

            Redis::set('cache:tag_table.html', collect($ob_html)->toJson());
            Redis::set('cache:tag_table.gmod', collect($ob_gmod)->toJson());
        }
        return true;
    }
}
