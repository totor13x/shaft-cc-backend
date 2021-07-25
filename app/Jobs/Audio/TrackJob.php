<?php

namespace App\Jobs\Audio;

use App\Models\Economy\Track;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class TrackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $track;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Открытие файла, как сущность
        $track = FFMpeg::fromDisk('local')
            ->open($this->track->path);

        // Получение базовых данных
        $length = $track->getDurationInSeconds();

        $path = storage_path('app/' . $this->track->path);

        $track_original = pathinfo($path);
        $fileName = $track_original['filename'];

        $filesize = filesize($path);

        $out = []; // массив для получения результатов вывода ffmpeg
        exec("ffmpeg -i \"$path\" -af loudnorm=I=-16:TP=-1:print_format=json -f null /dev/null 2>&1", $out);
        $out = implode("\n", $out);

        $re = '/.*\[Parsed_loudnorm.*](.*)/sm';
        preg_match_all($re, $out, $matches, PREG_SET_ORDER, 0);

        // JSON
        $data = json_decode($matches[0][1], true);
        // Изменение формата трека трека - долгая прогрузка
        $track
            ->export()
            ->toDisk('local')
            ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
            ->addFilter('-vn') // Отключаем рендер видео
            //ffmpeg -i in.wav  loudnorm=I=-16:TP=-1:measured_I=-22.27:measured_TP=0.03:measured_LRA=20.10:measured_thresh=-33.99:offset=1.39:print_format=summary -ar 48k out.wav
            ->addFilter('-af', "loudnorm=I=-16:TP=-1:measured_I=" . ( floatval($data['input_i']) > 0 ? '0' : $data['input_i'] ) . ":measured_TP={$data['input_tp']}:measured_LRA={$data['input_lra']}:measured_thresh={$data['input_thresh']}:offset={$data['target_offset']}:print_format=summary")
            ->addFilter('-ar', '48k')
            ->save($fileName . '.ogg');


        // Сохранение файла звуковой дорожки (куда-то)
        FFMpeg::fromDisk('local')
            ->open($fileName . '.ogg')
            ->waveform(640, 120, ['#000000'])
            ->save( $fileName . '.png' );

        // Сохранение файла звуковой дорожки для локалки
        Storage::disk('local')->put($fileName . '.png', file_get_contents($fileName . '.png'));

        // Удаление файла звуковой дорожки (где-то)
        unlink($fileName . '.png');

        // Получение содержимого файлов для последующей загрузки в облако
        $fileDisk       = Storage::disk('local')->get($fileName . '.ogg');
        $waveformDisk   = Storage::disk('local')->get($fileName . '.png');

        // Формировка пути файлов
        $fileS3Path = 'tracks/file/'.$fileName.'.ogg';
        $waveformS3Path = 'tracks/waveform/'.$fileName.'.png';

        // Сохранение в облако
        Storage::cloud()->put($fileS3Path, $fileDisk);
        Storage::cloud()->put($waveformS3Path, $waveformDisk);

        // Создание модели трека
        // $save_track = new \App\Economy\Track();
        $this->track->path = $fileS3Path;
        $this->track->waveform = $waveformS3Path;
        $this->track->size = $filesize;
        $this->track->length = $length;
        $this->track->is_uploaded = true;
        $this->track->save();
        // TODO: Включить удаление на продакшене
        // Удаление файлов с диска

        Storage::disk('local')->delete([
            // $fileName . '.' . $track->getClientOriginalExtension(),
            $this->track->path,
            $fileName . '.ogg',
            $fileName . '.png'
        ]);
        Redis::publish('track/finish', json_encode([
            'user_id' => $this->track->user->id,
            'track_id' => $this->track->id
        ]));
    }
}
