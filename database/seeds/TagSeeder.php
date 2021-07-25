<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Economy\Tag;

class TagSeeder extends Seeder
{

    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('core_tags.json');
        $data = json_decode($data);

        Tag::truncate();

        foreach($data as $tag) {
            if ($tag->id == 'admintag') continue;
            if ($tag->id == 'customtag') continue;

            $n = new Tag;
            $n->id = $tag->id;
            $n->is_primary_gradient = $tag->{'gradient-is'} == true;
            $n->primary_color_1 = json_decode($tag->{'color-1'}, true);
            $n->primary_color_2 = json_decode($tag->{'color-2'}, true);
            $n->primary_text = $tag->{'tags-text'};
            $n->is_secondary_gradient = false;
            $n->secondary_color_1 = json_decode($tag->{'additional-color'}, true);
            $n->secondary_color_2 = null;
            $n->secondary_text = $tag->{'additional-text'};
            $n->border_color_1 = json_decode($tag->{'border-color1'}, true);
            $n->border_color_2 = json_decode($tag->{'border-color2'}, true);
            $n->is_hidden = $tag->{'no-view'} == 1;
            $n->description = $tag->descr;
            $n->beautiful_text = $tag->{'tags-beauty-text'};
            $n->save();
        }
    }
}
