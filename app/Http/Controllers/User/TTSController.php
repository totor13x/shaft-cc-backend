<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Economy\TTS\TTSItem;
use App\Http\Resources\TTS\ShowItemsResource;

use UnitPay;
use DB;

class TTSController extends Controller
{
    public function items(Request $request) {
        $items = TTSItem::whereIsHidden(false)->get();
        return ShowItemsResource::collection($items);
    }
	
}
