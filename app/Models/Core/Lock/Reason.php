<?php

namespace App\Models\Core\Lock;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $table = 'lock_reasons';

    protected $casts = [
        'penalties' => 'array',
        'comments' => 'array'
    ];
}
