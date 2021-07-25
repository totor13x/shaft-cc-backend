<?php

use App\Models\Economy\Pointshop\PointshopCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Economy\Pointshop\PointshopItem;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonMachine;

class PointshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

		$ocsid = [
            'YANDERERP' => Server::whereIdName('gm_yandererp')->first(),
            'STARWARSRP' => Server::whereIdName('gm_starwarsrp')->first(),
            'PROPHUNT' => Server::whereIdName('gm_prophunt')->first(),
            'CINEMA' => Server::whereIdName('gm_cinema')->first(),
            'DEATHRUN' => Server::whereIdName('gm_deathrun')->first(),
            'MURDER' => Server::whereIdName('gm_murder')->first(),
        ];

        $categories = [];
        PointshopCategory::truncate();

        foreach($categories as $category) {
            $cat = new PointshopCategory;
            $cat->name = $category['name'];
            $cat->have_preview = $category['have_preview'];
            $cat->compile_string_equip = $category['compile_string_equip'];
            $cat->compile_string_holster = $category['compile_string_holster'];
            $cat->save();
        }

        PointshopItem::truncate();

        $data = Storage::get('pointshop_items.json');
        $data = json_decode($data);

        $oo = [];

        foreach($data as $item) {
            if ($item->ServerID == 'OLD_DEATHRUN') {
                continue;
            }
            $item->TypeData = json_decode($item->TypeData);
            $item->FunctionCompile = json_decode($item->FunctionCompile);
            if (strpos('private', $item->TypeData->Name)) {
                continue;
            }
            $psitem = new PointshopItem;

            if ($item->FunctionCompile) {
                if ($item->FunctionCompile->OnEquip == 'wepEquip') {
                    $psitem->category = 'Разное';
                    $psitem->type = "swep";
                    $psitem->data = [
                        'type' => 'swep',
                        'swep' => $item->TypeData->WeaponClass
                    ];
                }

                if ($item->FunctionCompile->OnEquip == 'items&hatsEquip') {
                    $psitem->category = 'Аксессуары';
                    $psitem->type = "attach";

                    $pos = explode(' ', $item->TypeData->Pos);
                    $ang = explode(' ', $item->TypeData->Ang);
                    // dump($item);
                    $psitem->data = [
                        'mdl' => $item->TypeData->Model,
                        'attach' => $item->TypeData->Attachment ?? null,
                        'bone' => $item->TypeData->Bone ?? null,
                        'ang' => [
                            'p' => (float)$ang[1],
                            'y' => (float)$ang[2],
                            'r' => (float)$ang[0],
                        ],
                        'pos' => [
                            'x' => (float)$pos[0],
                            'y' => (float)$pos[1],
                            'z' => (float)$pos[2],
                        ],
                        'scale' => (float)$item->TypeData->Scale,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'particlesEquip') {
                    // dump($item->TypeData);
                    $setdata = [
                        'effect' => $item->TypeData->ParamEquip ?? null,
                        'attach' => $item->TypeData->Attachment,
                        'bone' => null,
                        'pos' => [],
                    ];
                    if (optional($item->TypeData)->MusicOrb) {
                        // $setdata['lua'] = 'musicorb';
                        $setdata['lua'] = true;
                        $setdata['effect'] = 'musicorb';
                    }
                    $psitem->category = 'Эффекты на модель';
                    $psitem->type = "attacheffect";
                    $psitem->data = $setdata;
                }
                if ($item->FunctionCompile->OnEquip == 'knifeEquip') {
                    // continue;

                    $psitem->category = 'Ножи';
                    $psitem->type = "swep";
                    $psitem->data = [
                        'type' => 'knife',
                        'skin' => $item->TypeData->skin ?? null,
                        'swep' => $item->TypeData->WeaponClass,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'skinsAWEquip') {
                    // continue;

                    $psitem->category = 'Оружия';
                    $psitem->type = "swep";
                    $psitem->data = [
                        'type' => 'swep',
                        'skin' => $item->TypeData->skin ?? null,
                        'swep' => $item->TypeData->WeaponClass,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'revEquip') {
                    // continue;

                    $psitem->category = 'Револьверы';
                    $psitem->type = "swep";
                    $psitem->data = [
                        'type' => 'rev',
                        'skin' => $item->TypeData->skin ?? null,
                        'swep' => $item->TypeData->WeaponClass,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'modelEquip') {
                    $psitem->category = 'Модели';
                    $psitem->type = "model";
                    $psitem->data = [
                        'mdl' => $item->TypeData->Model,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'tauntPackEquip') {
                    $psitem->category = 'Таунты';
                    $psitem->type = "taunt";
                    $psitem->data = [
                        'taunt' => $item->TypeData->ParamEquip,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'dissolveEquip') {
                    $psitem->category = 'Эффекты смерти';
                    $psitem->type = "deatheffect";
                    $psitem->data = [
                        'dissolve' => $item->TypeData->TypeDissolve,
                    ];
                }
                if ($item->FunctionCompile->OnEquip == 'footstepEquip') {
                    $psitem->category = 'Эффекты ходьбы';
                    $psitem->type = "footstepeffect";
                    $psitem->data = [
                        'effect' => $item->TypeData->Footstep,
                    ];
                }
            }


            $psitem->name = $item->TypeData->Name;
            $psitem->price = 0;
            $psitem->is_hidden = false;
            $psitem->is_tradable = true;

            $psitem->save();
            $psitem->refresh();
            $psitem->servers()->detach();

			if ($item->ServerID == 'MURDER,DEATHRUN' || $item->ServerID == 'DEATHRUN,MURDER') {
				$psitem->servers()->attach($ocsid['MURDER']->id);
				$psitem->servers()->attach($ocsid['DEATHRUN']->id);
			}
			if ($item->ServerID == 'MURDER') {
				$psitem->servers()->attach($ocsid['MURDER']->id);
			}
			if ($item->ServerID == 'DEATHRUN') {
				$psitem->servers()->attach($ocsid['DEATHRUN']->id);
			}
			if ($item->ServerID == 'YANDERERP') {
				$psitem->servers()->attach($ocsid['YANDERERP']->id);
			}

            $oo[$item->ID] = $psitem->id;
        }


        DB::table('user_pointshops')->delete();


        $data = Storage::get('user_pointshop.json');
        $user_items = JsonMachine::fromString($data, '', new ExtJsonDecoder);

        foreach($user_items as $usit) {
            // dump($usit);
            if (empty($usit->steamid) || is_null($usit->steamid)) continue;
            if ($usit->steamid == 'STEAM_0:0:6.7553994410557E+15') continue;
            if ($usit->steamid == 'STEAM_0:1:6.7553994410557E+15') continue;
            if ($usit->steamid == 'STEAM_0:1.0000000000:67192409') continue;
            if ($usit->steamid == 'STEAM_0:0.0000000000:502071809') continue;

            if (strpos($usit->steamid, '.')) continue;
            if ($usit->id == 121) continue;
            if ($usit->id == 8679) continue;


            $shass = new \SteamID($usit->steamid);
            $shass = $shass->ConvertToUInt64();
            $user = User::whereSteamid($shass)->first();

            if ($user) {
                $usit->data = json_decode($usit->data);
                foreach($usit->data as $item_id => $items) {
                    if (isset($oo[$item_id])) {
                        $user->pointshopAddItem(
                            $ocsid[$usit->server_id]->id,
                            $oo[$item_id]
                        );
                    }
                }
            }
        }
    }
}
