<?php

namespace App\Http\Resources\Track;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowListResource extends JsonResource
{
    public function toArray($request)
    {
		$steamid = new \SteamID($this->user->steamid);
        return [
            'id' => $this->id,
            'is_shared' => $this->is_shared,
            'length' => $this->length,
            'track_author' => $this->track_author,
            'track_name' => $this->track_name,
            'user' => [
                'username' => $this->user->username,
				'steamid32' => $steamid->RenderSteam2(),
            ],
        ];
    }
}
