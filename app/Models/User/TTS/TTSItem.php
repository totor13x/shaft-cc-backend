<?php

namespace App\Models\User\TTS;

use App\Models\Economy\TTS\TTSItem as TTSTTSItem;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TTSItem extends Model
{
    protected $table = 'user_tts_items';

    protected $fillable = [
        'item_id',
        'server_id',
        'is_tradable',
        'is_activated',
        'created_at',
        'updated_at',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
    public function item()
    {
        return $this->belongsTo(TTSTTSItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function run()
    {
        return $this->item->run($this->user, $this->server_id);
    }
}
