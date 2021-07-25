<?php

namespace App\Http\Controllers\Locks;

use App\Http\Resources\Locks\ShowListResource;
use App\Http\Controllers\User\TrackController as UserTrackController;

use App\Http\Controllers\Controller;
use App\Models\Economy\ProphuntTaunt;
use Illuminate\Http\Request;
use App\Models\Core\Lock;

class LocksController extends Controller
{
    public function show (Request $request)
    {
        $locks = Lock::with([
            'formatted_reason',
            'user',
            'executor',
            'history'
        ])
            // ->withCount('history')
            ->when($request->type, function($builder, $type) {
                $builder->whereType($type);
            })
            ->when($request->search, function($builder, $steam) use ($request) {
                $request->steamid = $steam;
                $data = new UserTrackController();
                $user = $data->steam_check($request);
                if ($user) {
                    $builder->whereUserId($user['id']);
                }
            })
            ->orderBy('id','desc')
            ->paginate(10);

        // return $locks;
        return ShowListResource::collection($locks);
    }
}
