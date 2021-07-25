<?php

namespace App\Models\User\Craft;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\User\Pointshop as UserPointshop;
use App\Models\User\Inventory as UserInventory;
use App\Models\Economy\Craft\CraftRecipe as EconomyCraftRecipe;
use App\Models\Economy\Craft\CraftItem as EconomyCraftItem;

class CraftRecipe extends Model
{
    protected $table = 'user_craft_recipes';    
    protected $fillable = [];
    
    protected $casts = [
        'data'          => 'array',
        'items'          => 'array',
    ];

    public function output ()
    {
        return $this->morphTo();
    }
    
    public function user ()
    {
        return $this->belongsTo(User::class);
    }
    public function craft_recipe ()
    {
        return $this->belongsTo(EconomyCraftRecipe::class);
    }

    public function items_morph($has_class = false)
    {
        $items = $this->items;
        $output = collect();
        
        $items_morphs = [];
        foreach($items as $key => $count)
        {
            $subs = explode(':', $key);
            if (isset($subs[1])) { // Это значит что какая-то сущность, надо в морф
                $items_morphs[$subs[0]] = $items_morphs[$subs[0]] ?? [];

                $items_morphs[$subs[0]][$subs[1]] = $count;

                $items[$key] = null;
            }
        }

        foreach($items_morphs as $class => $ids)
        {
            $class::whereIn('id', array_keys($ids))
                ->get()
                ->each(function($morph) use (&$output, $class, $items_morphs, $has_class) {
                    $toPush = [
                        'id' => $morph->id,
                        'count' => $items_morphs[$class][$morph->id],
                        'morph' => $morph,
                    ];
                    if ($has_class) {
                        $toPush['class'] = $class;
                    }
                    $output->push($toPush);
                });
        }


        EconomyCraftItem::whereIn('id', array_keys($items))
            ->get()
            ->each(function($morph) use (&$output, $items) {
                $output->push([
                    'id' => $morph->id,
                    'count' => $items[$morph->id],
                    'morph' => $morph,
                ]);
            });
        return $output;
    }
    // Объясняю почему я решил сделать не статик
    //
    // Потому что при связывании массива с функцией
    // статичного массива, то выдает грустную рожицу
    public function opposite () {
        return [
            'App\Models\Economy\Craft\CraftItem' => [
                'category' => 'TTS',
                'namespace' => 'App\Models\User\Craft\CraftItem',
                'label' => 'craft_item_id',
                'user_label' => 'user_id',

                'hasNeedServerId' => false,
                'onCrafted' => function($recipe, $user_id, $data = false) {
                    $item = new CraftItem();
                    $item->user_id = $user_id;
                    $item->craft_item_id = $recipe->output->id;
                    $item->save();
                    
                    return 'Предмет скрафчен, посмотри ремесленную сумку!';
                },
                'after_crafted' => function($recipe, $user_id, $data = false) {

                },
            ],
            'App\Models\Economy\TTS\TTSItem' => [
                'category' => 'TTS',
                'namespace' => 'App\Models\User\TTS\TTSItem',
                'label' => 'item_id',
                'where' => [
                    'is_activated' => true,
                ],
                'user_label' => 'user_id',

                'hasNeedServerId' => false,
                'onCrafted' => function($recipe, $user_id, $data = false) {

                },
                'after_crafted' => function($recipe, $user_id, $data = false) {

                },
            ],
            'App\Models\Economy\Pointshop\PointshopItem' => [
                'category' => 'PS',
                'namespace' => 'App\Models\User\Pointshop\PointshopItem',
                'label' => 'pointshop_item_id',
                'user_label' => function($builder, $user_id) {
                    return $builder->whereHas('pointshop', function($builder) use ($user_id) {
                        return $builder->where('user_id', $user_id);
                    });
                },
                'hasNeedServerId' => true,
                'onCrafted' => function($recipe, $user_id, $data = false) {
                    $item = UserPointshop::whereServerId($data['server_id'])
                        ->whereUserId($user_id)
                        ->first()
                        ->itemsWithInventory()
                        ->create([
                            'pointshop_item_id' => $recipe->output->id,
                            'deleted_at' => now(),
                        ]);
                    
                    $itemInv = new UserInventory();
                    $itemInv->server_id = $data['server_id'];
                    $itemInv->user_id = $user_id;
                    $itemInv->itemable()->associate($item);
                    $itemInv->save();
                    
                    return 'Предмет скрафчен, посмотри серверное хранилище!';
                },
                'after_crafted' => function($recipe, $user_id, $data = false) {

                    // $item_id = $recipe->output;


                    // $itemInv = new UserInventory();
                    // $itemInv->user_id = $user_id;
                    // $itemInv->user_id = $user_id;
                    // $inv = UserInventory::whereUserId($user_id)->first();
                    // if (!$inv) {
                    //     return false;
                    // }
                    // $inv->itemable()->create()
                }
            ],
        ];
    }
}
