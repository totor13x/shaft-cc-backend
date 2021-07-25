<?php

namespace App\Http\Controllers\Server;

use App\Models\Economy\Pointshop\PointshopCategory;
use App\Models\Economy\Pointshop\PointshopItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class PointshopController extends Controller
{
    protected function load(Request $request)
    {
        if ($request->srv) {
            // TODO: коммисии

            $commissions = json_decode(Redis::get('cache:pointshop.commissions'), true);
            $items = $request->srv
                ->pointshop_items()
                ->with('roles')
                ->get()
                ->each(function($item){
                    foreach($item->roles as $role) {
                        unset($role['created_at']);
                        unset($role['id']);
                        unset($role['immunity']);
                        unset($role['pivot']);
                        unset($role['updated_at']);
                    }
                    unset($item->pivot);
                });

            $category = PointshopCategory::get();

            return [
                'e' => 'success',
                'd' => [
                    'cdn_url' => cdn_asset(''),
                    'items' => $items,
                    'categories' => $category,
                    'commissions' => $commissions
                ]
            ];
        }
    }

	protected function itemDataSave(Request $request)
	{
        $item = PointshopItem::findOrFail($request->item_id);

		$data = $item->data;

		$data['pos'] = json_decode($request->pos, true);
		$data['ang'] = json_decode($request->ang, true);
		$data['scale'] = $request->scale;

		$item->data = $data;
		$item->name = $request->name;

        $item->save();

         return [
			'e' => 'success',
			'd' => 'Конфиг предмета сохранен'
		];
	}

	protected function itemIconSave(Request $request)
	{
        $item = PointshopItem::findOrFail($request->item_id);
		$image = $request->icon;
		$image = base64_decode($image);

        $fileS3Path = 'pointshop/items/icon_' . $item->id . '.jpg';
        Storage::cloud()->put($fileS3Path, $image);
		$item->icon = $fileS3Path;

        $item->save();

         return [
			'e' => 'success',
			'd' => 'Иконка предмета сохранена'
		];
	}
}
