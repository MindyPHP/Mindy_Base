<?php

class TextHelper
{
    public static function rightLetter($str)
    {
        return strtr($str, [
            "q" => "й", "w" => "ц", "e" => "у", "r" => "к",
            "t" => "е", "y" => "н", "u" => "г", "i" => "ш", "o" => "щ",
            "p" => "з", "[" => "х", "]" => "ъ", "a" => "ф", "s" => "ы",
            "d" => "в", "f" => "а", "g" => "п", "h" => "р", "j" => "о",
            "k" => "л", "l" => "д", ";" => "ж", "'" => "э", "z" => "я",
            "x" => "ч", "c" => "с", "v" => "м", "b" => "и", "n" => "т",
            "m" => "ь", "," => "б", "." => "ю"
        ]);
    }

    public static function limit($text, $length, $escape = true, $last = '...')
    {
        if ($escape) {
            $text = strip_tags($text);
        }
        if (mb_strlen($text, "UTF-8") > $length) {
            return mb_substr($text, 0, $length, "UTF-8") . $last;
        } else {
            return $text;
        }
    }

    public static function revertLimit($text, $length, $escape = true, $last = '...', $first = '')
    {
        if ($escape) {
            $text = strip_tags($text);
        }
        if (mb_strlen($text, "UTF-8") > $length) {
            return $first . mb_substr($text, mb_strlen($text, "UTF-8") - $length, $length, "UTF-8") . $last;
        } else {
            return $text;
        }
    }

    public static function limitword($text, $limit, $ends = '...')
    {
        if (mb_strlen($text, 'utf-8') > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);

            if (isset($pos[$limit])) {
                $text = mb_substr($text, 0, $pos[$limit], 'utf-8') . $ends;
            }
        }
        return $text;
    }

    public static function mbUcfirst($word)
    {
        return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_convert_case($word, MB_CASE_LOWER, 'UTF-8'), 1, mb_strlen($word), 'UTF-8');
    }
}
