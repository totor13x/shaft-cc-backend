<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class TTS extends Model
{
    protected $table = 'user_tts_balance';

    protected $fillable = [
        'cost',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
