<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryPhotosResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'created_at' => $this->created_at->format('d.m.Y в H:i'),
        ];
    }
}
