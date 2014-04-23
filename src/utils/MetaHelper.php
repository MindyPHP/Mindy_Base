<?php

/**
 *
 * MetaHelper class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.utils
 * @since 1.1.1
 * @version 1.0
 *
 */
class MetaHelper
{
    public static $description_length = 30;
    public static $keywords_max_length = 5;
    public static $keywords_count = 10;

    public static function loadDict($lang = null)
    {
        $arrayWords = array();
        if (!$lang)
            $lang = Yii::app()->language;

        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'meta';
        if (file_exists($path . DIRECTORY_SEPARATOR . $lang . '.php'))
            $arrayWords = include($path . DIRECTORY_SEPARATOR . $lang . '.php');

        return $arrayWords;
    }

    /**
     * Return an array of arrays for punctuation values.
     *
     * Returns an array of arrays for punctuation values keyed by a name, including
     * the value and a textual description.
     * Can and should be expanded to include "all" non text punctuation values.
     *
     * @return
     *   An array of arrays for punctuation values keyed by a name, including the
     *   value and a textual description.
     *
     * Handle " ' ` , . - _ : ; | { [ } ] + = * & % ^ $ # @ ! ~ ( ) ? < > \
     */


    /**
     * Clean path separators from a given string.
     *
     * Trims duplicates and strips leading and trailing separators.
     *
     * @param $string
     *   The string to clean path separators from.
     * @param $separator
     *   The path separator to use when cleaning.
     * @return
     *   The cleaned version of the string.
     *
     * @see pathauto_cleanstring()
     * @see pathauto_clean_alias()
     */
    public static function cleanSeparators($string, $separator = NULL, $toLowCase = TRUE)
    {
        $output = $string;

        // Clean duplicate or trailing separators.
        if (isset($separator) && strlen($separator)) {
            // Escape the separator.
            $seppattern = preg_quote($separator, '/');

            // Trim any leading or trailing separators.
            $output = preg_replace("/^$seppattern+|$seppattern+$/", '', $output);

            // Replace trailing separators around slashes.
            $output = preg_replace("/$seppattern+\/|\/$seppattern+/", "/", $output);

            // Replace multiple separators with a single one.
            $output = preg_replace("/$seppattern+/", $separator, $output);
        }

        // Optionally convert to lower case.
        if ($toLowCase) {
            $output = strtolower($output);
        }

        return $output;
    }

    public static function cleanString($string, $separator = '-', $cleanPunctuation = TRUE, $cleanSlash = TRUE)
    {
        // Default words to ignore
        $ignoreWords = array(
            'a', 'an', 'as', 'at', 'before', 'but', 'by', 'for', 'from', 'is', 'in',
            'into', 'like', 'of', 'off', 'on', 'onto', 'per', 'since', 'than', 'the',
            'this', 'that', 'to', 'up', 'via', 'with',
        );

        $output = $string;
        if ($cleanPunctuation) {
            $punctuation = self::$punctuations;
            foreach ($punctuation as $name => $details) {
                // Slightly tricky inline if which either replaces with the separator or nothing
                //                $output = str_replace($details['value'], $separator, $output);
                $output = str_replace($details, $separator, $output);
            }
        }

        // If something is already urlsafe then don't remove slashes
        if ($cleanSlash) {
            $output = str_replace('/', '', $output);
        }

        // Optionally remove accents and transliterate
        $translations = self::$dictArray;
        $output = strtr($output, $translations);

        // Reduce to the subset of ASCII96 letters and numbers
        //$pattern = '/[^a-zA-Z0-9\/]+/ ';
        //$output = preg_replace($pattern, $separator, $output);

        // Get rid of words that are on the ignore list
        //m::d($ignoreWords);
        $ignoreWords = implode(',', $ignoreWords);
        $ignoreRe = '\b' . preg_replace('/,/', '\b|\b', $ignoreWords) . '\b';

        $output = preg_replace("/$ignoreRe/i", '', $output);

        // Always replace whitespace with the separator.
        $output = preg_replace('/\s+/', $separator, $output);

        // Trim duplicates and remove trailing and leading separators.
        $output = self::cleanSeparators($output, $separator);

        // Enforce the maximum component length
        //$maxlength = 128;
        //$output = substr($output, 0, $maxlength);

        return $output;
    }

