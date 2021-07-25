<?php

use Illuminate\Support\Facades\Route;
use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Economy\Pointshop\PointshopItem;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'IndexController@webIndex');

Route::get('auth/steam', 'Auth\SteamController@redirectToSteam')->name('auth.steam');
Route::get('auth/steam/handle', 'Auth\SteamController@handle')->name('auth.steam.handle');

Route::get('unitpay/result', 'TTS\OrderController@handlePayment');
