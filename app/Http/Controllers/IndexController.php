<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Models\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{

	public function webIndex(Request $request)
	{
		return view('welcome');
	}
	public function index(Request $request)
	{
		$lastNews = Cache::get('last_news');

		return [
			'lastNews' => $lastNews
		];
	}

	public function indexConfig()
	{
		$ob = [];
		foreach( Server::all() as $data) {
			$ob[$data->id] = $data;
		}

		return [
			'appName' => config('app.name'),
			'steamAuth' => route('auth.steam.handle'),
			'economy' => [
				'tracks' => config('economy.tracks'),
			],
			'servers' => collect($ob)
		];
	}

	public function serversOnline(Request $request)
	{
		$servers = Server::whereIsEnabled(true)->get();
		$output = [];
		foreach($servers as $server) {
			$players = Redis::get("ws:server:{$server->id}:online:players");
			$max_players = Redis::get("ws:server:{$server->id}:online:max_players");
			$ip = Redis::get("ws:server:{$server->id}:online:ip");
			$workshop = Redis::get("ws:server:{$server->id}:online:workshop");

			if (is_null($players) || is_null($max_players) || is_null($ip)) {
				$online = false;
			} else {
				$percent = $players/$max_players*100;

				if ($percent <= 35 ) {
					$class = 'is-success';
				} elseif ($percent > 35 && $percent < 75) {
					$class = 'is-warning';
				} elseif ($percent >= 75) {
					$class = 'is-primary';
				}

				$workshop = json_decode($workshop, true);

				$online = [
					'players' => $players,
					'max_players' => $max_players,
					'ip' => $ip,
					'class' => $class,
					'workshop' => (!is_null($workshop) && !empty($workshop))
						? $workshop
						: null,
				];
			}
			array_push($output, [
				'name' => $server->beautiful_name,
				'online' => $online,
			]);
		}
		return $output;
	}

	public function cacheTagsChecksum()
	{
		return Redis::get('cache:tag_table.checksum');
	}
	public function cacheTags()
	{
		return str_replace("\\'", "\"", Redis::get('cache:tag_table.html'));
	}
	function cacheTagsGmod()
	{
		return [
			'e' => 'success',
			'd' => json_decode(Redis::get('cache:tag_table.gmod')),
		];
	}
}
