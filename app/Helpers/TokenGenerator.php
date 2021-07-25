<?php
	function random_strings($length_of_string)
	{
        $str_result = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($str_result),  0, $length_of_string);
    }

    // Рекомендуется увеличить до 256 символов
	function generate_token() //АПИ токен максимум 60 символов
	{
		$id1 = random_strings(29);
		$id2 = random_strings(29);
		return $id1.str_shuffle("-_").$id2;
    }

    function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
?>
