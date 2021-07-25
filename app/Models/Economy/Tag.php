<?php

namespace App\Models\Economy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class Tag extends Model
{
    private $type = 'html';
    private $compile = 'standart';
    protected $casts = [
        'id'                => 'string',
        'primary_color_1'   => 'array',
        'primary_color_2'   => 'array',
        'secondary_color_1' => 'array',
        'secondary_color_2' => 'array',
        'border_color_1'    => 'array',
        'border_color_2'    => 'array',
    ];

    public static function checksum()
    {
        $tableName = with(new static)->getTable();
        return md5(DB::select(DB::raw(sprintf('select MAX(updated_at) as checksum from %s', $tableName)))[0]->checksum);
    }

	public function users(){
        return $this->belongsToMany(User::class, 'users_tags');
    }

    /* Функционал по компиляции тегов */

    public function setCompile($type)
    {
        switch ($type) {
            case 'addensive':
                $this->compile = 'addensive';
                break;
            case 'standart':
            default:
                $this->compile = 'standart';
                break;
        }
        return $this;
    }
    public function setType($type)
    {
        switch ($type) {
            case 'gmod':
                $this->type = 'gmod';
                break;
            case 'html':
            default:
                $this->type = 'html';
                break;
        }
        return $this;
    }

    protected function fill_standart()
    {
        if (is_null($this->border_color_1))
            $this->border_color_1 =  ['r' => 255, 'g' => 255, 'b' => 255];
        if (is_null($this->border_color_2))
            $this->border_color_2 =  ['r' => 255, 'g' => 255, 'b' => 255];
    }
    protected function fill_addensive()
    {
        if (is_null($this->border_color_1))
            $this->border_color_1 = $this->primary_color_1;

        if (is_null($this->border_color_2))
            $this->border_color_2 =
                (
                    !is_null($this->secondary_color_2)
                        ? $this->secondary_color_2
                        : (
                            !is_null($this->secondary_color_1)
                                ? $this->secondary_color_1
                                : (
                                    !is_null($this->primary_color_2)
                                        ? $this->primary_color_2
                                        : $this->primary_color_1
                                )
                        )
                );
    }
    protected function fill_helper()
    {
        if ($this->is_primary_gradient)
            if (is_null($this->primary_color_2))
                $this->primary_color_2 = ['r' => 255, 'g' => 255, 'b' => 255];

        if ($this->is_secondary_gradient)
        {
            if (is_null($this->secondary_color_1))
                $this->secondary_color_1 = ['r' => 255, 'g' => 255, 'b' => 255];
            if (is_null($this->secondary_color_2))
                $this->secondary_color_2 = ['r' => 255, 'g' => 255, 'b' => 255];
        }
    }

    protected function char($char, $color = ['r' => 255, 'g' => 255, 'b' => 255])
    {
        if (!isset($color['a']) || is_null($color['a'])) $color['a'] = 255;

        if ($this->type == 'html')
            return $this->html_char($char, $color);
        elseif ($this->type == 'gmod')
            return $this->gmod_char($char, $color);
    }
    protected function gmod_char($char, $color)
    {
        return [$color, $char];
    }
    protected function html_char($char, $color)
    {
        return "<span style=\\'color: rgb({$color['r']},{$color['g']},{$color['b']})\\'>{$char}</span>";
    }
/*
    addensive нужен для генерации колоров для чата в браузере
    standart подходит в большей степени для гарриса и т.п.
*/
    public function generate()
    {
        $compile = [];
        if ($this->compile == 'standart')
            $this->fill_standart();
        elseif ($this->compile == 'addensive')
            $this->fill_addensive();

        $this->fill_helper();
        $compile[] = $this->char('[', $this->border_color_1);


        if ($this->is_primary_gradient)
        {
            $leng = mb_strlen($this->primary_text)-1;

            foreach(str_split_unicode($this->primary_text) as $ix => $sym) {
                $y = $this->primary_color_2;
                $z = $this->primary_color_1;

                $t_r = round($z['r']+((($y['r']-$z['r'])/($leng))*($ix)));
                $t_g = round($z['g']+((($y['g']-$z['g'])/($leng))*($ix)));
                $t_b = round($z['b']+((($y['b']-$z['b'])/($leng))*($ix)));


                $compile[] = $this->char($sym, ['r' => $t_r, 'g' => $t_g, 'b' => $t_b]);
            }
        }
        else
        {
            $compile[] = $this->char($this->primary_text, $this->primary_color_1);
        }
        if (!is_null($this->secondary_text))
        {
            if ($this->is_secondary_gradient)
            {
                $leng = mb_strlen($this->secondary_text)-1;

                foreach(str_split_unicode($this->secondary_text) as $ix => $sym) {
                    $y = $this->secondary_color_2;
                    $z = $this->secondary_color_1;

                    $t_r = round($z['r']+((($y['r']-$z['r'])/($leng))*($ix)));
                    $t_g = round($z['g']+((($y['g']-$z['g'])/($leng))*($ix)));
                    $t_b = round($z['b']+((($y['b']-$z['b'])/($leng))*($ix)));


                    $compile[] = $this->char($sym, ['r' => $t_r, 'g' => $t_g, 'b' => $t_b]);
                }
            }
            else
            {
                $compile[] = $this->char($this->secondary_text, $this->secondary_color_1);
            }
        }

        $compile[] = $this->char(']', $this->border_color_2);
        return $compile;
    }
}
