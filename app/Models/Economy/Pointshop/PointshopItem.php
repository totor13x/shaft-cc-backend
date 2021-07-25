<?php

namespace App\Models\Economy\Pointshop;

use App\Models\Core\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Server;
class PointshopItem extends Model
{
    protected $table = 'pointshop_items';
    // TODO: Есть ли смысл прописывать касты для булевых значений?
    // Имеет, но уже пошло так, так что останется так
    protected $casts = [
        'data'   => 'array',
        'triggers'   => 'array',
        'server_id'   => 'array',
        'is_premium'   => 'boolean',
    ];
    protected $fillable = [
        'name',
        'category',
        'price',
        'is_tradable',
        'is_hidden',
        'server_id',
        'is_premium',
        'data',
        'triggers',
        'type',
        'always_equip',
        'once',
        'hoe',
        'compile_string_equip',
        'compile_string_holster'
    ];
    public static function checksum()
    {
        $tableName = with(new static)->getTable();
        return md5(DB::select(DB::raw(sprintf('select MAX(updated_at) as checksum from %s', $tableName)))[0]->checksum);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_items', 'item_id', 'role_id');
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'server_items', 'item_id', 'server_id');
    }

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
