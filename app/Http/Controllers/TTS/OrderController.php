<?php

namespace App\Http\Controllers\TTS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Economy\TTS\TTSItem;
use App\Http\Resources\TTS\ShowItemsResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\User;

use UnitPay;

class OrderController extends Controller
{
	/**
     * Search the order in your database and return that order
     * to paidOrder, if status of your order is 'paid'
     *
     * @param Request $request
     * @param $order_id
     * @return bool|mixed
     */
    public function searchOrder(Request $request, $order_id)
    {
		$order = DB::table('unitpay_orders')->where('id', $order_id)->first();

		if ($order) {
			return (array) $order;
		}

        return false;
    }

    /**
     * When paymnet is check, you can paid your order
     *
     * @param Request $request
     * @param $order
     * @return bool
     */
    public function paidOrder(Request $request, $order)
    {
		$user = User::find($order['user_id']);

		if ($user) {
			$user->tts_history()->create([
				'type' => 'fill',
				'cost' => $order['_orderSum'],
			]);
            Redis::publish('tts/fill_balance', json_encode([
                'user_id' => $user->id,
            ]));
		}

		DB::table('unitpay_orders')
			->where('id', $order['id'])
			->update([
				'_orderStatus' => 'paid'
			]);

        return true;
    }

    /**
     * Start handle process from route
     *
     * @param Request $request
     * @return mixed
     */
    public function handlePayment(Request $request)
    {
        return UnitPay::handle($request);
    }
}
