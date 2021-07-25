<?php
namespace App\Traits;

use App\Models\User;
use App\Models\Core\Lock\Reason;

trait HasLocks
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

    public function user()
    {
      return $this->belongsTo(User::class);
    }
    public function executor()
    {
      return $this->belongsTo(User::class, 'executor_user_id', 'id');
    }
    public function formatted_reason()
    {
      return $this->belongsToJson(Reason::class, 'reason', 'slug');
    }
}
