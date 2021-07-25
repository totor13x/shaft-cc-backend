<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Economy\ProphuntTaunt;
use Illuminate\Http\Request;

class ProphuntController extends Controller
{
    public function taunts () {
        $taunts = ProphuntTaunt::with('taunt')->get();
        $output = [];
        $taunts->map(function($item) use (&$output){
            $temp = [];

            $temp['name'] = $item->name;
            $temp['slug'] = $item->slug;
            $temp['data'] = [];
            if ($item->taunt) {
                $temp['cdn'] = cdn_asset("taunts/{$item->taunt->slug}/");
                $temp['data'] = $item->taunt->data;
            } else {
                $temp['cdn'] = cdn_asset("ph/taunts/{$item->slug}/");
                $temp['data']['all'] = $item->data;
            }

            $output[] = $temp;
        });

        return ['e' => 'success', 'd' => $output];
    }
}