    public static function generateKeywords($text)
    {
        /*
         * Разбиваем текст по словам
         */
        $wordsArray = self::keywordsExplodeStr($text);

        /*
         * Подсчитываем количество одинаковых слов
         */
        $resultArray = self::keywordsCount($wordsArray);

        $arr = array_slice($resultArray, 0, 30);
        $str = "";

        $i = 0;
        foreach ($arr as $key => $val) {
            $str .= $key . ", ";
            $i++;
            if ($i == self::$keywords_count)
                break;
        }

        return trim(substr($str, 0, strlen($str) - 2));
    }

    /*
    * Очищаем текст от мусора
    */
    protected static function keywordsExplodeStr($text)
    {
        $text = self::clearText($text);

        $text = preg_replace("( +)", " ", $text);

        return explode(" ", trim($text));
    }

    public static function clearText($text, $keywords = true)
    {
        $search = array(
            "'&\w+;'i", // Удаление тегов
            "/\s+/", // Удаление двойных пробелов и табуляций
            "/\d+/", // Удаление двойных пробелов и табуляций
        );

        $replace = array(
            " ",
            " ",
            "",
        );

        $search[] = ($keywords) ? "/[^A-ZА-Я0-9]+/ui" : "/[^A-ZА-Я0-9,.;!?]+/ui";
        $replace[] = ($keywords) ? " " : " ";

        return preg_replace($search, $replace, strip_tags($text));
    }

    protected static function keywordsCount($wordsArray)
    {
        $tmp_arr = array();

        $stopWords = self::loadDict();
        foreach ($wordsArray as $item) {
            if (mb_strlen($item, 'UTF-8') >= self::$keywords_max_length && (!in_array($item, $stopWords))) {
                $item = strtolower($item);
                if (array_key_exists($item, $wordsArray)) {
                    $tmp_arr[$item]++;
                } else {
                    $tmp_arr[$item] = 1;
                }
            }
        }

        arsort($tmp_arr);
        return $tmp_arr;
    }

    public static function generateDescription($text)
    {
        $clearText = self::clearText($text, false);
        return self::descriptionLimit($clearText);
    }

    protected static function descriptionLimit($text, $sep = ' ')
    {
        //Ограничиваем description по длине
        $counttext = self::$description_length;
        $words = explode(' ', $text);

        if (count($words) > $counttext) {
            $text = join($sep, array_slice($words, 0, $counttext));
        }

        return (mb_strlen($text, 'utf-8') > 200) ? mb_substr($text, 0, 200, 'utf-8') : $text;
    }

