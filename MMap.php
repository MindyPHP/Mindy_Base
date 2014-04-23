<?php

/**
 * Created by Studio107.
 * Date: 10.04.13
 * Time: 16:58
 * All rights reserved.
 */
class MMap extends CMap
{
    public function toArrayClear()
    {
        return MMap::recursiveClear($this->toArray());
    }

    public static function recursiveClear($array)
    {
        $array = array_map(function ($value) {
            return is_array($value) ? MMap::recursiveClear(array_filter($value)) : $value;
        }, $array);
        return array_filter($array);
    }
}
