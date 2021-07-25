<?php

use App\Models\Core\Lock;
use App\Models\Core\Lock\{History, Active, Proofs};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Economy\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LockMugaSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $ids = [];

        $data = Storage::get('core_muga.json');
        $data = json_decode($data);
        $dataCopied = $data;

        $dataParse = Carbon::createFromDate(2020, 01, 01);

        $re = '/STEAM_\d:\d:\d+/m';
        $reReason = '/^(.+) - (.+)$/U';

        foreach($data as $lock) {
            preg_match_all($re, $lock->admin, $matches, PREG_SET_ORDER, 0);
            preg_match_all($re, $lock->admin_r, $matchesRazban, PREG_SET_ORDER, 0);

            if (empty($matches)) {
                $admin = '76561197960381939';
            } else {
                if ($matches[0][0] == "STEAM_0:0:0") {
                    $matches[0][0] = "STEAM_0:1:58105";
                }
                $admin = new \SteamID($matches[0][0]);
                $admin = $admin->ConvertToUInt64();
            }
            if (empty($matchesRazban)) {
                $adminRazban = '76561197960381939';
            } else {
                if ($matchesRazban[0][0] == "STEAM_0:0:0") {
                    $matchesRazban[0][0] = "STEAM_0:1:58105";
                }
                $adminRazban = new \SteamID($matchesRazban[0][0]);
                $adminRazban = $adminRazban->ConvertToUInt64();
            }

            $target = new \SteamID(trim(urldecode($lock->sid)));
            $target = $target->ConvertToUInt64();

            $admin = User::whereSteamid($admin)->first();
            $target = User::whereSteamid($target)->first();
            $adminRazban = User::whereSteamid($adminRazban)->first();

            if ($admin && $target) {
                if ($lock->razban == 'true' && !$adminRazban) continue;
                if ($dataParse <= Carbon::createFromTimestamp($lock->time)) {
                    preg_match_all($reReason, $lock->reason, $reasonMath, PREG_OFFSET_CAPTURE, 0);

                    if (isset($reasonMath[2]) && !empty($reasonMath[2])) {
                        $reason = explode(',', $reasonMath[1][0][0]);
                        $comment = $reasonMath[2][0][0];
                    } else {
                        $reason = explode(',', $lock->reason);
                        $comment = null;
                    }
                } else {
                    $reason = null;
                    $comment = $lock->reason;
                }
                $info = Lock::create([
                    'user_id' => $target->id,
                    'type' => $lock->type,
                    'reason' => collect($reason)->map(function($a) { return trim($a); }),
                    'comment' => $comment,
                    'immunity' => 100,
                    'locked_at' => Carbon::createFromTimestamp($lock->time),
                    'length' => $lock->unban != "0" ? $lock->unban - $lock->time : 0,
                    'executor_user_id' => $admin->id,
                    'created_at' => Carbon::createFromTimestamp($lock->time),
                    'updated_at' => Carbon::createFromTimestamp($lock->time),

                    'unlock_at' => $lock->razban == "true" ? Carbon::createFromTimestamp($lock->time_r) : null,
                    'unlock_reason' => null,
                    'unlock_user_id' => $lock->razban == "true" ? $adminRazban->id : null,

                    'system' => (
                        $dataParse <= Carbon::createFromTimestamp($lock->time)
                            ? 'old_cc'
                            : 'old_im'
                        )
                ]);

                tap(Lock::toHistory($info),
                    function($lock) use ($info) {
                        $lock->is_first = true;
                        $lock->created_at = $info->locked_at;
                        $lock->updated_at = $info->updated_at;
                        $lock->save();
                    }
                );
                $ids[$lock->id] = $info;
            }
        }


        $data = Storage::get('core_muga_active.json');
        $data = json_decode($data);

        foreach($data as $act) {
            if (isset($ids[$act->parent_id])) {
                $info = Active::create([
                    'lock_id' => $ids[$act->parent_id]->id,
                    'user_id' => $ids[$act->parent_id]->user_id,
                    'type' => $ids[$act->parent_id]->type,
                    'locked_at' => $ids[$act->parent_id]->locked_at,
                    'length' => $ids[$act->parent_id]->length,
                ]);
            }
        }
        $data = Storage::get('core_muga_if_modifed.json');
        $data = json_decode($data);

        foreach($data as $hist) {
            if ($hist->isfirst == 'true') continue;
          preg_match_all($re, $hist->admin, $matches, PREG_SET_ORDER, 0);
          preg_match_all($re, $hist->admin_r, $matchesRazban, PREG_SET_ORDER, 0);


          if (empty($matches)) {
              $admin = '76561197960381939';
          } else {
            if ($matches[0][0] == "STEAM_0:0:0") {
                $matches[0][0] = "STEAM_0:1:58105";
            }
              $admin = new \SteamID($matches[0][0]);
              $admin = $admin->ConvertToUInt64();
          }
          if (empty($matchesRazban)) {
                $adminRazban = '76561197960381939';
            } else {
                if ($matchesRazban[0][0] == "STEAM_0:0:0") {
                    $matchesRazban[0][0] = "STEAM_0:1:58105";
                }
                $adminRazban = new \SteamID($matchesRazban[0][0]);
                $adminRazban = $adminRazban->ConvertToUInt64();
            }
          $target = new \SteamID($hist->sid);
          $target = $target->ConvertToUInt64();

          $admin = User::whereSteamid($admin)->first();
          $target = User::whereSteamid($target)->first();
          $adminRazban = User::whereSteamid($adminRazban)->first();

        if ($admin && $target && isset($ids[$hist->parent_id])) {
            if ($hist->razban == 'true' && !$adminRazban) continue;
            if ($dataParse <= Carbon::createFromTimestamp($hist->time)) {

                preg_match_all($reReason, $hist->reason, $reasonMath, PREG_OFFSET_CAPTURE, 0);

                if (isset($reasonMath[2]) && !empty($reasonMath[2])) {
                    $reason = explode(',', $reasonMath[1][0][0]);
                    $comment = $reasonMath[2][0][0];
                } else {
                    $reason = explode(',', $hist->reason);
                    $comment = null;
                }
            } else {
                $reason = null;
                $comment = $hist->reason;
            }

            $info = History::create([
                'lock_id' => $ids[$hist->parent_id]->id,
                'user_id' => $ids[$hist->parent_id]->user_id,
                'type' => $ids[$hist->parent_id]->type,
                'reason' => collect($reason)->map(function($a) { return trim($a); }),
                'comment' => $comment,
                'immunity' => 100,
                'locked_at' => Carbon::createFromTimestamp($hist->time),
                'length' => $hist->unban != "0" ? $hist->unban - $hist->time : 0,
                'executor_user_id' => $admin->id,
                'created_at' => Carbon::createFromTimestamp($hist->time),
                'updated_at' => Carbon::createFromTimestamp($hist->time),

                'unlock_at' => $hist->razban == "true" ? Carbon::createFromTimestamp($hist->time_r) : null,
                'unlock_reason' => null,
                'unlock_user_id' => $hist->razban == "true" ? $adminRazban->id : null,
            ]);
          }
        }
        foreach($dataCopied as $hist) {
            if ($hist->razban == "true") {
                if (isset($ids[$hist->id])) {
                    Lock::toHistory($ids[$hist->id]);
                    Lock::toActive($ids[$hist->id]);
                }
            }
        }
    }
}
