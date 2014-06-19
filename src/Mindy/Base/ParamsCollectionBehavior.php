<?php

namespace Mindy\Base;

use Mindy\Helper\Alias;
use Mindy\Helper\Params;

class ParamsCollectionBehavior extends Behavior
{
    public $modulesDirs = [
        'Contrib',
        'Modules',
    ];

    public function attach($owner)
    {
        parent::attach($owner);

        $paths = [];
        foreach($this->modulesDirs as $alias) {
            $paths[] = Alias::get($alias);
        }
        Params::collect($paths);
    }
}
