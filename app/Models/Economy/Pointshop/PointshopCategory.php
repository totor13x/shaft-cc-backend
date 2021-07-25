<?php

namespace App\Models\Economy\Pointshop;

use Illuminate\Database\Eloquent\Model;

class PointshopCategory extends Model
{
    protected $table = 'pointshop_categories';
    
    protected $fillable = [
        'name',
        'max_items',
        'compile_string_holster',
        'compile_string_equip',
        'have_preview', // Превью для категории
        // удален, так как hoe будет внутри категории 'have_hoe' // Триггер, либо hoe либо max_items
    ];
}
