<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

use App\Http\Requests\User\TrackUploadRequest;
use App\Http\Requests\User\TrackEditRequest;
use App\Models\Economy\Track;

use App\Jobs\Audio\TrackJob;

class TrackController extends Controller
{
    private $steamid;

    public function show(Request $request)
    {
        return $request->user()
            ->tracks()
            ->with('shared_users')
            ->orderBy('id', 'desc')
            ->get()
            ->makeVisible(['cdn_path', 'cdn_waveform']);
    }
    public function upload(TrackUploadRequest $request)
    {
        // dd(123);
        $file = $request->file;
        $fileName = date('YmdHis') ."_". random_strings(6);
		// echo($fileName);
        // $save_track = Track::findOrFail($request->track_id);
        // Сохранение файла для локалки
		$file->storeAs( '', $fileName . '.' . $file->getClientOriginalExtension());
        // Storage::disk('local')->put($fileName . '.' . $file->getClientOriginalExtension(), file_get_contents($file));

        $track = $request->track;
        $track->track_name = 'Без названия';
        $track->track_author = 'Неизвестен';
        $track->path = $fileName . '.' . $file->getClientOriginalExtension();
        $track->user_id = $request->user()->id;
        $track->save();

        TrackJob::dispatch($track);

        return $track
                ->makeVisible(['cdn_path', 'cdn_waveform'])
                ->toArray();
    }
    public function create(Request $request)
    {
        // TODO: Добавить функционализм и денюжки
        $buy_cost = config('economy.tracks.buy_slot');
        $user = $request->user();

        abort_if(
            $user->tts_balance() < $buy_cost,
            422,
            'Недостаточно плюшек'
        );

        $save_track = new Track();
        $save_track->user_id = $user->id;
        $save_track->save();

        $user->tts_history()->create([
            'cost' => $buy_cost * -1,
            'type' => 'economy.tracks.buy_slot'
        ]);

        return response($save_track->toArray(), 200);
    }
    public function edit(TrackEditRequest $request)
    {
        $request->validate([
            'track_author' => 'required|min:3|max:32',
            'track_name' => 'required|min:3|max:32',
        ]);
        $request->track->fill($request->only([
            'track_author',
            'track_name',
            'is_shared',
            'shared_user_ids',
        ]));
        $request->track->save();
    }
    public function delete(TrackEditRequest $request)
    {
        $track = $request->track;
        $clear_slot = config('economy.tracks.clear_slot');
        $user = $request->user();

        abort_if(
            $user->tts_balance() < $clear_slot,
            422,
            'Недостаточно плюшек'
        );
        // abort_if(
            // true,
            // 422,
            // 'Функционал отключен в целях сохранности целостности данных'
        // );
        // Storage::cloud()->delete([
        //     $track->path,
        //     $track->waveform
        // ]);

        if ($track->system == 'old') {
            Storage::disk('minio_tracks')->delete($track->path);
        } else {
            Storage::cloud()->delete([
                $track->path,
                $track->waveform
            ]);
        }

        $track->path = null;
        $track->waveform = null;
        $track->track_author = null;
        $track->track_name = null;
        $track->is_uploaded = false;
        $track->save();

        $user->tts_history()->create([
            'cost' => $clear_slot * -1,
            'type' => 'economy.tracks.clear_slot'
        ]);
        // TODO: Денюжки
    }
    public function steam_check (Request $request)
    {
        try {
            $this->steamid = new \SteamID( $request->steamid );
        } catch (\Throwable $th) {
            abort(422, 'SteamID невалидный');
        }

        abort_if(
            !$this->steamid->IsValid(),
            422,
            'SteamID невалидный'
        );

        $user = \App\Models\User::whereSteamid($this->steamid->ConvertToUInt64())->first();

        abort_if(
            is_null($user),
            422,
            'Пользователь не найден'
        );

        return $user->toArray();
    }
}
