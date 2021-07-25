<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\Models\Core\Colloquy\Request as ColloquyRequest;

class Colloquy extends Model
{
    protected $table = 'colloquy';

    protected $dates = [
        'created_at',
        'updated_at',
        'closed_at',
        'scheduled_at',
    ];

    protected $casts = [
        'is_open' => 'boolean'
    ];

    public function requests()
    {
        return $this->hasMany(ColloquyRequest::class);
    }
}
