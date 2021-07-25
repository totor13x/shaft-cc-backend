<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Superbalist\PubSub\PubSubAdapterInterface;
use App\Models\User;
use App\Models\Economy\TTS\TTSItem;

class TestEvent extends Command
{
    protected $i = 1;
    /**
     * The name and signature of the subscriber command.
     *
     * @var string
     */
    protected $signature = 'redis:worker';

    /**
     * The subscriber description.
     *
     * @var string
     */
    protected $description = 'PubSub subscriber for ________';

    /**
     * @var PubSubAdapterInterface
     */
    protected $pubsub;

    /**
     * Create a new command instance.
     *
     * @param PubSubAdapterInterface $pubsub
     */
    public function __construct(PubSubAdapterInterface $pubsub)
    {
        parent::__construct();

        $this->pubsub = $pubsub;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Run');
        $this->pubsub->subscribe('tts/run', function ($data) {
            $item = TTSItem::findOrFail($data['item_id']);
            $user = User::find($data['user_id']);

            $item->run($user, $data['server_id']);
            // dump($message);
        });
    }
}
