<?php

namespace App\Models\Core\Lock;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLocks;
use Illuminate\Support\Facades\DB;

class History extends Model
{
    use HasLocks;

    protected $table = 'lock_histories';

    protected $dates = [
        'created_at',
        'updated_at',
        'locked_at',
        'unlock_at',
    ];

    protected $fillable = [
        'lock_id',
        'is_first',
        'user_id',
        'type',
        'reason',
        'comment',
        'immunity',
        'locked_at',
        'length',
        'executor_user_id',
        'unlock_at',
        'unlock_reason',
        'unlock_user_id',
    ];

    protected $casts = [
        'reason' => 'array'
    ];
}
