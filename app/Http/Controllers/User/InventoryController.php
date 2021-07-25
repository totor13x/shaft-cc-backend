<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Economy\TTS\TTSItem;
use App\Http\Resources\User\InventoryTTSItemsResource;
use App\Http\Resources\Inventory\ShowLocksResource;
use App\Http\Resources\User\InventoryPhotosResource;
use App\Models\User\Photo as UserPhoto;

class InventoryController extends Controller
{
    public function show(Request $request) {
        $user = $request->user();

        $user->load([
            'roles' => function($builder) {
                $builder->with(
                    'serverable.server',
                    'timeable',
                    'role'
                );
            }
        ]);

        $last_online = $user->online_servers()
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        return [
            'locks' => ShowLocksResource::collection($user->locks),
            'tts_balance' => $user->tts_balance(),
            'roles' => $user->roles,
            'is_premium' => $user->is_premium(),
            'premium_at' => $user->premium_at,
            'photos_count' => $user->photos()->count(),
            'tracks_count' => $user->tracks()->count(),
            'last_online' => [
                'server_id' => $last_online->server_id,
                'server_short_name' => $last_online->server->short_name,
                'start' => $last_online->start->format('d.m.Y'),
                'minutes' => $last_online->end->diffInMinutes($last_online->start),
            ],
        ];
    }

    public function tts_items(Request $request) {
        $user = $request->user();

        $collect = InventoryTTSItemsResource::collection(
            $user->tts_items()
                ->with(['item', 'server'])
                ->whereIsActivated(false)
                ->when($request->server_id, function($builder, $server_id) {
                    $builder
                        ->where(function($builder) use ($server_id) {
                            $builder
                                ->where('server_id', $server_id)
                                ->orWhereHas('item', function($builder) {
                                    $builder->where('is_global', true);
                                });
                        });
                })
                ->orderBy('id', 'desc')
                ->get()
        );
        if ($request->server_id) {
            return [
                'e' => 'success',
                'd' => $collect
            ];
        }
        return $collect;
    }

    public function tts_activate(Request $request) {
        $user = $request->user();

        $item = $user->tts_items()
            ->whereItemId($request->item_id)
            ->first();

        $item->run();
    }

    public function online(Request $request) {
        $user = $request->user();

        $online = $user->online_servers()
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function($data) {
                return [
                    'server_id' => $data->server_id,
                    'server_short_name' => $data->server->short_name,
                    'start' => $data->start->format('d.m.Y'),
                    'minutes' => $data->end->diffInMinutes($data->start),
                ];
            });

        $online_week = [];
        $user->online_servers()
            ->where('start', '>', now()->addDays(-7)->startOfDay())
            ->get()
            ->map(function($data) use (&$online_week) {
                $online_week[$data->server_id] = $online_week[$data->server_id] ?? 0;
                $online_week[$data->server_id] += $data->end->diffInHours($data->start);
            });


        return [
            'online' => $online,
            'week' => $online_week,
        ];
    }

    public function photos(Request $request) {
        $user = $request->user();

        $photos = $user->photos()
            ->orderBy('id', 'desc')
            ->limit(10)
            ->paginate(9);

        return InventoryPhotosResource::collection($photos);
    }

    public function photo_delete(Request $request, $photo_id) {
        $user       = $request->user();

        $photo = $user->photos()
            ->where('id', $photo_id)
            ->first();

        abort_if(
            !$photo,
            422,
            'Фото не найдено'
        );

        $photo->delete();

        return response(200);
    }
}
