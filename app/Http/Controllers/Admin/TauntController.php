<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Economy\Taunt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class TauntController extends Controller
{
    public function show (Request $request) {
        return Taunt::get();
    }
    public function create (Request $request) {
        $positive = $request->file('positive');
        $death = $request->file('death');
        $kill = $request->file('kill');

        $renamed = json_decode($request->input('renamed'), true);
        $slug = $request->slug;
        $is_enabled = boolval($request->is_enabled);

        $data = [];
        $s3Path = 'taunts/' . $slug . '/';
        $s3PathTemp = 'taunts/' . $slug . '/temp_';

        $taunt = new Taunt();
        $taunt->fill($request->all());
        $taunt->is_enabled = $is_enabled;

        $data['positive'] = [];

        foreach ($positive as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;


            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['positive'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);

            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }

        $data['death'] = [];

        foreach ($death as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;

            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['death'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);
            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }

        $data['kill'] = [];

        foreach ($kill as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;

            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['kill'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);
            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }


        $taunt->data = $data;
        $taunt->save();

        // dump($taunt);
        // dump($renamed);
        // dd($positive);

        return response($taunt, 201);
    }
    public function save (Request $request, Taunt $taunt) {

        $data = [];
        $s3Pathes = collect($taunt->data)->pluck('*.s3')->flatten();

        $positive_files = $request->file('positive', []);
        $death_files = $request->file('death', []);
        $kill_files = $request->file('kill', []);

        $positive_json = $request->input('positive', []);
        $death_json = $request->input('death', []);
        $kill_json = $request->input('kill', []);

        $renamed = json_decode($request->input('renamed'), true);
        $is_enabled = $request->is_enabled == 'true';

        $slug = $request->slug;
        $slug_old = $taunt->slug;

        $s3Path = 'taunts/' . $slug . '/';
        $s3PathOld = 'taunts/' . $slug_old .'/' ;
        $s3PathTemp = 'taunts/' . $slug . '/temp_';

        // $taunt = new Taunt();
        $taunt->fill($request->all());
        $taunt->is_enabled = $is_enabled;

        if ($s3PathOld != $s3Path) {
            foreach ($s3Pathes as $s3) {
                Storage::cloud()->move($s3PathOld . $s3, $s3Path . $s3);
            }
        }

        $data['positive'] = [];
        foreach($positive_json as $json) {
            $arr = json_decode($json, true);
            $arr['name'] = $renamed[$arr['name']] ?? $arr['name'];
            array_push($data['positive'], $arr);
            $s3Pathes = $s3Pathes->filter(function($s3) use ($arr) {
                return $s3 != $arr['s3'];
            });
        }

        $data['death'] = [];
        foreach($death_json as $json) {
            $arr = json_decode($json, true);
            $arr['name'] = $renamed[$arr['name']] ?? $arr['name'];
            array_push($data['death'], $arr);
            $s3Pathes = $s3Pathes->filter(function($s3) use ($arr) {
                return $s3 != $arr['s3'];
            });
        }

        $data['kill'] = [];
        foreach($kill_json as $json) {
            $arr = json_decode($json, true);
            $arr['name'] = $renamed[$arr['name']] ?? $arr['name'];
            array_push($data['kill'], $arr);
            $s3Pathes = $s3Pathes->filter(function($s3) use ($arr) {
                return $s3 != $arr['s3'];
            });
        }

        foreach ($s3Pathes as $path) {
            Storage::cloud()->delete($s3Path . $path);
        }
        // dd($positive_files);
        foreach ($positive_files as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;


            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['positive'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);

            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }

        foreach ($death_files as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;


            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['death'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);

            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }
        foreach ($kill_files as $file) {
            $name = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $content = File::get($file->getRealPath());
            $fileName = date('YmdHis') ."_". random_strings(6) . '.' . $ext;


            Storage::disk('local')->put($s3Path . $fileName, $content);

            $track = FFMpeg::fromDisk('local')
                ->open($s3Path . $fileName);

            $track
                ->export()
                ->toDisk('local')
                ->inFormat(new \FFMpeg\Format\Audio\Vorbis)
                ->addFilter('-vn') // Отключаем рендер видео
                ->addFilter('-ar', '16k')
				->addFilter('-ac', '2')
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data['kill'], [
                'id' => random_strings(3),
                'name' => $renamed[$name] ?? $name,
                's3' => $fileName,
                'length' => $track->getDurationInMiliseconds() / 1000,
            ]);

            Storage::disk('local')->delete([
                $s3Path . $fileName,
                $s3PathTemp . $fileName . '.ogg'
            ]);
        }

        $taunt->data = $data;
        $taunt->save();

        return response($taunt, 200);
        // dd($data);
        // dd($taunt);
    }
}
