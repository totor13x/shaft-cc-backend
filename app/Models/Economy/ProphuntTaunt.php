<?php

namespace App\Models\Economy;

use Illuminate\Database\Eloquent\Model;

class ProphuntTaunt extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    public function taunt()
    {
        return $this->belongsTo(Taunt::class);
    }
}
