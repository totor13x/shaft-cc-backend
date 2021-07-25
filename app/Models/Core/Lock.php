<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLocks;

use App\Models\Core\Lock\{History, Active, Proofs};

class Lock extends Model
{
    use HasLocks;

    protected $fillable = [
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
        'system'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'locked_at',
        'unlock_at',
    ];

    protected $casts = [
        'reason' => 'array'
    ];

    protected $hidden = [
        'system'
    ];

    protected static function toActive($lock)
    {
        return Active::updateOrCreate([
            'lock_id'               => $lock->id,
        ],[
            'user_id'               => $lock->user_id,
            'type'                  => $lock->type,
            'locked_at'             => $lock->locked_at,
            'unlock_at'             => $lock->unlock_at,
            'length'                => $lock->length,
        ]);
    }

    protected static function toHistory($lock)
    {
        return History::create([
            'lock_id'               => $lock->id,
            'user_id'               => $lock->user_id,
            'type'                  => $lock->type,
            'reason'                => $lock->reason,
            'comment'               => $lock->comment,
            'immunity'              => $lock->immunity,
            'locked_at'             => $lock->locked_at,
            'length'                => $lock->length,
            'executor_user_id'      => $lock->executor_user_id,
            'unlock_at'             => $lock->unlock_at,
            'unlock_reason'         => $lock->unlock_reason,
            'unlock_user_id'        => $lock->unlock_user_id,
        ]);
    }
    protected static function boot()
    {
        parent::boot();

        static::created(function($lock){
            tap(self::toHistory($lock),
                function($lock){
                    $lock->is_first = true;
                    $lock->save();
                }
            );
            self::toActive($lock);
        });
        static::updated(function($lock){
            self::toHistory($lock);
            self::toActive($lock);
        });
    }
    public function active()
    {
        return $this->belongsTo(Active::class, 'id', 'lock_id');
    }

    public function history()
    {
        return $this->hasMany(History::class)->orderBy('id', 'desc');
    }
    public function proofs()
    {
        return $this->hasMany(Proofs::class)->orderBy('id', 'desc');
    }
}
