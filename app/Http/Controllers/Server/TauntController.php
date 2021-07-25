<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Economy\Taunt;
use Illuminate\Http\Request;

class TauntController extends Controller
{
    public function show () {
        $taunts = Taunt::whereIsEnabled(true)->get();
        $output = [];
        $taunts->map(function($item) use (&$output){
            $temp = [];

            $temp['name'] = $item->name;
            $temp['slug'] = $item->slug;

			$temp['cdn'] = cdn_asset("taunts/{$item->slug}/");
            $temp['data'] = [];
            $temp['data'] = $item->data;

            $output[] = $temp;
        });

        return ['e' => 'success', 'd' => $output];
    }
}
