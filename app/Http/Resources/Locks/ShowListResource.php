<?php

namespace App\Http\Resources\Locks;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowListResource extends JsonResource
{
    public function toArray($request)
    {
        switch ($this->type) {
            case 'ban':
                $this->type = 'бан';
            break;
            case 'discord_mute':
                $this->type = 'дискорд-чат';
            break;
        }
        return [
            'id' => $this->id,
            'executor' => !is_null($this->executor) ? [
                'id' => $this->executor->id,
                'username' => $this->executor->username,
                'steam_id32' => $this->executor->steam_id32,
            ] : false,
            'user' => !is_null($this->user) ? [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'steam_id32' => $this->user->steam_id32,
            ] : false,
            'formatted_reason' => !is_null($this->formatted_reason)
                ? $this->formatted_reason->map(function($item){
                    return [
                        'description' => $item->description,
                        'slug' => $item->slug,
                    ];
                })
                : null,
            'reason' => $this->reason,
            'system' => $this->system,
            'comment' => $this->comment,
            'unlock_at' => $this->unlock_at,
            'unlock_reason' => $this->unlock_reason,
            'unlock_user_id' => $this->unlock_user_id,
            'type' => $this->type,
            'length' => $this->length,
            'locked_at' => $this->locked_at,
            'unlocked_at' => $this->locked_at->addSeconds($this->length)
        ];
    }
}
