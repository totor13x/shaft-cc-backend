<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Economy\Craft\{ CraftItem, CraftRecipe };
use App\Models\User\Craft\{ CraftItem as UserCraftItem, CraftRecipe as UserCraftRecipe };

class CraftController extends Controller
{
    public function show (Request $request)
    {
        $user = $request->user();

        switch ($request->type) {
            case 'my_items':
                return UserCraftItem::with('craft_item')
                    ->whereUserId($user->id)
                    ->get()
                    ->mapToGroups(function($item, $key){
                        return [
                            $item->craft_item->name => $item->id
                        ];
                    });
                break;
            case 'my_recipes':
                return UserCraftRecipe::with('output')
                    ->whereUserId($user->id)
                    ->whereHas('craft_recipe', function($builder) {
                        $builder->whereIsOpen(false);
                    })
                    ->get()
                    ->each(function($recipe){
                        $items_morph = $recipe->items_morph();

                        $recipe->items_morph = $items_morph;
                    })
                    ->map(function($recipe) {
                        return [
                            'id' => $recipe->id,
                            'items' => $recipe->items_morph
                                ->map(function($item) {
                                    return [
                                        'name' => optional($item['morph'])->name,
                                        'count' => $item['count']
                                    ];
                                }),
                            'name' => $recipe->output->name,
                            'is_reworkable' => $recipe->craft_recipe->is_reworkable
                        ];
                    });
                break;
            case 'recipes':
                return CraftRecipe::with('output')
                    ->whereIsOpen(true)
                    ->get()
                    ->each(function($recipe){
                        $items_morph = $recipe->items_morph();

                        $recipe->items_morph = $items_morph;
                    })
                    ->map(function($recipe) {
                        return [
                            'id' => $recipe->id,
                            'items' => $recipe->items_morph
                                ->map(function($item) {
                                    return [
                                        'name' => optional($item['morph'])->name,
                                        'count' => $item['count']
                                    ];
                                }),
                            'name' => $recipe->output->name,
                            'is_reworkable' => $recipe->is_reworkable
                        ];
                    });
                break;
            default:
                return [];
                break;
        }
    }
    public function recipe_craft_show (Request $request)
    {
        $type = $request->get('type', 'my_recipes');
        $user = $request->user();
        if ($type == 'my_recipes') {
            $recipe = UserCraftRecipe::with('output')
                ->find($request->recipe_id);

            abort_if(
                $user->id != $recipe->user_id,
                422,
                'Нет доступа'
            );
        } elseif ($type == 'recipes') {
            $recipe = CraftRecipe::with('output')
                ->find($request->recipe_id);
        } else {
            abort(422, 'Нет рецепта');
        }
        return [
            'id' => $recipe->id,
            'items' => $recipe->items_morph(true)
                ->map(function($item) use ($user) {
                    $data = [
                        'name' => optional($item['morph'])->name,
                        'count' => $item['count']
                    ];
                    if (isset($item['class'])) {
                        $opposite = new UserCraftRecipe();
                        $opposite = $opposite->opposite();

                        $opClass = $opposite[$item['class']];
                        // $data['class'] = $opClass['namespace'];
                        $builder = $opClass['namespace']::where($opClass['label'], $item['id']);

                        if (isset($opClass['user_label'])) {
                            if (is_callable($opClass['user_label'])) {
                                $opClass['user_label']($builder, $user->id);
                            } else {
                                $builder->where($opClass['user_label'], $user->id);
                            }
                        }

                        if (isset($opClass['where'])) {
                            foreach($opClass['where'] as $where_key => $where_value)
                            {
                                $builder->where($where_key, $where_value);
                            }
                        }

                        $data['have'] = $builder->count();
                    } else {
                        $data['have'] = UserCraftItem::whereUserId($user->id)
                            ->where('craft_item_id', $item['id'])
                            ->count();
                    }

                    return $data;
                }),
            'name' => $recipe->output->name,
            'icon' => !is_null($recipe->output->icon)
                ? cdn_asset($recipe->output->icon)
                : null,
            'servers' => optional($recipe->output->servers)->pluck('id'),
        ];
    }

    public function recipe_craft_start (Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'my_recipes');

        if ($type == 'recipes') {
            $recipe = UserCraftRecipe::with('output')
                ->where('user_id', $user->id)
                ->where('craft_recipe_id', $request->recipe_id)
                ->first();

            if (!$recipe) {
                $economyrecipe = CraftRecipe::find($request->recipe_id);

                abort_if(
                    !$economyrecipe,
                    422,
                    'Произошла серверная ошибка'
                );

                abort_if(
                    !$economyrecipe->is_open,
                    422,
                    'Произошла ошибка в сборке рецепта'
                );

                $newrecipe = new UserCraftRecipe();
                $newrecipe->output()->associate($economyrecipe->output);
                $newrecipe->user_id = $user->id;
                $newrecipe->craft_recipe_id = $economyrecipe->id;
                $newrecipe->data = $economyrecipe->data;
                $newrecipe->items = $economyrecipe->items;
                $newrecipe->save();

                $request->recipe_id = $newrecipe->id;
            } else {
                $request->recipe_id = $recipe->id;
            }
        }

        $recipe = UserCraftRecipe::with('output')
            ->find($request->recipe_id);

        abort_if(
            $user->id != $recipe->user_id,
            422,
            'Нет доступа'
        );

        $items = $recipe->items_morph(true);

        $opposite = new UserCraftRecipe();
        $opposite = $opposite->opposite();
        $output = [];
        foreach($items as $item) {
            $count = $item['count'];

            abort_if(
                isset($item['class']),
                422,
                'Скрафтить невозможно'
            );

            $user_count = UserCraftItem::whereUserId($user->id)
                ->where('craft_item_id', $item['id'])
                ->count();

            $minus = $count - $user_count;
            if ($minus > 0) {
                array_push($output, 'Не хватает ' . $minus . ' шт'. plural(['уки','ук','ук'], $minus) .' ' .optional($item['morph'])->name);
            }
        }

        abort_if(
            !empty($output),
            422,
            "Недостаточно ингридиентов:\n " . implode(',', $output)
        );
        $serverId = $request->get('server_id', null);

        $namespace = get_class($recipe->output);

        $opClass = $opposite[$namespace];

        abort_if(
            $opClass['hasNeedServerId'] && !$serverId,
            422,
            'Не выбран сервер'
        );

        $getSomething = $opClass['onCrafted']($recipe, $user->id, [
            'server_id' => $serverId
        ]);

        foreach($items as $item) {
            $count = $item['count'];
            $user_count = UserCraftItem::whereUserId($user->id)
                ->where('craft_item_id', $item['id'])
                ->limit($count)
                ->orderBy('id', 'asc')
                ->delete();
        }

        return $getSomething;
    }
}
