<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{

    protected static function booted()
    {
        static::deleted(function ($photo) {
            if ($photo->system == 'old') {
                Storage::disk('minio_photos')->delete($photo->path);
            } else {
                Storage::cloud()->delete('photos/' . $photo->path);
            }
        });
    }

    protected $table = 'user_photos';

    protected $fillable = [
        'path',
        'map',
        'system',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        if ($this->system == 'old') {
            return env('CDN_OLD_URL') . 'photos/' . $this->path;
        } else {
            return cdn_asset('photos/' . $this->path);
        }
    }
}
