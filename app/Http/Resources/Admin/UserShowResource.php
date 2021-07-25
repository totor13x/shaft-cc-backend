<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Locks\ShowListResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShowResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'steamid' => $this->steamid,
            'steam_id32' => $this->steam_id32,

            'roles' => $this->roles,
            'locks' => ShowListResource::collection($this->locks),

            'roles_count' => $this->roles_count,
            'locks_count' => $this->locks_count,
        ];
    }
}
