<?php

namespace App\Jobs\Audio;

use App\Models\Economy\Track;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class VoiceGmodJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $steamid;
    protected $server_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data['data'];
        $this->steamid = $data['steamid'];
        $this->server_id = $data['server_id'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = "voice_" . date('YmdHis') ."_". random_strings(6);
        $extenc = "dat";

        Storage::disk('local')->put($fileName . '.' . $extenc, base64_decode($this->data));

        $path = storage_path('app/' . $fileName . '.' . $extenc);
        $path_to = storage_path('app/' . $fileName . '.ogg');

        exec("ffmpeg -f s16le -ar 44k -i \"$path\" \"$path_to\" -y 2>&1");

        $fileS3Path = 'voices/' . $this->server_id . '/' . date('Ymd') . '/'.$fileName.'.ogg';
        $fileDisk       = Storage::disk('local')->get($fileName . '.ogg');

        Storage::cloud()->put($fileS3Path, $fileDisk);

        Storage::disk('local')->delete($fileName . '.' . $extenc);

        $user = User::whereSteamid($this->steamid)->first();

        Redis::publish('logs/voice', json_encode([
            'user_id' => $user->id,
            'voice' => $fileS3Path,
            'server_id' => $this->server_id
        ]));
    }
}
