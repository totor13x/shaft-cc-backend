<?php

namespace App\Models\Economy;

use Illuminate\Database\Eloquent\Model;

class Taunt extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'data' => 'array',
    ];
}
