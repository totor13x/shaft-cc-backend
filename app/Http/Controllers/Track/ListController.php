<?php

namespace App\Http\Controllers\Track;

use App\Http\Controllers\Controller;
use App\Http\Resources\Track\ShowListResource;
use App\Models\Economy\Track;
use Illuminate\Http\Request;

class ListController extends Controller
{
  public function show (Request $request) {
    $search = $request->input('search');
    $type = $request->input('type', 'public');

    $tracks = Track::with('user')
       ->whereIsUploaded(true)
       ->when($type, function($query) use ($request, $type) {
          switch ($type) {
            case 'mytracks':
                return
                    $query->whereUserId($request->user()->id);
            break;
            case 'public':
                return
                    $query->whereIsShared(true);
            break;
            case 'shared':
                return
                    $query->whereHas('shared_users', function($query) use ($request) {
                        $query->where('id', $request->user()->id);
                    });
            break;
            case 'favorite':
                return
                    $query->whereHas('user_favorites', function($query) use ($request) {
                        $query->where('user_id', $request->user()->id);
                    });
            break;
            default:
                return
                    $query->whereRaw('1 = 0'); // Блокировка других типов
            break;
          }
      })
      ->when($search, function($query) use ($search) {
        return $query
          ->where('track_author', 'like', "%{$search}%")
          ->orWhere('track_name', 'like', "%{$search}%");
      })
	  ->orderBy('id', 'desc')
      ->paginate(20);


    return [
      'e' => 'success',
      'd' => ShowListResource::collection($tracks)
        ->response()
        ->getData(true)
    ];
  }
  public function favoriteToggle (Request $request, Track $track) {
    $track->user_favorites()->toggle([ $request->user()->id ]);

    return [];
  }
}
