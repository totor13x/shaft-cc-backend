<?php

namespace App\Models\Core\Lock;

use Illuminate\Database\Eloquent\Model;

class Proofs extends Model
{
    protected $table = 'lock_proofs';

    protected $fillable = [
        'lock_id',
        'path',
        'name',
        'user_id',
        'approved',
        'system',
    ];

    protected $appends = [
        'url'
    ];

    protected $hidden = [
        'system'
    ];

    public function lock()
    {
        return $this->belongsTo(\App\Models\Core\Lock::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function getUrlAttribute()
    {
        if ($this->system == 'old') {
            return env('CDN_OLD_URL') . 'proofs/' . $this->path;
        } else {
            return cdn_asset($this->path);
        }
    }
}
