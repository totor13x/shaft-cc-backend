<?php

namespace App\Http\Controllers\Admin\TTS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Economy\Pointshop\PointshopItem;
use App\Models\Economy\TTS\TTSItem;

class ItemController extends Controller
{
    protected function show(Request $request)
    {
        // dd(PointshopItem::with('formatted_servers')->ge
        return TTSItem::with(['servers', 'itemable'])
            ->get()
            ->each(function($item) {
                $item->servers_ids = $item->servers->pluck('id');
                unset($item->servers);
                // dump($item->servers);
            })
            ->toArray();
    }

    protected function create(Request $request) {
        $new = new TTSItem;
        $new->is_hidden = true;
        $new->save();
        return $new->toArray();
    }

    protected function pointshopItems(Request $request)
    {
        abort_if(
            !is_array($request->server_ids),
            422,
            'Не заданы сервера для поиска предметов'
        );
        return PointshopItem::whereHas(
                'servers',
                function ($query) use ($request) {

                    $query->whereIn('server_id', $request->server_ids);
                }
            )
            ->get()
            ->toArray();
    }

    protected function assign(Request $request, $item_id)
    {
        $request->validate([
            'type' => 'in:pointshop_item'
        ]);

        $item = TTSItem::findOrFail($item_id);
        // dump($re->type);
        if ($request->type == 'pointshop_item') {
            $toassociate = PointshopItem::findOrFail($request->associate_id);
        }

        // $item->fill($request->all());
        $item->type = $request->type;
        $item->itemable()->associate($toassociate);
        $item->save();

        // $item->servers()->sync($request->servers_ids);
        // var_dump($item_id);
        return response('', 200);
    }

    protected function save(Request $request, $item_id)
    {
        $item = TTSItem::findOrFail($item_id);
        $item->fill($request->all());
        $item->save();

        $item->servers()->sync($request->servers_ids);
        // var_dump($item_id);
        return response('', 200);
    }
}
