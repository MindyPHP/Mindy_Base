<?php

use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

class NewApplicationComponent
{
    use Configurator, Accessors;

    private $_text = NULL;

    public function getText($text = NULL)
    {
        if (NULL === $text) {
            return $this->_text;
        }
        return $text;
    }

    public function setText($val)
    {
        $this->_text = $val;
    }
}
