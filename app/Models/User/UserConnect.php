<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserConnect extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    protected $casts = [
        'relations'          => 'array',
        'original_haystack'  => 'array',
    ];
	protected $fillable = [
		'user_id',
	];
    protected $table = 'old_user_connects';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ip()
    {
        return $this->hasMany(UserIp::class, 'user_id', 'user_id');
    }
    public function connects()
    {
        return $this->belongsToJson(User::class, 'relations', 'id');
    }
}
