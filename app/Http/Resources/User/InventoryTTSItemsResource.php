<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TTS\ShowItemsResource;

class InventoryTTSItemsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'is_tradable' => $this->is_tradable,
            'is_activated' => $this->is_activated,
            'created_at' => $this->created_at->format('d.m.Y'),
            'server' => $this->server
                ? [
                    'id' => $this->server->id,
                    'short_name' => $this->server->short_name,
                ]
                : false,
            'item' => new ShowItemsResource($this->item),
        ];
    }
}
