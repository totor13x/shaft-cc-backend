<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Core\Lock;
use App\Models\Core\Lock\Active as LockActive;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AdminController extends Controller
{
    public function lockCreate(Request $request) {
        $user_id            = $request->user_id;
        $type               = $request->type;
        $reason             = $request->reason;
        $comment            = $request->comment;
        $executor_user_id   = $request->executor_user_id;
        $length             = $request->length;

        $executor = User::findOrFail($executor_user_id);
        $user = User::findOrFail($user_id);

        $executor_immunity = $executor->maxImmunity();
        $user_immunity = $user->maxImmunity();

        if ($executor_immunity < $user_immunity) {
            // TODO: Break Notify
            return ['e' => 'success', 'd' => 'Пользователь выше твоей категории', 'status' => 422];
        }

        if (!is_array($reason)) {
            $reason_ = json_decode($reason, true);

            if (!is_array($reason_)) {
                $reason_ = [
                    $request->reason
                ];
            }

            $reason = $reason_;
        }

        $lockActive = $user->locks()
            ->whereType($type)
            ->first();

        if ($lockActive)
        {
            $lockActive = $lockActive->lock;
            if
            (
                $executor->id == $lockActive->executor_user_id
                || $lockActive->immunity < $executor_immunity
            )
            {
                $lockActive->reason = $reason;
                $lockActive->comment = $comment;
                $lockActive->length = $length;
                $lockActive->executor_user_id = $executor_user_id;
                $lockActive->immunity = $executor_immunity;
                $lockActive->save();
                //TODO: Banned notify
                return ['e' => 'success', 'd' => 'Изменена блокировка', 'status' => 200];
            } else {
                // TODO: Break Notify
                return ['e' => 'success', 'd' => 'Недостаточно прав на изменение блокировки', 'status' => 422];
            }
        }

        $lock = Lock::create([
            'user_id' => $user->id,
            'type' => $type,
            'reason' => $reason,
            'comment' => $comment,
            'immunity' => $executor_immunity,
            'locked_at' => now(),
            'length' => $length,
            'executor_user_id' => $executor->id,
        ]);
        return ['e' => 'success', 'd' => 'Внесена блокировка', 'status' => 200];
    }
    public function lockDelete(Request $request) {
        $lock_id            = $request->lock_id;
        // $user_id            = $request->user_id;
        // $type               = $request->type;

        $reason             = $request->reason;
        $executor_user_id   = $request->executor_user_id;

        $executor = User::findOrFail($executor_user_id);
        // $user = User::findOrFail($user_id);

        $executor_immunity = $executor->maxImmunity();
        // $user_immunity = $user->maxImmunity();


        $lockActive = LockActive::whereLockId($lock_id)
            ->first();
        // $lockActive = $user->locks()
            // ->whereId($lock_id)
            // ->first();

        if ($lockActive)
        {
            $lockFinded = $lockActive;
            $lockActive = $lockActive->lock;

            // $user = $lockActive->user;
            // if ($lockActive->immunity) {
            //     if ($executor_immunity < $lockActive->immunity) {
            //         // TODO: Break Notify
            //         return ['e' => 'success', 'd' => 'Пользователь выше твоей категории', 'status' => 422];
            //     }
            // }
            if
            (
                $executor->id == $lockActive->executor_user_id
                || $lockActive->immunity < $executor_immunity
            )
            {
                $lockActive->unlock_reason = $reason;
                $lockActive->unlock_user_id = $executor_user_id;
                $lockActive->unlock_at = now();
                $lockActive->save();

                $lockFinded->delete();
                //TODO: Banned notify
                return ['e' => 'success', 'd' => 'Изменена блокировка', 'status' => 200];
            } else {
                // TODO: Break Notify
                return ['e' => 'success', 'd' => 'Недостаточно прав на изменение блокировки', 'status' => 422];
            }
        } else {
            return ['e' => 'success', 'd' => 'Нет активной блокировки', 'status' => 422];
        }

        // $lock = Lock::create([
        //     'user_id' => $user->id,
        //     'type' => $type,
        //     'reason' => $reason,
        //     'comment' => $comment,
        //     'immunity' => $executor_immunity,
        //     'locked_at' => now(),
        //     'length' => $length,
        //     'executor_user_id' => $executor->id,
        // ]);
        // return ['e' => 'success', 'd' => 'Внесена блокировка', 'status' => 200];
    }
}
