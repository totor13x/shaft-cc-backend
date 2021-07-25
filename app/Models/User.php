<?php

namespace App\Models;

use App\Models\Economy\Pointshop\Item as PointshopItem;

use App\Traits\HasRolesAndPermissions;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

use App\Models\User\Pointshop;
use App\Models\User\TTS;
use App\Models\Core\Lock\Active as LockActive;
use App\Models\Economy\Track;
use App\Models\User\Crate\CrateHistory;
use App\Models\User\Online;
use App\Models\User\Photo;
use App\Models\User\TTS\TTSItem;
use App\Models\User\UserConnect;
use App\Models\Economy\Tag;

class User extends Authenticatable implements Wallet //, MustVerifyEmail
{
    use HasRolesAndPermissions, Notifiable, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'token', 'avatar', 'premium_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'last_parse_websteamapi_at', // Чтобы данные не занимали много места
        'server_data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_parse_websteamapi_at' => 'datetime',
        'premium_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'online',
        'steam_id32'
    ];

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    // public function getPhotoUrlAttribute()
    // {
    //     return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
    // }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     $this->notify(new ResetPassword($token));
    // }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    // public function sendEmailVerificationNotification()
    // {
    //     $this->notify(new VerifyEmail);
    // }
    public function chat_globals() {
        return $this->belongsTo('App\ChatGlobal');
    }
    public function getOnlineAttribute()
    {
        return Redis::get('ws:user:'.$this->id.':online')?:'offline';
    }
    public function getSteamID32Attribute()
    {
        $s = new \SteamID( $this->steamid );
        if ($s->IsValid())
        {
           return $s->RenderSteam2();
        }
        return false;
    }
    public function updateFromSteamWebAPI()
    {
        $jsonurl = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . env('STEAM_API_KEY') . "&steamids=".$this->steamid;
        $json = file_get_contents($jsonurl);
        $data = json_decode($json);
        if (isset($data->response)) {
            $response = $data->response->players[0];
            $this->username = $response->personaname;
            $this->avatar = $response->avatarfull;
            $this->save();
            return $this;
        }
        //https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=70449E6E69DAA578D83622AA05A8CD29&steamids=76561197960435530
    }
    public function shedule_updateFromSteamWebAPI()
    {
        if (now()->addDays(15) > $this->last_parse_websteamapi_at)
        {
            $this->updateFromSteamWebAPI();
        }
    }

    # ПШ инвентарь
    public function pointshop($server_id)
    {
        return Pointshop::findPointshopUser($this->id, $server_id);
    }

    # Добавить/Убавить поинтов
    public function pointshopAddPoints($server_id, $points)
    {
        return Pointshop::addPoints($this->id, $server_id, $points);
    }
    public function pointshopDelPoints($server_id, $points)
    {
        return Pointshop::delPoints($this->id, $server_id, $points);
    }

    # Добавить/Убавить поинтов
    public function pointshopAddItem($server_id, $item_id)
    {
        return Pointshop::addItem($this->id, $server_id, $item_id);
    }
    public function pointshopDelItem($server_id, $id)
    {
        return Pointshop::delItem($this->id, $server_id, $id);
    }
    public function pointshopSetItem($server_id, $id, $data)
    {
        return Pointshop::setItem($this->id, $server_id, $id, $data);
    }
    public function pointshopOwned()
    {
        return $this->hasMany(PointshopItem::class);
    }


    # Блокировки

    public function locks()
    {
        return $this->hasMany(LockActive::class);
    }
    public function is_ban()
    {
        return $this->locks()->whereType('ban')->first();
    }
    public function is_discord_mute()
    {
        return $this->locks()->whereType('discord_mute')->first();
    }

    # Дополнительные фишки к правам

    public function maxImmunity($server_id = null) {
        return $this
            ->roles()
            ->with('role')
            ->when($server_id, function($query) use ($server_id) {
                $query
                    ->whereHas('serverable',
                    function($query) use ($server_id) {
                        $query->whereServerId($server_id);
                    });
            })
            ->get()
            ->max('role.immunity') ?? 0;
        // return $this->whereHas
    }

    # Разная всячина
    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
    public function track_favorites()
    {
        return $this->belongsToMany(Track::class, 'tracks_users', 'user_id', 'track_id');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }


    public function user_connects()
    {
        return $this->belongsTo(UserConnect::class, 'id', 'user_id');
    }

    public function crates_history()
    {
        return $this->hasMany(CrateHistory::class);
    }
    public function online_servers()
    {
        return $this->hasMany(Online::class);
    }

    public function tts_items()
    {
        return $this->hasMany(TTSItem::class);
    }
    public function tts_history()
    {
        return $this->hasMany(TTS::class);
    }
    public function tts_balance()
    {
        return $this->tts_history()->sum('cost');
    }
	public function tags(){
        return $this->belongsToMany(Tag::class, 'users_tags');
    }
    public function is_premium()
    {
        return !is_null($this->premium_at) ? $this->premium_at > now() : false;
    }
}
