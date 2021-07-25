<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use App\Models\User\UserConnect;
class DefenderController extends Controller
{
    protected function userConnects(Request $request)
    {

        if ($request->srv) {
			$type = $request->input('type');
			$user_id = $request->input('user_id');

			$connections = $request->input('data');
			$connections = json_decode($connections, true);

			$user_connects = UserConnect::firstOrCreate([
				'user_id' => $user_id,
			]);

			$shared = [];
			foreach ($connections as $sss) {
				try {
					$shass = new \SteamID($sss);
					$shass = $shass->ConvertToUInt64();
					$shass = User::whereSteamid($shass)->first();
					if ($shass) {
						if (in_array($shass->id, $shared)) continue;
						$shared[] = $shass->id;
					}
				} catch (\Throwable $th) {}
            }
			$user_connects->original_haystack = json_decode($request->input('original'));
			$user_connects->relations = $shared;
			$user_connects->save();

			return [
					'e' => 'success',
					'd' => $user_connects,
			];
        }
    }
}

