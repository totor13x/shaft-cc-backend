<?php

namespace App\Models\Core\Lock;

use Illuminate\Database\Eloquent\Model;

class Active extends Model
{
    protected $table = 'lock_active';

    protected $fillable = [
        'lock_id',
        'user_id',
        'type',
        'locked_at',
        'length',
        'unlock_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'locked_at',
        'unlock_at',
    ];

    public function lock()
    {
        return $this->belongsTo(\App\Models\Core\Lock::class);
    }
}
