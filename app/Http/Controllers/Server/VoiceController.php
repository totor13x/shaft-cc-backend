<?php

namespace App\Http\Controllers\Server;

// use App\Models\Economy\Pointshop\Category as PointshopCategory;

use App\Http\Controllers\Controller;
use App\Jobs\Audio\VoiceGmodJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use App\Models\User;

class VoiceController extends Controller
{
    protected function receive(Request $request)
    {
        if ($request->srv) {
            $data = $request->data;

            VoiceGmodJob::dispatch([
                'data' => $data,
                'steamid' => $request->steamid,
                'server_id' => $request->srv->id,
            ]);
            return [];
        }
    }
}
