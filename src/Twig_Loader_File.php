<?php

/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/04/14.04.2014 14:03
 */
class Twig_Loader_File extends Twig_Loader_Filesystem
{
    protected function findTemplate($name)
    {
        if(isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if(is_file($name)) {
            $this->cache[$name] = $name;
            return $name;
        }

        return parent::findTemplate($name);
    }
}
