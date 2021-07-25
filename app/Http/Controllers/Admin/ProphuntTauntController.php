<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Economy\ProphuntTaunt;
use App\Models\Economy\Taunt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProphuntTauntController extends Controller
{
    public function show () {
        return ProphuntTaunt::all();
    }

    public function listTaunts () {
        return Taunt::all();
    }

    public function create (Request $request)
    {
        $files = $request->file('taunts');
        $taunt_id = $request->input('taunt_id');

        $slug = $request->input('slug');
        $name = $request->input('name');

        $renamed = json_decode($request->input('renamed'), true);

        if ($taunt_id) {
            $taunt = Taunt::findOrFail($taunt_id);
            $ph_taunt = new ProphuntTaunt();
            $ph_taunt->name = $name;
            $ph_taunt->slug = $slug;
            $ph_taunt->taunt_id = $taunt->id;
            $ph_taunt->save();

            return response([], 201);
        }


        $s3Path = 'ph/taunts/' . $slug . '/';
        $s3PathTemp = 'ph/taunts/' . $slug . '/temp_';

        $ph_taunt = new ProphuntTaunt();
        $ph_taunt->name = $name;
        $ph_taunt->slug = $slug;

        $data = [];


        foreach ($files as $file) {
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
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            // $content = File::get($fileDisk->getRealPath());

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data, [
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

        $ph_taunt->data = $data;
        $ph_taunt->save();

        return response('Ok', 201);
    }
    public function save(Request $request, ProphuntTaunt $ph_taunt)
    {
        // TODO: Проверить, если сначала были треки, а потом выбран таунт
        $data = [];
        $s3Pathes = collect($ph_taunt->data)->pluck('s3')->flatten();
        // dd($s3Pathes);
        $files = $request->file('taunts', []);
        $json = $request->input('taunts', []);

        $renamed = json_decode($request->input('renamed'), true);

        $slug = $request->input('slug');
        $name = $request->input('name');
        $slug_old = $ph_taunt->slug;

        $s3Path = 'ph/taunts/' . $slug . '/';
        $s3PathOld = 'ph/taunts/' . $slug_old .'/';
        $s3PathTemp = 'ph/taunts/' . $slug . '/temp_';

        $ph_taunt->name = $name;
        $ph_taunt->slug = $slug;
        // $ph_taunt->taunt_id = $taunt->id;
        $taunt_id = $request->input('taunt_id');

        // $slug = $request->input('slug');
        // $name = $request->input('name');

        // $renamed = json_decode($request->input('renamed'), true);

        if ($taunt_id) {
            $taunt = Taunt::findOrFail($taunt_id);
            // $ph_taunt = new ProphuntTaunt();
            // $ph_taunt->name = $name;
            // $ph_taunt->slug = $slug;
            $ph_taunt->taunt_id = $taunt->id;
            // $ph_taunt->save();

            // return response([], 201);
        }

        if ($s3PathOld != $s3Path) {
            foreach ($s3Pathes as $s3) {
                Storage::cloud()->move($s3PathOld . $s3, $s3Path . $s3);
            }
        }

        $data = [];
        foreach($json as $aaa) {
            $arr = json_decode($aaa, true);
            $arr['name'] = $renamed[$arr['name']] ?? $arr['name'];
            array_push($data, $arr);
            $s3Pathes = $s3Pathes->filter(function($s3) use ($arr) {
                return $s3 != $arr['s3'];
            });
        }

        foreach ($s3Pathes as $path) {
            Storage::cloud()->delete($s3Path . $path);
        }

        foreach ($files as $file) {
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
                ->save($s3PathTemp . $fileName . '.ogg');

            $fileDisk = Storage::disk('local')
                ->get($s3PathTemp . $fileName . '.ogg');

            Storage::cloud()->put($s3Path . $fileName, $fileDisk);

            array_push($data, [
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

        $ph_taunt->data = $data;
        $ph_taunt->save();

        return response($ph_taunt, 200);
    }
}
