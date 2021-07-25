<?php

namespace App\Http\Controllers\Admin\Pointshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Economy\Pointshop\PointshopItem;

class ItemsController extends Controller
{
    protected function show(Request $request)
    {
        // dd(PointshopItem::with('formatted_servers')->ge
        return PointshopItem::with(['owner', 'servers'])
            ->get()
            ->each(function($item) {
                $item->servers_ids = $item->servers->pluck('id');
                $item->roles_ids = $item->roles->pluck('id');
                $item->icon = !is_null($item->icon) 
					? cdn_asset($item->icon)
					: null;
                unset($item->servers);
                // dump($item->servers);
            })
            ->toArray();
    }

    protected function create(Request $request) {
        $new = new PointshopItem;
        $new->is_hidden = true;
        $new->save();
        return $new->toArray();
    }

    protected function save(Request $request, $item_id)
    {
        $item = PointshopItem::findOrFail($item_id);
        // dump($item);
        // dump($request->all());
        $item->fill($request->all());
        $item->save();

        $item->servers()->sync($request->servers_ids);
        $item->roles()->sync($request->roles_ids);
        // var_dump($item_id);
        return response('', 200);
    }
}
