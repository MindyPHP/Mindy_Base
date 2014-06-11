<?php

use Mindy\Helper\Params;

class ParamsCollectionBehavior extends CBehavior
{
    public $modulesDir = 'application.modules';

    public function attach($owner)
    {
        parent::attach($owner);
        Params::collect(Yii::getPathOfAlias($this->modulesDir));
    }
}