    private static $dictArray = array(
        "À" => "A",
        "Á" => "A",
        "Â" => "A",
        "Ã" => "A",
        "Ä" => "Ae",
        "Å" => "A",
        "Æ" => "A",
        "Ā" => "A",
        "Ą" => "A",
        "Ă" => "A",
        "Ç" => "C",
        "Ć" => "C",
        "Č" => "C",
        "Ĉ" => "C",
        "Ċ" => "C",
        "Ď" => "D",
        "Đ" => "D",
        "È" => "E",
        "É" => "E",
        "Ê" => "E",
        "Ë" => "E",
        "Ē" => "E",
        "Ę" => "E",
        "Ě" => "E",
        "Ĕ" => "E",
        "Ė" => "E",
        "Ĝ" => "G",
        "Ğ" => "G",
        "Ġ" => "G",
        "Ģ" => "G",
        "Ĥ" => "H",
        "Ħ" => "H",
        "Ì" => "I",
        "Í" => "I",
        "Î" => "I",
        "Ï" => "I",
        "Ī" => "I",
        "Ĩ" => "I",
        "Ĭ" => "I",
        "Į" => "I",
        "İ" => "I",
        "Ĳ" => "IJ",
        "Ĵ" => "J",
        "Ķ" => "K",
        "Ľ" => "K",
        "Ĺ" => "K",
        "Ļ" => "K",
        "Ŀ" => "K",
        "Ł" => "L",
        "Ñ" => "N",
        "Ń" => "N",
        "Ň" => "N",
        "Ņ" => "N",
        "Ŋ" => "N",
        "Ò" => "O",
        "Ó" => "O",
        "Ô" => "O",
        "Õ" => "O",
        "Ö" => "Oe",
        "Ø" => "O",
        "Ō" => "O",
        "Ő" => "O",
        "Ŏ" => "O",
        "Œ" => "OE",
        "Ŕ" => "R",
        "Ř" => "R",
        "Ŗ" => "R",
        "Ś" => "S",
        "Ş" => "S",
        "Ŝ" => "S",
        "Ș" => "S",
        "Š" => "S",
        "Ť" => "T",
        "Ţ" => "T",
        "Ŧ" => "T",
        "Ț" => "T",
        "Ù" => "U",
        "Ú" => "U",
        "Û" => "U",
        "Ü" => "Ue",
        "Ū" => "U",
        "Ů" => "U",
        "Ű" => "U",
        "Ŭ" => "U",
        "Ũ" => "U",
        "Ų" => "U",
        "Ŵ" => "W",
        "Ŷ" => "Y",
        "Ÿ" => "Y",
        "Ý" => "Y",
        "Ź" => "Z",
        "Ż" => "Z",
        "Ž" => "Z",
        "à" => "a",
        "á" => "a",
        "â" => "a",
        "ã" => "a",
        "ä" => "ae",
        "ā" => "a",
        "ą" => "a",
        "ă" => "a",
        "å" => "a",
        "æ" => "ae",
        "ç" => "c",
        "ć" => "c",
        "č" => "c",
        "ĉ" => "c",
        "ċ" => "c",
        "ď" => "d",
        "đ" => "d",
        "è" => "e",
        "é" => "e",
        "ê" => "e",
        "ë" => "e",
        "ē" => "e",
        "ę" => "e",
        "ě" => "e",
        "ĕ" => "e",
        "ė" => "e",
        "ƒ" => "f",
        "ĝ" => "g",
        "ğ" => "g",
        "ġ" => "g",
        "ģ" => "g",
        "ĥ" => "h",
        "ħ" => "h",
        "ì" => "i",
        "í" => "i",
        "î" => "i",
        "ï" => "i",
        "ī" => "i",
        "ĩ" => "i",
        "ĭ" => "i",
        "į" => "i",
        "ı" => "i",
        "ĳ" => "ij",
        "ĵ" => "j",
        "ķ" => "k",
        "ĸ" => "k",
        "ł" => "l",
        "ľ" => "l",
        "ĺ" => "l",
        "ļ" => "l",
        "ŀ" => "l",
        "ñ" => "n",
        "ń" => "n",
        "ň" => "n",
        "ņ" => "n",
        "ŉ" => "n",
        "ŋ" => "n",
        "ò" => "o",
        "ó" => "o",
        "ô" => "o",
        "õ" => "o",
        "ö" => "oe",
        "ø" => "o",
        "ō" => "o",
        "ő" => "o",
        "ŏ" => "o",
        "œ" => "oe",
        "ŕ" => "r",
        "ř" => "r",
        "ŗ" => "r",
        "ś" => "s",
        "š" => "s",
        "ş" => "s",
        "ť" => "t",
        "ţ" => "t",
        "ù" => "u",
        "ú" => "u",
        "û" => "u",
        "ü" => "ue",
        "ū" => "u",
        "ů" => "u",
        "ű" => "u",
        "ŭ" => "u",
        "ũ" => "u",
        "ų" => "u",
        "ŵ" => "w",
        "ÿ" => "y",
        "ý" => "y",
        "ŷ" => "y",
        "ż" => "z",
        "ź" => "z",
        "ž" => "z",
        "ß" => "ss",
        "ſ" => "ss",
        "Α" => "A",
        "Ά" => "A",
        "Ἀ" => "A",
        "Ἁ" => "A",
        "Ἂ" => "A",
        "Ἃ" => "A",
        "Ἄ" => "A",
        "Ἅ" => "A",
        "Ἆ" => "A",
        "Ἇ" => "A",
        "ᾈ" => "A",
        "ᾉ" => "A",
        "ᾊ" => "A",
        "ᾋ" => "A",
        "ᾌ" => "A",
        "ᾍ" => "A",
        "ᾎ" => "A",
        "ᾏ" => "A",
        "Ᾰ" => "A",
        "Ᾱ" => "A",
        "Ὰ" => "A",
        "Ά" => "A",
        "ᾼ" => "A",
        "Β" => "B",
        "Γ" => "G",
        "Δ" => "D",
        "Ε" => "E",
        "Έ" => "E",
        "Ἐ" => "E",
        "Ἑ" => "E",
        "Ἒ" => "E",
        "Ἓ" => "E",
        "Ἔ" => "E",
        "Ἕ" => "E",
        "Έ" => "E",
        "Ὲ" => "E",
        "Ζ" => "Z",
        "Η" => "I",
        "Ή" => "I",
        "Ἠ" => "I",
        "Ἡ" => "I",
        "Ἢ" => "I",
        "Ἣ" => "I",
        "Ἤ" => "I",
        "Ἥ" => "I",
        "Ἦ" => "I",
        "Ἧ" => "I",
        "ᾘ" => "I",
        "ᾙ" => "I",
        "ᾚ" => "I",
        "ᾛ" => "I",
        "ᾜ" => "I",
        "ᾝ" => "I",
        "ᾞ" => "I",
        "ᾟ" => "I",
        "Ὴ" => "I",
        "Ή" => "I",
        "ῌ" => "I",
        "Θ" => "TH",
        "Ι" => "I",
        "Ί" => "I",
        "Ϊ" => "I",
        "Ἰ" => "I",
        "Ἱ" => "I",
        "Ἲ" => "I",
        "Ἳ" => "I",
        "Ἴ" => "I",
        "Ἵ" => "I",
        "Ἶ" => "I",
        "Ἷ" => "I",
        "Ῐ" => "I",
        "Ῑ" => "I",
        "Ὶ" => "I",
        "Ί" => "I",
        "Κ" => "K",
        "Λ" => "L",
        "Μ" => "M",
        "Ν" => "N",
        "Ξ" => "KS",
        "Ο" => "O",
        "Ό" => "O",
        "Ὀ" => "O",
        "Ὁ" => "O",
        "Ὂ" => "O",
        "Ὃ" => "O",
        "Ὄ" => "O",
        "Ὅ" => "O",
        "Ὸ" => "O",
        "Ό" => "O",
        "Π" => "P",
        "Ρ" => "R",
        "Ῥ" => "R",
        "Σ" => "S",
        "Τ" => "T",
        "Υ" => "Y",
        "Ύ" => "Y",
        "Ϋ" => "Y",
        "Ὑ" => "Y",
        "Ὓ" => "Y",
        "Ὕ" => "Y",
        "Ὗ" => "Y",
        "Ῠ" => "Y",
        "Ῡ" => "Y",
        "Ὺ" => "Y",
        "Ύ" => "Y",
        "Φ" => "F",
        "Χ" => "X",
        "Ψ" => "PS",
        "Ω" => "O",
        "Ώ" => "O",
        "Ὠ" => "O",
        "Ὡ" => "O",
        "Ὢ" => "O",
        "Ὣ" => "O",
        "Ὤ" => "O",
        "Ὥ" => "O",
        "Ὦ" => "O",
        "Ὧ" => "O",
        "ᾨ" => "O",
        "ᾩ" => "O",
        "ᾪ" => "O",
        "ᾫ" => "O",
        "ᾬ" => "O",
        "ᾭ" => "O",
        "ᾮ" => "O",
        "ᾯ" => "O",
        "Ὼ" => "O",
        "Ώ" => "O",
        "ῼ" => "O",
        "α" => "a",
        "ά" => "a",
        "ἀ" => "a",
        "ἁ" => "a",
        "ἂ" => "a",
        "ἃ" => "a",
        "ἄ" => "a",
        "ἅ" => "a",
        "ἆ" => "a",
        "ἇ" => "a",
        "ᾀ" => "a",
        "ᾁ" => "a",
        "ᾂ" => "a",
        "ᾃ" => "a",
        "ᾄ" => "a",
        "ᾅ" => "a",
        "ᾆ" => "a",
        "ᾇ" => "a",
        "ὰ" => "a",
        "ά" => "a",
        "ᾰ" => "a",
        "ᾱ" => "a",
        "ᾲ" => "a",
        "ᾳ" => "a",
        "ᾴ" => "a",
        "ᾶ" => "a",
        "ᾷ" => "a",
        "β" => "b",
        "γ" => "g",
        "δ" => "d",
        "ε" => "e",
        "έ" => "e",
        "ἐ" => "e",
        "ἑ" => "e",
        "ἒ" => "e",
        "ἓ" => "e",
        "ἔ" => "e",
        "ἕ" => "e",
        "ὲ" => "e",
        "έ" => "e",
        "ζ" => "z",
        "η" => "i",
        "ή" => "i",
        "ἠ" => "i",
        "ἡ" => "i",
        "ἢ" => "i",
        "ἣ" => "i",
        "ἤ" => "i",
        "ἥ" => "i",
        "ἦ" => "i",
        "ἧ" => "i",
        "ᾐ" => "i",
        "ᾑ" => "i",
        "ᾒ" => "i",
        "ᾓ" => "i",
        "ᾔ" => "i",
        "ᾕ" => "i",
        "ᾖ" => "i",
        "ᾗ" => "i",
        "ὴ" => "i",
        "ή" => "i",
        "ῂ" => "i",
        "ῃ" => "i",
        "ῄ" => "i",
        "ῆ" => "i",
        "ῇ" => "i",
        "θ" => "th",
        "ι" => "i",
        "ί" => "i",
        "ϊ" => "i",
        "ΐ" => "i",
        "ἰ" => "i",
        "ἱ" => "i",
        "ἲ" => "i",
        "ἳ" => "i",
        "ἴ" => "i",
        "ἵ" => "i",
        "ἶ" => "i",
        "ἷ" => "i",
        "ὶ" => "i",
        "ί" => "i",
        "ῐ" => "i",
        "ῑ" => "i",
        "ῒ" => "i",
        "ΐ" => "i",
        "ῖ" => "i",
        "ῗ" => "i",
        "κ" => "k",
        "λ" => "l",
        "μ" => "m",
        "ν" => "n",
        "ξ" => "ks",
        "ο" => "o",
        "ό" => "o",
        "ὀ" => "o",
        "ὁ" => "o",
        "ὂ" => "o",
        "ὃ" => "o",
        "ὄ" => "o",
        "ὅ" => "o",
        "ὸ" => "o",
        "ό" => "o",
        "π" => "p",
        "ρ" => "r",
        "ῤ" => "r",
        "ῥ" => "r",
        "σ" => "s",
        "ς" => "s",
        "τ" => "t",
        "υ" => "y",
        "ύ" => "y",
        "ϋ" => "y",
        "ΰ" => "y",
        "ὐ" => "y",
        "ὑ" => "y",
        "ὒ" => "y",
        "ὓ" => "y",
        "ὔ" => "y",
        "ὕ" => "y",
        "ὖ" => "y",
        "ὗ" => "y",
        "ὺ" => "y",
        "ύ" => "y",
        "ῠ" => "y",
        "ῡ" => "y",
        "ῢ" => "y",
        "ΰ" => "y",
        "ῦ" => "y",
        "ῧ" => "y",
        "φ" => "f",
        "χ" => "x",
        "ψ" => "ps",
        "ω" => "o",
        "ώ" => "o",
        "ὠ" => "o",
        "ὡ" => "o",
        "ὢ" => "o",
        "ὣ" => "o",
        "ὤ" => "o",
        "ὥ" => "o",
        "ὦ" => "o",
        "ὧ" => "o",
        "ᾠ" => "o",
        "ᾡ" => "o",
        "ᾢ" => "o",
        "ᾣ" => "o",
        "ᾤ" => "o",
        "ᾥ" => "o",
        "ᾦ" => "o",
        "ᾧ" => "o",
        "ὼ" => "o",
        "ώ" => "o",
        "ῲ" => "o",
        "ῳ" => "o",
        "ῴ" => "o",
        "ῶ" => "o",
        "ῷ" => "o",
        "¨" => "",
        "΅" => "",
        "᾿" => "",
        "῾" => "",
        "῍" => "",
        "῝" => "",
        "῎" => "",
        "῞" => "",
        "῏" => "",
        "῟" => "",
        "῀" => "",
        "῁" => "",
        "΄" => "",
        "΅" => "",
        "`" => "",
        "῭" => "",
        "ͺ" => "",
        "᾽" => "",
        "А" => "A",
        "Б" => "B",
        "В" => "V",
        "Г" => "G",
        "Д" => "D",
        "Е" => "E",
        "Ё" => "E",
        "Ж" => "ZH",
        "З" => "Z",
        "И" => "I",
        "Й" => "I",
        "К" => "K",
        "Л" => "L",
        "М" => "M",
        "Н" => "N",
        "О" => "O",
        "П" => "P",
        "Р" => "R",
        "С" => "S",
        "Т" => "T",
        "У" => "U",
        "Ф" => "F",
        "Х" => "KH",
        "Ц" => "TS",
        "Ч" => "CH",
        "Ш" => "SH",
        "Щ" => "SHCH",
        "Ы" => "Y",
        "Э" => "E",
        "Ю" => "YU",
        "Я" => "YA",
        "а" => "A",
        "б" => "B",
        "в" => "V",
        "г" => "G",
        "д" => "D",
        "е" => "E",
        "ё" => "E",
        "ж" => "ZH",
        "з" => "Z",
        "и" => "I",
        "й" => "I",
        "к" => "K",
        "л" => "L",
        "м" => "M",
        "н" => "N",
        "о" => "O",
        "п" => "P",
        "р" => "R",
        "с" => "S",
        "т" => "T",
        "у" => "U",
        "ф" => "F",
        "х" => "KH",
        "ц" => "TS",
        "ч" => "CH",
        "ш" => "SH",
        "щ" => "SHCH",
        "ы" => "Y",
        "э" => "E",
        "ю" => "YU",
        "я" => "YA",
        "Ъ" => "",
        "ъ" => "",
        "Ь" => "",
        "ь" => "",
        "ð" => "d",
        "Ð" => "D",
        "þ" => "th",
        "Þ" => "TH"
    );

    private static $punctuations = array(
        "double_quotes" => '"',
        "quotes" => "'",
        "backtick" => "`",
        "comma" => ",",
        "period" => ".",
        "hyphen" => "-",
        "underscore" => "_",
        "colon" => ":",
        "semicolon" => ";",
        "pipe" => "|",
        "left_curly" => "{",
        "left_square" => "[",
        "right_curly" => "}",
        "right_square" => "]",
        "plus" => "+",
        "equal" => "=",
        "asterisk" => "*",
        "ampersand" => "&",
        "percent" => "%",
        "caret" => "^",
        "dollar" => "$",
        "hash" => "#",
        "at" => "@",
        "exclamation" => "!",
        "tilde" => "~",
        "left_parenthesis" => "(",
        "right_parenthesis" => ")",
        "question_mark" => "?",
        "less_than" => "<",
        "greater_than" => ">",
        "back_slash" => '\\',
        "number" => "№",
        "left_arrow" => "«",
        "right_arrow" => "»",
        "quote" => '"',
        "dot" => ".",
        "treedot" => "…",
        "quote1" => "”",
        "quote2" => "“",
        "tiredash" => "-—-",
        "tripletire" => "---"
    );
}
