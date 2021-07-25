<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(Request $request, $steamid)
    {
        $s = new \SteamID( $steamid );
        if ($s->IsValid())
        {
            $user = User::whereSteamid($s->ConvertToUInt64())->get()->first();
            return response()->json([
                'data' => $user,
            ]);
        }
        else
        {
            return response()->json([
                'data' => false,
            ], 400);
        }
    }
}
