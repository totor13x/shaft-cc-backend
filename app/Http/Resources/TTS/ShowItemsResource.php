<?php

namespace App\Http\Resources\TTS;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowItemsResource extends JsonResource
{
    public function toArray($request)
    {
        $format = plural(
            [
                '%d плюшка',
                '%d плюшки',
                '%d плюшек'
            ],
            $this->price
        );

        if ($this->price == 0) {
            $format = 'Бесплатно';
        }
        // $servers = $this->servers;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'price_format' => $format,
            'is_tradable' => $this->is_tradable,
            'is_hidden' => $this->is_hidden,
            'is_once' => $this->is_once,
            'is_global' => $this->is_global,
            'servers' => $this->servers->pluck('short_name', 'id'),
        ];
    }
}
