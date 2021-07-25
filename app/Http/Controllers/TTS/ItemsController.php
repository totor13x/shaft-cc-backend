<?php

namespace App\Http\Controllers\TTS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Economy\TTS\TTSItem;
use App\Http\Resources\TTS\ShowItemsResource;
use App\Models\User\TTS\TTSItem as UserTTSItem;

use UnitPay;
use DB;

class ItemsController extends Controller
{
    public function items(Request $request) {
        $items = TTSItem::whereIsHidden(false)
            ->when($request->server_id, function($builder, $server_id) {
                $builder
                    ->whereHas('servers', function($builder) use ($server_id) {
                        $builder->where('server_id', $server_id);
                    })
                    ->orWhere('is_global', true);
            })
            ->get();

        if ($request->server_id) {
            return [
                'e' => 'success',
                'd' => ShowItemsResource::collection($items),
            ];
        }
        return ShowItemsResource::collection($items);
    }
	
	public function fillAccount(Request $request) {
		$request->validate([
            'amount' 	=> 'required|integer|min:0',
            'email' 	=> 'required|email',
        ]);
		
		$amount = $request->get('amount');
		$email = $request->get('email');
		$user = $request->user();
		
		DB::table('unitpay_orders')
			->insert([
				'_orderSum' => $amount,
				'user_id' => $user->id
			]);
			
		$order_id = DB::getPdo()->lastInsertId();
		
		$url = UnitPay::getPayUrl($amount, $order_id, $email, "Пополнение баланса пользователя #{$user->id}");
        if ($request->server_id) {
            return [
                'e' => 'success',
                'd' => $url,
            ];
        }
        return $url;
	}
	
    public function activateItem(Request $request, $item_id) {
        $server_id = $request->server_id;
        $user = $request->user();
		
		$userItem = UserTTSItem::find($item_id);
		
        abort_if(
            !$userItem,
            422,
            'Предмет не найден'
        );
		
		abort_if(
			$userItem->user_id != $user->id,
			422,
			'Предмет не твой'
		);
		
		abort_if(
			$userItem->is_activated,
			422,
			'Предмет уже активирован'
		);
		
        if (!$userItem->item->is_global) {
            if (!is_null($userItem->server_id)) {
                abort_if(
                    $userItem->server_id != $server_id,
                    422,
                    'Предмет недоступен для активации на выбранном сервере'
                );
            } else {
                abort_if(
                    is_null($server_id),
                    422,
                    'Не выбран сервер для активации'
                );
                abort_if(
                    !$userItem->item->servers->pluck('id')->contains($server_id),
                    422,
                    'Предмет недоступен для активации на этом сервере'
                );
            }
        } else {
            $server_id = 1;
        }
		
		$userItem->server_id = $server_id;
		
		$data = $userItem->run();
		
		abort_if(
			!$data,
			422,
			'Произошла ошибка при активации, вероятно предмет отключен для активации. Повторите попытку позже.'
		);
		
		$userItem->is_activated = true;
		$userItem->save();
		if ($request->server_id) {
            return [
                'e' => 'success',
                'd' => 'Предмет активирован'
            ];
        }
		response('OK', 200);
	}

    public function buyItem(Request $request, TTSItem $item) {
        $server_id = $request->server_id;
        $user = $request->user();
        $balance = $user->tts_balance();

        abort_if(
            $balance < $item->price,
            422,
            'Недостаточно средств для покупки'
        );

        if ($item->is_once) {
            $count = $user
                ->tts_items()
                ->whereHas('item', function($query) use ($item) {
                    $query->whereId($item->id);
                })
                ->count();

            abort_if(
                $count != 0,
                422,
                'Такой предмет уже был куплен ранее'
            );
        }
        if (!$item->is_global) {
            abort_if(
                !$item->servers->pluck('id')->contains($server_id),
                422,
                'Предмет недоступен для покупки на этом сервере'
            );
        } else {
            $server_id = 1;
        }

        $item_buyed = $user->tts_items()->create([
            'item_id' => $item->id,
            'server_id' => $server_id,
        ]);

        $user->tts_history()->create([
            'cost' => $item->price * -1,
            'type' => 'buy_tts_' . $item_buyed->id,
        ]);

        $message = "Ты купил " . $item->name . (!$item->is_global ?? " на сервер " . $item_buyed->server->beautiful_name);

        
        Redis::publish('tts/refresh_balance', json_encode([
            'user_id' => $user->id,
        ]));

        if ($request->on_server) {
            return [
                'e' => 'success',
                'd' => $message
            ];    
        }
        // dump($user);
        return [
            'data' => $message
        ];
    }
}
