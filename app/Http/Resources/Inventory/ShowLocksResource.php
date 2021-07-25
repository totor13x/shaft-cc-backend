<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowLocksResource extends JsonResource
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
            'lock_id' => $this->lock_id,
            'type' => $this->type,
            'length' => $this->length,
            'locked_at' => $this->locked_at,
        ];
    }
}
