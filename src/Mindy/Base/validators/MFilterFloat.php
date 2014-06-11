<?php

class MFilterFloat
{
    public static function commaReplace($data)
    {
        return str_replace(',', '.', $data);
    }
}