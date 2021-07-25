<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Economy\Track;
use Illuminate\Http\Request;
use App\Http\Resources\Track\ShowListResource;
use Illuminate\Support\Facades\Redis;

class TrackController extends Controller
{
  public function list () {

  }

  public function play (Request $request, Track $track) {
    $user_id = $request->user_id;
	
	Redis::publish('logs/track/play', json_encode([
		'user_id' => $user_id,
		'track_id' => $track->id,
		'server_id' => $request->srv->id
	]));
		
    return [
        'e' => 'success',
        'd' => [
            'info' => new ShowListResource($track),
            'url' => $track->getCdnPathAttribute()
        ]
    ];
  }
}
