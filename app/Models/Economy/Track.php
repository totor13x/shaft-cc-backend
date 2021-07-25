<?php

namespace App\Models\Economy;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Track extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

    protected static function booted()
    {
        static::updated(function ($model) {
            $changes = $model->getChanges();
            if (isset($changes['path'])) {
                $model->user_favorites()->detach();
            }
            if (isset($changes['is_shared'])) {
                if ($changes['is_shared']) {
                    $model->user_favorites()
                        ->whereNotIn('user_id', $model->shared_user_ids)
                        ->detach();
                }
            }
            if (isset($changes['shared_user_ids'])) {
                if (!$model->is_shared) {
                    $model->user_favorites()
                        ->whereNotIn('user_id', $model->shared_user_ids)
                        ->detach();
                }
            }
        });
    }

    protected $casts = [
        'shared_user_ids'   => 'array',
        'is_shared'         => 'boolean',
        'is_uploaded'       => 'boolean'
    ];

    protected $fillable = [
        'track_name',
        'track_author',
        'path',
        'waveform',
        'user_id',
        'size',
        'length',
        'shared_user_ids',
        'is_shared',
        'is_uploaded',
        'system',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'cdn_path',
        'cdn_waveform'
    ];

    protected $appends = [
        'cdn_path',
        'cdn_waveform'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shared_users()
    {
        return $this->belongsToJson(User::class, 'shared_user_ids', 'id');
    }
    public function getCdnPathAttribute()
    {
        if ($this->system == 'old') {
            return env('CDN_OLD_URL') . 'tracks/' . $this->path;
        } else {
            return cdn_asset($this->path);
        }

    }
    public function getCdnWaveformAttribute()
    {
        return cdn_asset($this->waveform);
    }
    public function user_favorites()
    {
        return $this->belongsToMany(User::class, 'tracks_users', 'track_id', 'user_id');
    }
}
