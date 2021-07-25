<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('index', 'IndexController@index');
Route::get('servers/online', 'IndexController@serversOnline');
Route::get('configs', 'IndexController@indexConfig');

Route::get('rules', 'Admin\ReasonController@show'); // Два пути подведенных к одному контроллеру - совместимость типа, хотя хз
Route::post('rules', 'Admin\ReasonController@show'); // И ПОСТ метод для сервера
Route::middleware('auth:api')->get('/user', 'User\CommonController@userInfo');

Route::prefix('profile')->group(function () {
    Route::get('/{steamid}', 'Profile\ProfileController@show');
});
Route::get('tts/items', 'TTS\ItemsController@items');
Route::post('tts/items', 'TTS\ItemsController@items'); // Специально для сервера
Route::post('locks', 'Locks\LocksController@show');

Route::group(['middleware' => ['api', 'auth:api']], function () {
	Route::post('tts/fill_account', 'TTS\ItemsController@fillAccount');
    Route::post('tts/item/{item}/buy', 'TTS\ItemsController@buyItem')->middleware('bindings');
    Route::post('tts/item/{item}/activate', 'TTS\ItemsController@activateItem');

    Route::get('inventory', 'User\InventoryController@show');
    Route::get('inventory/tts_items', 'User\InventoryController@tts_items');
    Route::post('inventory/tts_items', 'User\InventoryController@tts_items'); // Специально для сервера
    Route::post('inventory/tts_activate', 'User\InventoryController@tts_activate');
    Route::get('inventory/online', 'User\InventoryController@online');
    Route::get('inventory/photos', 'User\InventoryController@photos');
    Route::get('inventory/photo/{photo_id}/delete', 'User\InventoryController@photo_delete');

	Route::get('craft', 'User\CraftController@show');
	Route::post('craft/recipe_craft/show', 'User\CraftController@recipe_craft_show');
	Route::post('craft/recipe_craft/start', 'User\CraftController@recipe_craft_start');

    Route::get('track/show', 'User\TrackController@show');
    Route::put('track/create', 'User\TrackController@create');
    Route::post('track/upload', 'User\TrackController@upload');
    Route::patch('track/edit', 'User\TrackController@edit');
    Route::post('track/steam_check', 'User\TrackController@steam_check');
    Route::patch('track/delete', 'User\TrackController@delete');

    // Роутинг, который связан с сервером
    Route::post('tracks/list', 'Track\ListController@show');
    Route::post('tracks/{track}/favorite', 'Track\ListController@favoriteToggle')->middleware('bindings');

	Route::prefix('admin')->group(function() {
		Route::get('user/roles', 'Admin\UserController@roles');
		Route::get('user/servers', 'Admin\UserController@servers');
		Route::get('user/{user}', 'Admin\UserController@show')->middleware('bindings');
		Route::get('user/{user}/roles', 'Admin\UserController@showRoles')->middleware(['permission:ap-mng-user-role','bindings']);
		Route::post('user/{user}/roles/save', 'Admin\UserController@saveRole')->middleware(['permission:ap-mng-user-role','bindings']);
		Route::post('user/{user}/roles/delete', 'Admin\UserController@deleteRole')->middleware(['permission:ap-mng-user-role','bindings']);

		Route::group(['middleware' => 'permission:ap-mng-tts-items'], function () {
			Route::get('tts/items', 'Admin\TTS\ItemController@show');
			Route::get('tts/items/pointshop_items', 'Admin\TTS\ItemController@pointshopItems');
			Route::post('tts/items/create', 'Admin\TTS\ItemController@create');
			Route::post('tts/items/{item_id}/assign', 'Admin\TTS\ItemController@assign');
			Route::post('tts/items/{item_id}/save', 'Admin\TTS\ItemController@save');
		});

		Route::group(['middleware' => 'permission:ap-mng-ps-items'], function () {
			Route::get('pointshop/items', 'Admin\Pointshop\ItemsController@show');
			Route::put('pointshop/items/create', 'Admin\Pointshop\ItemsController@create');
			Route::post('pointshop/items/{item_id}/save', 'Admin\Pointshop\ItemsController@save');
		});
		Route::group(['middleware' => 'permission:ap-mng-ps-categories'], function () {
			Route::get('pointshop/categories', 'Admin\Pointshop\CategoriesController@show');
			Route::put('pointshop/categories/create', 'Admin\Pointshop\CategoriesController@create');
			Route::post('pointshop/categories/{category_id}/save', 'Admin\Pointshop\CategoriesController@save');
		});

		Route::group(['middleware' => 'permission:ap-mng-permission'], function () {
			Route::get('permissions/show', 'Admin\PermissionController@show');
			Route::put('permissions/create', 'Admin\PermissionController@create');
			Route::post('permissions/{permission}/save', 'Admin\PermissionController@save')->middleware('bindings');
			// Route::get('/admin/permissions/{permission_id}/roles_save', 'Admin\PermissionController@show');
		});

		Route::group(['middleware' => 'permission:ap-mng-roles'], function () {
			Route::get('roles/show', 'Admin\RoleController@show');
			Route::put('roles/create', 'Admin\RoleController@create');
			Route::post('roles/{role}/save', 'Admin\RoleController@save')->middleware('bindings');
		});
		Route::post('roles/{role}/link', 'Admin\RoleController@link')->middleware([ 'permission:ap-mng-roles-link', 'bindings' ]);

		Route::get('locks/reason/show', 'Admin\ReasonController@show');
		Route::group(['middleware' => 'permission:ap-mng-locks-reason'], function () {
			Route::post('locks/reason/save', 'Admin\ReasonController@save');
		});

		Route::group(['middleware' => 'permission:ap-locks'], function () {
			Route::get('locks/show', 'Admin\LockController@show');
			Route::post('locks/create', 'Admin\LockController@create');
			Route::post('locks/delete', 'Admin\LockController@delete');
			Route::get('locks/{lock_id}/history', 'Admin\LockController@history_show');

			Route::get('locks/{lock_id}/proofs', 'Admin\LockController@get_proofs');
			Route::post('locks/{lock_id}/proofs', 'Admin\LockController@upload_proof');
			Route::patch('locks/{lock_id}/proofs/{proof_id}', 'Admin\LockController@update_proof');
			Route::delete('locks/{lock_id}/proofs/{proof_id}', 'Admin\LockController@delete_proof');
		});


		Route::group(['middleware' => 'permission:ap-mng-taunts'], function () {
			Route::get('taunts/show', 'Admin\TauntController@show');
			Route::post('taunts/create', 'Admin\TauntController@create');
			Route::post('taunts/{taunt}/save', 'Admin\TauntController@save')->middleware('bindings');
		});

		Route::group(['middleware' => 'permission:ap-mng-taunts-ph'], function () {
			Route::get('prophunt/taunts/list-taunts', 'Admin\ProphuntTauntController@listTaunts');
			Route::get('prophunt/taunts/show', 'Admin\ProphuntTauntController@show');
			Route::post('prophunt/taunts/create', 'Admin\ProphuntTauntController@create');
			Route::post('prophunt/taunts/{ph_taunt}/save', 'Admin\ProphuntTauntController@save')->middleware('bindings');
		});
	});
});

// Только POST запросы для сервера
Route::prefix('server')->middleware(['server', 'throttle:1000,1'])->group(function () {
    Route::post('pointshop', 'Server\PointshopController@load');
    Route::post('pointshop/save_item_data', 'Server\PointshopController@itemDataSave');
    Route::post('pointshop/save_icon_data', 'Server\PointshopController@itemIconSave');
    Route::post('voice/receive', 'Server\VoiceController@receive');

    Route::post('track/{track}/play', 'Server\TrackController@play')->middleware('bindings');

    Route::post('taunts', 'Server\TauntController@show');
    Route::post('prophunt/taunts', 'Server\ProphuntController@taunts');

    Route::post('admin/lock/create', 'Server\AdminController@lockCreate');

    Route::post('defender/user_connect', 'Server\DefenderController@userConnects');
});

Route::get('cache/tags_checksum', 'IndexController@cacheTagsChecksum');
Route::get('cache/tags', 'IndexController@cacheTags');

Route::post('cache/tags/gmod', 'IndexController@cacheTagsGmod');
