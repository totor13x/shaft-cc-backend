<?php

namespace App\Http\Controllers\Admin\Pointshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// use App\Models\Economy\PointshopItem;
// use App\Models\Economy\PointshopCategory;

use App\Models\Economy\Pointshop\PointshopItem;
use App\Models\Economy\Pointshop\PointshopCategory;

class CategoriesController extends Controller
{
    protected function show(Request $request) {
        return [
            'list' => PointshopItem::get()
                        ->pluck('category')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray(),
            'categories' => PointshopCategory::get()->toArray()
        ];
    }
	
    protected function create(Request $request) {
        $new = new PointshopCategory;
        $new->name = $request->category;
        $new->save();
        return $new->toArray();
    }
    protected function save(Request $request, $category_id)
    {
        $category = PointshopCategory::findOrFail($category_id);
        // PointshopCategory $item
        $category->fill($request->all());
        $category->save();
        // dump($request);
        // dump($item);
        // dump(PointshopItem::find($pointshop_item));
        return $category->toArray();
    }
}
