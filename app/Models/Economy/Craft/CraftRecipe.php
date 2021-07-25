<?php

namespace App\Models\Economy\Craft;

use Illuminate\Database\Eloquent\Model;
use App\Models\Economy\Craft\CraftItem;

class CraftRecipe extends Model
{
    protected $table = 'craft_recipes';
    
    protected $fillable = [];

    protected $casts = [
        'data'              => 'array',
        'items'             => 'array',
        'is_reworkable'     => 'boolean',
        'is_open'           => 'boolean',
    ];

    public function output()
    {
        return $this->morphTo();
    }

    public function items_morph()
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


        CraftItem::whereIn('id', array_keys($items))
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
}
